<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_grades')
                ->label('رفع معدلات الطلبة')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('ملف المعدلات (CSV)')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('يجب أن يحتوي الملف على عمودين: student_id, gpa')
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/' . $data['file']);

                    if (!file_exists($filePath)) {
                        \Filament\Notifications\Notification::make()
                            ->title('خطأ')
                            ->body('الملف غير موجود.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $file = fopen($filePath, 'r');
                    $header = fgetcsv($file); // Skip header row

                    $updated = 0;
                    $errors = [];

                    while (($row = fgetcsv($file)) !== false) {
                        if (count($row) < 2) continue;

                        $studentId = trim($row[0]);
                        $gpa = trim($row[1]);

                        if (empty($studentId) || empty($gpa)) continue;

                        $user = \App\Models\User::where('student_id', $studentId)->first();

                        if ($user) {
                            $user->update(['gpa' => $gpa]);
                            $updated++;
                        } else {
                            $errors[] = "الطالب برقم {$studentId} غير موجود";
                        }
                    }

                    fclose($file);

                    // Clean up the uploaded file
                    @unlink($filePath);

                    $message = "تم تحديث معدلات {$updated} طالب.";
                    if (!empty($errors)) {
                        $message .= "\n\nأخطاء: " . implode(', ', array_slice($errors, 0, 5));
                        if (count($errors) > 5) {
                            $message .= " وغيرها...";
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('تم رفع المعدلات')
                        ->body($message)
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('admin')),
            Actions\Action::make('delete_all_students')
                ->label('حذف جميع الطلبة')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('حذف جميع الطلبة')
                ->modalDescription('هل أنت متأكد من رغبتك في حذف جميع الطلبة؟ سيتم حذف جميع المستخدمين الذين لديهم معدل ورقم جامعي. هذا الإجراء لا يمكن التراجع عنه.')
                ->modalSubmitActionLabel('نعم، احذف جميع الطلبة')
                ->action(function () {
                    $deletedCount = \App\Models\User::whereNotNull('gpa')
                        ->whereNotNull('student_id')
                        ->delete();

                    \Filament\Notifications\Notification::make()
                        ->title('تم حذف الطلبة')
                        ->body("تم حذف {$deletedCount} طالب.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('admin')),
        ];
    }
}
