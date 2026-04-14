<?php

namespace App\Http\Controllers;

use App\Mail\GenMailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
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

      Mail::to($email)->send(
        new GenMailable($data, 'Confirmar cuenta', 'UserAccountConfirmation')
      );
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

    Mail::to($emails->all())->send(
      new GenMailable($data, 'Nueva orden de pago', 'OrderPaymentStore')
    );
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

  public static function vehiclesInventoryReleased($emails, $data): void
  {
    $emails = collect($emails)->filter()->unique()->values();

    if ($emails->isEmpty()) {
      return;
    }

    $subject = 'Vehículos agregados a inventario';
    $view = 'VehiclesInventoryReleased';

    if (GenController::isAppDebug()) {
      $debug_email = env('MAIL_DEBUG');

      if (GenController::empty($debug_email)) {
        return;
      }

      Mail::to([$debug_email])->send(new GenMailable($data, $subject, $view));
      return;
    }

    Mail::to($emails->all())->send(new GenMailable($data, $subject, $view));
  }

  public static function vehicleReservationResponseSeller($email, $data): void
  {
    if (GenController::isAppDebug()) {
      $email = env('MAIL_DEBUG');
    }

    if (GenController::empty($email)) {
      return;
    }

    $subject =
      'Apartado ' .
      ($data->is_approved ? 'aprobado' : 'rechazado') .
      ' | ' .
      ($data->vehicle?->uiid ?? 'AUTO');

    Mail::to($email)->send(
      new GenMailable($data, $subject, 'VehicleReservationResponseSeller')
    );
  }

  public static function vehicleReservationApprovedCustomer($email, $data): void
  {
    if (GenController::isAppDebug()) {
      $email = env('MAIL_DEBUG');
    }

    if (GenController::empty($email)) {
      return;
    }

    $subject = 'Tu apartado fue aprobado | ' . ($data->vehicle?->uiid ?? 'AUTO');

    Mail::to($email)->send(
      new GenMailable($data, $subject, 'VehicleReservationApprovedCustomer')
    );
  }

  public static function vehicleSalePaymentStoreCustomer($email, $data): void
  {
    if (GenController::isAppDebug()) {
      $email = env('MAIL_DEBUG');
    }

    if (GenController::empty($email)) {
      return;
    }

    $pdf = Pdf::loadView('pdf.VehicleSalePaymentReceipt', [
      'data' => $data,
    ]);

    $pdf_content = $pdf->output();

    $attachments = [
      [
        'data' => $pdf_content,
        'name' => 'recibo_pago_apartado_' . ($data->vehicle?->uiid ?? 'AUTO') . '.pdf',
        'mime' => 'application/pdf',
      ]
    ];

    $subject = 'Pago de apartado recibido | ' . ($data->vehicle?->uiid ?? 'AUTO');

    Mail::to($email)->send(
      new GenMailable($data, $subject, 'VehicleSalePaymentStoreCustomer', $attachments)
    );
  }

  public static function vehicleSalePaymentStoreSeller($email, $data): void
  {
    if (GenController::isAppDebug()) {
      $email = env('MAIL_DEBUG');
    }

    if (GenController::empty($email)) {
      return;
    }

    $subject = 'Pago de apartado registrado | ' . ($data->vehicle?->uiid ?? 'AUTO');

    Mail::to($email)->send(
      new GenMailable($data, $subject, 'VehicleSalePaymentStoreSeller')
    );
  }
}