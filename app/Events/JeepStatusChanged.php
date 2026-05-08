<?php

namespace App\Events;

use App\Models\Jeep;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JeepStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Jeep $jeep) {}

    public function broadcastOn(): array
    {
        return [new Channel("jeep.{$this->jeep->id}")];
    }

    public function broadcastAs(): string
    {
        return 'status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'jeep_id' => $this->jeep->id,
            'status'  => $this->jeep->status,
        ];
    }
}
