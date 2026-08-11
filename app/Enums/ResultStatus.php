<?php
namespace App\Enums;

enum ResultStatus: string {
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case WITHHELD = 'withheld';
    case CANCELLED = 'cancelled';
}