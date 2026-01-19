<?php

namespace SimoneBianco\LaravelProcesses\Models;

use Illuminate\Database\Eloquent\Model;
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

    /**
     * Get the parent processable model (morph relation).
     */
    public function processable()
    {
        return $this->morphTo();
    }

    /**
     * Get a context value by key.
     */
    public function getContext(string $key, mixed $default = null): mixed
    {
        return data_get($this->context, $key, $default);
    }

    /**
     * Set a context value by key.
     */
    public function setContext(string $key, mixed $value): self
    {
        $context = $this->context ?? [];
        data_set($context, $key, $value);
        $this->context = $context;
        
        return $this;
    }

    /**
     * Merge multiple values into the context.
     */
    public function mergeContext(array $values): self
    {
        $this->context = array_merge($this->context ?? [], $values);
        
        return $this;
    }
}
