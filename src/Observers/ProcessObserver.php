<?php

namespace SimoneBianco\LaravelProcesses\Observers;

use SimoneBianco\LaravelProcesses\Events\ProcessStatusUpdated;
use SimoneBianco\LaravelProcesses\Models\Process;

class ProcessObserver
{
    public function created(Process $process): void
    {
        ProcessStatusUpdated::dispatch($process);
    }

    public function updated(Process $process): void
    {
        if ($process->wasChanged('status') || $process->wasChanged('context')) {
            ProcessStatusUpdated::dispatch($process);
        }
    }
}
