# Agents Brief — AIA v3.0.0

- Entry: `ai-inventory-agent.php`
- Core: `includes/Core/Plugin.php`
- REST: `aia/v1` with `inventory`, `chat`, `reports`
- Admin UI: templates under `templates/admin`, assets under `assets`
- Add features by extending `Core\Plugin` (more services/modules can be added later)
- Keep provider code under `includes/API` and `includes/AI` when implementing