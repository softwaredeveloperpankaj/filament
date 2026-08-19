<?php

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

            ImportColumn::make('employee_id')
                ->requiredMapping()
                    ->rules([
                        'nullable',
                        'string',
                        'max:100',
                    ]),

            ImportColumn::make('phone')
                ->rules([
                    'nullable',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('date_of_birth')
                ->rules([
                    'nullable',
                    'date',
                ]),

            ImportColumn::make('gender')
                ->rules([
                    'nullable',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('profile_photo')
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('qualification')
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('specialization')
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('joining_date')
                ->rules([
                    'nullable',
                    'date',
                ]),

            ImportColumn::make('address')
                ->rules([
                    'nullable',
                    'string',
                ]),

            ImportColumn::make('status')
                ->requiredMapping()
                ->rules([
                    'required',
                    'string',
                    'max:30',
                ]),

            ImportColumn::make('salary')
                ->rules([
                    'nullable',
                    'numeric',
                    'min:0',
                ]),

            ImportColumn::make('branch')
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),

            ImportColumn::make('subject')
                ->rules([
                    'nullable',
                    'string',
                    'max:255',
                ]),
        ];
    }

    public function resolveRecord(): User
    {
        $employeeId = trim((string) ($this->data['employee_id'] ?? ''));

        /*
        * First priority: employee_id.
        */
        if ($employeeId !== '') {
            $teacherProfile = TeacherProfile::query()
                ->where('employee_id', $employeeId)
                ->with('user')
                ->first();

            if ($teacherProfile?->user) {
                $this->createdNewUser = false;

                return $teacherProfile->user;
            }

            /*
            * Employee ID was provided but not found.
            * If the email already belongs to a user, fail rather than
            * attaching the teacher profile to the wrong account.
            */
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

        /*
        * Second priority: email.
        */
        $existingUser = User::query()
            ->where('email', $this->data['email'])
            ->first();

        if ($existingUser) {
            $this->createdNewUser = false;

            return $existingUser;
        }

        /*
        * No employee_id and no matching email:
        * create a new user and teacher profile.
        */
        $this->createdNewUser = true;

        return new User();
    }

    protected function beforeSave(): void
    {
        /*
         * Never accept password data from the import file.
         */
        $this->record->name = $this->data['name'];
        $this->record->email = $this->data['email'];

        $this->record->email_verified_at =
            $this->data['email_verified_at'] ?? null;

        if ($this->createdNewUser) {
            /*
             * The password is random and is never sent by email.
             * The user receives a password reset link instead.
             */
            $this->record->password = Hash::make(
                Str::random(64),
            );
        }
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles(['teacher']);

        $this->record->teacherProfile()->updateOrCreate(
            [],
            [
                'employee_id' => $this->nullableValue('employee_id'),
                'phone' => $this->nullableValue('phone'),
                'date_of_birth' => $this->nullableValue('date_of_birth'),
                'gender' => $this->nullableValue('gender'),
                'profile_photo' => $this->nullableValue('profile_photo'),
                'qualification' => $this->nullableValue('qualification'),
                'specialization' => $this->nullableValue('specialization'),
                'joining_date' => $this->nullableValue('joining_date'),
                'address' => $this->nullableValue('address'),
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

    private function nullableValue(string $key): ?string
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
        $branchName = trim((string) ($this->data['branch'] ?? ''));

        if ($branchName === '') {
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
        $subjectName = trim((string) ($this->data['subject'] ?? ''));

        if ($subjectName === '') {
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
