<?php

namespace SimoneBianco\LaravelProcesses\Enums;

enum ProcessStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETE = 'complete';
    case ERROR = 'error';
}
