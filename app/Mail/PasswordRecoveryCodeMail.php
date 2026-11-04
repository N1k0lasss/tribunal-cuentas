<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;

class PasswordRecoveryCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Código para recuperar su contraseña');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.password-recovery-code');
    }

    public function build(): static
    {
        return $this->withSymfonyMessage(function (Email $message): void {
            $logo = DataPart::fromPath(
                public_path('images/logo-tc-mail.png'),
                'logo-tc-mail.png',
                'image/png',
            );

            $message->addPart(
                $logo->setContentId('logo-tc-mail@tcse.local')->asInline(),
            );
        });
    }
}
