<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;

class PengaturanSitus extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    use RestrictsFileUploadsToSchemaComponents;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.pengaturan-situs';

    public static function getNavigationGroup(): ?string
    {
        return 'Konten';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengaturan Situs';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key');

        Log::info('[PengaturanSitus] mount() - DB settings loaded', [
            'payment_qris_image' => $settings['payment_qris_image'] ?? 'NULL',
            'payment_banks' => $settings['payment_banks'] ?? 'NULL',
            'all_keys' => $settings->keys()->toArray(),
        ]);

        $textKeys = [
            'announcement_1', 'announcement_1_link',
            'announcement_2', 'announcement_2_link',
            'announcement_3', 'announcement_3_link',
            'stat_kemeja_terjual', 'stat_kota', 'stat_kualitas', 'stat_garansi',
            'contact_email', 'contact_phone', 'contact_address', 'contact_whatsapp',
            'social_instagram', 'social_facebook', 'social_tiktok', 'social_youtube',
            'payment_qris_name',
        ];

        $data = [];
        foreach ($textKeys as $key) {
            $data[$key] = $settings[$key] ?? '';
        }

        $banksJson = $settings['payment_banks'] ?? '[]';
        $banks = json_decode($banksJson, true) ?? [];
        $data['payment_banks'] = array_values($banks);

        $qrisImage = $settings['payment_qris_image'] ?? '';
        $data['payment_qris_image'] = $qrisImage ? [$qrisImage] : [];

        $data['payment_cod_enabled'] = (bool) ($settings['payment_cod_enabled'] ?? false);

        Log::info('[PengaturanSitus] mount() - form fill data', [
            'payment_qris_image' => $data['payment_qris_image'],
            'payment_banks' => $data['payment_banks'],
        ]);

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengumuman (Announcement Bar)')
                    ->description('Teks yang muncul di banner pengumuman bagian atas website.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('announcement_1')
                            ->label('Pengumuman 1'),
                        TextInput::make('announcement_1_link')
                            ->label('Link Pengumuman 1'),
                        TextInput::make('announcement_2')
                            ->label('Pengumuman 2'),
                        TextInput::make('announcement_2_link')
                            ->label('Link Pengumuman 2'),
                        TextInput::make('announcement_3')
                            ->label('Pengumuman 3'),
                        TextInput::make('announcement_3_link')
                            ->label('Link Pengumuman 3'),
                    ]),

                Section::make('Statistik')
                    ->description('Angka-angka yang ditampilkan di halaman utama.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('stat_kemeja_terjual')
                            ->label('Kemeja Terjual'),
                        TextInput::make('stat_kota')
                            ->label('Kota Terjangkau'),
                        TextInput::make('stat_kualitas')
                            ->label('Kualitas (%)'),
                        TextInput::make('stat_garansi')
                            ->label('Garansi (hari)'),
                    ]),

                Section::make('Kontak & Sosial Media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email'),
                        TextInput::make('contact_phone')
                            ->label('No. Telepon'),
                        TextInput::make('contact_whatsapp')
                            ->label('No. WhatsApp'),
                        TextInput::make('contact_address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                        TextInput::make('social_instagram')
                            ->label('Instagram'),
                        TextInput::make('social_facebook')
                            ->label('Facebook'),
                        TextInput::make('social_tiktok')
                            ->label('TikTok'),
                        TextInput::make('social_youtube')
                            ->label('YouTube'),
                    ]),

                Section::make('Pembayaran')
                    ->description('Kelola rekening bank, QRIS, dan opsi COD.')
                    ->schema([
                        Repeater::make('payment_banks')
                            ->label('Rekening Bank')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->placeholder('Contoh: BCA, Mandiri, BNI'),
                                TextInput::make('account_no')
                                    ->label('No. Rekening')
                                    ->required(),
                                TextInput::make('account_name')
                                    ->label('Nama Pemilik')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Tambah Rekening')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string =>
                                ($state['bank_name'] ?? 'Bank Baru') . ' — ' . ($state['account_no'] ?? '')
                            ),

                        FileUpload::make('payment_qris_image')
                            ->label('Gambar QRIS')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->directory('payment')
                            ->visibility('public')
                            ->disk('public')
                            ->helperText('Upload gambar QR code QRIS. Format JPG, PNG, atau WebP, maks. 2MB.'),

                        TextInput::make('payment_qris_name')
                            ->label('Nama QRIS')
                            ->placeholder('Contoh: Aliesmo Store'),

                        Toggle::make('payment_cod_enabled')
                            ->label('Aktifkan COD (Bayar di Tempat)'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Log::info('[PengaturanSitus] save() - form getState()', [
            'full_state' => $state,
            'payment_qris_image_type' => gettype($state['payment_qris_image'] ?? null),
            'payment_qris_image_value' => $state['payment_qris_image'] ?? 'NULL',
        ]);

        $textKeys = [
            'announcement_1', 'announcement_1_link',
            'announcement_2', 'announcement_2_link',
            'announcement_3', 'announcement_3_link',
            'stat_kemeja_terjual', 'stat_kota', 'stat_kualitas', 'stat_garansi',
            'contact_email', 'contact_phone', 'contact_address', 'contact_whatsapp',
            'social_instagram', 'social_facebook', 'social_tiktok', 'social_youtube',
            'payment_qris_name',
        ];

        foreach ($textKeys as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $state[$key] ?? '',
                    'label' => $key,
                    'type' => 'string',
                    'group' => 'general',
                ]
            );
        }

        $banks = array_values($state['payment_banks'] ?? []);
        SiteSetting::updateOrCreate(
            ['key' => 'payment_banks'],
            [
                'value' => json_encode($banks),
                'label' => 'payment_banks',
                'type' => 'json',
                'group' => 'payment',
            ]
        );

        $qrisValue = $state['payment_qris_image'] ?? [];
        Log::info('[PengaturanSitus] save() - QRIS before processing', [
            'type' => gettype($qrisValue),
            'value' => $qrisValue,
        ]);

        if (is_array($qrisValue)) {
            $qrisValue = $qrisValue[0] ?? '';
        }

        Log::info('[PengaturanSitus] save() - QRIS after processing', [
            'final_value' => $qrisValue,
        ]);

        SiteSetting::updateOrCreate(
            ['key' => 'payment_qris_image'],
            [
                'value' => $qrisValue ?? '',
                'label' => 'payment_qris_image',
                'type' => 'string',
                'group' => 'payment',
            ]
        );

        SiteSetting::updateOrCreate(
            ['key' => 'payment_cod_enabled'],
            [
                'value' => ($state['payment_cod_enabled'] ?? false) ? '1' : '0',
                'label' => 'payment_cod_enabled',
                'type' => 'boolean',
                'group' => 'payment',
            ]
        );

        Log::info('[PengaturanSitus] save() - completed');

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
