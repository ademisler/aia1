# AI Inventory Agent (AIA) v3.0.0

Clean, modern AI-powered inventory assistant for WooCommerce with professional admin UI and robust REST API.

## Features (Initial v3)
- Professional, unified admin UI (Dashboard, Chat, Reports, Settings)
- REST API: `/wp-json/aia/v1/{inventory|chat|reports}`
- Minimal, extensible core with autoloading and clean hooks

## Requirements
- WordPress 6.0+
- WooCommerce 8.0+
- PHP 8.0+

## Install
- Upload the plugin folder as a zip or place into `wp-content/plugins`
- Activate the plugin

## Development
- Entry: `ai-inventory-agent.php`
- Core: `includes/Core/Plugin.php`
- Templates: `templates/admin/*`
- Assets: `assets/css/admin.css`, `assets/js/admin.js`

## Roadmap
- Provider configuration (OpenAI/Gemini) + connection tests
- Inventory data layer and live metrics
- Report generation and export
- Chat memory and context-aware responses

## License
GPL v2+