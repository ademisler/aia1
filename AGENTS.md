# AI Inventory Agent — Agent Brief (v2.4.0)

This document equips autonomous agents with the essentials to understand, build and troubleshoot the AIA WordPress plugin quickly.

## Overview
- Type: WordPress + WooCommerce plugin (admin-focused)
- Entry: `ai-inventory-agent.php`
- Namespace: `AIA\*`
- Tech: PHP 8+, WP 6+, WC 8+, JS (ES6 modules), CSS (single combined), REST & admin-ajax
- Core purpose: Inventory analysis, forecasting, alerts, reporting, AI chat (OpenAI/Gemini)

## Key Directories
- `includes/Core/`: bootstrap, services, modules manager, settings, assets, memory
- `includes/Admin/`: admin menu/pages, settings UI, AJAX handlers, enqueues
- `includes/Modules/`: `InventoryAnalysis`, `AIChat`, `DemandForecasting`, `Notifications`, `Reporting`, `SupplierAnalysis`
- `includes/API/`: `AIProviderManager`, providers (`OpenAIProvider`, `GeminiProvider`), interface
- `templates/admin/`: page templates for Dashboard/Analysis/Chat/Alerts/Reports/Settings
- `assets/css/`: `aia-combined.css` (source of design system overrides)
- `assets/js/`: `aia-optimized.js` (UI app), `admin.js` (legacy), others

## Boot & Lifecycle
- `ai-inventory-agent.php` defines constants and initializes `AIA\Core\Plugin` after WC check
- `AIA\Core\Plugin` uses `ServiceContainer` -> registers services -> loads settings -> admin UI -> registers hooks
- Asset optimization: `AIA\Core\AssetOptimizer::init()` now called in bootstrap
- Modules initialized via `ModuleManager` (container-aware)

## Admin Assets
- Centralized in `AIA\Admin\AdminInterface::enqueue_admin_assets()`
- If setting `enable_asset_optimization` true (and not WP_DEBUG), AssetOptimizer combines/minifies
- Otherwise loads `assets/css/aia-combined.css` and `assets/js/aia-optimized.js`

## REST & AJAX
- REST (added v2.4.0):
  - `GET /wp-json/aia/v1/inventory` → inventory summary (perm: manage_woocommerce or view_woocommerce_reports)
  - `POST /wp-json/aia/v1/chat` → AI message (perm: manage_woocommerce or edit_shop_orders)
  - `GET /wp-json/aia/v1/reports` → reports summary
- Admin-AJAX:
  - `aia_chat`, `aia_get_inventory_data`, plus settings handlers in AdminInterface
- Rate limiting: `includes/Utils/RateLimiter.php`

## Settings
- Access through `SettingsManager` (caching, validation)
- Use `SettingsManager::update_setting()` or `SettingsManager::update_settings()` to keep caches coherent
- Key options: `ai_provider`, `api_key`, thresholds, enable flags per module

## Providers
- `AIProviderManager` chooses provider based on settings; fallbacks supported
- Providers: `GeminiProvider`, `OpenAIProvider` with `test_connection()` and `generate_response()`

## Frontend (Admin UI)
- Design System Overrides appended in `aia-combined.css`: unified headers, buttons, cards, focus states, forms, responsive, dark mode
- `aia-optimized.js`: App orchestrates chat, forms, dropdowns, tabs, tooltips, global progress bar, a11y helpers, micro-interactions

## Common Tasks for Agents
- Add a module: Register in `ModuleManager::register_default_modules()`, create class under `includes/Modules`, add template if UI
- Add REST endpoint: Extend `rest_api_init` block in `Core/Plugin.php`
- Update settings UI: `AdminInterface::register_settings()` and `templates/admin/settings.php`
- Enqueue assets: Prefer `AdminInterface::enqueue_admin_assets()`; avoid duplicate enqueues from `Plugin`

## Debugging Tips
- Enable WP debug logging; look for logs prefixed with `AIA`
- Verify `AssetOptimizer` cache dir in uploads (`aia-cache`) permissions
- Check `ServiceContainer` service names if resolution errors
- Confirm WooCommerce and required capabilities when REST/AJAX returns 403

## Release Checklist
- Update version in `ai-inventory-agent.php` and badges in `README.md`
- Update `CHANGELOG.md`
- Build zip: include plugin root with all `includes`, `assets`, `templates`, `docs`
- Smoke test admin pages and REST endpoints