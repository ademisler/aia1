# Contributing to AI Inventory Agent

First off, thank you for considering contributing to AI Inventory Agent! It's people like you that make this plugin better for everyone.

## 📋 Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [How to Contribute](#how-to-contribute)
4. [Development Setup](#development-setup)
5. [Coding Standards](#coding-standards)
6. [Testing](#testing)
7. [Pull Request Process](#pull-request-process)
8. [Reporting Bugs](#reporting-bugs)
9. [Suggesting Features](#suggesting-features)

## Code of Conduct

This project adheres to the [WordPress Community Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/). By participating, you are expected to uphold this code.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/ai-inventory-agent.git`
3. Add upstream remote: `git remote add upstream https://github.com/original/ai-inventory-agent.git`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## How to Contribute

### Ways to Contribute

- 🐛 Report bugs
- 💡 Suggest new features
- 📝 Improve documentation
- 🌍 Add translations
- 🔧 Fix bugs
- ✨ Add new features
- 🎨 Improve UI/UX
- ⚡ Optimize performance

### Before You Start

1. Check existing issues to avoid duplicates
2. For major changes, open an issue first to discuss
3. Read the [Developer Guide](docs/DEVELOPER_GUIDE.md)
4. Understand the [AGENTS.md](AGENTS.md) guidelines

## Development Setup

### Prerequisites

- PHP 7.4+
- Composer
- Node.js 14+
- npm or yarn
- WordPress development environment
- WooCommerce plugin

### Local Setup

1. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Setup WordPress:**
   ```bash
   # Using wp-env (recommended)
   npm run wp-env start
   
   # Or use Local, XAMPP, or Docker
   ```

3. **Build assets:**
   ```bash
   npm run build
   ```

4. **Watch for changes:**
   ```bash
   npm run watch
   ```

### Environment Configuration

Create a `.env` file:
```env
OPENAI_API_KEY=your_api_key_here
WP_DEBUG=true
SCRIPT_DEBUG=true
```

## Coding Standards

### PHP Standards

Follow [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/):

```php
// Good
if ( ! function_exists( 'aia_function' ) ) {
    function aia_function( $param ) {
        return $param;
    }
}

// Bad
if(!function_exists('aia_function')){
    function aia_function($param){
        return $param;
    }
}
```

### JavaScript Standards

Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/):

```javascript
// Good
const myFunction = ( param ) => {
    return param;
};

// Bad
const myFunction = (param) => {
    return param
}
```

### CSS Standards

Follow [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/):

```css
/* Good */
.aia-button {
    padding: 10px 15px;
    background-color: #0073aa;
}

/* Bad */
.aia-button{
    padding:10px 15px;
    background-color:#0073aa;
}
```

### File Naming

- PHP files: `class-module-name.php`
- JS files: `module-name.js`
- CSS files: `module-name.css`
- Templates: `template-name.php`

## Testing

### PHP Tests

```bash
# Run all tests
composer test

# Run specific test
composer test -- --filter TestClassName

# Generate coverage report
composer test-coverage
```

### JavaScript Tests

```bash
# Run all tests
npm test

# Watch mode
npm test -- --watch

# Coverage
npm test -- --coverage
```

### End-to-End Tests

```bash
# Run E2E tests
npm run test:e2e

# Run in headed mode
npm run test:e2e -- --headed
```

### Writing Tests

PHP test example:
```php
class Test_Inventory_Analysis extends WP_UnitTestCase {
    public function test_stock_calculation() {
        $module = new InventoryAnalysis();
        $result = $module->calculate_total_stock_value();
        
        $this->assertIsNumeric( $result );
        $this->assertGreaterThanOrEqual( 0, $result );
    }
}
```

## Pull Request Process

### Before Submitting

1. **Update documentation** for any changed functionality
2. **Add tests** for new features
3. **Run all tests** and ensure they pass
4. **Check coding standards**: `composer phpcs`
5. **Update CHANGELOG.md** if applicable

### PR Guidelines

1. **Title Format**: `Type: Brief description`
   - Types: `Feature`, `Fix`, `Docs`, `Style`, `Refactor`, `Test`, `Chore`
   - Example: `Feature: Add bulk stock update functionality`

2. **Description Template**:
   ```markdown
   ## Description
   Brief description of changes
   
   ## Type of Change
   - [ ] Bug fix
   - [ ] New feature
   - [ ] Breaking change
   - [ ] Documentation update
   
   ## Testing
   - [ ] Unit tests pass
   - [ ] E2E tests pass
   - [ ] Manual testing completed
   
   ## Checklist
   - [ ] Code follows style guidelines
   - [ ] Self-review completed
   - [ ] Comments added for complex code
   - [ ] Documentation updated
   - [ ] No new warnings
   ```

### Review Process

1. Automated checks must pass
2. At least one maintainer approval required
3. All conversations must be resolved
4. Branch must be up to date with main

## Reporting Bugs

### Before Reporting

1. Check if already reported
2. Try latest version
3. Disable other plugins to check conflicts
4. Clear cache

### Bug Report Template

```markdown
**Describe the bug**
Clear description of the bug

**To Reproduce**
1. Go to '...'
2. Click on '...'
3. See error

**Expected behavior**
What should happen

**Screenshots**
If applicable

**Environment:**
- WordPress version:
- WooCommerce version:
- PHP version:
- Browser:
- Plugin version:

**Additional context**
Any other relevant information
```

## Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
Description of the problem

**Describe the solution**
What you'd like to see

**Alternatives considered**
Other solutions you've thought of

**Additional context**
Any other information or mockups
```

## Development Tips

### Debugging

```php
// Enable debug logging
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'AIA_DEBUG', true );

// Log debug info
error_log( 'AIA Debug: ' . print_r( $data, true ) );
```

### Performance

- Use `wp_cache_*` functions for caching
- Batch database operations
- Lazy load heavy resources
- Profile with Query Monitor

### Security

- Always escape output: `esc_html()`, `esc_attr()`, etc.
- Validate input: `sanitize_text_field()`, `absint()`, etc.
- Use nonces for forms
- Check capabilities: `current_user_can()`

## Questions?

Feel free to:
- Open an issue for questions
- Join our [Slack channel](#)
- Email: dev@example.com

## Recognition

Contributors are recognized in:
- README.md contributors section
- Release notes
- Project website

Thank you for contributing! 🎉