<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationRequestResource\Pages;
use App\Filament\Resources\RegistrationRequestResource\RelationManagers;
use App\Models\Major;
use App\Models\RegistrationRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistrationRequestResource extends Resource
{
    protected static ?string $model = RegistrationRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $modelLabel = 'طلب تسجيل';
    protected static ?string $pluralModelLabel = 'طلبات التسجيل';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()->hasRole('admin'))
                    ->required(),
                ...static::getFormFields()
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
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطالب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.gpa')
                    ->label('المعدل')
                    ->sortable(),
                Tables\Columns\TextColumn::make('المسارات')
                    ->getStateUsing(fn($record) => $record->majorRegistrationRequests->pluck('major.name'))
                    ->label('رغبات التسكين')
                    ->searchable(),
            ])
            ->defaultSort('user.gpa', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrationRequests::route('/'),
            'create' => Pages\CreateRegistrationRequest::route('/create'),
            'edit' => Pages\EditRegistrationRequest::route('/{record}/edit'),
        ];
    }

    public static function getFormFields(): array
    {
        return [
            Forms\Components\Repeater::make('majorRegistrationRequests')
                ->columnSpanFull()
                ->label('رغبات التسكين')
                ->relationship('majorRegistrationRequests')
                ->live()
                ->deletable(false)
                ->addable(false)
                ->minItems(fn() => Major::query()->count())
                ->maxItems(fn() => Major::query()->count())
                ->defaultItems(fn() => Major::query()->count())
                ->schema([
                    Forms\Components\Hidden::make('sort')
                        ->label('ترتيب')
                        ->default(function (Forms\Get $get, $component) {
                            $requests = $get('data.majorRegistrationRequests', true);
                            $path = explode('.', $component->getStatePath())[2];
                            return array_search($path, array_keys($requests));
                        })
                        ->required(),
                    Forms\Components\Select::make('major_id')
                        ->label('')
                        ->live()
                        ->relationship('major', 'name')
                        ->options(function (Forms\Get $get) {
                            // Retrieve current requests to exclude already selected majors
                            $requests = $get('data.majorRegistrationRequests', true);
                            $requests = array_values($requests);

                            $selectedIds = array_map(fn($request) => $request['major_id'], $requests);
                            $selectedIds = array_filter($selectedIds, fn($id) => $id !== null);

                            return Major::query()
                                ->whereNotIn('id', $selectedIds)
                                ->get()
                                ->mapWithKeys(fn($major) => [$major->id => $major->name]);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
        ];
    }
}
