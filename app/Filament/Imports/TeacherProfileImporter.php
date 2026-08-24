<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Branch;
use App\Models\Subject;
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
                ->example('Anita Sharma')
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->requiredMapping()
                ->example('anita.sharma@mailinator.com')                
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('email_verified_at')
                ->rules(['nullable', 'date']),

            ImportColumn::make('employee_id')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:100']),

            ImportColumn::make('phone')
                ->fillRecordUsing(fn () => null)
                ->example('9881234567')
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('date_of_birth')
                ->fillRecordUsing(fn () => null)
                ->example('1984-07-19')
                ->rules(['nullable', 'date']),

            ImportColumn::make('gender')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('profile_photo')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('qualification')
                ->fillRecordUsing(fn () => null)
                ->example('M.A. B.Ed.')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('specialization')
                ->fillRecordUsing(fn () => null)
                ->example('Early Childhood Education')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('joining_date')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'date']),

            ImportColumn::make('address')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string']),

            ImportColumn::make('status')
                ->fillRecordUsing(fn () => null)
                ->rules(['required', 'string', 'max:30']),

            ImportColumn::make('salary')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('branch')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('subjects')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): User
    {
        $email = $this->nullableString('email');
        $employeeId = $this->nullableString('employee_id');

        if ($employeeId !== null) {
            $existingUser = User::query()
                ->where('employee_id', $employeeId)
                ->first();

            if ($existingUser) {
                $this->createdNewUser = false;

                return $existingUser;
            }

            $userWithSameEmail = User::query()
                ->where('email', $email)
                ->first();

            if ($userWithSameEmail) {
                $this->createdNewUser = false;

                return $userWithSameEmail;
            }

            $this->createdNewUser = true;

            return new User();
        }

        $existingUser = User::query()
            ->where('email', $email)
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
        $branchId = $this->resolveBranchId();

        $this->record->name = $this->data['name'];
        $this->record->email = $this->data['email'];
        $this->record->email_verified_at = $this->data['email_verified_at'] ?? null;
        $this->record->branch_id = $branchId;

        $employeeId = $this->nullableString('employee_id');

        if ($employeeId === null && $branchId !== null) {
            $employeeId = User::generateEmployeeId('teacher', $branchId);
        }

        $this->record->employee_id = $employeeId;

        if ($this->createdNewUser) {
            $this->record->password = Hash::make(Str::random(64));
        }
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles(['teacher']);

        $branchId = $this->resolveBranchId();

        $profile = $this->record->teacherProfile()->updateOrCreate(
            ['user_id' => $this->record->id],
            [
                'branch_id' => $branchId,
                'phone' => $this->nullableString('phone'),
                'date_of_birth' => $this->parseDate('date_of_birth'),
                'gender' => $this->nullableString('gender'),
                'profile_photo' => $this->nullableString('profile_photo'),
                'qualification' => $this->nullableString('qualification'),
                'specialization' => $this->nullableString('specialization'),
                'joining_date' => $this->parseDate('joining_date'),
                'address' => $this->nullableString('address'),
                'status' => $this->nullableString('status') ?? 'inactive',
                'salary' => $this->nullableNumber('salary'),
            ],
        );

        $profile->subjects()->sync($this->resolveSubjectIds($branchId));

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

    private function resolveSubjectIds(?int $branchId = null): array
    {
        $subjectsValue = $this->nullableString('subjects');

        if ($subjectsValue === null) {
            return [];
        }

        $items = collect(explode(',', $subjectsValue))
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->values();

        if ($items->isEmpty()) {
            return [];
        }

        $resolvedIds = [];
        $missing = [];

        foreach ($items as $item) {
            $name = null;
            $code = null;

            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $item, $matches)) {
                $name = trim($matches[1]);
                $code = trim($matches[2]);
            } else {
                $name = $item;
            }

            $subject = Subject::query()
                ->when(
                    $branchId,
                    fn ($query) => $query->where('branch_id', $branchId)
                )
                ->when(
                    $code,
                    fn ($query) => $query->where('code', $code),
                    fn ($query) => $query->where('name', $name)
                )
                ->first();

            if (! $subject && $name && $code) {
                $subject = Subject::query()
                    ->when(
                        $branchId,
                        fn ($query) => $query->where('branch_id', $branchId)
                    )
                    ->where('name', $name)
                    ->where('code', $code)
                    ->first();
            }

            if (! $subject) {
                $missing[] = $item;
                continue;
            }

            $resolvedIds[] = (int) $subject->getKey();
        }

        if (! empty($missing)) {
            throw ValidationException::withMessages([
                'subjects' => 'These subjects were not found'
                    . ($branchId ? ' in the selected branch' : '')
                    . ': ' . implode(', ', $missing),
            ]);
        }

        return array_values(array_unique($resolvedIds));
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
                $key => "Invalid date [{$value}] for {$key}. Expected YYYY-MM-DD or DD-MM-YYYY.",
            ]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your teacher import has completed and '
            . Number::format($import->successful_rows)
            . ' '
            . str('row')->plural($import->successful_rows)
            . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '
                . Number::format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed to import.';
        }

        return $body;
    }
}