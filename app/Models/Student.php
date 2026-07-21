<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'branch_id',
        'branch_class_id',
        'section_id',
        'form_template_id',
        'registration_number',
        'roll_no',
        'admission_date',
        'academic_year',
        'form_data',
        'status',
    ];

    protected $casts = [
        'form_data' => 'array',
        'admission_date' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Class belongs to a Branch — enforced via branch_id scope.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(BranchClass::class, 'branch_class_id')
                    ->where('branch_id', $this->branch_id);
    }

    /**
     * Section belongs to the class — enforced via branch_class_id scope.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'section_id')
                    ->where('branch_class_id', $this->branch_class_id);
    }

    public function formTemplate(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getFormValue(string $key): mixed
    {
        return $this->form_data[$key] ?? null;
    }

    // ─── Boot / Registration Number Logic ────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Student $student) {
            if (empty($student->registration_number)) {
                $student->registration_number = static::generateRegistrationNumber($student);
            }
        });

        static::updating(function (Student $student) {
            if ($student->isDirty('registration_number')) {
                $student->registration_number = $student->getOriginal('registration_number');
            }
        });
    }
    
    protected static function generateRegistrationNumber(Student $student): string
    {
        // 1. Load the FormTemplate (already eager-loaded or fetch it)
        $template = $student->formTemplate
            ?? FormTemplate::find($student->form_template_id);

        // 2. Count existing students in this branch (for incrementing)
        $branchCount = static::withTrashed()
            ->where('branch_id', $student->branch_id)
            ->count();

        // 3. Determine the next number
        if ($template && !is_null($template->registration_serial)) {
            // User-defined serial start → offset by how many already exist in this branch
            $nextNumber = (int) $template->registration_serial + $branchCount;
        } else {
            // Fallback: auto-increment from 1 within this branch
            $nextNumber = $branchCount + 1;
        }

        // 4. Build registration number
        // Format: {BRANCH_CODE}-{YEAR}-{PADDED_NUMBER}
        // e.g. "BR01-2026-00042"
        $branchCode = str_pad($student->branch_id, 2, '0', STR_PAD_LEFT);
        $year       = now()->year;
        $paddedNum  = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return "BR{$branchCode}-{$year}-{$paddedNum}";
    }
}