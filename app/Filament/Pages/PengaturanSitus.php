<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PengaturanSitus extends Page implements HasForms
{
    use InteractsWithForms;

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

    // Announcement
    public ?string $announcement_1 = null;
    public ?string $announcement_1_link = null;
    public ?string $announcement_2 = null;
    public ?string $announcement_2_link = null;
    public ?string $announcement_3 = null;
    public ?string $announcement_3_link = null;

    // Stats
    public ?string $stat_kemeja_terjual = null;
    public ?string $stat_kota = null;
    public ?string $stat_kualitas = null;
    public ?string $stat_garansi = null;

    // General
    public ?string $contact_email = null;
    public ?string $contact_phone = null;
    public ?string $contact_address = null;
    public ?string $contact_whatsapp = null;
    public ?string $social_instagram = null;
    public ?string $social_facebook = null;
    public ?string $social_tiktok = null;
    public ?string $social_youtube = null;

    // Payment — banks sebagai array untuk Repeater
    public array $payment_banks = [];
    public ?string $payment_qris_image = null;
    public ?string $payment_qris_name = null;
    public bool $payment_cod_enabled = false;

    public function mount(): void
    {
        $settings = SiteSetting::all()->pluck('value', 'key');

        // Isi properties text biasa
        $textKeys = [
            'announcement_1', 'announcement_1_link',
            'announcement_2', 'announcement_2_link',
            'announcement_3', 'announcement_3_link',
            'stat_kemeja_terjual', 'stat_kota', 'stat_kualitas', 'stat_garansi',
            'contact_email', 'contact_phone', 'contact_address', 'contact_whatsapp',
            'social_instagram', 'social_facebook', 'social_tiktok', 'social_youtube',
            'payment_qris_image', 'payment_qris_name',
        ];

        foreach ($textKeys as $key) {
            if (isset($settings[$key])) {
                $this->$key = $settings[$key];
            }
        }

        // Banks dari JSON
        $banksJson = $settings['payment_banks'] ?? '[]';
        $this->payment_banks = json_decode($banksJson, true) ?? [];

        // COD boolean
        $this->payment_cod_enabled = (bool) ($settings['payment_cod_enabled'] ?? false);
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
            ]);
    }

    public function save(): void
    {
        // Text keys biasa
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
            SiteSetting::where('key', $key)->update(['value' => $this->$key ?? '']);
        }

        // Banks — simpan sebagai JSON
        SiteSetting::where('key', 'payment_banks')
            ->update(['value' => json_encode($this->payment_banks ?? [])]);

        // QRIS image — FileUpload return path string
        if ($this->payment_qris_image !== null) {
            SiteSetting::where('key', 'payment_qris_image')
                ->update(['value' => $this->payment_qris_image]);
        }

        // COD boolean
        SiteSetting::where('key', 'payment_cod_enabled')
            ->update(['value' => $this->payment_cod_enabled ? '1' : '0']);

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }
}
