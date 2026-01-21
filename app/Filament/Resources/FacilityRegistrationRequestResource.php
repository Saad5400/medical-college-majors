<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\CreateFacilityRegistrationRequest;
use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\EditFacilityRegistrationRequest;
use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\ListFacilityRegistrationRequests;
use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\Specialization;
use App\Models\TrackSpecialization;
use App\Settings\RegistrationSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FacilityRegistrationRequestResource extends Resource
{
    protected static ?string $model = FacilityRegistrationRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'طلب تسجيل منشأة';

    protected static ?string $pluralModelLabel = 'طلبات تسجيل المنشآت';

    public static function canCreate(): bool
    {
        $user = auth()->user();

        // Admins can always create
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if facility registration is open
        $settings = app(RegistrationSettings::class);
        if (! $settings->facility_registration_open) {
            return false;
        }

        // User must have an assigned track
        if (! $user->track_id) {
            return false;
        }

        return true;
    }

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
                            ->state(auth()->user()->name),
                        TextEntry::make('user_track')
                            ->label('المسار')
                            ->state(auth()->user()->track?->name ?? 'غير محدد'),
                        TextEntry::make('user_gpa')
                            ->label('المعدل')
                            ->state(auth()->user()->gpa),
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
                Select::make('month_index')
                    ->searchable()
                    ->preload()
                    ->label('الشهر')
                    ->options(function () {
                        $options = [];
                        for ($i = 1; $i <= 12; $i++) {
                            $options[$i] = "الشهر {$i}";
                        }

                        return $options;
                    })
                    ->required()
                    ->live(),
                ...static::getWishFormFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'assignedFacility', 'wishes']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.gpa')
                    ->label('المعدل')
                    ->sortable(),
                TextColumn::make('month_index')
                    ->label('الشهر')
                    ->formatStateUsing(fn (int $state): string => "الشهر {$state}")
                    ->sortable(),
                TextColumn::make('assignedFacility.name')
                    ->label('المنشأة المعينة')
                    ->placeholder('لم يتم التعيين'),
                TextColumn::make('wishes_count')
                    ->label('عدد الرغبات')
                    ->counts('wishes'),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListFacilityRegistrationRequests::route('/'),
            'create' => CreateFacilityRegistrationRequest::route('/create'),
            'edit' => EditFacilityRegistrationRequest::route('/{record}/edit'),
        ];
    }

    public static function getWishFormFields(): array
    {
        return [
            Repeater::make('wishes')
                ->columnSpanFull()
                ->label('رغبات المنشأة')
                ->relationship('wishes')
                ->live()
                ->deletable(false)
                ->addable(false)
                ->minItems(5)
                ->maxItems(5)
                ->defaultItems(5)
                ->schema([
                    Hidden::make('priority')
                        ->default(function (Get $get, $component) {
                            $wishes = $get('data.wishes', true);
                            $path = explode('.', $component->getStatePath())[2];

                            return array_search($path, array_keys($wishes)) + 1;
                        })
                        ->required(),
                    Toggle::make('is_custom')
                        ->label('منشأة مخصصة')
                        ->helperText('اختر منشأة غير مسجلة في النظام (لن تدخل في المنافسة)')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            if ($state) {
                                $set('is_competitive', false);
                                // Mark all subsequent wishes as non-competitive
                                // This is handled in the model/controller
                            }
                        }),
                    Select::make('facility_id')
                        ->label(function (Get $get, $component): string {
                            $priority = ($component->getParentRepeaterItemIndex() ?? 0) + 1;

                            return "الرغبة {$priority}";
                        })
                        ->options(function (Get $get) {
                            $monthIndex = $get('../../month_index');
                            $user = auth()->user();

                            if (! $monthIndex || ! $user->track_id) {
                                return [];
                            }

                            // Get the specialization for this month from the user's track
                            $trackSpec = TrackSpecialization::where('track_id', $user->track_id)
                                ->where('month_index', $monthIndex)
                                ->first();

                            if (! $trackSpec) {
                                return Facility::query()->pluck('name', 'id');
                            }

                            // Get facilities that match the specialization type
                            $spec = $trackSpec->specialization;

                            return Facility::query()
                                ->where('type', $spec->facility_type)
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => ! $get('is_custom'))
                        ->required(fn (Get $get) => ! $get('is_custom')),
                    TextInput::make('custom_facility_name')
                        ->label('اسم المنشأة المخصصة')
                        ->visible(fn (Get $get) => $get('is_custom'))
                        ->required(fn (Get $get) => $get('is_custom')),
                    Select::make('specialization_id')
                        ->label('التخصص (للأشهر الاختيارية)')
                        ->options(Specialization::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(function (Get $get) {
                            // Only show for elective months
                            $monthIndex = $get('../../month_index');
                            $user = auth()->user();

                            if (! $monthIndex || ! $user->track) {
                                return false;
                            }

                            $electiveMonths = $user->track->elective_months ?? [];

                            return in_array($monthIndex, $electiveMonths);
                        }),
                    TextInput::make('custom_specialization_name')
                        ->label('اسم التخصص المخصص')
                        ->visible(fn (Get $get) => $get('is_custom')),
                    Hidden::make('is_competitive')
                        ->default(true),
                ]),
        ];
    }
}
