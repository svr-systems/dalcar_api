<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderVehicle;
use App\Models\User;
use App\Models\Vehicle;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class PurchaseOrderReceiptController extends Controller
{
  public function index(Request $request, $purchase_order_id)
  {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        PurchaseOrderReceipt::getItems($request, $purchase_order_id),
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function storeUpdate(Request $request)
  {
    DB::beginTransaction();

    try {
      $user_id = $request->user()->id;
      $purchase_order_id = (int) $request->purchase_order_id;

      $purchase_order = PurchaseOrder::query()->find($purchase_order_id);
      if (is_null($purchase_order)) {
        return $this->apiRsp(404, 'Orden de pago no encontrada');
      }

      $was_paid = !is_null($purchase_order->paid_at);

      $purchase_order_receipts = json_decode((string) $request->purchase_order_receipts);
      if (!is_array($purchase_order_receipts)) {
        return $this->apiRsp(422, 'purchase_order_receipts inválido');
      }

      foreach ($purchase_order_receipts as $key => $purchase_order_receipt) {
        $is_active = GenController::filter($purchase_order_receipt->is_active, 'b');
        $file_name = 'purchase_order_receipts_file_doc_' . $key;

        $purchase_order_receipt_item = PurchaseOrderReceipt::query()
          ->where('id', $purchase_order_receipt->id)
          ->where('purchase_order_id', $purchase_order_id)
          ->first();

        if (is_null($purchase_order_receipt_item)) {
          $purchase_order_receipt_item = new PurchaseOrderReceipt();
          $purchase_order_receipt_item->purchase_order_id = $purchase_order_id;
        }

        $purchase_order_receipt_item->is_active = $is_active;

        if ($is_active) {
          $purchase_order_receipt_item->note = GenController::trim($purchase_order_receipt->note);

          $file_doc = $request->file($file_name);

          if (!is_null($file_doc)) {
            $purchase_order_receipt_item->file_path = DocMgrController::replaceOrDelete(
              $purchase_order_receipt_item->file_path,
              $file_doc,
              'PurchaseOrderReceipt'
            );
          }
        }

        $purchase_order_receipt_item->save();
      }

      $has_active = PurchaseOrderReceipt::query()
        ->where('purchase_order_id', $purchase_order_id)
        ->where('is_active', 1)
        ->exists();

      $purchase_order->updated_by_id = $user_id;
      $purchase_order->paid_by_id = $has_active ? $user_id : null;
      $purchase_order->paid_at = $has_active ? Carbon::now() : null;
      $purchase_order->save();

      $is_now_paid = !is_null($purchase_order->paid_at);

      if (!$was_paid && $is_now_paid) {
        $emails = User::query()
          ->where('is_active', 1)
          ->where('receives_vehicle_emails', 1)
          ->pluck('email')
          ->filter()
          ->unique()
          ->values();

        if ($emails->isNotEmpty()) {
          $vehicles = PurchaseOrderVehicle::query()
            ->join('vehicles', 'vehicles.id', '=', 'purchase_order_vehicles.vehicle_id')
            ->join('vehicle_versions', 'vehicle_versions.id', '=', 'vehicles.vehicle_version_id')
            ->join('vehicle_models', 'vehicle_models.id', '=', 'vehicle_versions.vehicle_model_id')
            ->join('vehicle_brands', 'vehicle_brands.id', '=', 'vehicle_models.vehicle_brand_id')
            ->join('vehicle_colors', 'vehicle_colors.id', '=', 'vehicles.vehicle_color_id')
            ->where('purchase_order_vehicles.purchase_order_id', $purchase_order_id)
            ->where('purchase_order_vehicles.is_active', 1)
            ->orderBy('vehicles.id', 'asc')
            ->get([
              'vehicles.id AS vehicle_id',
              'vehicle_brands.name AS vehicle_brand_name',
              'vehicle_models.name AS vehicle_model_name',
              'vehicle_versions.name AS vehicle_version_name',
              'vehicle_versions.model_year AS vehicle_version_model_year',
              'vehicle_colors.name AS vehicle_color_name',
            ])
            ->unique('vehicle_id')
            ->values()
            ->each(function ($item) {
              $item->uiid = Vehicle::getUiid((int) $item->vehicle_id);
            });

          if ($vehicles->isNotEmpty()) {
            $data = (object) [
              'vehicles' => $vehicles,
            ];

            EmailController::vehiclesInventoryReleased($emails, $data);
          }
        }
      }

      DB::commit();

      return $this->apiRsp(
        200,
        'Comprobantes actualizados correctamente',
        ['item' => ['id' => $purchase_order->id]]
      );
    } catch (Throwable $err) {
      DB::rollBack();
      return $this->apiRsp(500, null, $err);
    }
  }
}
