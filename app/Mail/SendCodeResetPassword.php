<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendCodeResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public function __construct($code)
    {
        $this->code = $code;
    }
    public function build()
    {
        return $this->subject('Password Reset Code')
            ->markdown('emails.send-code-reset-password')
            ->with(['code' => $this->code]);
    }
//    public function build()
//    {
//        return $this->markdown('emails.send-code-reset-password');
//    }

//    public function envelope(): Envelope
//    {
//        return new Envelope(
//            subject: 'Send Code Reset Password',
//        );
//    }
//
//
//    public function content(): Content
//    {
//        return new Content(
//            view: 'view.name',
//        );
//    }
//
//    public function attachments(): array
//    {
//        return [];
//    }
}
