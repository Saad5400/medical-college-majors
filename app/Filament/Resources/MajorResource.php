<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages\CreateMajor;
use App\Filament\Resources\MajorResource\Pages\EditMajor;
use App\Filament\Resources\MajorResource\Pages\ListMajors;
use App\Filament\Resources\MajorResource\RelationManagers\UsersRelationManager;
use App\Models\Major;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'مسار';

    protected static ?string $pluralModelLabel = 'المسارات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('المسار')
                    ->required()
                    ->maxLength(255),
                TextInput::make('max_users')
                    ->label('الحد الأقصى لعدد الطلاب')
                    ->required()
                    ->integer()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('المسار')
                    ->searchable(),
                TextColumn::make('max_users')
                    ->label('الحد الأقصى لعدد الطلاب')
                    ->sortable(),
                TextColumn::make('first_choice_users_count')
                    ->label('عدد الطلاب الذين إختاروه كأول رغبة')
                    ->getStateUsing(function (Major $record) {
                        $count = 0;
                        foreach ($record->registrationRequests as $registrationRequest) {
                            if ($registrationRequest->majors()->orderByPivot('sort')->first()->id === $record->id) {
                                $count++;
                            }
                        }

                        return $count;
                    })
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('عدد الطلاب')
                    ->getStateUsing(function (Major $record) {
                        return $record->users()->count();
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('distribute')
                    ->label('توزيع الطلاب على المسارات')
                    ->action(function () {
                        // Reset all users' major_id
                        User::query()->update(['major_id' => null]);

                        $users = User::query()
                            ->orderBy('gpa', 'desc')
                            ->get();

                        /** @var User $user */
                        foreach ($users as $user) {
                            if ($user->registrationRequests()->count() === 0) {
                                continue;
                            }

                            $registrationRequest = $user->registrationRequests()->latest()->first();
                            $majors = $registrationRequest->majors()->orderByPivot('sort')->get();

                            foreach ($majors as $major) {
                                if ($major->users()->count() < $major->max_users) {
                                    $user->major()->associate($major);
                                    $user->save();

                                    break;
                                }
                            }
                        }

                        Notification::make()
                            ->title('تم توزيع الطلاب على المسارات')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMajors::route('/'),
            'create' => CreateMajor::route('/create'),
            'edit' => EditMajor::route('/{record}/edit'),
        ];
    }
}
