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
    $item->uses_payment_link = (bool) $item->uses_payment_link;
    $item->requires_reference = (bool) $item->requires_reference;
    $item->requires_statement = (bool) $item->requires_statement;
    $item->vendor_type = VendorType::find($item->vendor_type_id, ['name']);

    $item->vendor_banks = VendorBank::where('vendor_id', $item->id)->where('is_active', 1)->get();
    foreach ($item->vendor_banks as $vendor_bank) {
      $vendor_bank->bank = Bank::find($vendor_bank->bank_id, ['name']);
      $vendor_bank->is_commission = (bool) $vendor_bank->is_commission;
    }

    $item->vendor_invoice_types = VendorInvoiceType::where('vendor_id', $item->id)->where('is_active', 1)->get();
    foreach ($item->vendor_invoice_types as $vendor_invoice_type) {
      $vendor_invoice_type->invoice_type = InvoiceType::find($vendor_invoice_type->invoice_type_id, ['name']);
    }

    $item->vendor_document_types = VendorDocumentType::where('vendor_id', $item->id)->where('is_active', 1)->get();
    foreach ($item->vendor_document_types as $vendor_document_type) {
      $vendor_document_type->document_type = DocumentType::find($vendor_document_type->document_type_id, ['name']);
    }

    return $item;
  }

  public static function getItemToPurchaseOrder($req): array
  {
    $vendor_id = (int) $req->id;

    $vendor = Vendor::query()->find($vendor_id, [
      'id',
      'payment_days',
      'uses_payment_link',
      'requires_reference',
      'requires_statement',
    ]);

    if (!$vendor) {
      return [
        'error' => 'Proveedor no encontrado.',
        'vendor' => null,
        'purchase_order_payments' => [],
        'due_date' => null,
      ];
    }

    $due_date = Carbon::createFromFormat('Y-m-d', (string) $req->order_date)
      ->addWeekdays((int) $vendor->payment_days)
      ->toDateString();

    $vendor_banks = VendorBank::query()
      ->where('vendor_id', $vendor_id)
      ->where('is_active', 1)
      ->get([
        'bank_id',
        'account_holder',
        'clabe_number',
        'account_number',
        'cie_code',
        'is_commission',
      ]);

    $for_commission = $vendor_banks->where('is_commission', 1)->count();
    $for_payment = $vendor_banks->where('is_commission', 0)->count();

    if ($for_payment <= 0) {
      return [
        'error' => 'El proveedor no tiene cuentas bancarias de pago activas.',
        'vendor' => $vendor,
        'purchase_order_payments' => [],
        'due_date' => $due_date,
      ];
    }

    $bank_ids = $vendor_banks->pluck('bank_id')->unique()->values();

    $banks = Bank::query()
      ->whereIn('id', $bank_ids)
      ->get(['id', 'name'])
      ->keyBy('id');

    $subtotal_amount = (float) $req->subtotal_amount;
    $commission_amount = (float) $req->commission_amount;
    $warranty_amount = (float) $req->warranty_amount;

    $net_amount = $subtotal_amount - $warranty_amount + ($for_commission === 0 ? $commission_amount : 0.0);

    $purchase_order_payments = [];

    foreach ($vendor_banks as $vendor_bank) {
      $is_commission = (bool) $vendor_bank->is_commission;

      $amount = $is_commission
        ? ($for_commission > 0 ? ($commission_amount / $for_commission) : 0.0)
        : ($net_amount / $for_payment);

      $purchase_order_payments[] = [
        'id' => null,
        'is_active' => 1,
        'bank_id' => (int) $vendor_bank->bank_id,
        'account_holder' => $vendor_bank->account_holder,
        'clabe_number' => $vendor_bank->clabe_number,
        'account_number' => $vendor_bank->account_number,
        'cie_code' => $vendor_bank->cie_code,
        'amount' => $amount,
        'bank' => $banks->get($vendor_bank->bank_id),
        'is_commission' => $is_commission,
      ];
    }

    return [
      'error' => null,
      'vendor' => $vendor,
      'purchase_order_payments' => $purchase_order_payments,
      'due_date' => $due_date,
    ];
  }
}
