<?php

namespace App\Events;

use App\Models\Jeep;
use App\Models\JeepLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JeepLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Jeep $jeep,
        public JeepLocation $location
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("jeep.{$this->jeep->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'jeep' => [
                'id'              => $this->jeep->id,
                'name'            => $this->jeep->name,
                'plate_number'    => $this->jeep->plate_number,
                'route_name'      => $this->jeep->route_name,
                'status'          => $this->jeep->status,
                'seats_available' => $this->jeep->seats_available ?? 'available',
            ],
            'location' => [
                'latitude'    => $this->location->latitude,
                'longitude'   => $this->location->longitude,
                'speed'       => $this->location->speed,
                'heading'     => $this->location->heading,
                'accuracy'    => $this->location->accuracy,
                'recorded_at' => $this->location->recorded_at,
            ],
        ];
    }
}
