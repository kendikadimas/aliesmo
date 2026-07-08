# Changelog

## [Unreleased]

### Admin Panel — Visual Overhaul
- **Sidebar dark mode**: forced dark background (`#0a0a0a`) via custom `public/css/admin.css`; logo inverted to white, menu items use subtle gray/white hover/active states.
- **Widget color coding**: each `StatsOverview` stat now shows a left accent border + soft gradient background matching its semantic color (success/info/warning/danger/primary) — makes data easier to scan at a glance.
- **SalesOverviewWidget** — all 5 stats now have explicit `->color()` calls, descriptive Indonesian labels, and conditional danger/warning based on thresholds.
- **RevenueChartWidget** — heading changed to "Revenue 30 Hari Terakhir" with description; moved from property to `getHeading()/getDescription()` methods to avoid Filament v5 `ChartWidget` static conflict.
- **TopSellingProductsWidget** — heading "Produk Terlaris", description "5 produk dengan jumlah terjual terbanyak".
- **PendingOrdersWidget** — existing color logic preserved (dynamic danger/warning/success).
- **LowStockWidget** — existing color logic preserved (warning/danger on thresholds).
- **Widget shadows & rounding** — applied in CSS for a cleaner card look.

### Technical
- **`public/css/admin.css`** — new file containing all admin custom CSS overrides.
- **`app/Providers/AppServiceProvider.php`** — registers `FilamentView::registerRenderHook` for `PanelsRenderHook::HEAD_START` to load `admin.css`.
- **`app/Providers/Filament/AdminPanelProvider.php`** — removed dark mode toggle, added `sidebarCollapsibleOnDesktop()`, changed font to Outfit.
- **Cache** — `php artisan optimize:clear` run to apply all changes.
