<?php

namespace SimoneBianco\LaravelProcesses\Models\Traits;

use Illuminate\Database\Eloquent\Model;
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

    public function activeProcesses(): MorphMany
    {
        return $this->processes()->whereIn('status', [ProcessStatus::PENDING, ProcessStatus::PROCESSING]);
    }

    public function hasActiveProcesses(): bool
    {
        return $this->activeProcesses()->exists();
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

    /**
     * @return Process|Model
     */
    public function startProcess(string $type = 'default', array $initialContext = []): Process
    {
        $process = $this->processes()->create([
            'type' => $type,
            'status' => ProcessStatus::PENDING,
            'context' => $initialContext,
        ]);

        $this->setRelation('latestProcess', $process);

        return $process;
    }
}
