<?php

namespace SimoneBianco\LaravelProcesses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SimoneBianco\LaravelProcesses\Casts\ProcessLogCast;

class Process extends Model
{
    protected $fillable = [
        'processable_type',
        'processable_id',
        'status',
        'type',
        'error',
        'log',
        'context',
    ];

    protected $casts = [
        'log' => ProcessLogCast::class,
        'context' => 'array',
    ];

    public function processable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getContext(string $key, mixed $default = null): mixed
    {
        return data_get($this->context, $key, $default);
    }

    public function setContext(string $key, mixed $value): self
    {
        $context = $this->context ?? [];
        data_set($context, $key, $value);
        $this->context = $context;

        return $this;
    }

    public function mergeContext(array $values): self
    {
        $this->context = array_merge($this->context ?? [], $values);

        return $this;
    }

    public function log(string $method, string $content, array $context = []): self
    {
        $this->log->{$method}($content, $context);
        $this->log = $this->log;
        $this->save();

        return $this;
    }

    public function error(string $content, array $context = []): self
    {
        return $this->log('error', $content, $context);
    }

    public function warning(string $content, array $context = []): self
    {
        return $this->log('warning', $content, $context);
    }

    public function info(string $content, array $context = []): self
    {
        return $this->log('info', $content, $context);
    }

    public function setStatus(
        string $method,
        \SimoneBianco\LaravelProcesses\Enums\ProcessStatus $status,
        ?string $logContext = null,
        array $context = []
    ): self {
        $this->status = $status;

        if ($logContext) {
            $this->log->{$method}($logContext, $context);
            $this->log = $this->log;
        }

        $this->save();

        return $this;
    }

    public function setComplete(?string $logContent = null, array $context = []): self
    {
        return $this->setStatus('info', \SimoneBianco\LaravelProcesses\Enums\ProcessStatus::COMPLETE, $logContent, $context);
    }

    public function setError(?string $logContent = null, array $context = []): self
    {
        return $this->setStatus('error', \SimoneBianco\LaravelProcesses\Enums\ProcessStatus::ERROR, $logContent, $context);
    }

    public function setPending(?string $logContent = null, array $context = []): self
    {
        return $this->setStatus('info', \SimoneBianco\LaravelProcesses\Enums\ProcessStatus::PENDING, $logContent, $context);
    }

    public function setProcessing(?string $logContent = null, array $context = []): self
    {
        return $this->setStatus('info', \SimoneBianco\LaravelProcesses\Enums\ProcessStatus::PROCESSING, $logContent, $context);
    }
}
