<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class Branch extends Model {
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
            'company_id' => 'required|numeric',
            'zip' => 'required|numeric',
        ];

        if (!$is_req) {
            array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
        }

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    static public function getUiid($id) {
        return 'S-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req) {
        $items = Branch::
            where('company_id', $req->company_id)->
            where('is_active', boolval($req->is_active));

        $items = $items->
            get([
                'id',
                'is_active',
                'name',
                'company_id',
                'zip',
            ]);

        foreach ($items as $key => $item) {
            $item->key = $key;
            $item->company = Company::find($item->company_id, ['name']);
            $item->uiid = Branch::getUiid($item->id);
        }

        return $items;
    }

    static public function getItem($req, $id) {
        $item = Branch::
            where('company_id', $req->company_id)->
            find($id, [
                'id',
                'is_active',
                'created_at',
                'updated_at',
                'created_by_id',
                'updated_by_id',
                'company_id',
                'name',
                'street',
                'exterior_number',
                'interior_number',
                'neighborhood',
                'zip',
                'municipality_id',
                'email',
                'phone',
                'company_id',
            ]);

        if ($item) {
            $item->created_by = User::find($item->created_by_id, ['email']);
            $item->updated_by = User::find($item->updated_by_id, ['email']);
            $item->company = Company::find($item->company_id, ['name']);
            $item->municipality = Municipality::find($item->municipality_id, ['name','state_id']);
            $item->state = State::find($item->municipality->state_id, ['name',]);
            $item->uiid = Branch::getUiid($item->id);
        }

        return $item;
    }
}
