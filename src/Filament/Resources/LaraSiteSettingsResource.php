<?php

namespace LaraGrape\Filament\Resources;

use LaraGrape\Filament\Resources\SiteSettingsResource\Pages;
use LaraGrape\Models\SiteSettings;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CodeEditor;
use Illuminate\Support\Str;

class LaraSiteSettingsResource extends Resource
{
    protected static ?string $model = SiteSettings::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $modelLabel = 'Setting';

    protected static ?string $pluralModelLabel = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Design System';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Setting Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label('Key')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Unique identifier for this setting (e.g., site_name, header_logo_text)'),
                                Select::make('type')
                                    ->label('Type')
                                    ->options(SiteSettings::getTypes())
                                    ->required()
                                    ->live()
                                    ->default('text'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->helperText('Human-readable name (auto-generated from key if blank)'),
                                Select::make('group')
                                    ->label('Group')
                                    ->options(SiteSettings::getGroups())
                                    ->default('general'),
                            ]),
                    ]),
                Section::make('Value')
                    ->schema([
                        // Render the appropriate input based on type
                        TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'text')
                            ->helperText('Text value'),

                        Textarea::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'textarea')
                            ->rows(4)
                            ->helperText('Multi-line text value'),

                        Toggle::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'boolean')
                            ->helperText('Toggle on/off'),

                        ColorPicker::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['color']))
                            ->helperText('Hex color value'),

                        FileUpload::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'image')
                            ->image()
                            ->directory('site/settings')
                            ->helperText('Upload an image'),

                        Select::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'select')
                            ->options([])
                            ->helperText('Select from predefined options'),

                        Textarea::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['json', 'code']))
                            ->rows(6)
                            ->helperText('JSON or code value'),
                    ]),
                Section::make('Additional Info')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2)
                                    ->helperText('Optional description of what this setting does'),
                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Order within group (lower = first)'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'header' => 'primary',
                        'footer' => 'success',
                        'seo' => 'warning',
                        'social' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(SiteSettings::getGroups()),
                Tables\Filters\SelectFilter::make('type')
                    ->options(SiteSettings::getTypes()),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\LaraListSiteSettings::route('/'),
            'create' => Pages\LaraCreateSiteSettings::route('/create'),
            'edit' => Pages\LaraEditSiteSettings::route('/{record}/edit'),
        ];
    }
}
