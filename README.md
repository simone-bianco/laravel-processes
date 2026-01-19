# Laravel Processes

[![Latest Version](https://img.shields.io/packagist/v/simone-bianco/laravel-processes.svg?style=flat-square)](https://packagist.org/packages/simone-bianco/laravel-processes)
[![Total Downloads](https://img.shields.io/packagist/dt/simone-bianco/laravel-processes.svg?style=flat-square)](https://packagist.org/packages/simone-bianco/laravel-processes)
[![License](https://img.shields.io/packagist/l/simone-bianco/laravel-processes.svg?style=flat-square)](https://packagist.org/packages/simone-bianco/laravel-processes)

A powerful Laravel package for tracking and managing long-running processes with structured logging and context storage. Perfect for background jobs, async operations, and any task that needs progress tracking.

## Features

- 🔄 **Process Tracking**: Track the status of long-running operations (pending, processing, complete, error)
- 📝 **Structured Logging**: Rich logging with severity levels (info, warning, error) and context
- 📊 **Context Storage**: Store process-specific variables without automatic logging
- 🎯 **Polymorphic Relations**: Attach processes to any Eloquent model via morphs
- 🧹 **Auto-Cleanup**: Built-in method to clean old processes
- 🔧 **Fluent API**: Chainable methods for easy status and log management

## Installation

Install the package via Composer:

```bash
composer require simone-bianco/laravel-processes
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag=laravel-processes-migrations
php artisan migrate
```

Or use the install command:

```bash
php artisan laravel-processes:install
```

## Quick Start

### 1. Add the Trait to Your Model

Add the `HasProcesses` trait to any model that needs process tracking:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SimoneBianco\LaravelProcesses\Models\Traits\HasProcesses;

class Document extends Model
{
    use HasProcesses;
}
```

### 2. Start and Track Processes

```php
use App\Models\Document;

$document = Document::find(1);

// Start a new process
$process = $document->startProcess('pdf-conversion');

// Log progress
$document->info('Starting PDF conversion');
$document->info('Processing page 1 of 10', ['page' => 1, 'total' => 10]);

// Update status
$document->setProcessing('Converting PDF to images...');

// Complete the process
$document->setComplete('PDF conversion finished successfully');
```

### 3. Store Context Variables

The `context` field stores process-specific variables that are NOT automatically logged:

```php
// Start process with initial context
$process = $document->startProcess('import', [], [
    'total_items' => 1000,
    'source_file' => 'data.csv',
]);

// Access context
$total = $process->getContext('total_items');  // 1000

// Update context during processing
$process->setContext('processed_items', 250);
$process->save();

// Merge multiple values
$process->mergeContext([
    'processed_items' => 500,
    'errors_count' => 2,
]);
$process->save();
```

## Process Status

The `ProcessStatus` enum defines four states:

| Status       | Description                         |
| ------------ | ----------------------------------- |
| `PENDING`    | Process created but not yet started |
| `PROCESSING` | Process is actively running         |
| `COMPLETE`   | Process finished successfully       |
| `ERROR`      | Process encountered an error        |

```php
use SimoneBianco\LaravelProcesses\Enums\ProcessStatus;

// Check current status
if ($document->latestProcess->status === ProcessStatus::COMPLETE) {
    // Process is done
}

// Find processes by status
$pendingProcess = $document->latestPendingProcess('import');
$errorProcess = $document->latestErrorProcess('import');
$completeProcess = $document->latestCompleteProcess('import');
```

## Logging Methods

### Adding Log Entries

```php
// Different severity levels
$document->info('Processing started');
$document->warning('Low disk space detected');
$document->error('Failed to connect to external service');

// With context data (gets logged automatically)
$document->info('Processing item', [
    'item_id' => 123,
    'progress' => '50%',
]);
```

### Status Update with Logging

```php
// These methods update status AND add a log entry
$document->setPending('Waiting for resource...');
$document->setProcessing('Working on task...');
$document->setComplete('All done!');
$document->setError('Something went wrong');

// With additional context
$document->setComplete('Processed all items', [
    'total_processed' => 100,
    'duration_seconds' => 45,
]);
```

### Reading Logs

```php
$process = $document->latestProcess;

// Get all log entries as formatted string
echo $process->log->toFormattedString();

// Get only errors
$errors = $process->log->errors();

// Filter log entries
$errorLogs = $process->log->logErrors();
```

## Working with Context

The `context` field is a JSON array for storing arbitrary process variables. Unlike log entries, context is meant for data you need to access/modify during processing:

```php
// Access via model methods
$process->getContext('key');                    // Get value
$process->getContext('nested.key', 'default'); // Dot notation with default
$process->setContext('key', 'value');          // Set single value
$process->mergeContext(['a' => 1, 'b' => 2]);  // Merge multiple values

// Access directly
$process->context;                              // Full array
$process->context = ['foo' => 'bar'];          // Replace all
```

**Use Cases for Context:**

- Storing progress counters (`processed`, `total`, `failed`)
- Tracking pagination state (`current_page`, `last_id`)
- Storing configuration for retries
- Caching intermediate results

## Process Cleanup

Remove old processes to keep your database clean:

```php
// Clean processes older than 7 days (default)
$document->cleanOldProcesses();

// Custom retention period
$document->cleanOldProcesses(30); // Keep last 30 days
```

## Advanced Usage

### Multiple Process Types

Use the `type` parameter to track different kinds of processes:

```php
// Start different process types
$document->startProcess('import');
$document->startProcess('thumbnail-generation');
$document->startProcess('ocr-extraction');

// Get latest by type
$importProcess = $document->latestPendingProcess('import');
$ocrProcess = $document->latestCompleteProcess('ocr-extraction');
```

### Accessing Process Relations

```php
// Get all processes
$allProcesses = $document->processes;

// Get latest process
$latest = $document->latestProcess;

// Query processes
$failedToday = $document->processes()
    ->where('status', ProcessStatus::ERROR)
    ->whereDate('created_at', today())
    ->get();
```

### Process Model Methods

```php
use SimoneBianco\LaravelProcesses\Models\Process;

$process = Process::find(1);

// Access the parent model
$parent = $process->processable;

// Context helpers
$process->getContext('key', 'default');
$process->setContext('key', 'value');
$process->mergeContext(['multiple' => 'values']);
```

## Migration Schema

The processes table includes:

| Column             | Type       | Description                    |
| ------------------ | ---------- | ------------------------------ |
| `id`               | bigint     | Primary key                    |
| `processable_type` | string     | Morph type class               |
| `processable_id`   | bigint     | Morph ID                       |
| `status`           | string(50) | Current status                 |
| `type`             | string     | Process type identifier        |
| `error`            | text       | Error message (nullable)       |
| `log`              | json       | Structured log entries         |
| `context`          | json       | Process variables (not logged) |
| `timestamps`       | datetime   | Created/updated at             |

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Credits

- [Simone Bianco](https://github.com/simone-bianco)

## Support

If you discover any issues, please open an issue on GitHub.
