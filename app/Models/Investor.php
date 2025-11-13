<?php

namespace App\Models;

use App\Http\Controllers\DocMgrController;
use App\Http\Controllers\GenController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Investor extends Model
{
  use HasFactory;
  public $timestamps = false;

  public static function valid($data, $is_req = true)
  {
    $rules = [
      'user_id' => 'required|numeric',
      'investor_type_id' => 'required|numeric',
    ];

    if (!$is_req) {
      array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
    }

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  static public function getUiid($id)
  {
    return 'I-' . str_pad($id, 4, '0', STR_PAD_LEFT);
  }

  static public function getItems($req)
  {
    $items = Investor::
      join('users', 'investors.user_id', 'users.id')->
      where('is_active', boolval($req->is_active));

    $items = $items->
      get([
        'investors.id',
        'users.is_active',
        'user_id',
        'investor_type_id',
      ]);

    foreach ($items as $key => $item) {
      $item->key = $key;
      $item->uiid = Investor::getUiid($item->id);
      $item->user = User::find($item->user_id, ['name', 'paternal_surname', 'maternal_surname']);
      $item->investor_type = InvestorType::find($item->investor_type_id);
      $item->full_name = GenController::getFullName($item->user);
    }

    return $items;
  }

  static public function getItem($req, $id)
  {
    $item = Investor::
      find($id, [
        'id',
        'user_id',
        'investor_type_id',
      ]);

    if ($item) {
      $item->uiid = Investor::getUiid($item->id);
      $item->created_by = User::find($item->created_by_id, ['email']);
      $item->updated_by = User::find($item->updated_by_id, ['email']);
      $item->user = User::find($item->user_id);
      $item->investor_type = InvestorType::find($item->investor_type_id);
      $item->investor_companies = InvestorCompany::where('investor_id', $item->id)->where('is_active', true)->get();

      foreach ($item->investor_companies as $investor_company) {
        $investor_company->company = Company::find($investor_company->company_id, ['name']);
      }
    }

    return $item;
  }
}
