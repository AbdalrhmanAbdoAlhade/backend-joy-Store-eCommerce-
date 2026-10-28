<?php
// app/Jobs/GenerateInvoicePdf.php
namespace App\Jobs;

use App\Models\Order;
use App\Mail\InvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf; // يحتاج barryvdh/laravel-dompdf

class GenerateInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        // توليد رقم فاتورة متسلسل لو مش موجود
        if (!$this->order->invoice_number) {
            $lastInvoice = Order::whereNotNull('invoice_number')
                ->orderByDesc('id')
                ->value('invoice_number');

            $nextNumber = $lastInvoice
                ? ((int) str_replace('INV-', '', $lastInvoice)) + 1
                : 1001;

            $this->order->update([
                'invoice_number' => 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT),
            ]);
        }

        $pdf = Pdf::loadView('pdf.invoice', ['order' => $this->order->load('items', 'user')]);

        $path = 'invoices/' . $this->order->invoice_number . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // إرسال بالبريد
        if ($this->order->user?->email) {
            Mail::to($this->order->user->email)
                ->send(new InvoiceMail($this->order, $path));
        }
    }
}
