<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\SalePaymentType;
use App\Models\VehicleReservation;
use App\Models\VehicleSale;
use App\Models\VehicleSalePayment;
use DB;
use Illuminate\Http\Request;
use Throwable;

class VehicleSaleController extends Controller
{
  public function getReservationItems(Request $request)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => VehicleReservation::getItemsToSale($request)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function getReservationItem(Request $request, int $vehicle_reservation_id)
  {
    try {
      $item = VehicleReservation::getItemToSale($vehicle_reservation_id);

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

  public function store(Request $request)
  {
    DB::beginTransaction();

    try {
      $user_id = (int) $request->user()->id;

      $vehicle_reservation = VehicleReservation::find((int) $request->vehicle_reservation_id);
      if (!$vehicle_reservation) {
        DB::rollBack();
        return $this->apiRsp(404, 'Reservación no encontrada');
      }

      if (!boolval($vehicle_reservation->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La reservación no se encuentra activa');
      }

      if (!boolval($vehicle_reservation->is_approved)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La reservación no se encuentra aprobada');
      }

      if (!is_null($vehicle_reservation->paid_at)) {
        DB::rollBack();
        return $this->apiRsp(422, 'La reservación ya fue pagada');
      }

      $payment_method = PaymentMethod::find((int) $request->payment_method_id);
      if (!$payment_method || !boolval($payment_method->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'Método de pago no válido');
      }

      $sale_payment_type = SalePaymentType::find(1);
      if (!$sale_payment_type || !boolval($sale_payment_type->is_active)) {
        DB::rollBack();
        return $this->apiRsp(422, 'Tipo de pago no válido');
      }

      $amount = GenController::filter($request->amount, 'd');
      if (is_null($amount) || $amount <= 0) {
        DB::rollBack();
        return $this->apiRsp(422, 'El monto es requerido');
      }

      if ((int) $payment_method->id !== 1 && !$request->file('voucher_doc')) {
        DB::rollBack();
        return $this->apiRsp(422, 'El comprobante de pago es requerido');
      }

      $customer_data = [
        'name' => $request->name,
        'paternal_surname' => $request->paternal_surname,
        'maternal_surname' => $request->maternal_surname,
        'email' => $request->email,
        'phone' => $request->phone,
        'rfc' => $request->rfc,
        'notes' => $request->customer_notes,
      ];

      $valid_customer = Customer::valid($customer_data);
      if ($valid_customer->fails()) {
        DB::rollBack();
        return $this->apiRsp(422, $valid_customer->errors()->first());
      }

      $vehicle_sale_data = [
        'vehicle_id' => $vehicle_reservation->vehicle_id,
        'customer_id' => 1,
        'vehicle_reservation_id' => $vehicle_reservation->id,
        'seller_user_id' => $vehicle_reservation->seller_user_id,
        'is_finance' => $vehicle_reservation->is_finance,
        'financier_id' => $vehicle_reservation->financier_id,
        'notes' => $request->notes,
      ];

      $valid_vehicle_sale = VehicleSale::validStore($vehicle_sale_data);
      if ($valid_vehicle_sale->fails()) {
        DB::rollBack();
        return $this->apiRsp(422, $valid_vehicle_sale->errors()->first());
      }

      $vehicle_sale_payment_data = [
        'vehicle_sale_id' => 1,
        'sale_payment_type_id' => 1,
        'payment_method_id' => $payment_method->id,
        'amount' => $amount,
        'notes' => $request->notes,
      ];

      $valid_vehicle_sale_payment = VehicleSalePayment::validStore($vehicle_sale_payment_data);
      if ($valid_vehicle_sale_payment->fails()) {
        DB::rollBack();
        return $this->apiRsp(422, $valid_vehicle_sale_payment->errors()->first());
      }

      $customer = new Customer();
      $customer->is_active = 1;
      $customer->created_by_id = $user_id;
      $customer->updated_by_id = $user_id;
      $customer = $this->saveCustomer($customer, $request);

      $vehicle_sale = new VehicleSale();
      $vehicle_sale->is_active = 1;
      $vehicle_sale->created_by_id = $user_id;
      $vehicle_sale->updated_by_id = $user_id;
      $vehicle_sale->vehicle_id = $vehicle_reservation->vehicle_id;
      $vehicle_sale->customer_id = $customer->id;
      $vehicle_sale->vehicle_reservation_id = $vehicle_reservation->id;
      $vehicle_sale->seller_user_id = $vehicle_reservation->seller_user_id;
      $vehicle_sale->is_finance = $vehicle_reservation->is_finance;
      $vehicle_sale->financier_id = $vehicle_reservation->financier_id;
      $vehicle_sale->notes = GenController::trim($request->notes);
      $vehicle_sale->save();

      $vehicle_sale_payment = new VehicleSalePayment();
      $vehicle_sale_payment->is_active = 1;
      $vehicle_sale_payment->created_by_id = $user_id;
      $vehicle_sale_payment->updated_by_id = $user_id;
      $vehicle_sale_payment->vehicle_sale_id = $vehicle_sale->id;
      $vehicle_sale_payment->sale_payment_type_id = 1;
      $vehicle_sale_payment->payment_method_id = (int) $payment_method->id;
      $vehicle_sale_payment->amount = $amount;
      $vehicle_sale_payment->voucher_path = DocMgrController::replaceOrDelete(
        $vehicle_sale_payment->voucher_path,
        $request->file('voucher_doc'),
        'VehicleSalePayment'
      );
      $vehicle_sale_payment->notes = GenController::trim($request->notes);
      $vehicle_sale_payment->save();

      $vehicle_reservation->updated_by_id = $user_id;
      $vehicle_reservation->paid_at = now();
      $vehicle_reservation->paid_by_id = $user_id;
      $vehicle_reservation->payment_method_id = (int) $payment_method->id;
      $vehicle_reservation->save();

      // DB::commit();

      $item = VehicleSale::getItem($vehicle_sale->id);

      $this->sendStoreEmails($item);

      return $this->apiRsp(
        201,
        'Pago de reservación registrado correctamente',
        ['item' => $item]
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }

  private function saveCustomer($item, $request)
  {
    $item->name = GenController::filter($request->name, 'U');
    $item->paternal_surname = GenController::filter($request->paternal_surname, 'U');
    $item->maternal_surname = GenController::filter($request->maternal_surname, 'U');
    $item->email = GenController::trim($request->email);
    $item->phone = GenController::trim($request->phone);
    $item->ine_path = DocMgrController::replaceOrDelete(
      $item->ine_path,
      $request->file('ine_doc'),
      'Customer'
    );
    $item->rfc = GenController::trim($request->rfc);
    $item->notes = GenController::trim($request->customer_notes);
    $item->save();

    return $item;
  }

  private function sendStoreEmails($item)
  {
    if (!$item) {
      return;
    }

    $this->sendStoreEmailToCustomer($item);
    $this->sendStoreEmailToSeller($item);
  }

  private function sendStoreEmailToCustomer($item)
  {
    if (!$item->customer?->email) {
      return;
    }

    EmailController::vehicleSalePaymentStoreCustomer($item->customer->email, $item);
  }

  private function sendStoreEmailToSeller($item)
  {
    if (!$item->seller_user?->email) {
      return;
    }

    EmailController::vehicleSalePaymentStoreSeller($item->seller_user->email, $item);
  }
}