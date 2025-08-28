<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Vendor extends Model
{
    protected function serializeDate(DateTimeInterface $date) {
        return Carbon::instance($date)->toISOString(true);
    }
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function valid($data, $is_req = true) {
        $rules = [
            'name' => 'required|min:2|max:100',
            'vendor_type_id' => 'required|numeric',
            'payment_days' => 'required|numeric',
        ];

        if (!$is_req) {
            array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
        }

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    static public function getUiid($id) {
        return 'V-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req) {
        $items = Vendor::
            where('is_active', boolval($req->is_active));

        if ($req->user()->id !== 1) {
            $items = $items->
                where('created_by_id', $req->user()->id);
        }

        $items = $items->
            get([
                'id',
                'is_active',
                'name',
                'vendor_type_id',
                'payment_days',
            ]);

        foreach ($items as $key => $item) {
            $item->key = $key;
            $item->uiid = Vendor::getUiid($item->id);
            $item->vendor_type = VendorType::find($item->vendor_type_id);
        }

        return $items;
    }

    static public function getItem($req, $id) {
        $item = Vendor::
            find($id, [
                'id',
                'is_active',
                'created_at',
                'updated_at',
                'created_by_id',
                'updated_by_id',
                'name',
                'vendor_type_id',
                'payment_days',
            ]);

        if ($item) {
            $item->uiid = Vendor::getUiid($item->id);
            $item->created_by = User::find($item->created_by_id, ['email']);
            $item->updated_by = User::find($item->updated_by_id, ['email']);
            $item->vendor_type = VendorType::find($item->vendor_type_id);
            $item->vendor_banks = VendorBank::where('vendor_id',$item->id)->where('is_active',true)->get();
        }

        return $item;
    }
}
