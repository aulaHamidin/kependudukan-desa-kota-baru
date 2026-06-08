<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\SuratTerbit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi yang dikirim ke pembuat surat ketika job PDF generation
 * gagal secara permanen (setelah seluruh percobaan retry habis).
 */
class SuratPdfFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly SuratTerbit $surat,
        private readonly string $errorMessage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[SIAK-Desa] Gagal: PDF Surat ' . $this->surat->nomor_surat)
            ->greeting('Halo, ' . $notifiable->name)
            ->line('PDF untuk surat berikut **gagal di-generate** setelah 3 kali percobaan:')
            ->line('**Nomor Surat:** ' . $this->surat->nomor_surat)
            ->line('**Keperluan:** ' . $this->surat->keperluan)
            ->line('**Alasan Gagal:** ' . $this->errorMessage)
            ->line('Silakan login ke sistem dan coba generate ulang PDF secara manual, atau hubungi administrator sistem jika masalah berlanjut.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'surat_pdf_failed',
            'surat_id'     => $this->surat->id,
            'nomor_surat'  => $this->surat->nomor_surat,
            'error'        => $this->errorMessage,
        ];
    }
}
