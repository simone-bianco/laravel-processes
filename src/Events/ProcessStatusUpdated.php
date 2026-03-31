<?php

namespace SimoneBianco\LaravelProcesses\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Str;
use SimoneBianco\LaravelProcesses\Models\Process;

class ProcessStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public Process $process,
    ) {}

    public function broadcastAs(): string
    {
        return 'process.updated';
    }

    public function broadcastOn(): PrivateChannel
    {
        $type = Str::snake(class_basename($this->process->processable_type));
        return new PrivateChannel("{$type}.{$this->process->processable_id}");
    }

    public function broadcastWith(): array
    {
        $context = $this->process->context ?? [];

        return [
            'process' => [
                'id' => $this->process->id,
                'type' => $this->process->type,
                'status' => $this->process->status->value,
                'error' => $this->process->error,
                'context' => array_intersect_key($context, array_flip(['phase'])),
                'processable_type' => class_basename($this->process->processable_type),
                'processable_id' => $this->process->processable_id,
                'created_at' => $this->process->created_at?->toISOString(),
                'updated_at' => $this->process->updated_at?->toISOString(),
            ],
        ];
    }
}
