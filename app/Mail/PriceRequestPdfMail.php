<?php

namespace App\Mail;

use App\Models\PriceRequest;
use App\Services\PriceRequestPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class PriceRequestPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PriceRequest $priceRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Price Request #:id', ['id' => $this->priceRequest->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.price-request-pdf',
            with: [
                'priceRequest' => $this->priceRequest,
                'shareUrl' => URL::temporarySignedRoute(
                    'price-requests.share',
                    now()->addDays(30),
                    ['priceRequest' => $this->priceRequest->id],
                ),
            ],
        );
    }

    public function build(): static
    {
        $pdfContent = app(PriceRequestPdfService::class)->generate($this->priceRequest);

        return $this->attachData(
            $pdfContent,
            'price-request-' . $this->priceRequest->id . '.pdf',
            ['mime' => 'application/pdf'],
        );
    }
}

