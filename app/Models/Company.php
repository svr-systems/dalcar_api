<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\DocMgrController;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class Company extends Model {
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
            'logo' => 'nullable|min:2|max:50',
        ];

        if (!$is_req) {
            array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
        }

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    static public function getUiid($id) {
        return 'E-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req) {
        $items = Company::
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
            ]);

        foreach ($items as $key => $item) {
            $item->key = $key;
            $item->uiid = Company::getUiid($item->id);
        }

        return $items;
    }

    static public function getItem($req, $id) {
        $item = Company::
            find($id, [
                'id',
                'is_active',
                'created_at',
                'updated_at',
                'created_by_id',
                'updated_by_id',
                'name',
                'logo_path',
            ]);

        if ($item) {
            $item->uiid = Company::getUiid($item->id);
            $item->created_by = User::find($item->created_by_id, ['email']);
            $item->updated_by = User::find($item->updated_by_id, ['email']);
            $item->logo_b64 = DocMgrController::getB64($item->logo_path, 'Company');
            $item->logo_doc = null;
            $item->logo_dlt = false;
        }

        return $item;
    }
}
