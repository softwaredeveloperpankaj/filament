<?php

namespace App\Models;

use App\Enums\ExamMode;
use App\Enums\ExamStatus;
use App\Models\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory, SoftDeletes, HasBranchScope;

    protected $fillable = [
        'branch_id',
        'branch_class_id',
        'section_id',
        'name',
        'slug',
        'mode',
        'academic_year_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
        'settings',
        'instructions',
        'is_practical',
        'created_by',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'start_time'      => 'datetime:H:i',
        'end_time'        => 'datetime:H:i',
        'mode'            => ExamMode::class,
        'status'          => ExamStatus::class,
        'settings'        => 'array',
        'is_practical'    => 'boolean',
    ];

    /* ---------- Relationships ---------- */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchClass(): BelongsTo
    {
        return $this->belongsTo(BranchClass::class, 'branch_class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(ExamSubject::class)->orderBy('display_order');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class)->orderBy('exam_date')->orderBy('start_time');
    }

    public function studentEntries(): HasMany
    {
        return $this->hasMany(ExamStudentEntry::class);
    }

    public function questionPapers(): HasMany
    {
        return $this->hasMany(ExamQuestionPaper::class);
    }

    public function onlineAttempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(ExamMark::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /* ---------- Scopes ---------- */

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForClassAndSection($query, int $branchClassId, int $sectionId)
    {
        return $query->where('branch_class_id', $branchClassId)
                     ->where('section_id', $sectionId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByMode($query, string $mode)
    {
        return $query->where('mode', $mode);
    }

    public function scopeCurrentAcademicYear($query)
    {
        return $query->where('academic_year_id', AcademicYear::current()?->id);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
                     ->whereIn('status', [ExamStatus::DRAFT->value, ExamStatus::SCHEDULED->value]);
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now()->toDateString())
                     ->where('end_date', '>=', now()->toDateString())
                     ->where('status', ExamStatus::ONGOING->value);
    }

    /* ---------- Boot Logic ---------- */

    protected static function booted(): void
    {
        static::creating(function (Exam $exam) {
            $exam->slug ??= Str::slug($exam->name) . '-' . now()->format('YmdHis');
            $exam->created_by ??= auth()->id();
            $exam->status ??= ExamStatus::DRAFT;
        });

        static::updating(function (Exam $exam) {
            // Auto-transition status based on dates
            if ($exam->isDirty(['start_date', 'end_date', 'status'])) {
                $today = now()->toDateString();
                if ($exam->start_date <= $today && $exam->end_date >= $today && $exam->status === ExamStatus::SCHEDULED) {
                    $exam->status = ExamStatus::ONGOING;
                } elseif ($exam->end_date < $today && $exam->status === ExamStatus::ONGOING) {
                    $exam->status = ExamStatus::COMPLETED;
                }
            }
        });

        static::deleting(function (Exam $exam) {
            // Prevent deletion if results published
            if ($exam->results()->where('status', 'published')->exists()) {
                return false;
            }
        });
    }

    /* ---------- Accessors / Helpers ---------- */

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isOnline(): bool
    {
        return $this->mode === ExamMode::ONLINE;
    }

    public function isOffline(): bool
    {
        return $this->mode === ExamMode::OFFLINE;
    }

    public function canGenerateAdmitCards(): bool
    {
        return in_array($this->status, [ExamStatus::SCHEDULED->value, ExamStatus::ONGOING->value]);
    }

    public function canEnterMarks(): bool
    {
        return in_array($this->status, [ExamStatus::ONGOING->value, ExamStatus::COMPLETED->value]);
    }

    public function canPublishResults(): bool
    {
        return $this->status === ExamStatus::COMPLETED->value;
    }
}