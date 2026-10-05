<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgentBulkEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agent;
    public $subjectText;
    public $body;
    public $senderName;
    public $replyToEmail;
    public $attachmentPath;
    public $attachmentName;
    public $attachmentMime;

    public function __construct(
        $agent,
        $subjectText,
        $body,
        $senderName,
        $replyToEmail,
        $attachmentPath = null,
        $attachmentName = null,
        $attachmentMime = null
    ) {
        $this->agent = $agent;
        $this->subjectText = $subjectText;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->replyToEmail = $replyToEmail;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
        $this->attachmentMime = $attachmentMime;
    }

    public function build() {
        $mail = $this->subject($this->subjectText)
            ->from(config('mail.from.address'), $this->senderName)
            ->replyTo($this->replyToEmail)
            ->view('admin.emails.agent-bulk-email');

        if ($this->attachmentPath) {
            $mail->attach($this->attachmentPath, [
                'as'   => $this->attachmentName,
                'mime' => $this->attachmentMime,
            ]);
        }

        return $mail;
    }
}