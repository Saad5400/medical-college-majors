<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

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
                ->rules(['required', 'max:255'])
                ->example('430748574'),
            ImportColumn::make('phone_number')
                ->label('رقم الهاتف')
                ->rules(['nullable', 'max:255'])
                ->example('0501234567'),
            ImportColumn::make('gpa')
                ->label('المعدل (من 4)')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:4'])
                ->example('3.45'),
        ];
    }

    public function resolveRecord(): User
    {
        $studentId = $this->data['student_id'];
        $email = 's'.$studentId.'@uqu.edu.sa';

        return User::firstOrNew(['student_id' => $studentId])
            ->fill([
                'name' => $this->data['name'],
                'email' => $email,
                'phone_number' => $this->data['phone_number'] ?? null,
                'password' => Hash::make(Str::random(16)),
                'gpa' => $this->normalizeGpa($this->data['gpa']),
            ]);
    }

    /**
     * Normalize GPA value from various formats (Arabic numerals, different decimal separators).
     */
    protected function normalizeGpa(mixed $gpa): float|int|string|null
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
        $body = 'تم استيراد '.Number::format($import->successful_rows).' طالب بنجاح.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' صف فشل استيراده.';
        }

        return $body;
    }
}
