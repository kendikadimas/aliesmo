<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[PengaturanSitus] Page loaded');

            if (window.Livewire) {
                console.log('[PengaturanSitus] Livewire detected');

                Livewire.on('form-submitted', () => {
                    console.log('[PengaturanSitus] Form submitted');
                });

                Livewire.on('notification-sent', () => {
                    console.log('[PengaturanSitus] Notification sent - save completed');
                });

                document.addEventListener('livewire:init', () => {
                    console.log('[PengaturanSitus] Livewire initialized');
                });

                document.addEventListener('livewire:navigate', () => {
                    console.log('[PengaturanSitus] Navigation occurred');
                });
            } else {
                console.warn('[PengaturanSitus] Livewire not found');
            }

            const form = document.querySelector('form[wire\\:submit]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('[PengaturanSitus] Form submit event triggered');
                });
            }

            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach((input, index) => {
                input.addEventListener('change', function(e) {
                    console.log(`[PengaturanSitus] File input ${index} changed:`, {
                        name: e.target.name,
                        files: Array.from(e.target.files).map(f => ({
                            name: f.name,
                            size: f.size,
                            type: f.type
                        }))
                    });
                });
            });
        });
    </script>
</x-filament-panels::page>
