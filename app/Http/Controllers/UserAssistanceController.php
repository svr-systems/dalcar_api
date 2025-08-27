<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserAssistance;
use Throwable;
use Storage;
use File;
use DB;

class UserAssistanceController extends Controller {
    public function index(Request $req) {
        try {
            return $this->apiRsp(
                200,
                'Registros retornados correctamente',
                ['items' => UserAssistance::getItems($req)]
            );
        } catch (Throwable $err) {
            return $this->apiRsp(500, null, $err);
        }
    }

    public function show(Request $req, $id) {
        try {
            return $this->apiRsp(
                200,
                'Registro retornado correctamente',
                ['item' => UserAssistance::getItem($req, $id)]
            );
        } catch (Throwable $err) {
            return $this->apiRsp(500, null, $err);
        }
    }

    public function destroy(Request $req, $id) {
        DB::beginTransaction();
        try {
            $item = UserAssistance::find($id);

            if (!$item) {
                return $this->apiRsp(422, 'ID no existente');
            }

            $item->active = false;
            $item->updated_by_id = $req->user()->id;
            $item->save();

            DB::commit();
            return $this->apiRsp(
                200,
                'Registro inactivado correctamente'
            );
        } catch (Throwable $err) {
            DB::rollback();
            return $this->apiRsp(500, null, $err);
        }

    }

    public function store(Request $req) {
        return $this->storeUpdate($req, null);
    }

    public function update(Request $req, $id) {
        return $this->storeUpdate($req, $id);
    }

    public function storeUpdate($req, $id) {
        DB::beginTransaction();
        try {

            $store_mode = is_null($id);

            if ($store_mode) {
                $item = new UserAssistance;
                $item->created_by_id = $req->user()->id;
                $item->updated_by_id = $req->user()->id;
            } else {
                $item = UserAssistance::find($id);
                $item->updated_by_id = $req->user()->id;
            }

            $item = $this->saveItem($item, $req);

            DB::commit();
            return $this->apiRsp(
                $store_mode ? 201 : 200,
                'Registro ' . ($store_mode ? 'agregado' : 'editado') . ' correctamente',
                $store_mode ? ['item' => ['id' => $item->id]] : null
            );
        } catch (Throwable $err) {
            DB::rollback();
            return $this->apiRsp(500, null, $err);
        }
    }

    public static function saveItem($item, $data, $is_req = true) {
        if (!$is_req) {
            $item->active = GenController::filter($data->active, 'b');
        }

        $item->registered_at = $data->registered_at;
        $item->user_id = $data->user_id;
        $item->user_assistance_files_id = $data->user_assistance_files_id;
        $item->save();

        return $item;
    }
}
