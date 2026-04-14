<?php

namespace App\Http\Controllers;

use App\Models\Financier;
use App\Models\PaymentMethod;
use App\Models\Vehicle;
use App\Models\VehicleReservation;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VehicleReservationController extends Controller
{
  public function index(Request $request)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => VehicleReservation::getItems($request)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function show(Request $request, int $id)
  {
    try {
      $item = VehicleReservation::getItem($id);

      if (!$item) {
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => $item]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function sellerStore(Request $request)
  {
    DB::beginTransaction();

    try {
      $user_id = (int) $request->user()->id;

      $valid = VehicleReservation::validStore($request->all());

      if ($valid->fails()) {
        DB::rollBack();
        return $this->apiRsp(422, $valid->errors()->first());
      }

      $vehicle_id = (int) $request->vehicle_id;
      $vehicle = Vehicle::find($vehicle_id);

      if (!$vehicle) {
        DB::rollBack();
        return $this->apiRsp(404, 'Auto no encontrado');
      }

      if (!boolval($vehicle->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'El auto no está disponible');
      }

      if (!boolval($vehicle->is_published)) {
        DB::rollBack();
        return $this->apiRsp(422, 'El auto no está publicado');
      }

      if ((float) $vehicle->sale_price <= 0) {
        DB::rollBack();
        return $this->apiRsp(422, 'El auto no tiene precio de venta válido');
      }

      if ((float) (int) $request->reservation_amount < 5000) {
        DB::rollBack();
        return $this->apiRsp(422, 'El monto de apartado mínimo es de $5,000.00');
      }

      if (VehicleReservation::hasBlockingReservation($vehicle_id, $user_id)) {
        DB::rollBack();
        return $this->apiRsp(422, 'El auto ya cuenta con una solicitud de apartado activa');
      }

      $current_reservation = VehicleReservation::getItemSeller($vehicle_id, $user_id);

      if ($current_reservation) {
        DB::rollBack();
        return $this->apiRsp(422, 'Ya cuentas con una solicitud de apartado activa para este auto');
      }

      $payment_method = PaymentMethod::find((int) $request->payment_method_id);

      if (!$payment_method || !boolval($payment_method->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'Método de pago no válido');
      }

      $is_finance = GenController::filter($request->is_finance, 'b');

      if ($is_finance) {
        $financier = Financier::find((int) $request->financier_id);

        if (!$financier || !boolval($financier->is_active)) {
          DB::rollBack();
          return $this->apiRsp(422, 'Financiera no válida');
        }
      }

      $item = new VehicleReservation();
      $item->is_active = 1;
      $item->created_by_id = $user_id;
      $item->updated_by_id = $user_id;
      $item->vehicle_id = $vehicle_id;
      $item->seller_user_id = $user_id;
      $item->is_approved = null;
      $item->response_at = null;
      $item->response_by_id = null;
      $item->response_note = null;
      $item->expires_at = null;

      $item = self::saveItem($item, $request);

      DB::commit();

      return $this->apiRsp(
        201,
        'Solicitud de apartado registrada correctamente',
        ['item' => ['id' => $item->id]]
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  public function sellerShow(Request $request, int $vehicle_id)
  {
    try {
      $seller_user_id = (int) $request->user()->id;

      $item = VehicleReservation::getItemSeller($vehicle_id, $seller_user_id);

      if (!$item) {
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      return $this->apiRsp(
        200,
        'Registro retornado correctamente',
        ['item' => VehicleReservation::getItem($item->id)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public static function saveItem($item, $request)
  {
    $item->customer_name = GenController::filter($request->customer_name, 'U');
    $item->customer_paternal_surname = GenController::filter($request->customer_paternal_surname, 'U');
    $item->customer_maternal_surname = GenController::filter($request->customer_maternal_surname, 'U');
    $item->customer_email = GenController::trim($request->customer_email);
    $item->customer_phone = GenController::trim($request->customer_phone);

    $item->customer_ine_path = DocMgrController::replaceOrDelete(
      $item->customer_ine_path,
      $request->file('customer_ine_doc'),
      'VehicleReservation'
    );

    $item->is_finance = GenController::filter($request->is_finance, 'b');
    $item->financier_id = $item->is_finance
      ? GenController::filter($request->financier_id, 'id')
      : null;
    $item->is_preapproved = $item->is_finance
      ? GenController::filter($request->is_preapproved, 'b')
      : null;

    $item->preapproval_path = $item->is_finance
      ? DocMgrController::replaceOrDelete(
        $item->preapproval_path,
        $request->file('preapproval_doc'),
        'VehicleReservation'
      )
      : null;

    $item->reservation_days = GenController::filter($request->reservation_days, 'i');
    $item->reservation_amount = GenController::filter($request->reservation_amount, 'd');
    $item->payment_method_id = GenController::filter($request->payment_method_id, 'id');

    $item->has_trade_in = GenController::filter($request->has_trade_in, 'b');
    $item->trade_in_brand = $item->has_trade_in ? GenController::trim($request->trade_in_brand) : null;
    $item->trade_in_model = $item->has_trade_in ? GenController::trim($request->trade_in_model) : null;
    $item->trade_in_version = $item->has_trade_in ? GenController::trim($request->trade_in_version) : null;
    $item->trade_in_model_year = $item->has_trade_in ? GenController::filter($request->trade_in_model_year, 'i') : null;
    $item->trade_in_color = $item->has_trade_in ? GenController::trim($request->trade_in_color) : null;
    $item->trade_in_km = $item->has_trade_in ? GenController::filter($request->trade_in_km, 'i') : null;
    $item->trade_in_invoice_type = $item->has_trade_in ? GenController::trim($request->trade_in_invoice_type) : null;
    $item->trade_in_is_refactored = $item->has_trade_in
      ? GenController::filter($request->trade_in_is_refactored, 'b')
      : null;

    $item->notes = GenController::trim($request->notes);

    $item->save();

    return $item;
  }

  public function response(Request $request, int $id)
  {
    DB::beginTransaction();

    try {
      $user_id = (int) $request->user()->id;

      $item = VehicleReservation::find($id);

      if (!$item) {
        DB::rollBack();
        return $this->apiRsp(404, 'Registro no encontrado');
      }

      if (!boolval($item->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La solicitud ya no se encuentra activa');
      }

      if (!is_null($item->is_approved)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La solicitud ya fue respondida');
      }

      $vehicle = Vehicle::find($item->vehicle_id);

      if (!$vehicle) {
        DB::rollBack();
        return $this->apiRsp(404, 'Auto no encontrado');
      }

      $is_approved = GenController::filter($request->is_approved, 'b');

      if (is_null($is_approved)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La respuesta es requerida');
      }

      $item->updated_by_id = $user_id;
      $item->is_approved = $is_approved;
      $item->response_at = now();
      $item->response_by_id = $user_id;
      $item->response_note = GenController::trim($request->response_note);

      if ($is_approved) {
        $expires_at = GenController::filter($request->expires_at, 'd');

        if (!$expires_at) {
          DB::rollBack();
          return $this->apiRsp(422, 'La fecha de vencimiento es requerida');
        }

        $final_sale_price = GenController::filter($request->final_sale_price, 'd');

        if (is_null($final_sale_price) || $final_sale_price <= 0) {
          DB::rollBack();
          return $this->apiRsp(422, 'El precio final de venta es requerido');
        }

        $sale_commission_amount = GenController::filter($request->sale_commission_amount, 'd');

        if (is_null($sale_commission_amount) || $sale_commission_amount < 0) {
          DB::rollBack();
          return $this->apiRsp(422, 'La comisión de venta es requerida');
        }

        $item->expires_at = $expires_at;

        $vehicle->updated_by_id = $user_id;
        $vehicle->final_sale_price = $final_sale_price;
        $vehicle->sale_commission_amount = $sale_commission_amount;
        $vehicle->save();
      } else {
        $item->is_active = 0;
        $item->expires_at = null;
      }

      $item->save();

      DB::commit();

      $item = VehicleReservation::getItem($item->id);

      $this->sendResponseEmails($item);

      return $this->apiRsp(
        200,
        'Solicitud ' . ($is_approved ? 'aprobada' : 'rechazada') . ' correctamente',
        ['item' => $item]
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  private function sendResponseEmails($item)
  {
    if (!$item) {
      return;
    }

    $this->sendResponseEmailToSeller($item);

    if ($item->is_approved) {
      $this->sendApprovedEmailToCustomer($item);
    }
  }

  private function sendResponseEmailToSeller($item)
  {
    if (!$item->seller_user?->email) {
      return;
    }

    EmailController::vehicleReservationResponseSeller($item->seller_user->email, $item);
  }

  private function sendApprovedEmailToCustomer($item)
  {
    if (!$item->customer_email) {
      return;
    }

    $item->customer_full_name = trim($item->customer_name . ' ' . $item->customer_paternal_surname . ' ' . $item->customer_maternal_surname);

    EmailController::vehicleReservationApprovedCustomer($item->customer_email, $item);
  }
}