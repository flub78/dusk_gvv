# Laravel Dusk Test Memoization Helper

This document describes a memoization helper for Laravel Dusk tests that enables caching of page data across test executions to improve test performance. This is particularly useful when tests need to repeatedly access the same page data.

## Problem Statement

Laravel Dusk tests often need to access web pages to extract information about the database, such as:
- Counting items in a table
- Getting element IDs from href attributes
- Extracting data attributes from elements
- Checking for the presence of specific elements

These operations can be time-consuming as they require HTTP requests. When the same information is needed multiple times across different tests, making repeated HTTP requests is inefficient.

## Solution

The memoization helper provides a way to cache the results of these operations across test executions. It includes:
1. A static cache store for memoized values
2. Methods to store and retrieve cached values
3. Methods to clear specific or all cached values
4. A trait for easy integration with test classes

## Implementation

### Core Memoization Class

```php
<?php

namespace Tests\Browser\Support;

use Laravel\Dusk\Browser;
use Closure;

class DuskMemoization
{
    /**
     * Store for memoized values
     */
    private static array $memoStore = [];

    /**
     * Memoize a browser operation
     *
     * @param string $key Unique key for the operation
     * @param Closure $operation Operation that returns the value to memoize
     * @param bool $forceRefresh Force refresh the cached value
     * @return mixed
     */
    public static function remember(string $key, Closure $operation, bool $forceRefresh = false)
    {
        if ($forceRefresh || !isset(self::$memoStore[$key])) {
            self::$memoStore[$key] = $operation();
        }
        
        return self::$memoStore[$key];
    }

    /**
     * Clear all memoized values
     */
    public static function clear(): void
    {
        self::$memoStore = [];
    }

    /**
     * Clear specific memoized value
     */
    public static function forget(string $key): void
    {
        unset(self::$memoStore[$key]);
    }
}
```

### Integration Trait

```php
/**
 * Trait to add memoization capabilities to Dusk test classes
 */
trait WithMemoization
{
    /**
     * Memoize a browser operation
     */
    protected function memoize(string $key, Closure $operation, bool $forceRefresh = false)
    {
        return DuskMemoization::remember($key, $operation, $forceRefresh);
    }

    /**
     * Clear all memoized values
     */
    protected function clearMemoized(): void
    {
        DuskMemoization::clear();
    }

    /**
     * Clear specific memoized value
     */
    protected function forgetMemoized(string $key): void
    {
        DuskMemoization::forget($key);
    }
}
```

## Usage Examples

### Basic Usage

```php
use Tests\Browser\Support\WithMemoization;

class ExampleTest extends DuskTestCase
{
    use WithMemoization;

    public function test_example()
    {
        $this->browse(function (Browser $browser) {
            // This will only make the HTTP request once across all test executions
            $totalItems = $this->memoize('total-items', function () use ($browser) {
                return $browser->visit('/items')
                    ->waitFor('.items-count')
                    ->text('.items-count');
            });
        });
    }
}
```

### Complex Example with User Management

```php
class UserTest extends DuskTestCase
{
    use WithMemoization;

    public function test_user_management()
    {
        $this->browse(function (Browser $browser) {
            // Get total users - cached
            $totalUsers = $this->memoize('users.count', function () use ($browser) {
                return (int) $browser->visit('/users')
                    ->waitFor('.users-count')
                    ->text('.users-count');
            });

            // Get user IDs - cached
            $userIds = $this->memoize('users.ids', function () use ($browser) {
                return $browser->visit('/users')
                    ->waitFor('.user-row')
                    ->elements('.user-row')
                    ->map(function ($element) {
                        return $element->getAttribute('data-user-id');
                    });
            });

            // Create new user
            $browser->visit('/users/create')
                ->type('name', 'John Doe')
                ->press('Submit');

            // Force refresh user count
            $newTotalUsers = $this->memoize('users.count', function () use ($browser) {
                return (int) $browser->text('.users-count');
            }, true);

            // Assert count increased
            $this->assertEquals($totalUsers + 1, $newTotalUsers);
        });
    }

    protected function tearDown(): void
    {
        // Clear memoization cache after each test
        $this->clearMemoized();
        parent::tearDown();
    }
}
```

## Best Practices

1. **Key Naming**
   - Use meaningful, unique keys for memoized operations
   - Consider using dot notation for related values (e.g., 'users.count', 'users.ids')
   - Be consistent with key naming conventions across tests

2. **Cache Management**
   - Clear cache when appropriate, especially after data modifications
   - Use `forceRefresh` when fresh data is required
   - Consider clearing cache in tearDown() method
   - Be careful with data that might change during test execution

3. **Performance Considerations**
   - Only memoize operations that are actually repeated
   - Consider the memory impact of storing large data structures
   - Balance between caching and test reliability

## Common Pitfalls

1. **Stale Data**
   - Cached values might become stale if database state changes
   - Use `forceRefresh` when data freshness is critical
   - Clear cache when running tests that modify data

2. **Memory Usage**
   - Large cached values can accumulate
   - Clear cache periodically or after test completion
   - Consider implementing cache size limits

3. **Test Independence**
   - Ensure tests remain independent despite shared cache
   - Clear relevant cache entries between related tests
   - Don't rely on cache state from other tests

## Potential Enhancements

The memoization helper could be enhanced with:

1. **Time-based Expiration**
```php
public static function remember(string $key, Closure $operation, ?int $ttl = null)
{
    if (isset(self::$memoStore[$key])) {
        [$value, $timestamp] = self::$memoStore[$key];
        if ($ttl === null || time() - $timestamp < $ttl) {
            return $value;
        }
    }
    
    $value = $operation();
    self::$memoStore[$key] = [$value, time()];
    return $value;
}
```

2. **Automatic Cache Invalidation**
```php
public static function invalidatePattern(string $pattern): void
{
    foreach (array_keys(self::$memoStore) as $key) {
        if (preg_match($pattern, $key)) {
            self::forget($key);
        }
    }
}
```

3. **Debug Logging**
```php
private static function logCacheHit(string $key): void
{
    \Log::debug("Cache hit for key: {$key}");
}

private static function logCacheMiss(string $key): void
{
    \Log::debug("Cache miss for key: {$key}");
}
```

4. **Scoped Memoization**
```php
public static function scope(string $scope, Closure $callback): void
{
    $previousStore = self::$memoStore;
    self::$memoStore = [];
    
    try {
        $callback();
    } finally {
        self::$memoStore = $previousStore;
    }
}
```

## Installation

1. Create the `Tests/Browser/Support` directory if it doesn't exist
2. Copy the `DuskMemoization.php` file containing both the class and trait
3. Add `use Tests\Browser\Support\WithMemoization;` to your test classes

## Conclusion

This memoization helper can significantly improve the performance of Laravel Dusk tests by reducing unnecessary HTTP requests. When used correctly, it provides a balance between test speed and reliability while maintaining test independence and data integrity.
