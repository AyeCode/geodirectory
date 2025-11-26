# GeoDirectory v3 Developer Documentation

Welcome to the GeoDirectory v3 developer documentation. This collection provides comprehensive guidance for developers and LLMs working with the GeoDirectory v3 codebase.

## 📚 Documentation Index

### [Architecture](architecture.md)
Core architectural patterns, design principles, and project structure for GeoDirectory v3.

**Topics covered:**
- Hybrid DI + Action Loader architecture
- PSR-4 file structure and organization
- **Asset build system with Vite** (`/resources` → `/assets`)
- Service Container and Dependency Injection
- Services vs Utils distinction
- Bootstrapping and initialization flow
- Addon extensibility patterns
- Public API and service access
- Coding standards and best practices

**When to read:** Start here to understand the overall design and structure of the plugin.

---

### [Services API](services.md)
Complete reference for all public service methods available via `geodirectory()`.

**Topics covered:**
- Container access
- Location services (`locations`, `geolocation`, `location_formatter`)
- Data formatting (`formatter`, `business_hours`)
- Media and images (`images`, `media`)
- Template utilities (`templates`)
- Helper utilities (`helpers`, `debug`)
- Database repositories (`reviewRepository`, `tables`)
- Settings and configuration (`settings`, `statuses`, `maps`)
- Post operations (`post_save_service`, `fields`)

**When to read:** Use this as a quick reference when implementing features that interact with GeoDirectory services.

---

### [Schema Management](schema-management.md)
Detailed guide on CPT table column management and the custom field system.

**Topics covered:**
- PostRepository table schema operations
- CustomFieldRepository field definition management
- Column lifecycle (add/update/remove)
- Field type to MySQL column mappings
- Constructor dependency injection
- Hooks and filters for extensions
- Usage examples and best practices

**When to read:** Essential reading when working with custom fields, CPT tables, or extending the field system.

---

## 🎯 Quick Start Guide

### For New Developers

1. Read [Architecture](architecture.md) to understand the core design
2. Familiarize yourself with the [Services API](services.md)
3. Review [Schema Management](schema-management.md) if working with custom fields

### For LLMs and AI Assistants

**CRITICAL RULES when working on GeoDirectory v3:**

1. **Code Location:**
   - All new PHP code: `/src` (classes) or `/inc` (procedural)
   - All new JS/CSS: `/resources` (compiled to `/assets` via Vite)
   - Main plugin file: `geodirectory.php` in root
   - **NEVER modify legacy files** outside these locations

2. **Adding/Modifying Services:**
   - **ALWAYS read** [adding-services.md](adding-services.md) **FIRST**
   - Services MUST be registered in `geodirectory.php` (DI container)
   - Services MUST be added to `/src/GeoDirectory.php` (facade)
   - **ALL public methods MUST be documented** in `/docs/services.md`
   - Use the checklist in adding-services.md - don't skip steps

3. **Architecture:**
   - Services = instance-based, DI-managed, accessed via `geodirectory()->service_name`
   - Utils = static classes, accessed via full class name
   - Follow PSR-4 autoloading and namespace structure
   - Use dependency injection for services

### Common Patterns

```php
// Accessing a service
$location = geodirectory()->locations->get_current();

// Dependency injection in a class constructor
public function __construct( Geolocation $geo, Formatter $formatter ) {
    $this->geo = $geo;
    $this->formatter = $formatter;
}

// Working with custom fields
$fields = geodirectory()->fields->get_field_info( 'htmlvar_name', 'business_hours', 'gd_place' );
```

---

## 🔧 Development Guidelines

### Code Location Rules

- ✅ **New PHP code:** `/src` (classes) or `/inc` (procedural)
- ✅ **New JS/CSS code:** `/resources` (compiled to `/assets` via Vite)
- ✅ **Main plugin file:** `geodirectory.php`
- ❌ **Legacy code:** All other locations (read-only, do not modify)
- ⚠️ **Never edit `/assets` directly** - always edit `/resources` and run `npm run build`

### Architecture Principles

1. **Services** = Instance-based, DI-managed, stateful
2. **Utils** = Static methods, no DI, stateless
3. **Repositories** = Database layer, all SQL isolated here
4. **Service Providers** = Orchestrators for major features

### Extensibility

Addons can:
- Replace services via `geodirectory/factory/{service_id}` filter
- Disable hook classes with `unhook_all()`
- Hook into column operations for custom fields
- Filter column definitions and field processing

---

## 📋 File Structure Reference

```
/geodirectory
├── docs/               # This documentation
├── src/                # Modern OOP code (PSR-4)
│   ├── Core/           # Services, Container, Interfaces
│   ├── Database/       # Repositories, Schema
│   ├── Fields/         # Field system
│   ├── Admin/          # Admin features
│   ├── Frontend/       # Frontend features
│   └── GeoDirectory.php # Main facade class
├── inc/                # Procedural helper functions
├── resources/          # Source files for Vite (JS/SCSS)
├── assets/             # Compiled assets (output from Vite)
├── geodirectory.php    # Main plugin bootstrap
└── vendor/             # Composer dependencies
```

---

## 🤝 Contributing

When contributing code to GeoDirectory v3:

1. Follow WordPress Coding Standards
2. Maintain PHP 7.4 compatibility
3. Use type hints and declare(strict_types=1)
4. Write security-first code (sanitize input, escape output, use nonces)
5. Document with developer-persona comments
6. Add to these docs if introducing new patterns

---

## 📞 Support & Feedback

- **Issues:** [GitHub Repository](https://github.com/AyeCode/geodirectory)
- **Documentation:** Keep these files updated as the codebase evolves
- **Questions:** Refer to inline code documentation and these guides

---


**Version:** 3.0.0
