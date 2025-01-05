<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages;
use App\Filament\Resources\MajorResource\RelationManagers;
use App\Models\Major;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'مسار';
    protected static ?string $pluralModelLabel = 'المسارات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('المسار')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('max_users')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->label('المسار')
                    ->searchable(),
                Tables\Columns\TextColumn::make('max_users')
                    ->label('الحد الأقصى لعدد الطلاب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_choice_users_count')
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
                Tables\Columns\TextColumn::make('users_count')
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('distribute')
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
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMajors::route('/'),
            'create' => Pages\CreateMajor::route('/create'),
            'edit' => Pages\EditMajor::route('/{record}/edit'),
        ];
    }
}
