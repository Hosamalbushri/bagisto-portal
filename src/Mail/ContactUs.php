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
        $senderEmail = config('mail.from.address', 'admin@example.com');
        $senderName = config('mail.from.name', config('app.name', 'Admin'));
        $adminEmail = $senderEmail;
        $adminName = $senderName;

        if (function_exists('core')) {
            try {
                if (method_exists(core(), 'getSenderEmailDetails')) {
                    $sender = core()->getSenderEmailDetails();
                    $senderEmail = $sender['email'] ?? $senderEmail;
                    $senderName = $sender['name'] ?? $senderName;
                }

                if (method_exists(core(), 'getAdminEmailDetails')) {
                    $admin = core()->getAdminEmailDetails();
                    $adminEmail = $admin['email'] ?? $adminEmail;
                    $adminName = $admin['name'] ?? $adminName;
                }
            } catch (\Throwable) {
                // Graceful fallback to default mail settings
            }
        }

        return $this->from($senderEmail, $senderName)
            ->to($adminEmail, $adminName)
            ->replyTo($this->data['email'], $this->data['name'] ?? 'Visitor')
            ->subject(trans('portal::app.mail.contact-us.subject'))
            ->view('portal::emails.contact-us');
    }
}
