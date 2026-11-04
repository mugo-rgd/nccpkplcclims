<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $user;
    public $password;

    public function __construct(Customer $customer, User $user, string $password)
    {
        $this->customer = $customer;
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this
            ->subject('Your Meter Calibration Laboratory Account')
            ->view('emails.customer-account-created');
    }
}
