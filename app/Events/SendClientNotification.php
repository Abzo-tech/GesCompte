<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendClientNotification
{
    use Dispatchable, SerializesModels;

    public $client;
    public $type; // 'email' or 'sms'

    /**
     * Create a new event instance.
     */
    public function __construct(Client $client, string $type = 'both')
    {
        $this->client = $client;
        $this->type = $type;
    }
}
