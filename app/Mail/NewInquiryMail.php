<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry) {}

    public function envelope(): Envelope
    {
        $type = Inquiry::TYPES[$this->inquiry->type] ?? 'Inquiry';

        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: "[{$type}] ".($this->inquiry->item_name ?: $this->inquiry->subject ?: 'from '.$this->inquiry->name),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.inquiry');
    }
}
