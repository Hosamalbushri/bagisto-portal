# Bagisto Platform Portal (`platform/portal`)
## Architecture Specification, Developer Guidelines & AI Rules

> **Target Audience**: Human Contributors, Core Maintainers, and Autonomous AI Coding Agents.  
> **Status**: Official Package Standard.  
> **Compatibility**: Bagisto 2.4.x / Laravel 11.x & 12.x / PHP 8.3+.

---

## 1. Package Purpose & Philosophy

The **Portal Package** (`platform/portal`) transforms Bagisto from a rigid e-commerce store into a **domain-agnostic, enterprise-grade content and presentation portal**.

### Key Tenets
1. **No E-Commerce Lock-In**: Storefront presentation, pages, sections, menus, and layouts are decoupled from products and carts.
2. **Zero External Crutches**: Never write ad-hoc styles, custom un-scoped CSS, or foreign JavaScript widgets. Strictly leverage native Bagisto Admin components (`<x-admin::...>`, `<x-portal::...>`) and Vue 3 app instances.
3. **First-Class Multi-Tenancy & Multi-Lingualism**: Every page, section, menu, and slot assignment must be scope-aware (Channel & Locale) with automatic graceful fallback.
4. **Developer Freedom & Component Independence**: Designers and developers can craft any Blade view without design constraints; simply by placing `<x-portal::menu-slot id="..." />`, the system automatically discovers and enables dynamic customization.

---

## 2. System Architecture & Component Diagram

```mermaid
flowchart TD
    subgraph AdminLayer["Admin Management Layer (Native Bagisto Admin)"]
        NavUI["Navigation Customizer<br/>/admin/portal/navigation"]
        MenuUI["Menu Tree Builder<br/>/admin/portal/menus"]
        PageUI["Page & Section Builder<br/>/admin/portal/pages"]
    end

    subgraph ServiceLayer["Service & Discovery Engine"]
        Scanner["MenuSlotScanner<br/>(Static AST/Regex Blade Scanner)"]
        Registry["SectionTypeRegistry<br/>(Modular Section Handlers)"]
        SlotRepo["PortalMenuSlotAssignmentRepository"]
        MenuRepo["PortalMenuRepository"]
    end

    subgraph DataLayer["Database & Proxy Layer"]
        SlotsDB[("portal_menu_slot_assignments<br/>(slot_id, channel_id, theme, locale)")]
        MenusDB[("portal_menus & items<br/>+ item_translations")]
        PagesDB[("portal_pages & sections<br/>+ translations")]
    end

    subgraph StorefrontLayer["Portal Storefront Presentation"]
        BladeLayout["Storefront Layout<br/>(x-portal::layouts)"]
        DarkEngine["Theme & Dark Engine<br/>(v-dark / darkMode: class)"]
        SlotComponent["Slot Resolver<br/>x-portal::menu-slot"]
        MenuRenderer["Menu Renderer<br/>x-portal::menu"]
    end

    NavUI --> Scanner
    NavUI --> SlotRepo
    MenuUI --> MenuRepo
    PageUI --> Registry

    SlotRepo --> SlotsDB
    MenuRepo --> MenusDB
    Registry --> PagesDB

    BladeLayout --> DarkEngine
    BladeLayout --> SlotComponent
    SlotComponent --> SlotRepo
    SlotComponent --> MenuRenderer
    MenuRenderer --> MenuRepo
```

---

## 3. Core Modules & Data Models

### 3.1 Dynamic Menu Slots Engine
Enables developers to declare dynamic navigation containers in any Blade view without hardcoding IDs or styles:

```mermaid
sequenceDiagram
    autonumber
    actor Admin
    participant Scanner as MenuSlotScanner
    participant NavUI as Navigation Controller
    participant DB as Slot Assignments Table
    participant Storefront as Storefront (Visitor)

    Admin->>NavUI: Open Navigation Customizer
    NavUI->>Scanner: scan() all Blade views
    Scanner-->>NavUI: Discovered Slots (Header, Footer, Custom)
    NavUI->>DB: Fetch assignments (Channel, Theme, Locale)
    NavUI-->>Admin: Render standard Bagisto Admin Form
    Admin->>NavUI: Save slot assignment (Channel, Locale, Menu, Title)
    NavUI->>DB: updateOrCreate(slot_id, channel_id, theme_code, locale_code)

    Storefront->>Storefront: Visitor loads page with locale
    Storefront->>DB: findForSlot(slot_id, channel_id, theme_code, locale_code)
    Note over Storefront,DB: Graceful fallback to default locale if untranslated
    DB-->>Storefront: Assignment & Menu
    Storefront->>Storefront: Render x-portal::menu
```

#### Placement in Theme Templates:
```blade
<!-- Header Main Nav Slot -->
<x-portal::menu-slot
    id="header_main"
    location="header"
    class="flex items-center gap-6"
/>

<!-- Footer Column Slot -->
<x-portal::menu-slot
    id="footer_column_1"
    location="footer"
    direction="column"
    class="space-y-2 text-sm"
/>
```

---

## 4. Strict Engineering Guidelines

### 4.1 Bagisto Component Purity Rule
> [!IMPORTANT]
> **NEVER inject raw or external HTML/JS libraries when standard Bagisto components exist.**

- **Admin Layout**: `<x-admin::layouts>`
- **Forms**: `<x-admin::form :action="...">`
- **Dropdowns & Selectors**: `<x-admin::dropdown>`
- **Cards & Accordions**: `<x-admin::accordion>`
- **Form Controls**: `<x-admin::form.control-group>`, `<x-admin::form.control-group.label>`, `<x-admin::form.control-group.control>`, `<x-admin::form.control-group.error>`
- **Modals & Confirmation**: `<x-admin::modal.confirm>`
- **Shimmer / Skeletons**: `<x-admin::shimmer...>`

### 4.2 Multi-Language & Multi-Channel Rules
1. **Always use Core Helpers**:
   - `core()->getRequestedChannel()` for current active channel scope.
   - `core()->getRequestedLocale()` for current active locale scope.
2. **Form Action Query Preservation**:
   - Always pass channel and locale in form action routes:
     ```blade
     <x-admin::form :action="route('admin.portal.navigation.update', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code])">
         <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
         <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
     ```
3. **Locale Badges on Form Labels**:
   - Translatable fields must always carry the standard Bagisto locale badge:
     ```html
     <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
         {{ $currentLocale->name }}
     </span>
     ```
4. **Zero Hardcoded Text**:
   - All text strings must strictly reside in `src/Resources/lang/ar/app.php` and `src/Resources/lang/en/app.php`.

### 4.3 Tailwind & Dark Mode Protocol
1. **Tailwind Config**:
   - `darkMode: 'class'` MUST be set in `tailwind.config.js`.
2. **Vue Switcher Component**:
   - Must use the official Bagisto `<v-dark>` component matching `Webkul/Admin`.
   - Never write self-closing custom elements like `<v-dark />` or `<v-theme-switcher />` in Blade because the browser HTML5 parser will swallow subsequent siblings. Always write explicit opening and closing tags `<v-dark></v-dark>`.
   - Always provide SSR static fallback markup inside `<v-dark>` to avoid hydration flicker:
     ```html
     <v-dark>
         <div class="flex">
             <span class="{{ request()->cookie('dark_mode') ? 'icon-light' : 'icon-dark' }} cursor-pointer rounded-md p-1.5 text-xl sm:text-2xl text-gray-700 dark:text-gray-300"></span>
         </div>
     </v-dark>
     ```
3. **Color Tokens**:
   - Backgrounds: Light `bg-white` / `bg-gray-50`, Dark `dark:bg-gray-900` / `dark:bg-gray-950` / `dark:bg-zinc-900`.
   - Borders: Light `border-gray-200` / `border-zinc-200`, Dark `dark:border-gray-800` / `dark:border-zinc-800`.
   - Texts: Light `text-gray-900` / `text-zinc-900`, Dark `dark:text-white` / `dark:text-gray-100`.

### 4.4 Repository Pattern & Proxy Models
Bagisto utilizes Proxy models to allow downstream packages to override models without breaking inheritance.
- Always implement the Contract (e.g. `PortalMenuSlotAssignmentContract`).
- Always create Proxy classes (e.g. `PortalMenuSlotAssignmentProxy`).
- Always register bindings in `ModuleServiceProvider`:
  ```php
  $this->app->bind(
      \Platform\Portal\Contracts\PortalMenuSlotAssignment::class,
      \Platform\Portal\Models\PortalMenuSlotAssignment::class
  );
  ```
- **Eloquent Collection Warning**: When merging keyed Eloquent collections, never use `merge()` as it resets keys via `array_values()`. Use `replace()` to preserve string keys (`slot_id`).

### 4.5 Namespace Isolation & Routing Decoupling
1. **Namespace Purity**:
   - The package strictly owns the `portal` namespace (`portal::views`, `portal::lang`, `<x-portal::...>` components).
   - Never hijack foreign namespaces like `shop::` or register views/components under third-party prefixes.
2. **Coexistence & Routing Modes**:
   - Configured via `config/portal.php` (`PORTAL_ROUTING_MODE`):
     - `override_shop` (default): Portal serves as the primary storefront at `/`, cleanly providing compatibility route aliases.
     - `prefix`: Portal runs alongside `Webkul\Shop` on a dedicated prefix (e.g., `/portal`) without route collision.
3. **Paginator & Middleware Scoping**:
   - Never globally override Laravel's `Paginator::defaultView` or modify the host application's `shop` middleware group. Always encapsulate middleware inside the dedicated `portal` group.

### 4.6 Native Configuration (`system.php`), Logos & UI Icon Rules (Mandatory)
> [!CAUTION]
> **NEVER build custom settings controllers, custom database columns/tables, custom file uploaders, or external UI scripts when Bagisto's native configuration and Portal's built-in helpers/components already exist.**

1. **System Configuration (`src/Config/system.php`) for All Site Settings**:
   - **REQUIRED**: Every configurable site setting (toggles, announcement bar, CTA button, contact info, social links, copyright, brand logos, feature flags) **MUST** be declared in `src/Config/system.php` under `portal.*` (`portal.general`, `portal.auth`, `portal.header`, `portal.footer`) using Bagisto's native field types (`boolean`, `text`, `textarea`, `select`, `color`, `image`, `file`).
   - **Scoping Discipline**: Always set `'channel_based'` and `'locale_based'` accurately per field:
     - Shared settings (booleans, URLs, colors, phone, email, social handles): `'channel_based' => true, 'locale_based' => false`.
     - Translatable / localized assets & copy (announcement text, CTA text, bio, address, copyright, localized light/dark logos): `'channel_based' => true, 'locale_based' => true`.
   - **Navigation Customizer Separation**: `admin/portal/navigation/header` and `footer` (`NavigationCustomizeController`) are **strictly for Menu Slot Assignments (`slots`)**. Do NOT re-add configuration setting forms or accordions to `navigation/header.blade.php` or `navigation/footer.blade.php`.
   - **Reading Settings**: Always read via `core()->getConfigData('portal.*')` or `portal_layout_settings('header' | 'footer')` (which automatically merges `system.php` values via `portal_merge_system_settings()` with default-locale fallback and legacy compatibility).
2. **Dual-Mode & Localized Site Logo Standard**:
   - **FORBIDDEN**: Hardcoding `core()->getCurrentChannel()->logo_url`, `bagisto_asset('images/logo.svg')`, or writing manual `<img>` logo markup in Blade templates.
   - **REQUIRED**:
     - Always render UI logos using `<x-portal::logo ... />` (`src/Resources/views/components/logo/index.blade.php`).
     - Always resolve programmatic logo URLs (Manifest, SEO, APIs) using `portal_logo('light' | 'dark', ?string $locale = null)`.
     - Light/Dark logo switching relies on `.portal-logo-light` and `.portal-logo-dark` CSS rules in `layouts/index.blade.php` and `app.css` — never duplicate custom JS logo-swapping scripts.
3. **CSS Font Icons & Header Architecture**:
   - **FORBIDDEN**: Writing inline `<svg>` elements for standard UI icons when Bagisto/Portal CSS icon classes exist in `src/Resources/assets/css/app.css` (e.g., `.icon-search`, `.icon-users`, `.icon-dark`, `.icon-light`, `.icon-arrow-down`, `.icon-settings`).
   - **Theme Toggle**: Always use a single `<span data-portal-theme-icon class="icon-dark ..."></span>` toggling between Bagisto's native `.icon-dark` and `.icon-light` classes — never render two separate icon elements or custom CSS icon definitions.
   - **Header & Topbar Layout**: Keep `desktop.top` outside `<header class="sticky ...">` so the currency/locale switcher bar scrolls away naturally, and keep `<header>` solid opaque (`bg-white dark:bg-[#0b0f19]`) without transparency/blur.

---

## 5. Instructions for AI Coding Agents

When working on `platform/portal`:
1. **Read Rules & Existing Primitives First**:
   - Before writing any new feature, setting, or UI element, read `GUIDELINES.md`, `src/Config/system.php`, and `src/Helpers/helpers.php`. Reuse existing Portal/Bagisto primitives instead of inventing external code.
2. **Strictly Refrain from Committing**:
   - NEVER execute `git commit` unless the human user explicitly commands you to do so.
3. **Verify Tests After Every Change**:
   - Run `./vendor/bin/phpunit` in the package root and verify 100% pass rate.
4. **Style & Asset Verification**:
   - Run Laravel Pint after any PHP change and `npm run build` if `src/Resources/assets/` is modified.
5. **No E-Commerce Reintroduction**:
   - Do NOT re-introduce hard dependencies on `Webkul\Product`, `Webkul\Cart`, or `Webkul\Checkout` in the core portal flow.

---

## 6. Directory Structure Overview

```text
packages/Platform/Portal/
├── src/
│   ├── Config/              # acl.php, menu.php, portal.php, themes.php
│   ├── Contracts/           # Model contracts for proxy resolution
│   ├── Database/
│   │   └── Migrations/      # Schema migrations
│   ├── Helpers/             # PortalStorefront, PortalAuth helpers
│   ├── Http/
│   │   ├── Controllers/     # Admin (Menus, Navigation, Pages) & Storefront
│   │   ├── Middleware/      # Locale, Currency, StorefrontStatus, Theme
│   │   └── Requests/        # Form requests & validation
│   ├── Models/              # Eloquent models & proxy classes
│   ├── Repositories/        # Database repositories
│   ├── Resources/
│   │   ├── assets/          # CSS, JS (Vite), Fonts, Images
│   │   ├── lang/            # Localization (ar, en)
│   │   └── views/           # Blade templates (admin, storefront, components)
│   ├── Routes/              # admin-routes.php, store-front-routes.php
│   ├── Sections/            # Modular section type handlers & registry
│   └── Services/            # MenuSlotScanner and business services
├── tailwind.config.js       # Tailwind CSS config (darkMode: 'class')
├── vite.config.js           # Vite bundle configuration
└── composer.json            # Package definition
```

