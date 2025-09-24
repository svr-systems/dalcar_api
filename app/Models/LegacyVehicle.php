<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class LegacyVehicle extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toISOString(true);
    }
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function valid($data, $is_req = true)
    {
        $rules = [
            'branch_id' => 'required|numeric',
            'vendor_id' => 'required|numeric',
            'purchase_date' => 'required|date',
            'vehicle_version_id' => 'required|numeric',
            'vehicle_transmission_id' => 'required|numeric',
            'vehicle_color_id' => 'required|numeric',
            'vin' => 'required|min:2|max:17',
            'engine_number' => 'nullable|min:2|max:30',
            'repuve' => 'nullable|min:2|max:25',
            'vehicle_key' => 'nullable|min:2|max:20',
            'purchase_price' => 'required|numeric',
            'commission_amount' => 'required|numeric',
            'vat_type_id' => 'required|numeric',
            'invoice_amount' => 'required|numeric',
            'notes' => 'nullable',
            'origin_type_id' => 'required|numeric',
            'pediment_number' => 'nullable|min:2|max:30',
            'pediment_date' => 'nullable|date',
            'custom_office_id' => 'nullable|numeric',
            'pediment_notes' => 'nullable',
        ];

        if (!$is_req) {
            array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
        }

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    static public function getUiid($id)
    {
        return 'AH-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req)
    {
        $items = LegacyVehicle::query()
            ->where('is_active', boolval($req->is_active))
            ->orderByDesc('purchase_date')
            ->get([
                'id',
                'is_active',
                'purchase_date',
                'vehicle_version_id',
                'vehicle_color_id',
                'vin',
                'purchase_price',
                'invoice_amount',
            ]);

        foreach ($items as $key => $item) {
            $item->key = $key;
            $item->uiid = LegacyVehicle::getUiid($item->id);

            $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
            $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
            $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
            $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
        }

        return $items;
    }

    static public function getItem($req, $id)
    {
        $item = LegacyVehicle::find($id, [
            'id',
            'is_active',
            'created_at',
            'updated_at',
            'created_by_id',
            'updated_by_id',
            'branch_id',
            'purchase_date',
            'vendor_id',
            'vehicle_version_id',
            'vehicle_transmission_id',
            'vehicle_color_id',
            'vin',
            'engine_number',
            'repuve',
            'vehicle_key',
            'purchase_price',
            'commission_amount',
            'vat_type_id',
            'invoice_amount',
            'notes',
            'origin_type_id',
            'pediment_number',
            'pediment_date',
            'custom_office_id',
            'pediment_notes',
        ]);

        $item->uiid = LegacyVehicle::getUiid($item->id);
        $item->created_by = User::find($item->created_by_id, ['email']);
        $item->updated_by = User::find($item->updated_by_id, ['email']);
        $item->branch = Branch::find($item->branch_id, ['name']);

        $item->vendor = Vendor::find($item->vendor_id, ['name']);
        $item->vehicle_version = VehicleVersion::find($item->vehicle_version_id, ['name', 'vehicle_model_id', 'model_year']);
        $item->vehicle_version->vehicle_model = VehicleModel::find($item->vehicle_version->vehicle_model_id, ['name', 'vehicle_brand_id']);
        $item->vehicle_version->vehicle_model->vehicle_brand = VehicleBrand::find($item->vehicle_version->vehicle_model->vehicle_brand_id, ['name']);
        $item->vehicle_transmission = VehicleTransmission::find($item->vehicle_transmission_id, ['name']);
        $item->vehicle_color = VehicleColor::find($item->vehicle_color_id, ['name']);
        $item->vat_type = VatType::find($item->vat_type_id, ['name']);
        $item->origin_type = OriginType::find($item->origin_type_id, ['name']);
        $item->custom_office = CustomOffice::find($item->custom_office_id, ['name']);

        $item->legacy_vehicle_investors = LegacyVehicleInvestor::query()
            ->where('legacy_vehicle_id', $item->id)
            ->where('is_active', 1)
            ->get([
                'id',
                'is_active',
                'investor_id',
                'percentages',
                'amount',
            ]);

        foreach ($item->legacy_vehicle_investors as $legacy_vehicle_investor) {
            $legacy_vehicle_investor->investor = Investor::find($legacy_vehicle_investor->investor_id, ['user_id']);
            $legacy_vehicle_investor->investor->user = User::find($legacy_vehicle_investor->investor->user_id, ['name', 'paternal_surname', 'maternal_surname']);
            $legacy_vehicle_investor->investor->user->full_name = GenController::getFullName($legacy_vehicle_investor->investor->user);
        }

        $item->legacy_vehicle_expenses = LegacyVehicleExpense::query()
            ->where('legacy_vehicle_id', $item->id)
            ->where('is_active', 1)
            ->get([
                'id',
                'is_active',
                'expense_type_id',
                'note',
                'expense_date',
                'amount',
            ]);

        foreach ($item->legacy_vehicle_expenses as $legacy_vehicle_expense) {
            $legacy_vehicle_expense->expense_type = ExpenseType::find($legacy_vehicle_expense->expense_type_id, ['name']);
        }

        return $item;
    }
}
