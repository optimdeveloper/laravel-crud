<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRequestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $data;
    public int $user_id;

    public function __construct(string $data, int $user_id)
    {
        $this->data = $data;
        $this->user_id = $user_id;
    }

    public function broadcastAs(): string
    {
        return 'event';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('payment.channel.' . $this->user_id);
    }
}
