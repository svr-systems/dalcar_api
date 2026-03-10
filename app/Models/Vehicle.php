<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vehicle extends Model
{
  protected function serializeDate(DateTimeInterface $date)
  {
    return Carbon::instance($date)->toISOString(true);
  }
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
    'is_published' => 'boolean',
  ];

  public static function valid($data, $id)
  {
    $rules = [
      'branch_id' => 'required|numeric',
      'vehicle_version_id' => 'required|numeric',
      'vehicle_transmission_id' => 'required|numeric',
      'vehicle_color_id' => 'required|numeric',
      'vin' => 'nullable|min:2|max:17|unique:vehicles,vin,' . $id,
      'engine_number' => 'nullable|min:2|max:30',
      'repuve' => 'nullable|min:2|max:25',
      'vehicle_key' => 'nullable|min:2|max:20',
      'passenger_capacity' => 'nullable|min:1|max:70',
      'notes' => 'nullable',
      'origin_type_id' => 'nullable|numeric',
      'pediment_number' => 'nullable|min:2|max:30',
      'pediment_date' => 'nullable|date',
      'custom_office_id' => 'nullable|numeric',
      'pediment_notes' => 'nullable',
    ];

    $msgs = ['vin.unique' => 'El VIN ya ha sido registrado'];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'A-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($request)
  {
    $items = self::query()
      ->join('purchase_order_vehicles', 'purchase_order_vehicles.vehicle_id', 'vehicles.id')
      ->join('purchase_orders', 'purchase_orders.id', 'purchase_order_vehicles.purchase_order_id')
      ->where('vehicles.is_active', 1)
      ->where('purchase_order_vehicles.is_active', 1)
      ->where('purchase_orders.is_active', 1)
      ->whereNotnull('purchase_orders.paid_at')
      ->orderByDesc('vehicles.id')
      ->get([
        'vehicles.id',
        'vehicles.vehicle_version_id',
        'vehicles.vehicle_color_id',
        'vehicles.vin',
        'vehicles.sale_price',
        'purchase_order_vehicles.purchase_price',
        'purchase_orders.order_date',
        'vehicles.is_published',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = self::getUiid($item->id);
      $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
      $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
      $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
      $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    }

    return $items;
  }

  static public function getItem($id)
  {
    $item = self::find($id);
    $item->created_by = User::find($item->created_by_id, ['email']);
    $item->updated_by = User::find($item->updated_by_id, ['email']);

    $item->uiid = Vehicle::getUiid($item->id);
    $item->branch = Branch::find($item->branch_id, ['name']);
    $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
    $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
    $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
    $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
    $item->vehicle_transmission = VehicleTransmission::find($item->vehicle_transmission_id, ['name']);
    $item->origin_type = OriginType::find($item->origin_type_id, ['name']);
    $item->custom_office = CustomOffice::find($item->custom_office_id, ['name']);

    $item->purchase_order_vehicle = PurchaseOrderVehicle::query()
      ->where('vehicle_id', $item->id)
      ->first([
        'id',
        'invoice_amount',
        'commission_amount',
        'purchase_price',
        'vat_type_id',
        'purchase_order_id',
      ]);

    $item->purchase_order_vehicle->vat_type = VatType::find($item->purchase_order_vehicle->vat_type_id, ['name']);
    $item->purchase_order_vehicle->purchase_order_uiid = PurchaseOrder::getUiid($item->purchase_order_vehicle->purchase_order_id);
    $item->purchase_order_vehicle->purchase_order = PurchaseOrder::find($item->purchase_order_vehicle->purchase_order_id, ['statement_path']);
    $item->purchase_order_vehicle->purchase_order->statement_b64 = DocMgrController::getB64($item->purchase_order_vehicle->purchase_order->statement_path, 'PurchaseOrder');

    $item->total = [
      'vehicle_investors' => VehicleInvestor::where('vehicle_id', $item->id)->where('is_active', 1)->count(),
      'vehicle_expenses' => VehicleExpense::where('vehicle_id', $item->id)->where('is_active', 1)->count(),
      'vehicle_invoices' => VehicleInvoice::where('vehicle_id', $item->id)->where('is_active', 1)->count(),
      'vehicle_documents' => VehicleDocument::where('vehicle_id', $item->id)->where('is_active', 1)->count(),
    ];

    return $item;
  }

  static public function getItemsSeller($seller_user_id)
  {
    $items = self::query()
      ->join('branches', 'branches.id', '=', 'vehicles.branch_id')
      ->join('vehicle_versions', 'vehicle_versions.id', '=', 'vehicles.vehicle_version_id')
      ->join('vehicle_models', 'vehicle_models.id', '=', 'vehicle_versions.vehicle_model_id')
      ->join('vehicle_brands', 'vehicle_brands.id', '=', 'vehicle_models.vehicle_brand_id')
      ->join('vehicle_colors', 'vehicle_colors.id', '=', 'vehicles.vehicle_color_id')
      ->join('vehicle_transmissions', 'vehicle_transmissions.id', '=', 'vehicles.vehicle_transmission_id')
      ->where('vehicles.is_active', 1)
      ->where('vehicles.is_published', 1)
      ->where('vehicles.sale_price', '>', 0)
      ->whereNotExists(function ($query) use ($seller_user_id) {
        $query
          ->select(\DB::raw(1))
          ->from('vehicle_reservations')
          ->whereColumn('vehicle_reservations.vehicle_id', 'vehicles.id')
          ->where('vehicle_reservations.is_active', 1)
          ->where(function ($query) {
            $query
              ->whereNull('vehicle_reservations.is_approved')
              ->orWhere('vehicle_reservations.is_approved', 1);
          })
          ->where('vehicle_reservations.seller_user_id', '!=', $seller_user_id);
      })
      ->orderByDesc('vehicles.id')
      ->get([
        'vehicles.id',
        'vehicles.vin',
        'vehicles.sale_price',
        'branches.name AS branch_name',
        'vehicle_versions.name AS vehicle_version_name',
        'vehicle_versions.model_year AS vehicle_version_model_year',
        'vehicle_models.name AS vehicle_model_name',
        'vehicle_brands.name AS vehicle_brand_name',
        'vehicle_colors.name AS vehicle_color_name',
        'vehicle_transmissions.name AS vehicle_transmission_name',
      ]);

    $reserved_vehicle_ids = VehicleReservation::query()
      ->where('seller_user_id', $seller_user_id)
      ->where('is_active', 1)
      ->where(function ($query) {
        $query
          ->whereNull('is_approved')
          ->orWhere('is_approved', 1);
      })
      ->pluck('vehicle_id')
      ->flip();

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = self::getUiid($item->id);
      $item->vin = !empty($item->vin) ? substr($item->vin, -4) : null;
      $item->has_seller_reservation = isset($reserved_vehicle_ids[$item->id]);
    }

    return $items;
  }

  static public function getItemSeller($id, $seller_user_id)
  {
    $item = self::query()
      ->join('branches', 'branches.id', '=', 'vehicles.branch_id')
      ->join('vehicle_versions', 'vehicle_versions.id', '=', 'vehicles.vehicle_version_id')
      ->join('vehicle_models', 'vehicle_models.id', '=', 'vehicle_versions.vehicle_model_id')
      ->join('vehicle_brands', 'vehicle_brands.id', '=', 'vehicle_models.vehicle_brand_id')
      ->join('vehicle_colors', 'vehicle_colors.id', '=', 'vehicles.vehicle_color_id')
      ->join('vehicle_transmissions', 'vehicle_transmissions.id', '=', 'vehicles.vehicle_transmission_id')
      ->where('vehicles.id', $id)
      ->where('vehicles.is_active', 1)
      ->where('vehicles.is_published', 1)
      ->where('vehicles.sale_price', '>', 0)
      ->whereNotExists(function ($query) use ($seller_user_id) {
        $query
          ->select(\DB::raw(1))
          ->from('vehicle_reservations')
          ->whereColumn('vehicle_reservations.vehicle_id', 'vehicles.id')
          ->where('vehicle_reservations.is_active', 1)
          ->where(function ($query) {
            $query
              ->whereNull('vehicle_reservations.is_approved')
              ->orWhere('vehicle_reservations.is_approved', 1);
          })
          ->where('vehicle_reservations.seller_user_id', '!=', $seller_user_id);
      })
      ->first([
        'vehicles.id',
        'vehicles.vin',
        'vehicles.engine_number',
        'vehicles.repuve',
        'vehicles.vehicle_key',
        'vehicles.passenger_capacity',
        'vehicles.notes',
        'vehicles.sale_price',
        'branches.name AS branch_name',
        'vehicle_versions.name AS vehicle_version_name',
        'vehicle_versions.model_year AS vehicle_version_model_year',
        'vehicle_models.name AS vehicle_model_name',
        'vehicle_brands.name AS vehicle_brand_name',
        'vehicle_colors.name AS vehicle_color_name',
        'vehicle_transmissions.name AS vehicle_transmission_name',
      ]);

    if ($item) {
      $item->uiid = self::getUiid($item->id);
      $item->vin = !empty($item->vin) ? substr($item->vin, -4) : null;

      $seller_reservation = VehicleReservation::getItemSeller($item->id, $seller_user_id);

      $item->has_seller_reservation = !is_null($seller_reservation);
      $item->seller_reservation_id = $seller_reservation?->id;
    }

    return $item;
  }
}
