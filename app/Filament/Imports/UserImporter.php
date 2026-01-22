<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('student_id')
                ->label('Student ID')
                ->requiredMapping()
                ->castStateUsing(function (string $state): string {
                    return static::normalizeStudentId($state);
                })
                ->rules(['required', 'string', 'regex:/^[0-9]+$/', 'min:5', 'max:20'])
                ->example('430748574'),
            ImportColumn::make('phone_number')
                ->label('Phone number')
                ->rules(['nullable', 'max:255'])
                ->example('0501234567'),
            ImportColumn::make('gpa')
                ->label('GPA (out of 4)')
                ->requiredMapping()
                ->castStateUsing(function (string $state): ?string {
                    return static::normalizeGpa($state);
                })
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:4'])
                ->example('3.45'),
        ];
    }

    public function resolveRecord(): User
    {
        // Normalize student_id (as fallback, castStateUsing() should have already normalized it)
        $studentId = static::normalizeStudentId($this->data['student_id']);
        $email = 's'.$studentId.'@uqu.edu.sa';

        $user = User::firstOrNew(['student_id' => $studentId])
            ->fill([
                'name' => $this->data['name'],
                'email' => $email,
                'phone_number' => $this->data['phone_number'] ?? null,
                'password' => Hash::make(Str::random(16)),
                // Normalize GPA (as fallback, castStateUsing() should have already normalized it)
                'gpa' => static::normalizeGpa($this->data['gpa']),
            ]);

        return $user;
    }

    protected function afterSave(): void
    {
        // Assign student role to the imported user
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        if (! $this->record->hasRole('student')) {
            $this->record->assignRole($studentRole);
        }
    }

    /**
     * Normalize student ID from various formats (emails, prefixed IDs, Arabic numerals).
     */
    protected static function normalizeStudentId(mixed $studentId): string
    {
        if ($studentId === null || $studentId === '') {
            return '';
        }

        $studentId = (string) $studentId;

        // Remove whitespace
        $studentId = trim($studentId);

        // Extract student ID from email format (e.g., s444444@uqu.edu.sa -> s444444)
        if (str_contains($studentId, '@')) {
            $studentId = explode('@', $studentId)[0];
        }

        // Remove 's' or 'S' prefix if present (e.g., s444444 -> 444444)
        if (preg_match('/^[sS]/', $studentId)) {
            $studentId = substr($studentId, 1);
        }

        // Convert Arabic-Indic numerals (Unicode 0660-0669) to Western numerals (0-9)
        $westernNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $studentId = str_replace(static::arabicIndicDigits(), $westernNumerals, $studentId);
        $studentId = str_replace(static::easternArabicIndicDigits(), $westernNumerals, $studentId);

        // Remove any remaining non-numeric characters
        $studentId = preg_replace('/[^0-9]/', '', $studentId);

        return $studentId;
    }

    /**
     * Normalize GPA value from various formats (Arabic numerals, different decimal separators).
     */
    protected static function normalizeGpa(mixed $gpa): float|int|string|null
    {
        if ($gpa === null || $gpa === '') {
            return null;
        }

        $gpa = (string) $gpa;

        // Remove whitespace
        $gpa = trim($gpa);

        // Convert Arabic-Indic numerals (Unicode 0660-0669) to Western numerals (0-9)
        $westernNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $gpa = str_replace(static::arabicIndicDigits(), $westernNumerals, $gpa);
        $gpa = str_replace(static::easternArabicIndicDigits(), $westernNumerals, $gpa);

        // Replace comma with period for decimal separator
        $gpa = str_replace(',', '.', $gpa);

        // Remove any remaining non-numeric characters except period
        $gpa = preg_replace('/[^0-9.]/', '', $gpa);

        return $gpa;
    }

    /**
     * @return array<int, string>
     */
    private static function arabicIndicDigits(): array
    {
        return array_map(
            fn (int $codepoint): string => mb_chr($codepoint, 'UTF-8'),
            range(0x0660, 0x0669),
        );
    }

    /**
     * @return array<int, string>
     */
    private static function easternArabicIndicDigits(): array
    {
        return array_map(
            fn (int $codepoint): string => mb_chr($codepoint, 'UTF-8'),
            range(0x06F0, 0x06F9),
        );
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = '**Import results:**'."\n\n";
        $body .= '✅ Successfully imported '.Number::format($import->successful_rows).' students.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\n".'❌ Failed to import '.Number::format($failedRowsCount).' rows.';
            $body .= "\n\n".'**Common issues:**'."\n";
            $body .= '• Ensure the student ID contains numeric characters only'."\n";
            $body .= '• Ensure the GPA is between 0 and 4'."\n";
            $body .= '• Make sure student IDs are not duplicated'."\n";
            $body .= "\n".'You can download the error report from the import operations page.';
        }

        return $body;
    }
}
