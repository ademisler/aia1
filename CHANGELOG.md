# Changelog

## 3.1.0 - Providers, Reports, Dashboard Enhancements
- Providers: OpenAI and Gemini provider sınıfları (OpenAI chat & models test, Gemini test stub)
- Settings: Model alanı; Test Connection sonuçları provider etiketiyle
- Inventory: Low stock listesi pagination ve kategori filtresi destekli
- REST: `/aia/v1/inventory/low`, `/aia/v1/provider/test`, `/aia/v1/reports/{summary.json,lowstock.csv}`
- Dashboard: Low Stock kartı, örnek Chart.js trend grafiği
- Chat: Mesaj geçmişi (localStorage), kopyala/temizle aksiyonları

## 3.0.0 - Fresh Rewrite
- Full clean rewrite from scratch with new architecture
- Minimal, robust core (autoloading, hooks, REST, admin UI)
- Professional unified styling and templates
- Placeholder implementations for AI and data layers (to be extended)