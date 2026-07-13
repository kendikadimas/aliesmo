<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 15;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Artikel';
    }

    public static function getModelLabel(): string
    {
        return 'Artikel';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Artikel';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Artikel')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()))
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->unique(Article::class, ignoreRecord: true)
                            ->helperText('Otomatis terisi dari judul. Bisa diubah manual.')
                            ->maxLength(255),

                        TextInput::make('author')
                            ->label('Penulis')
                            ->placeholder('Nama penulis artikel')
                            ->maxLength(100),

                        FileUpload::make('thumbnail')
                            ->label('Thumbnail / Cover')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120) // 5MB
                            ->disk('public')
                            ->directory('articles')
                            ->visibility('public')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('675')
                            ->helperText('Rasio ideal 16:9. Maks 5MB.'),

                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Ringkasan singkat artikel yang muncul di halaman listing. Maks 500 karakter.'),
                    ])
                    ->columns(2),

                Section::make('Konten')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Isi Artikel')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3',
                                'bulletList', 'orderedList', 'blockquote',
                                'link', 'attachFiles',
                                'undo', 'redo',
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('articles/content')
                            ->columnSpanFull(),
                    ]),

                Section::make('Penerbitan')
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(false)
                            ->helperText('Aktifkan untuk menampilkan artikel di halaman publik.'),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->nullable()
                            ->helperText('Kosongkan untuk langsung terbit saat dipublikasikan.'),

                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu.'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Cover')
                    ->disk('public')
                    ->height(48)
                    ->extraImgAttributes(['style' => 'object-fit:cover;border-radius:6px;aspect-ratio:16/9;width:86px']),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->description(fn (Article $record) => $record->author ?? ''),

                TextColumn::make('excerpt')
                    ->label('Ringkasan')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('is_published')
                    ->label('Publik')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Terbit')
                    ->dateTime('d M Y')
                    ->placeholder('Belum diatur')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status')
                    ->trueLabel('Dipublikasikan')
                    ->falseLabel('Draft'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit'   => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
