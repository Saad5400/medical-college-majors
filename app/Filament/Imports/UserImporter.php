<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;

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
        $password = 's'.$studentId.'@uqu.edu.sa';

        return User::firstOrNew(['student_id' => $studentId])
            ->fill([
                'name' => $this->data['name'],
                'email' => $email,
                'password' => Hash::make($password),
                'gpa' => $this->data['gpa'],
            ]);
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
