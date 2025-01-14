# Testing File Upload in Laravel using Dusk

## Setup Prerequisites

1. Install Laravel Dusk in your project:
```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

2. Create a sample upload form for testing:
```php
// resources/views/upload.blade.php
<form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="document" id="document">
    <button type="submit">Upload</button>
</form>
```

## Writing the Test

### Basic File Upload Test

```php
namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadTest extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_upload_file()
    {
        $this->browse(function (Browser $browser) {
            // Create a test file
            $file = UploadedFile::fake()->create('document.pdf', 500);
            
            // Get the absolute path of the test file
            $filePath = $file->getPathname();

            $browser->visit('/upload')
                    ->attach('document', $filePath)
                    ->press('Upload')
                    ->assertPathIs('/success')  // Adjust based on your redirect
                    ->assertSee('File uploaded successfully');

            // Verify file was stored
            Storage::disk('public')->assertExists($file->hashName());
        });
    }
}
```

### Advanced Testing Scenarios

#### Test File Size Validation

```php
public function test_large_file_upload_validation()
{
    $this->browse(function (Browser $browser) {
        $largeFile = UploadedFile::fake()->create('large.pdf', 2048);

        $browser->visit('/upload')
                ->attach('document', $largeFile->getPathname())
                ->press('Upload')
                ->assertSee('The document must not be greater than 2048 kilobytes');
    });
}
```

#### Test File Type Validation

```php
public function test_invalid_file_type_validation()
{
    $this->browse(function (Browser $browser) {
        $invalidFile = UploadedFile::fake()->create('document.exe', 100);

        $browser->visit('/upload')
                ->attach('document', $invalidFile->getPathname())
                ->press('Upload')
                ->assertSee('The document must be a file of type: pdf, doc, docx');
    });
}
```

#### Test Multiple File Upload

```php
public function test_multiple_file_upload()
{
    $this->browse(function (Browser $browser) {
        $file1 = UploadedFile::fake()->create('document1.pdf', 100);
        $file2 = UploadedFile::fake()->create('document2.pdf', 100);

        $browser->visit('/upload')
                ->attach('documents[]', $file1->getPathname())
                ->attach('documents[]', $file2->getPathname())
                ->press('Upload')
                ->assertPathIs('/success')
                ->assertSee('Files uploaded successfully');

        Storage::disk('public')->assertExists($file1->hashName());
        Storage::disk('public')->assertExists($file2->hashName());
    });
}
```

## Best Practices

1. **Clean Up After Tests**
```php
public function tearDown(): void
{
    Storage::fake('public')->deleteDirectory('');
    parent::tearDown();
}
```

2. **Use Custom Assertions**
```php
public function assertFileUploaded($browser, $filename)
{
    return $browser->assertSee('File uploaded successfully')
                  ->tap(function () use ($filename) {
                      Storage::disk('public')->assertExists($filename);
                  });
}
```

3. **Test Progress Indicators**
```php
public function test_upload_progress_indicator()
{
    $this->browse(function (Browser $browser) {
        $file = UploadedFile::fake()->create('large.pdf', 1000);

        $browser->visit('/upload')
                ->attach('document', $file->getPathname())
                ->waitFor('.progress-bar')  // If you have a progress indicator
                ->assertVisible('.progress-bar')
                ->waitUntilMissing('.progress-bar')
                ->assertPathIs('/success');
    });
}
```

## Common Issues and Solutions

1. **File Path Issues**
   - Always use `getPathname()` to get the absolute path
   - Ensure file permissions are correct

2. **Browser Timeouts**
   - Increase timeout for large files:
   ```php
   $browser->maximize()
           ->timeouts()->implicitWait(30000)
           ->visit('/upload');
   ```

3. **Memory Issues**
   - Use `php artisan dusk --memory=512M`
   - Configure PHP memory limit in php.ini

## Debugging Tips

1. **Screenshot on Failure**
```php
public function test_upload_with_screenshot()
{
    $this->browse(function (Browser $browser) {
        try {
            // Your test code
        } catch (\Exception $e) {
            $browser->screenshot('failed-upload');
            throw $e;
        }
    });
}
```

2. **Console Log Checking**
```php
$browser->tap(function ($browser) {
    $console = $browser->driver->manage()->getLog('browser');
    $this->assertEmpty(array_filter(
        $console,
        fn ($log) => $log['level'] === 'SEVERE'
    ));
});
```

Remember to adjust the paths, assertions, and timeouts according to your specific application structure and requirements.
