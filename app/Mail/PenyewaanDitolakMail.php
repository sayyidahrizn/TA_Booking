<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PenyewaanDitolakMail extends Mailable
{
    public $penyewaan;
    public $alasan;

    public function __construct($penyewaan, $alasan = null)
    {
        $this->penyewaan = $penyewaan;
        $this->alasan = $alasan;
    }

    public function build()
    {
        return $this
            ->subject('Penyewaan Ditolak')
            ->view('emails.ditolak');
    }
}