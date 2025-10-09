<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vendor extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toISOString(true);
    }
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function valid($data)
    {
        $rules = [
            'name' => 'required|min:2|max:100',
            'vendor_type_id' => 'required|numeric',
            'payment_days' => 'required|numeric',
        ];

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    static public function getUiid($id)
    {
        return 'V-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req)
    {
        $items = Vendor::query()
            ->where('is_active', boolval($req->is_active))
            ->get([
                'id',
                'is_active',
                'name',
                'vendor_type_id',
                'payment_days',
            ]);

        foreach ($items as $key => $item) {
            $item->key = $key;
            $item->vendor_type = VendorType::find($item->vendor_type_id, ['name']);
        }

        return $items;
    }

    static public function getItem($req, $id)
    {
        $item = Vendor::find($id);
        $item->uiid = Vendor::getUiid($item->id);
        $item->created_by = User::find($item->created_by_id, ['email']);
        $item->updated_by = User::find($item->updated_by_id, ['email']);
        $item->vendor_type = VendorType::find($item->vendor_type_id, ['name']);
        $item->vendor_banks = VendorBank::query()
            ->where('vendor_id', $item->id)
            ->where('is_active', true)
            ->get();

        foreach ($item->vendor_banks as $vendor_bank) {
            $vendor_bank->bank = Bank::find($vendor_bank->bank_id, ['name']);
        }

        return $item;
    }
}
