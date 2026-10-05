<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgentActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agent;

    public function __construct($agent) {
        $this->agent = $agent;
    }

    public function build() {
        return $this->subject('Your Sun Leisure World Agent Account is Activated')
            ->view('admin.emails.agent-activated');
    }
}