# Bagisto Platform Portal (`platform/portal`)

A modular, extensible presentation and frontend portal engine for **Bagisto 2.4.x / Laravel 12**.

Designed as a clean, domain-agnostic replacement for `Webkul/Shop`, providing an **Admin Appearance Customizer** to manage dynamic sections (Hero Sliders, Features Grids, Content Blocks, Footer Columns) for any web application without e-commerce lock-in.

---

## Features

- **Decoupled & Optional**: Can be enabled or disabled cleanly in `bootstrap/providers.php` without breaking the core backend.
- **Admin Appearance Customizer**: Manage sections dynamically from the admin panel (create, order, activate, and translate).
- **Extensible Section Handlers**: Easily add new section types by extending `AbstractSection`.
- **Full Localization**: Every section supports multi-locale translation natively.
- **Vite & Tailwind Asset Pipeline**: Includes its own independent asset bundle.

---

## Installation

Add to Bagisto `composer.json` repositories:

```json
"repositories": [
    {
        "type": "path",
        "url": "../bagisto-portal",
        "options": {
            "symlink": true
        }
    }
]
```

Then run:

```bash
composer require platform/portal
php artisan migrate
php artisan optimize:clear
```

---

## License

MIT License.

