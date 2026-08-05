<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use SoftDeletes;

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
        return $this->belongsTo(BranchClass::class, 'branch_class_id');
    }

    /**
     * Section belongs to the class — enforced via branch_class_id scope.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
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

            if (empty($student->roll_no)) {
                $student->roll_no = static::generateRollNo($student);
            }

            if (empty($student->admission_date)) {
                $student->admission_date = now();
            }            
        });

        static::updating(function (Student $student) {
            if ($student->isDirty('registration_number')) {
                $student->registration_number = $student->getOriginal('registration_number');
            }

            if ($student->isDirty('roll_no')) {
                $student->roll_no = $student->getOriginal('roll_no');
            }            
        });
    }
    
    // protected static function generateRegistrationNumber(Student $student): string
    // {
    //     // 1. Load the FormTemplate (already eager-loaded or fetch it)
    //     $template = $student->formTemplate
    //         ?? FormTemplate::find($student->form_template_id);

    //     // 2. Count existing students in this branch (for incrementing)
    //     $branchCount = static::withTrashed()
    //         ->where('branch_id', $student->branch_id)
    //         ->count();

    //     // 3. Determine the next number
    //     if ($template && !is_null($template->registration_serial)) {
    //         // User-defined serial start → offset by how many already exist in this branch
    //         $nextNumber = (int) $template->registration_serial + $branchCount;
    //     } else {
    //         // Fallback: auto-increment from 1 within this branch
    //         $nextNumber = $branchCount + 1;
    //     }

    //     // 4. Build registration number
    //     // Format: {BRANCH_CODE}-{YEAR}-{PADDED_NUMBER}
    //     // e.g. "BR01-2026-00042"
    //     $branchCode = str_pad($student->branch_id, 2, '0', STR_PAD_LEFT);
    //     $year       = now()->year;
    //     $paddedNum  = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

    //     return "BR{$branchCode}-{$year}-{$paddedNum}";
    // }

    protected static function generateRegistrationNumber(Student $student): string
    {
        $template = $student->formTemplate ?? FormTemplate::find($student->form_template_id);

        $serial = $template?->registration_serial;

        return DB::transaction(function () use ($student, $serial) {

            $last = static::withTrashed()
                ->where('branch_id', $student->branch_id)
                ->whereNotNull('registration_number')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            // No previous student in this branch yet
            if (!$last) {
                return !empty($serial)
                    ? $serial
                    : static::buildDefaultRegistrationNumber($student, 1);
            }

            // Increment based on the last registration_number's trailing digits
            if (preg_match('/^(.*?)(\d+)$/', $last->registration_number, $matches)) {
                $prefix = $matches[1];
                $number = (int) $matches[2];
                $length = strlen($matches[2]);

                return $prefix . str_pad((string) ($number + 1), $length, '0', STR_PAD_LEFT);
            }

            // Last value didn't match expected pattern — fall back
            return !empty($serial)
                ? $serial
                : static::buildDefaultRegistrationNumber($student, 1);
        });
    }

    protected static function buildDefaultRegistrationNumber(Student $student, int $nextNumber): string
    {
        $branchCode = (string) $student->branch->code;
        $year       = now()->year;
        $paddedNum  = str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);

        return "REG{$branchCode}{$year}{$paddedNum}";
    }    

    protected static function generateRollNo(Student $student): int
    {
        // Load template (use eager-loaded relation or fetch)
        $template = $student->formTemplate ?? FormTemplate::find($student->form_template_id);

        $scope = $template?->rollno_generation_scope; // 'section' | 'branch_class' | null

        switch ($scope) {

            case 'section':
                $source     = Section::find($student->section_id);
                $startFrom  = (int) ($source?->starting_roll_no ?? 1);
                $count      = static::withTrashed()
                                ->where('section_id', $student->section_id)
                                ->whereNotNull('roll_no')
                                ->count();
                break;

            case 'branch_class':
                $source     = BranchClass::find($student->branch_class_id);
                $startFrom  = (int) ($source?->starting_roll_no ?? 1);
                $count      = static::withTrashed()
                                ->where('branch_class_id', $student->branch_class_id)
                                ->whereNotNull('roll_no')
                                ->count();
                break;

            default:
                // No scope set — fallback: auto-increment within class from 1
                $startFrom  = 1;
                $count      = static::withTrashed()
                                ->where('branch_class_id', $student->branch_class_id)
                                ->whereNotNull('roll_no')
                                ->count();
                break;
        }

        return $startFrom + $count;
    }    
}