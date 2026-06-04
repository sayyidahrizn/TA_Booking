<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PenyewaanDisetujuiMail extends Mailable
{
    public $penyewaan;

    public function __construct($penyewaan)
    {
        $this->penyewaan = $penyewaan;
    }

    public function build()
    {
        return $this
            ->subject('Penyewaan Disetujui')
            ->view('emails.disetujui');
    }
}