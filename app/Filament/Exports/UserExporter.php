<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('track.name')
                ->label('المسار'),
            ExportColumn::make('name')
                ->label('الاسم'),
            ExportColumn::make('email')
                ->label('البريد الإلكتروني'),
            ExportColumn::make('student_id')
                ->label('الرقم الجامعي'),
            ExportColumn::make('phone_number')
                ->label('رقم الهاتف'),
            ExportColumn::make('gpa')
                ->label('المعدل'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with('track')->orderBy('track_id');
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = '**نتائج التصدير:**'."\n\n";
        $body .= '✅ تم تصدير '.Number::format($export->successful_rows).' طالب بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\n".'❌ فشل تصدير '.Number::format($failedRowsCount).' صف.';
        }

        return $body;
    }
}
