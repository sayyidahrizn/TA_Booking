<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class DendaPengembalianMail extends Mailable
{
    public $user;
    public $kodeBooking;
    public $totalDenda;

    public function __construct(
        $user,
        $kodeBooking,
        $totalDenda
    ) {
        $this->user = $user;
        $this->kodeBooking = $kodeBooking;
        $this->totalDenda = $totalDenda;
    }

    public function build()
    {
        return $this
            ->subject('Tagihan Denda Pengembalian')
            ->view('emails.denda_pengembalian');
    }
}