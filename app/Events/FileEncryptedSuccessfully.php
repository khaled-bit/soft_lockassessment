<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileEncryptedSuccessfully
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

     public function broadcastOn()
    {

        return new Channel('file-encrypted');
    }

    public function broadcastWith()
{
    return ['filePath' => $this->filePath];
}


}
