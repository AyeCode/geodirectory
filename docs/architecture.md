# GeoDirectory v3 Architectural Plan

This document outlines the core architectural plan for the GeoDirectory v3 refactor. The goal is to create a modern, efficient, organized, and highly extensible plugin that is compatible with **PHP 7.4**.

## Core Architecture: Hybrid Model

The plugin is built around a hybrid model to combine modern best practices with practical organization for a large-scale application.

1.  **Dependency Injection (DI) Core:** The main application logic is built around a **DI** pattern.
  - A central **Service Container** (`Container.php`) acts as a "factory" for all core services.
  - It automatically resolves and injects dependencies using PHP's Reflection API.
  - **Service Providers** are used to register "blueprints" (bindings) with the container and orchestrate the loading of major features.

2.  **Action Loader for AJAX:** A dedicated **`Loader.php`** class is used to bootstrap and register self-contained AJAX actions (Tools, Panes, etc.). This keeps procedural AJAX endpoint logic separate from the main object-oriented services.

## File Tree Structure

We use a **PSR-4** autoloading structure. The full plugin structure is as follows:

```
/geodirectory
├── assets/
│   ├── css/
│   └── js/
├── docs/                  # Developer documentation
│   ├── README.md              # Documentation index
│   ├── architecture.md        # This file - core architecture guide
│   ├── services.md            # Complete service API reference
│   └── schema-management.md   # CPT table/field management guide
├── inc/                   # Procedural helper functions
│   ├── wrapper-functions.php
│   ├── helper-functions.php
│   ├── core-functions.php
│   └── ...                    # Other legacy procedural code
├── languages/
│   └── geodirectory.pot
├── src/
│   ├── Admin/
│   │   ├── Features/          # Feature-specific admin classes
│   │   ├── Pages/             # Page controllers (SettingsPage.php)
│   │   ├── Settings/          # Settings management classes
│   │   ├── Utils/             # Admin utility classes
│   │   ├── config/            # Configuration arrays (settings.php)
│   │   ├── views/             # Admin-side template files
│   │   ├── AdminServiceProvider.php
│   │   ├── CptSettingsManager.php
│   │   └── Setup.php
│   ├── Ajax/
│   │   ├── Actions/           # Self-contained AJAX action classes
│   │   ├── ActionRegistry.php # Static registry for AJAX actions
│   │   ├── AjaxHandler.php    # Main AJAX request router
│   │   └── PaneRegistry.php   # Static registry for AJAX-loaded UI panes
│   ├── Common/
│   │   ├── Assets.php                  # Asset management (CSS/JS)
│   │   ├── CommonServiceProvider.php   # Common feature orchestrator
│   │   ├── CptConfig.php               # CPT/Taxonomy configuration
│   │   ├── PostReports.php             # Post reporting system
│   │   ├── PostStatusesRegistrar.php   # Post status registration
│   │   ├── PostTypesRegistrar.php      # CPT registration
│   │   └── TaxonomiesRegistrar.php     # Taxonomy registration
│   ├── Core/
│   │   ├── Data/              # Data objects and value classes
│   │   ├── Interfaces/        # "Contracts" for extensible services
│   │   ├── Seo/               # SEO-related services and strategies
│   │   ├── Services/          # Instance-based, container-managed services
│   │   │   ├── BusinessHours.php       # Business hours, timezones
│   │   │   ├── Debug.php               # Error logging, debug utilities
│   │   │   ├── Formatter.php           # Data formatting, sanitization
│   │   │   ├── Geolocation.php         # GPS, geocoding, IP location
│   │   │   ├── Helpers.php             # String, color, URL utilities
│   │   │   ├── Image.php               # Image upload/processing (deprecated)
│   │   │   ├── Images.php              # Image operations
│   │   │   ├── LocationFormatter.php   # Location display formatting
│   │   │   ├── Locations.php           # Countries, regions, cities
│   │   │   ├── Maps.php                # Map provider integration
│   │   │   ├── Media.php               # Media/attachment handling
│   │   │   ├── PostSaveService.php     # Post save orchestration
│   │   │   ├── Reviews.php             # Review operations
│   │   │   ├── Seo.php                 # SEO operations
│   │   │   ├── Settings.php            # Settings utilities
│   │   │   ├── Statuses.php            # Post status registration
│   │   │   ├── Tables.php              # DB table registry
│   │   │   └── Templates.php           # Template paths, utilities
│   │   ├── Utils/             # Static utility classes (NOT in container)
│   │   │   ├── Maps.php               # Map utilities
│   │   │   ├── PostTypes.php          # Post type helpers
│   │   │   ├── Settings.php           # Settings helpers
│   │   │   └── Utils.php              # Random hash, API utilities
│   │   ├── Container.php      # The main DI container
│   │   ├── Lifecycle.php      # Plugin lifecycle events
│   │   ├── Plugin.php         # Plugin information
│   │   └── PostSaveHooks.php  # Hooks for post save operations
│   ├── Database/
│   │   ├── Repository/        # Data access layer
│   │   │   ├── AttachmentRepository.php
│   │   │   ├── CustomFieldRepository.php
│   │   │   ├── PostRepository.php
│   │   │   └── ReviewRepository.php
│   │   └── Schema/            # Database schema management
│   ├── DummyData/
│   │   └── DummyDataService.php
│   ├── Fields/                # Custom field system
│   │   ├── Abstracts/         # Abstract base classes
│   │   ├── Interfaces/        # Field interfaces
│   │   ├── Types/             # Concrete field type classes
│   │   ├── FieldRegistry.php  # Field type registry
│   │   └── FieldsService.php  # High-level field API
│   ├── Frontend/
│   │   ├── Ajax/              # Frontend-specific AJAX actions
│   │   ├── FrontendServiceProvider.php
│   │   ├── PostHooks.php      # Frontend post display hooks
│   │   ├── ReviewForm.php     # Review form rendering
│   │   └── ReviewHooks.php    # Review system hooks
│   ├── ImportExport/
│   │   ├── Contracts/         # Import/export interfaces
│   │   ├── Exporters/         # Export handlers
│   │   ├── Handlers/          # Import/export orchestrators
│   │   ├── Importers/         # Import handlers
│   │   └── Utils/             # Import/export utilities
│   ├── Integrations/
│   │   └── Seo/               # SEO plugin integrations
│   ├── Public/                # Public-facing features
│   ├── Support/
│   │   └── Hookable.php       # Reusable trait for tracking/removing hooks
│   ├── GeoDirectory.php       # The main public "facade" class
│   ├── Loader.php             # Bootstrapper for the AJAX Action/Pane system
│   └── functions.php          # Global helper functions (e.g., geodirectory())
├── templates/
│   └── reviews.php            # Example theme-overridable view file
├── vendor/
│   └── autoload.php
├── composer.json
└── geodirectory.php           # Main plugin bootstrap file
```

## Bootstrapping & Initialization

1.  **`geodirectory.php`**: The main plugin file is a simple **bootstrapper**. It loads Composer, defines constants, and calls a boot function on `plugins_loaded`.

2.  **`geodirectory_boot()`**: This is the single entry point. It is responsible for:
  - Initializing the `Loader` class (which sets up the AJAX system).
  - Creating the `Container` instance.
  - `bind()`-ing all services to the container.
  - Initializing the main `geodirectory()` global helper with the container.
  - Running the main `*ServiceProvider` classes.

3.  **`*ServiceProvider.php`**: These classes are orchestrators for major features. They get services from the container and run dedicated `*Hooks.php` classes or register hooks themselves.

## Services vs Utils: A Clear Distinction

The plugin makes a clear architectural distinction between two types of classes:

### Services (`src/Core/Services/`)
- **Instance-based** classes managed by the DI container
- Accessed via `geodirectory()->service_name`
- Support dependency injection in constructors
- Can have internal state and be extended by addons
- Examples: `Geolocation`, `Formatter`, `BusinessHours`, `Settings`

### Utils (`src/Core/Utils/`)
- **Static utility** classes NOT managed by the container
- Called directly via class name: `PostTypes::get_posttypes()`
- Pure functions with no internal state
- Cannot be dependency-injected
- Examples: `PostTypes`, `Utils`

This separation provides clarity about which classes can be injected, extended, or replaced, and which are simple static helpers.

## Key Classes & Their Roles

* **`Container.php`**: The factory for core services. It `binds` and `gets` services, using Reflection for automatic dependency resolution.
* **`Loader.php`**: The bootstrapper for the AJAX system. It runs early and registers all the available tool/pane actions with their respective registries.
* **`AjaxHandler.php`**: The router for AJAX requests. It checks which action was requested and dispatches it from the `ActionRegistry`.
* **`GeoDirectory.php`**: The main public "facade" class. It uses the `__get()` magic method to **lazy-load** services from the container, making it very efficient.
* **`geodirectory()` function**: The single global helper function that provides easy access to the `GeoDirectory` facade object. This is the primary public API.
* **Interfaces (`*Interface.php`)**: These act as "contracts" that allow core services to be safely replaced by addons.

## Addon Extensibility

Addons can extend the core plugin in two primary, modern ways:

1.  **Replacing Services**: Addons can use the `geodirectory/factory/{service_id}` filter to provide their own implementation of a core service (e.g., replacing the `Locations` service with a `MultiLocations` service).
2.  **Disabling Features**: For features managed by a hook class that uses the `Hookable` trait, addons can get the hook class from the container and call `unhook_all()` to disable it.

## Public API & Service Access

We use a hybrid approach for maximum flexibility and ease of use:

* **For templates, procedural code, or quick access:** Use the global helper function: `geodirectory()->locations->get_default();`
* **For modern, internal classes (widgets, services, etc.):** Use **Dependency Injection**. A class should ask for its dependencies in its constructor: `public function __construct(LocationsInterface $locations) { ... }`

## Coding Standards & Best Practices

* **PHP 7.4 Compatibility:** The plugin must be fully compatible with PHP 7.4.
* **WordPress Coding Standards:** All code must adhere to the official WordPress Coding Standards for formatting, naming, and documentation.
* **Security Best Practices:** The plugin must be built with a security-first mindset, following these core principles:
  - Sanitize all input.
  - Escape all output.
  - Use nonces for all forms and actions.
  - Use prepared statements for all database queries.
  - Perform capability checks for all actions.
* **Developer-Persona Comments:** All code comments should be written from the perspective of a developer explaining the code to a teammate.
