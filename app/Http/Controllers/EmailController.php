<?php

namespace App\Http\Controllers;
use App\Mail\GenMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Http\Request;

class EmailController extends Controller
{
  public static function userAccountConfirmation($email, $data)
  {
    $email = GenController::isAppDebug() ? env('MAIL_DEBUG') : env('MAIL_DEBUG');

    if (!GenController::empty($email)) {
      $data->link =
        (GenController::isAppDebug() ? env('SERVER_DEBUG') : env('SERVER')) .
        '/confirmar_cuenta/' .
        Crypt::encryptString($data->id);
      Mail::to($email)->send(new GenMailable($data, 'Confirmar cuenta', 'UserAccountConfirmation'));
    }
  }

  public static function orderPaymentStore($emails, $data): void
  {
    $emails = collect($emails)->filter()->unique()->values();

    if (GenController::isAppDebug()) {
      $emails = collect([env('MAIL_DEBUG')])->filter()->unique()->values();
    }

    if ($emails->isEmpty()) {
      return;
    }

    Mail::to($emails->all())->send(new GenMailable($data, 'Nueva orden de pago', 'OrderPaymentStore'));
  }

  public static function invoiceCalendarPending($emails, $data): void
  {
    $emails = collect($emails)->filter()->unique()->values();

    if ($emails->isEmpty()) {
      return;
    }

    if (GenController::isAppDebug()) {
      $emails = collect([env('MAIL_DEBUG')])->filter()->unique()->values();

      if ($emails->isEmpty()) {
        return;
      }
    }

    Mail::to($emails->all())->send(
      new GenMailable($data, 'Facturas pendientes por cargar', 'VehicleInvoicesCalendarPending')
    );
  }

  public static function documentCalendarPending($emails, $data): void
  {
    $emails = collect($emails)->filter()->unique()->values();

    if ($emails->isEmpty()) {
      return;
    }

    if (GenController::isAppDebug()) {
      $emails = collect([env('MAIL_DEBUG')])->filter()->unique()->values();

      if ($emails->isEmpty()) {
        return;
      }
    }

    Mail::to($emails->all())->send(
      new GenMailable($data, 'Documentos pendientes por cargar', 'VehicleDocumentsCalendarPending')
    );
  }
}
