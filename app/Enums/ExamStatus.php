<?php
namespace App\Enums;

enum ExamStatus: string {
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case RESULTS_PUBLISHED = 'results_published';
    case CANCELLED = 'cancelled';
}

?>