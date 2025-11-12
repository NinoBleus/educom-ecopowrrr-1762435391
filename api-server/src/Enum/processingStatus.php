<?php

namespace App\Enum;

enum processingStatus: string {
    case OK = 'ok';
    case ERROR = 'error';
}