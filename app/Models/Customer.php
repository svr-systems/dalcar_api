<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Validator;

class Customer extends Model
{
  protected $casts = [
    'created_at' => 'datetime:Y-m-d H:i:s',
    'updated_at' => 'datetime:Y-m-d H:i:s',
    'is_active' => 'boolean',
  ];

  public static function valid($data, $id = null)
  {
    $rules = [
      'name' => 'required|min:2|max:191',
      'paternal_surname' => 'required|min:2|max:25',
      'maternal_surname' => 'nullable|min:2|max:25',
      'email' => 'nullable|email|max:191',
      'phone' => 'nullable|min:10|max:15',
      'ine_path' => 'nullable|max:50',
      'rfc' => 'nullable|min:12|max:13',
      'notes' => 'nullable',
      'user_id' => 'nullable|numeric',
    ];

    $msgs = [];

    return Validator::make($data, $rules, $msgs);
  }

  public static function getItem($id)
  {
    $item = self::query()->find($id);

    if (!$item) {
      return null;
    }

    $item->created_by = User::query()->find($item->created_by_id, ['id', 'email']);
    $item->updated_by = User::query()->find($item->updated_by_id, ['id', 'email']);
    $item->user = User::query()->find($item->user_id, ['id', 'email']);

    $item->full_name = GenController::getFullName($item);

    return $item;
  }

  public static function findMatch($data)
  {
    $email = GenController::trim($data['email'] ?? null);
    $phone = GenController::trim($data['phone'] ?? null);
    $name = GenController::filter($data['name'] ?? null, 'U');
    $paternal_surname = GenController::filter($data['paternal_surname'] ?? null, 'U');
    $maternal_surname = GenController::filter($data['maternal_surname'] ?? null, 'U');

    if (!GenController::empty($email)) {
      $item = self::query()
        ->where('is_active', 1)
        ->where('email', $email)
        ->first();

      if ($item) {
        return $item;
      }
    }

    if (
      !GenController::empty($name) &&
      !GenController::empty($paternal_surname) &&
      !GenController::empty($phone)
    ) {
      $query = self::query()
        ->where('is_active', 1)
        ->where('name', $name)
        ->where('paternal_surname', $paternal_surname)
        ->where('phone', $phone);

      if (!GenController::empty($maternal_surname)) {
        $query->where('maternal_surname', $maternal_surname);
      }

      $item = $query->first();

      if ($item) {
        return $item;
      }
    }

    return null;
  }
}