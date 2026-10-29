<?php

namespace Platform\Portal\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public array $data) {}

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from(
            core()->getSenderEmailDetails()['email'],
            core()->getSenderEmailDetails()['name']
        )
            ->to(core()->getAdminEmailDetails()['email'], core()->getAdminEmailDetails()['name'])
            ->replyTo($this->data['email'], $this->data['name'])
            ->subject(trans('portal::app.mail.contact-us.subject'))
            ->view('portal::emails.contact-us');
    }
}
