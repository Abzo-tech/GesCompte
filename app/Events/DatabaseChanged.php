<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatabaseChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $table;
    public $operation;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($table, $operation, $data = null)
    {
        $this->table = $table;
        $this->operation = $operation; // 'created', 'updated', 'deleted'
        $this->data = $data;
    }
}