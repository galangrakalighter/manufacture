<?php

namespace App\Events;

use App\Models\RequestPlat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestPlatUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $count;

    public function __construct()
    {
        $this->count = RequestPlat::where('status', 0)->count();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('gudang'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'request.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'count' => $this->count,
        ];
    }
}