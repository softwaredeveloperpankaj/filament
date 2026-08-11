<?php
namespace App\Enums;

enum MarksSource: string {
    case ONLINE_AUTO = 'online_auto';
    case ONLINE_MANUAL = 'online_manual';
    case OFFLINE_MANUAL = 'offline_manual';
}