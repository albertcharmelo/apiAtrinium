<?php

namespace App\Mail;

use App\Models\RoleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoleRequestAswered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var \App\Models\RoleRequest
     */
    public $roleRequest;

    /**
     * The user instance.
     *
     * @var \App\Models\User
     */

    public $user;


    /**
     * Create a new message instance.
     */
    public function __construct(RoleRequest $roleRequest, $user)
    {
        $this->roleRequest = $roleRequest;
        $this->user = $user;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Role request was" . $this->roleRequest->status,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.role-request-answered',
            with: [
                'roleRequest' => $this->roleRequest,
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
