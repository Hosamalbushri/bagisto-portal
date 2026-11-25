# AI Coding Agent & Developer Mandatory Rules (`platform/portal`)

All AI models and human developers working in this repository **MUST** read and strictly obey [`GUIDELINES.md`](./GUIDELINES.md) before making any changes.

## Non-Negotiable Architecture Rules

1. **No Custom External Settings Code**:
   - All site, header, footer, branding, and feature settings **MUST** be declared in `src/Config/system.php` using Bagisto's native configuration schema (`channel_based`, `locale_based`, native field types) and translated in `src/Resources/lang/ar/app.php` and `src/Resources/lang/en/app.php`.
   - Never build custom database tables, custom controllers, or custom uploaders for settings.
   - Read header/footer settings via `portal_layout_settings('header' | 'footer')` or `core()->getConfigData('portal.*')`.
   - `admin/portal/navigation/*` (`NavigationCustomizeController`) is strictly for **Menu Slot Assignments (`slots`)** only.

2. **Site Logo & Dual-Mode Branding**:
   - Never hardcode `core()->getCurrentChannel()->logo_url` or `logo.svg` in views.
   - Always render logos via `<x-portal::logo ... />` and resolve URLs via `portal_logo('light' | 'dark', ?string $locale)`.

3. **Native Components & CSS Font Icons Only**:
   - Strictly use Bagisto/Portal components (`<x-admin::...>`, `<x-portal::...>`) and CSS font icons (`.icon-search`, `.icon-users`, `.icon-dark`, `.icon-light`, `.icon-arrow-down`, `.icon-settings` in `src/Resources/assets/css/app.css`). Do not invent inline `<svg>` icons or third-party JS/CSS widgets where native primitives exist.

4. **Verification & Git Discipline**:
   - Never run `git commit` without explicit user permission.
   - Run `./vendor/bin/phpunit` after every change and ensure 100% pass rate.
