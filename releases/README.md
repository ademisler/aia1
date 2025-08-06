# AI Inventory Agent - Releases

## Latest Version: 1.0.2

### Download
- [ai-inventory-agent-v1.0.2.zip](ai-inventory-agent-v1.0.2.zip) - Latest stable release

### Installation
1. Download the latest ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Archive
Older versions are available in the [archive](archive/) folder.

### Version History

#### v1.0.2 (2025-01-08) - CRITICAL BUG FIXES
- **FIXED**: Fatal error preventing plugin activation (duplicate method declaration)
- **FIXED**: PHP parse error in InventoryContext.php (regex pattern syntax)
- **RESOLVED**: All PHP syntax errors that prevented plugin activation
- Plugin now activates successfully without errors

#### v1.0.1 (2025-01-08)
- WooCommerce compatibility improvements
- Enhanced multisite support
- Plugin stability fixes
- Better error handling

#### v1.0.0 (2024-01-15)
- Initial public release
- Full feature set
- Production ready

### System Requirements
- WordPress 5.0+
- PHP 7.4+
- WooCommerce 5.0+
- MySQL 5.7+

### Upgrade Instructions
1. Backup your site before upgrading
2. Deactivate the old version
3. Delete the old plugin folder
4. Install the new version
5. Reactivate the plugin

### Support
- [Documentation](../docs/)
- [User Guide](../docs/USER_GUIDE.md)
- [Developer Guide](../docs/DEVELOPER_GUIDE.md)
- [Changelog](../CHANGELOG.md)

### License
GPL v2 or later