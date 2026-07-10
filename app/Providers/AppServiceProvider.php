<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_START,
            fn(): string => '<link rel="stylesheet" href="' . asset('css/admin.css') . '">',
        );

        $this->registerModelDebugLogging();
        $this->registerLivewireDebugLogging();
    }

    private function registerLivewireDebugLogging(): void
    {
        \Livewire\Livewire::listen('dehydrate', function ($component, $context) {
            $name = $component->getName();
            if (str_contains($name, 'PengaturanSitus') || str_contains($name, 'pengaturan')) {
                $props = [];
                foreach (get_object_vars($component) as $k => $v) {
                    if (is_object($v)) $props[$k] = get_class($v);
                    elseif (is_array($v)) $props[$k] = 'array(' . count($v) . ')';
                    else $props[$k] = $v;
                }
                Log::info("[Livewire] Dehydrate: {$name}", $props);
            }
        });

        \Livewire\Livewire::listen('hydrate', function ($component, $memo, $context) {
            $name = $component->getName();
            if (str_contains($name, 'PengaturanSitus') || str_contains($name, 'pengaturan')) {
                $props = [];
                foreach (get_object_vars($component) as $k => $v) {
                    if (is_object($v)) $props[$k] = get_class($v);
                    elseif (is_array($v)) $props[$k] = 'array(' . count($v) . ')';
                    else $props[$k] = $v;
                }
                Log::info("[Livewire] Hydrate: {$name}", $props);
            }
        });

        \Livewire\Livewire::listen('update', function ($component, $path, $value) {
            $name = $component->getName();
            if (str_contains($name, 'PengaturanSitus') || str_contains($name, 'pengaturan')) {
                Log::info("[Livewire] Update property: {$name}", [
                    'path' => $path,
                    'value_type' => gettype($value),
                    'value' => is_object($value) ? get_class($value) : (is_array($value) ? json_encode($value) : $value),
                ]);
            }
        });
    }

    /**
     * Register debug logging for all Eloquent models.
     * Logs create, update, delete operations with before/after values.
     */
    private function registerModelDebugLogging(): void
    {
        $models = [
            \App\Models\Product::class,
            \App\Models\ProductVariant::class,
            \App\Models\ProductImage::class,
            \App\Models\Category::class,
            \App\Models\Banner::class,
            \App\Models\Order::class,
            \App\Models\OrderItem::class,
            \App\Models\Review::class,
            \App\Models\Coupon::class,
            \App\Models\User::class,
            \App\Models\SiteSetting::class,
            \App\Models\HomepageVideo::class,
            \App\Models\ProductVideo::class,
            \App\Models\StockMovement::class,
            \App\Models\Payment::class,
        ];

        foreach ($models as $model) {
            $modelName = class_basename($model);

            $model::creating(function ($record) use ($modelName) {
                Log::info("[ADMIN] Creating {$modelName}", [
                    'attributes' => $record->getAttributes(),
                    'user_id' => auth()->id(),
                ]);
            });

            $model::created(function ($record) use ($modelName) {
                Log::info("[ADMIN] Created {$modelName} #{$record->getKey()}", [
                    'id' => $record->getKey(),
                ]);
            });

            $model::updating(function ($record) use ($modelName) {
                Log::info("[ADMIN] Updating {$modelName} #{$record->getKey()}", [
                    'dirty' => $record->getDirty(),
                    'original' => $record->getOriginal(),
                    'user_id' => auth()->id(),
                ]);
            });

            $model::updated(function ($record) use ($modelName) {
                Log::info("[ADMIN] Updated {$modelName} #{$record->getKey()}", [
                    'changes' => $record->getChanges(),
                ]);
            });

            $model::deleting(function ($record) use ($modelName) {
                Log::warning("[ADMIN] Deleting {$modelName} #{$record->getKey()}", [
                    'attributes' => $record->getAttributes(),
                    'user_id' => auth()->id(),
                ]);
            });

            $model::deleted(function ($record) use ($modelName) {
                Log::warning("[ADMIN] Deleted {$modelName} #{$record->getKey()}");
            });
        }
    }
}
