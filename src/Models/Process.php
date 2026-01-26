<?php

namespace SimoneBianco\LaravelProcesses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SimoneBianco\LaravelProcesses\Enums\ProcessStatus;

/**
 * @property array $context
 * @property ProcessStatus $status
 */
class Process extends Model
{
    protected $fillable = [
        'processable_type',
        'processable_id',
        'status',
        'type',
        'error',
        'context',
    ];

    protected $casts = [
        'status' => ProcessStatus::class,
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

    public function setContextAndSave(string $key, mixed $value): self
    {
        $this->setContext($key, $value);
        $this->save();

        return $this;
    }

    public function mergeContext(array $values): self
    {
        $this->context = array_merge($this->context ?? [], $values);

        return $this;
    }

    public function mergeContextAndSave(array $values): self
    {
        $this->mergeContext($values);
        $this->save();

        return $this;
    }

    public function setStatus(
        ProcessStatus $status,
        array $context = []
    ): self {
        $this->status = $status;

        if (! empty($context)) {
            $this->mergeContext($context);
        }

        $this->save();

        return $this;
    }

    public function setComplete(array $context = []): self
    {
        return $this->setStatus(ProcessStatus::COMPLETE, $context);
    }

    public function setError(?string $message = null, array $context = []): self
    {
        if ($message) {
            $this->error = $message;
        }

        return $this->setStatus(ProcessStatus::ERROR, $context);
    }

    public function setPending(array $context = []): self
    {
        return $this->setStatus(ProcessStatus::PENDING, $context);
    }

    public function setProcessing(array $context = []): self
    {
        return $this->setStatus(ProcessStatus::PROCESSING, $context);
    }
}
