<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class DendaPengembalianMail extends Mailable
{
    public $penyewaan;
    public $totalDenda;

    public function __construct($penyewaan, $totalDenda)
    {
        $this->penyewaan = $penyewaan;
        $this->totalDenda = $totalDenda;
    }

    public function build()
    {
        return $this
            ->subject('Tagihan Denda Pengembalian')
            ->view('emails.denda_pengembalian')
            ->with([
                'penyewaan' => $this->penyewaan,
                'totalDenda' => $this->totalDenda,
            ]);
    }
}