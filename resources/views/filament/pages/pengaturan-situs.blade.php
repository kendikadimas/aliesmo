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
            console.group('[Admin Debug] PengaturanSitus');
            console.log('Page loaded at:', new Date().toISOString());

            if (window.Livewire) {
                console.log('Livewire: detected');

                Livewire.hook('request', ({ url, options, payload }) => {
                    console.log('[Request]', { url, payload });
                });

                Livewire.hook('response', ({ response }) => {
                    console.log('[Response]', { status: response.status });
                });

                document.querySelectorAll('input, select, textarea').forEach((el, i) => {
                    el.addEventListener('change', function(e) {
                        const name = e.target.getAttribute('wire:model') || e.target.name || `input-${i}`;
                        const value = e.target.type === 'file'
                            ? Array.from(e.target.files).map(f => `${f.name} (${f.size} bytes)`)
                            : e.target.value;
                        console.log(`[Change] ${name}:`, value);
                    });
                });

                const form = document.querySelector('form[wire\\:submit]');
                if (form) {
                    form.addEventListener('submit', function() {
                        console.log('[Submit] Form submitted');
                    });
                }
            } else {
                console.warn('Livewire: NOT found');
            }

            console.groupEnd();
        });
    </script>
</x-filament-panels::page>
