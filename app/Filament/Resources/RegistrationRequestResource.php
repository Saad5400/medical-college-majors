<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationRequestResource\Pages\CreateRegistrationRequest;
use App\Filament\Resources\RegistrationRequestResource\Pages\EditRegistrationRequest;
use App\Filament\Resources\RegistrationRequestResource\Pages\ListRegistrationRequests;
use App\Models\Major;
use App\Models\RegistrationRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationRequestResource extends Resource
{
    protected static ?string $model = RegistrationRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';

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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('بيانات الطالب')
                    ->schema([
                        TextEntry::make('user_name')
                            ->label('الاسم')
                            ->state(auth()->user()->name)
                            ->disabled(),
                        TextEntry::make('user_email')
                            ->label('البريد الإلكتروني')
                            ->state(auth()->user()->email)
                            ->disabled(),
                        TextEntry::make('user_student_id')
                            ->label('الرقم الجامعي')
                            ->state(auth()->user()->student_id)
                            ->disabled(),
                        TextEntry::make('user_gpa')
                            ->label('المعدل')
                            ->state(auth()->user()->gpa)
                            ->disabled(),
                    ])
                    ->visible(fn () => ! auth()->user()->hasRole('admin'))
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->required(),
                ...static::getFormFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'majorRegistrationRequests.major']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->sortable(),
                TextColumn::make('user.gpa')
                    ->label('المعدل')
                    ->sortable(),
                TextColumn::make('المسارات')
                    ->getStateUsing(fn ($record) => $record->majorRegistrationRequests->pluck('major.name'))
                    ->label('رغبات التسكين')
                    ->searchable(),
            ])
            ->defaultSort('user.gpa', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
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
            'index' => ListRegistrationRequests::route('/'),
            'create' => CreateRegistrationRequest::route('/create'),
            'edit' => EditRegistrationRequest::route('/{record}/edit'),
        ];
    }

    public static function getFormFields(): array
    {
        return [
            Repeater::make('majorRegistrationRequests')
                ->columnSpanFull()
                ->label('رغبات التسكين')
                ->relationship('majorRegistrationRequests')
                ->live()
                ->deletable(false)
                ->addable(false)
                ->minItems(fn () => Major::query()->count())
                ->maxItems(fn () => Major::query()->count())
                ->defaultItems(fn () => Major::query()->count())
                ->schema([
                    Hidden::make('sort')
                        ->label('ترتيب')
                        ->default(function (Get $get, $component) {
                            $requests = $get('data.majorRegistrationRequests', true);
                            $path = explode('.', $component->getStatePath())[2];

                            return array_search($path, array_keys($requests));
                        })
                        ->required(),
                    Select::make('major_id')
                        ->label(function (Get $get, $component): ?string {
                            return 'الرغبة '.($component->getParentRepeaterItemIndex() + 1);
                        })
                        ->live()
                        ->relationship('major', 'name')
                        ->options(function (Get $get) {
                            // Retrieve current requests to exclude already selected majors
                            $requests = $get('data.majorRegistrationRequests', true);
                            $requests = array_values($requests);

                            $selectedIds = array_map(fn ($request) => $request['major_id'], $requests);
                            $selectedIds = array_filter($selectedIds, fn ($id) => $id !== null);

                            return Major::query()
                                ->whereNotIn('id', $selectedIds)
                                ->get()
                                ->mapWithKeys(fn ($major) => [$major->id => $major->name]);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
        ];
    }
}
