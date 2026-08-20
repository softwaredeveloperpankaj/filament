<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeacherProfileImporter extends Importer
{
    protected static ?string $model = User::class;

    private bool $createdNewUser = false;

    public static function getColumns(): array
    {
        return [
            // User fields — these fill the User model normally.
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('email')
                ->requiredMapping()
                ->rules([
                    'required',
                    'email',
                    'max:255',
                ]),

            ImportColumn::make('email_verified_at')
                ->rules([
                    'nullable',
                    'date',
                ]),

            // TeacherProfile fields — fillRecordUsing(fn () => null)
            // prevents Filament from writing these to the users table.
            // They are still available in $this->data for afterSave().

            ImportColumn::make('employee_id')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:100',
                ]),

            ImportColumn::make('phone')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('date_of_birth')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'date',
                ]),

            ImportColumn::make('gender')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('profile_photo')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('qualification')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('specialization')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('joining_date')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'date',
                ]),

            ImportColumn::make('address')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                ]),

            ImportColumn::make('status')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'required',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('salary')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'numeric',
                    'min:0',
                ]),

            ImportColumn::make('branch')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('subject')
                ->fillRecordUsing(fn () => null)
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),
        ];
    }

    public function resolveRecord(): User
    {
        $employeeId = $this->nullableString('employee_id');

        if ($employeeId !== null) {
            $teacherProfile = TeacherProfile::query()
                ->where('employee_id', $employeeId)
                ->with('user')
                ->first();

            if ($teacherProfile?->user) {
                $this->createdNewUser = false;

                return $teacherProfile->user;
            }

            $existingUser = User::query()
                ->where('email', $this->data['email'])
                ->first();

            if ($existingUser) {
                throw ValidationException::withMessages([
                    'employee_id' => "Employee ID [{$employeeId}] was not found, "
                        .'but this email already belongs to another user.',
                ]);
            }

            $this->createdNewUser = true;

            return new User();
        }

        $existingUser = User::query()
            ->where('email', $this->data['email'])
            ->first();

        if ($existingUser) {
            $this->createdNewUser = false;

            return $existingUser;
        }

        $this->createdNewUser = true;

        return new User();
    }

    protected function beforeSave(): void
    {
        $this->record->name = $this->data['name'];
        $this->record->email = $this->data['email'];
        $this->record->email_verified_at =
            $this->data['email_verified_at'] ?? null;

        if ($this->createdNewUser) {
            $this->record->password = Hash::make(
                Str::random(64),
            );
        }
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles(['teacher']);

        $employeeId = $this->nullableString('employee_id');

        if ($employeeId === null) {
            $employeeId = (string) (
                ((int) TeacherProfile::query()->max('employee_id')) + 1
            );
        }

        $this->record->teacherProfile()->updateOrCreate(
            [],
            [
                'employee_id' => $employeeId,
                'phone' => $this->nullableString('phone'),
                'date_of_birth' => $this->parseDate('date_of_birth'),
                'gender' => $this->nullableString('gender'),
                'profile_photo' => $this->nullableString('profile_photo'),
                'qualification' => $this->nullableString('qualification'),
                'specialization' => $this->nullableString('specialization'),
                'joining_date' => $this->parseDate('joining_date'),
                'address' => $this->nullableString('address'),
                'status' => $this->data['status'],
                'salary' => $this->nullableNumber('salary'),
                'branch_id' => $this->resolveBranchId(),
                'subject_id' => $this->resolveSubjectId(),
            ],
        );

        if ($this->createdNewUser) {
            $this->sendPasswordResetLink();
        }
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableNumber(string $key): int|float|null
    {
        $value = $this->data[$key] ?? null;

        return filled($value) ? (float) $value : null;
    }

    private function resolveBranchId(): ?int
    {
        $branchName = $this->nullableString('branch');

        if ($branchName === null) {
            return null;
        }

        $branch = Branch::query()
            ->where('name', $branchName)
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch' => "Branch [{$branchName}] was not found.",
            ]);
        }

        return (int) $branch->getKey();
    }

    private function resolveSubjectId(): ?int
    {
        $subjectName = $this->nullableString('subject');

        if ($subjectName === null) {
            return null;
        }

        $subject = Subject::query()
            ->where('name', $subjectName)
            ->first();

        if (! $subject) {
            throw ValidationException::withMessages([
                'subject' => "Subject [{$subjectName}] was not found.",
            ]);
        }

        return (int) $subject->getKey();
    }

    private function sendPasswordResetLink(): void
    {
        $status = Password::broker()->sendResetLink([
            'email' => $this->record->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }

    private function parseDate(string $key): ?string
    {
        $value = $this->nullableString($key);

        if ($value === null) {
            return null;
        }

        $formats = [
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
            'm/d/Y',
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
        ];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);

            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $key => "Invalid date [{$value}] for {$key}. "
                    .'Expected YYYY-MM-DD or DD-MM-YYYY.',
            ]);
        }
    }    

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your teacher import has completed and '
            .Number::format($import->successful_rows)
            .' '
            .str('row')->plural($import->successful_rows)
            .' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '
                .Number::format($failedRowsCount)
                .' '
                .str('row')->plural($failedRowsCount)
                .' failed to import.';
        }

        return $body;
    }
}