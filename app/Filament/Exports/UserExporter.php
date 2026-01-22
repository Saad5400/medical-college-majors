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
                ->label('Track'),
            ExportColumn::make('name')
                ->label('Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('student_id')
                ->label('Student ID'),
            ExportColumn::make('phone_number')
                ->label('Phone number'),
            ExportColumn::make('gpa')
                ->label('GPA'),
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
        $body = '**Export results:**'."\n\n";
        $body .= '✅ Successfully exported '.Number::format($export->successful_rows).' students.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\n".'❌ Failed to export '.Number::format($failedRowsCount).' rows.';
        }

        return $body;
    }
}
