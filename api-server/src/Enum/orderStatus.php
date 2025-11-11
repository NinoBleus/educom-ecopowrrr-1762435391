<?php

namespace App\Enum;

enum orderStatus: string {
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}