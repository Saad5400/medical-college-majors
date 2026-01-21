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
                ->label('الاسم')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('student_id')
                ->label('الرقم الجامعي')
                ->requiredMapping()
                ->castStateUsing(function (string $state): string {
                    return static::normalizeStudentId($state);
                })
                ->rules(['required', 'string', 'regex:/^[0-9]+$/', 'min:5', 'max:20'])
                ->example('430748574'),
            ImportColumn::make('phone_number')
                ->label('رقم الهاتف')
                ->rules(['nullable', 'max:255'])
                ->example('0501234567'),
            ImportColumn::make('gpa')
                ->label('المعدل (من 4)')
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

        // Convert Arabic-Indic numerals (٠-٩) to Western numerals (0-9)
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $westernNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $studentId = str_replace($arabicNumerals, $westernNumerals, $studentId);

        // Convert Eastern Arabic-Indic numerals (۰-۹) to Western numerals (0-9)
        $easternArabicNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $studentId = str_replace($easternArabicNumerals, $westernNumerals, $studentId);

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

        // Convert Arabic-Indic numerals (٠-٩) to Western numerals (0-9)
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $westernNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $gpa = str_replace($arabicNumerals, $westernNumerals, $gpa);

        // Convert Eastern Arabic-Indic numerals (۰-۹) to Western numerals (0-9)
        $easternArabicNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $gpa = str_replace($easternArabicNumerals, $westernNumerals, $gpa);

        // Replace comma with period for decimal separator
        $gpa = str_replace(',', '.', $gpa);

        // Remove any remaining non-numeric characters except period
        $gpa = preg_replace('/[^0-9.]/', '', $gpa);

        return $gpa;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = '**نتائج الاستيراد:**'."\n\n";
        $body .= '✅ تم استيراد '.Number::format($import->successful_rows).' طالب بنجاح.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\n".'❌ فشل استيراد '.Number::format($failedRowsCount).' صف.';
            $body .= "\n\n".'**الأخطاء الشائعة:**'."\n";
            $body .= '• تأكد من أن الرقم الجامعي يحتوي على أرقام فقط'."\n";
            $body .= '• تأكد من أن المعدل بين 0 و 4'."\n";
            $body .= '• تأكد من عدم تكرار الأرقام الجامعية'."\n";
            $body .= "\n".'يمكنك تحميل تقرير الأخطاء من صفحة عمليات الاستيراد.';
        }

        return $body;
    }
}
