# AI Inventory Agent (AIA) v3.1.x

Clean, modern AI-powered inventory assistant for WooCommerce with professional admin UI and robust REST API.

## Highlights (3.1.x)
- Providers: OpenAI (chat, connectivity test) and Gemini (test stub); Dummy fallback
- Inventory: Summary metrics, low-stock list with pagination and category filter
- Reports: JSON summary and Low Stock CSV export
- Dashboard: Low Stock card (Load more, filter), Trend chart (Chart.js)
- Chat: Local history, copy/clear, rate-limited REST

## REST API
- `GET /wp-json/aia/v1/inventory` – Metrics (total/low/out)
- `GET /wp-json/aia/v1/inventory/low?limit=&page=&category=` – Low stock items
- `GET /wp-json/aia/v1/categories` – Product categories (slug,name)
- `GET /wp-json/aia/v1/metrics/trend` – Last 7 days order counts
- `POST /wp-json/aia/v1/chat` – AI Chat response (rate-limited)
- `GET /wp-json/aia/v1/provider/test` – Provider connectivity test
- `GET /wp-json/aia/v1/reports/summary.json` – JSON report
- `GET /wp-json/aia/v1/reports/lowstock.csv` – CSV export

## Requirements
- WordPress 6.0+, WooCommerce 8.0+, PHP 8.0+

## Install
- Upload `ai-inventory-agent-v3.1.1.zip` via WP Admin → Plugins → Upload → Activate

## Development
- Entry: `ai-inventory-agent.php`
- Core: `includes/Core/Plugin.php`, `includes/Core/Inventory.php`
- Providers: `includes/API/{OpenAIProvider,GeminiProvider,AIProviderInterface,DummyProvider}.php`
- Templates: `templates/admin/*`
- Assets: `assets/css/admin.css`, `assets/js/admin.js`

## Notes
- Provider key yoksa Dummy devreye girer; chat hata durumunda dummy yanıtı döner.
- Rate limit chat endpointinde uygulanır (10/dk).

## Roadmap
- Provider-specific ayarlar ve model listesi
- Gelişmiş raporlar (tarih filtreli grafikleri admin’de göstermek)
- Supplier paneli ve stok hareketleri görünümü

## License
GPL v2+