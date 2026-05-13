<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrderCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function build()
    {
        return $this
            ->subject('Jūsų užsakymas gautas #' . $this->order->id)
            ->view('emails.new_order_customer');
    }
}