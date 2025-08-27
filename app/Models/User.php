<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\GenController;
use App\Http\Controllers\DocMgrController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;
use DateTimeInterface;
use Validator;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;
    protected function serializeDate(DateTimeInterface $date) {
        return Carbon::instance($date)->toISOString(true);
    }
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
    ];

    public static function validEmail($data, $id) {
        $rules = ['email' => 'required|string|min:2|max:65|regex:/.+@.+\..+/|unique:users,email,' . $id];

        $msgs = ['email.unique' => 'El E-mail ya ha sido registrado'];

        return Validator::make($data, $rules, $msgs);
    }

    public static function valid($data, $is_req = true) {
        $rules = [
            'name' => 'required|min:2|max:50',
            'paternal_surname' => 'required|min:2|max:25',
            'maternal_surname' => 'nullable|min:2|max:25',
            'phone' => 'nullable|min:10|max:15',
            'role_id' => 'required|numeric',
        ];

        if (!$is_req) {
            array_push($rules, ['is_active' => 'required|in:true,false,1,0']);
        }

        $msgs = [];

        return Validator::make($data, $rules, $msgs);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    static public function getUiid($id) {
        return 'U-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    }

    static public function getItems($req) {
        $items = User::
            where('is_active', boolval($req->is_active));

        $items = $items->
            orderBy('name')->
            orderBy('paternal_surname')->
            orderBy('maternal_surname')->
            get([
                'id',
                'is_active',
                'name',
                'paternal_surname',
                'maternal_surname',
                'email',
                'role_id',
                'email_verified_at',
            ]);

        foreach ($items as $key => $item) {
            $item->uiid = User::getUiid($item->id);
            $item->key = $key;
            $item->full_name = GenController::getFullName($item);
            $item->role = Role::find($item->role_id, ['name']);
        }

        return $items;
    }

    static public function getItem($req, $id) {
        $item = User::
            find($id, [
                'id',
                'is_active',
                'created_at',
                'updated_at',
                'created_by_id',
                'updated_by_id',
                'email_verified_at',
                'name',
                'paternal_surname',
                'maternal_surname',
                'email',
                'role_id',
                'avatar_path',
            ]);

        if ($item) {
            $item->uiid = User::getUiid($item->id);
            $item->created_by = User::find($item->created_by_id, ['email']);
            $item->updated_by = User::find($item->updated_by_id, ['email']);
            $item->full_name = GenController::getFullName($item);
            $item->role = Role::find($item->role_id, ['name']);
            $item->branch = Branch::find($item->branch_id, ['name']);
            $item->avatar_b64 = DocMgrController::getB64($item->avatar, 'User');
            $item->avatar_doc = null;
            $item->avatar_dlt = false;
            $item->user_branches = UserBranche::where('user_id',$item->id)->
                                    where('is_active',true)->
                                    get();
        }

        return $item;
    }

  static public function getItemAuth($id) {
    $item = User::find($id, [
      'id',
      'name',
      'paternal_surname',
      'maternal_surname',
      'avatar_path',
      'email',
      'role_id',
    ]);

    $item->uiid = User::getUiid($item->id);
    $item->full_name = GenController::getFullName($item);
    // $item->avatar_b64 = DocMgrController::getB64($item->avatar, 'User');
    $item->role = Role::find($item->role_id, ['name']);

    return $item;
  }

    static public function getUserFile() {
        $items = User::
            where('is_active', true);

        $items = $items->
            get([
                'id',
                'name',
                'paternal_surname',
                'maternal_surname',
            ]);

        return $items;
    }
}