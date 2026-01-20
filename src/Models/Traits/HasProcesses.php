<?php

namespace SimoneBianco\LaravelProcesses\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use SimoneBianco\LaravelProcesses\Enums\ProcessStatus;
use SimoneBianco\LaravelProcesses\Models\Process;

trait HasProcesses
{
    public function processes(): MorphMany
    {
        return $this->morphMany(Process::class, 'processable');
    }

    public function latestProcess(): MorphOne
    {
        return $this->morphOne(Process::class, 'processable')->latestOfMany();
    }

    public function latestPendingProcess(string $type = 'default'): ?Process
    {
        return $this->processes()
            ->where('type', $type)
            ->where('status', ProcessStatus::PENDING)
            ->latest()
            ->first();
    }

    public function latestErrorProcess(string $type = 'default'): ?Process
    {
        return $this->processes()
            ->where('type', $type)
            ->where('status', ProcessStatus::ERROR)
            ->latest()
            ->first();
    }

    public function latestCompleteProcess(string $type = 'default'): ?Process
    {
        return $this->processes()
            ->where('type', $type)
            ->where('status', ProcessStatus::COMPLETE)
            ->latest()
            ->first();
    }





    public function cleanOldProcesses(int $retentionDays = 7): self
    {
        $this->processes()
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->delete();

        return $this;
    }

    public function startProcess(string $type = 'default', array $initialLog = [], array $initialContext = []): Process
    {
        $process = $this->processes()->create([
            'type'    => $type,
            'status'  => ProcessStatus::PENDING,
            'log'     => $initialLog,
            'context' => $initialContext,
        ]);

        $this->setRelation('latestProcess', $process);

        return $process;
    }
}
