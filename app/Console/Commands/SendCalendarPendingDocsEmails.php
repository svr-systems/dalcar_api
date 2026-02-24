<?php

namespace App\Console\Commands;

use App\Http\Controllers\EmailController;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendCalendarPendingDocsEmails extends Command
{
  protected $signature = 'dalcar:calendar-pendings';
  protected $description = 'Envía correos por facturas/documentos calendarizados pendientes (sin archivo)';

  public function handle(): int
  {
    $today = Carbon::today(config('app.timezone'));

    $invoice_emails = User::query()
      ->where('is_active', 1)
      ->where('receives_invoice_calendar_emails', 1)
      ->pluck('email')
      ->filter()
      ->unique()
      ->values();

    $document_emails = User::query()
      ->where('is_active', 1)
      ->where('receives_document_calendar_emails', 1)
      ->pluck('email')
      ->filter()
      ->unique()
      ->values();

    $pending_invoices = VehicleInvoice::query()
      ->join('vehicles', 'vehicles.id', '=', 'vehicle_invoices.vehicle_id')
      ->join('invoice_types', 'invoice_types.id', '=', 'vehicle_invoices.invoice_type_id')
      ->where('vehicle_invoices.is_active', 1)
      ->whereDate('vehicle_invoices.scheduled_date', '<=', $today)
      ->where(function ($q) {
        $q->whereNull('vehicle_invoices.document_path')
          ->orWhere('vehicle_invoices.document_path', '');
      })
      ->orderBy('vehicle_invoices.scheduled_date', 'asc')
      ->get([
        'vehicle_invoices.id',
        'vehicle_invoices.vehicle_id',
        'vehicle_invoices.invoice_type_id',
        'vehicle_invoices.scheduled_date',
        'invoice_types.name AS invoice_type_name',
      ])
      ->each(function ($item) use ($today) {
        $item->vehicle_uiid = Vehicle::getUiid($item->vehicle_id);
        $item->scheduled_date_fmt = Carbon::parse($item->scheduled_date)->toDateString();

        $days = Carbon::parse($item->scheduled_date)->startOfDay()->diffInDays($today, false);
        $item->overdue_days = $days < 0 ? 0 : $days;
      });

    $pending_documents = VehicleDocument::query()
      ->join('vehicles', 'vehicles.id', '=', 'vehicle_documents.vehicle_id')
      ->join('document_types', 'document_types.id', '=', 'vehicle_documents.document_type_id')
      ->where('vehicle_documents.is_active', 1)
      ->whereDate('vehicle_documents.scheduled_date', '<=', $today)
      ->where(function ($q) {
        $q->whereNull('vehicle_documents.document_path')
          ->orWhere('vehicle_documents.document_path', '');
      })
      ->orderBy('vehicle_documents.scheduled_date', 'asc')
      ->get([
        'vehicle_documents.id',
        'vehicle_documents.vehicle_id',
        'vehicle_documents.document_type_id',
        'vehicle_documents.scheduled_date',
        'document_types.name AS document_type_name',
      ])
      ->each(function ($item) use ($today) {
        $item->vehicle_uiid = Vehicle::getUiid($item->vehicle_id);
        $item->scheduled_date_fmt = Carbon::parse($item->scheduled_date)->toDateString();

        $days = Carbon::parse($item->scheduled_date)->startOfDay()->diffInDays($today, false);
        $item->overdue_days = $days < 0 ? 0 : $days;
      });

    $invoice_sent = false;
    $document_sent = false;

    if ($invoice_emails->isNotEmpty() && $pending_invoices->isNotEmpty()) {
      $data = (object) [
        'date' => $today->toDateString(),
        'items' => $pending_invoices,
      ];

      EmailController::invoiceCalendarPending($invoice_emails, $data);
      $invoice_sent = true;
    }

    if ($document_emails->isNotEmpty() && $pending_documents->isNotEmpty()) {
      $data = (object) [
        'date' => $today->toDateString(),
        'items' => $pending_documents,
      ];

      EmailController::documentCalendarPending($document_emails, $data);
      $document_sent = true;
    }

    $message = implode(' | ', [
      'dalcar:calendar-pendings',
      'at=' . now()->toDateTimeString(),
      'debug=' . (config('app.debug') ? '1' : '0'),
      'invoices=' . $pending_invoices->count(),
      'invoice_emails=' . $invoice_emails->count(),
      'invoice_sent=' . ($invoice_sent ? 'yes' : 'no'),
      'documents=' . $pending_documents->count(),
      'document_emails=' . $document_emails->count(),
      'document_sent=' . ($document_sent ? 'yes' : 'no'),
    ]);

    Log::channel('cron')->info($message);

    return Command::SUCCESS;
  }
}