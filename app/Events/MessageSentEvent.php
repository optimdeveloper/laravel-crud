<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public int $user_id;

    public function __construct($data, int $user_id)
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
        return new Channel('message.channel.' . $this->user_id);
    }
}

//https://pusher.com/docs/channels/server_api/webhooks/
//$data = BookingHelper::load($booking, $place, $place_types);
//
//PaymentRequestEvent::dispatch($data->toJson(), $booking->user_id);
