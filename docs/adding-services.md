# Adding New Services to GeoDirectory v3

This guide explains how to properly add new services to GeoDirectory v3, including all required registration steps and documentation updates.

## Overview

Services in GeoDirectory are managed through a Dependency Injection (DI) container. When adding a new service, you must:

1. Create the service class
2. Register it in the DI container
3. Add it to the GeoDirectory facade
4. Document it in the services API reference

## Step-by-Step Guide

### Step 1: Create the Service Class

Create your service class in the appropriate location:

**For Services (instance-based, DI-managed):**
- Location: `/src/Core/Services/YourService.php`
- Use dependency injection in constructor
- Can have internal state

**For Utils (static utility classes):**
- Location: `/src/Core/Utils/YourUtil.php`
- Use static methods only
- No internal state

**Example Service:**
```php
<?php
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

final class YourService {

    private SomeDependency $dependency;

    /**
     * Constructor with dependency injection.
     *
     * @param SomeDependency $dependency Injected dependency.
     */
    public function __construct( SomeDependency $dependency ) {
        $this->dependency = $dependency;
    }

    /**
     * Your public method.
     *
     * @param string $param Parameter description.
     * @return string Return value description.
     */
    public function your_method( string $param ): string {
        // Implementation
        return 'result';
    }
}
```

### Step 2: Register in DI Container

**File:** `geodirectory.php` (root)

Add your service binding in the `geodirectory_boot()` function:

```php
function geodirectory_boot() {
    // ... existing code ...

    // Your Service
    $container->bind( \AyeCode\GeoDirectory\Core\Services\YourService::class );

    // ... rest of bindings ...
}
```

**Location in file:** Find the comment `// Core Services (Business Logic & Utilities)` and add your binding with similar services.

### Step 3: Add to GeoDirectory Facade

**File:** `/src/GeoDirectory.php`

Add a `@property` docblock and lazy-loading method:

**At the top of the class (in docblock):**
```php
/**
 * @property YourService $your_service
 */
class GeoDirectory {
    // ...
}
```

**In the `__get()` method:**
```php
public function __get( $key ) {
    switch ( $key ) {
        // ... existing cases ...

        case 'your_service':
            return $this->container->get( Services\YourService::class );

        // ... more cases ...
    }
}
```

**In the `offsetGet()` method (same pattern):**
```php
public function offsetGet( $offset ) {
    return $this->__get( $offset );
}
```

### Step 4: Document in Services API

**File:** `/docs/services.md`

Add complete documentation for all public methods:

```markdown
## `geodirectory()->your_service`
**Class:** `AyeCode\GeoDirectory\Core\Services\YourService`

Brief description of what this service does.

\```php
/**
 * Brief description of the method.
 * @param string $param Parameter description.
 * @return string Return value description.
 */
geodirectory()->your_service->your_method( 'example' );
\```
```

**Important:** Document **ALL** public methods, not just commonly used ones.

### Step 5: Update Architecture Documentation (if needed)

**File:** `/docs/architecture.md`

If your service represents a new pattern or major feature:

1. Add it to the file tree structure
2. Mention it in the "Key Classes & Their Roles" section
3. Update any relevant architectural descriptions

## Checklist for Adding a Service

Use this checklist to ensure you haven't missed any steps:

- [ ] Created service class in `/src/Core/Services/` or `/src/Core/Utils/`
- [ ] Added dependency injection to constructor (Services only)
- [ ] Added all necessary public methods with docblocks
- [ ] Registered service in `geodirectory.php` (`$container->bind()`)
- [ ] Added `@property` docblock in `/src/GeoDirectory.php`
- [ ] Added case in `__get()` method in `/src/GeoDirectory.php`
- [ ] Added case in `offsetGet()` method in `/src/GeoDirectory.php`
- [ ] Documented all public methods in `/docs/services.md`
- [ ] Updated `/docs/architecture.md` (if major feature)
- [ ] Tested service access via `geodirectory()->your_service`

## Common Mistakes to Avoid

### ❌ Forgetting to Register in geodirectory.php
```php
// Missing: $container->bind( \AyeCode\GeoDirectory\Core\Services\YourService::class );
```
**Result:** Service won't be available, will cause errors.

### ❌ Not Adding to GeoDirectory.php Facade
```php
// Missing: case 'your_service': return $this->container->get(...);
```
**Result:** `geodirectory()->your_service` will return null.

### ❌ Incomplete Documentation
```php
// Only documenting 2 methods when service has 5 public methods
```
**Result:** Developers and LLMs won't know about undocumented methods.

### ❌ Wrong Service Location
```php
// Putting a service in /src/Admin/ when it's core functionality
```
**Result:** Poor organization, harder to find.

## Interfaces for Extensibility

If you want addons to be able to replace your service, create an interface:

**1. Create interface:**
```php
// /src/Core/Interfaces/YourServiceInterface.php
namespace AyeCode\GeoDirectory\Core\Interfaces;

interface YourServiceInterface {
    public function your_method( string $param ): string;
}
```

**2. Implement interface:**
```php
final class YourService implements Interfaces\YourServiceInterface {
    // ...
}
```

**3. Bind interface instead of concrete class:**
```php
$container->bind(
    \AyeCode\GeoDirectory\Core\Interfaces\YourServiceInterface::class,
    \AyeCode\GeoDirectory\Core\Services\YourService::class
);
```

**4. Use interface in facade:**
```php
case 'your_service':
    return $this->container->get( Interfaces\YourServiceInterface::class );
```

This allows addons to replace your service via the `geodirectory/factory/{service_id}` filter.

## Examples

### Adding a Simple Utility Class

```php
// 1. Create /src/Core/Utils/StringHelper.php
namespace AyeCode\GeoDirectory\Core\Utils;

final class StringHelper {
    public static function slugify( string $text ): string {
        return sanitize_title( $text );
    }
}

// 2. Register in geodirectory.php
$container->bind( \AyeCode\GeoDirectory\Core\Utils\StringHelper::class );

// 3. Add to /src/GeoDirectory.php
case 'string_helper':
    return $this->container->get( Utils\StringHelper::class );

// 4. Document in /docs/services.md
## `geodirectory()->string_helper`
**Class:** `AyeCode\GeoDirectory\Core\Utils\StringHelper`
...
```

### Adding a Service with Dependencies

```php
// 1. Create service with dependencies
public function __construct(
    PostRepository $posts,
    Formatter $formatter
) {
    $this->posts = $posts;
    $this->formatter = $formatter;
}

// 2-4. Same registration and documentation steps
```

The container will automatically resolve dependencies!

## Testing Your Service

After adding a service, test it:

```php
// In a template or test file:
$result = geodirectory()->your_service->your_method( 'test' );
var_dump( $result );
```

## For LLMs and AI Assistants

When asked to add a new service to GeoDirectory:

1. **Always** follow all 4 steps above
2. **Always** document ALL public methods in `/docs/services.md`
3. **Always** register in BOTH `geodirectory.php` AND `/src/GeoDirectory.php`
4. **Never** skip documentation - it's critical for other developers and future AI assistants
5. **Ask** if you're unsure whether something should be a Service or a Util

## Questions?

- Review existing services in `/src/Core/Services/` for patterns
- Check `/docs/architecture.md` for Services vs Utils distinction
- See `/docs/services.md` for documentation examples

---


**Version:** 3.0.0
