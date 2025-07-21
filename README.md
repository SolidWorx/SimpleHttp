# SimpleHttp

A PHP library that provides a fluent interface for making HTTP requests.

## Installation

You can install the package via composer:

```bash
composer require solidworx/simple-http
```

This package is an implementation of [PSR-18 (HTTP Client)](https://www.php-fig.org/psr/psr-18/) and requires a PSR-18 compatible HTTP client. If you don't have one installed already, you can use Guzzle:

```bash
composer require guzzlehttp/guzzle php-http/guzzle7-adapter
```

## Basic Usage

### Creating a Client

```php
use SolidWorx\SimpleHttp\HttpClient;

// Create a new client
$client = HttpClient::create();

// Create a client with a base URL
$client = HttpClient::createForBaseUrl('https://api.example.com');
```

### Making a Simple GET Request

```php
use SolidWorx\SimpleHttp\HttpClient;

$response = HttpClient::create()
    ->url('https://api.example.com/users')
    ->get()
    ->request();

// Get the response content as a string
$content = $response->getContent();

// Get the response status code
$statusCode = $response->getStatusCode();

// Get the response headers
$headers = $response->getHeaders();
```

### Making a POST Request with JSON Data

```php
use SolidWorx\SimpleHttp\HttpClient;

$response = HttpClient::create()
    ->url('https://api.example.com/users')
    ->post()
    ->json([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ])
    ->request();

// Parse the JSON response into an array
$data = $response->toArray();
```

## HTTP Methods

SimpleHttp supports all standard HTTP methods:

```php
// GET request
$client->get()->request();

// POST request
$client->post()->request();

// PUT request
$client->put()->request();

// PATCH request
$client->patch()->request();

// DELETE request
$client->delete()->request();

// OPTIONS request
$client->options()->request();

// Custom method
$client->method('CUSTOM')->request();
```

## Request Configuration

### Headers

```php
$client = HttpClient::create()
    ->url('https://api.example.com')
    ->header('Accept', 'application/json')
    ->header('X-API-Key', 'your-api-key');
```

### Query Parameters

```php
$client = HttpClient::create()
    ->url('https://api.example.com/search')
    ->query([
        'q' => 'search term',
        'page' => 1,
        'limit' => 10
    ]);
```

### Request Body

#### Form Data

```php
$client = HttpClient::create()
    ->url('https://api.example.com/form')
    ->post()
    ->formData([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
```

#### JSON

```php
$client = HttpClient::create()
    ->url('https://api.example.com/users')
    ->post()
    ->json([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
```

#### Raw Body

```php
$client = HttpClient::create()
    ->url('https://api.example.com/data')
    ->post()
    ->body('Raw request body content');
```

### File Uploads

```php
$client = HttpClient::create()
    ->url('https://api.example.com/upload')
    ->post()
    ->uploadFile('file', '/path/to/file.pdf');
```

## Authentication

### Basic Authentication

```php
$client = HttpClient::create()
    ->url('https://api.example.com/protected')
    ->basicAuth('username', 'password');
```

### Bearer Token Authentication

```php
$client = HttpClient::create()
    ->url('https://api.example.com/protected')
    ->bearerToken('your-token');
```

## SSL Configuration

### Disable SSL Verification

```php
$client = HttpClient::create()
    ->url('https://api.example.com')
    ->disableSslVerification();
```

## HTTP Version

```php
// Use HTTP/1.1 (default)
$client = HttpClient::create()
    ->url('https://api.example.com')
    ->httpVersion(HttpClient::HTTP_VERSION_1);

// Use HTTP/2
$client = HttpClient::create()
    ->url('https://api.example.com')
    ->http2();
```

## Response Handling

### Getting Response Data

```php
$response = HttpClient::create()
    ->url('https://api.example.com/users')
    ->get()
    ->request();

// Get status code
$statusCode = $response->getStatusCode();

// Get headers
$headers = $response->getHeaders();
$contentType = $response->getHeaderLine('Content-Type');

// Get body as string
$content = $response->getContent();

// Parse JSON response
$data = $response->toArray();
```

### Saving Response to a File

```php
// Save to a file
$response = HttpClient::create()
    ->url('https://example.com/large-file.zip')
    ->get()
    ->saveToFile('/path/to/save/file.zip')
    ->request();

// Append to a file
$response = HttpClient::create()
    ->url('https://example.com/data.txt')
    ->get()
    ->appendToFile('/path/to/existing/file.txt')
    ->request();

// Save response to a file after receiving it
$response = HttpClient::create()
    ->url('https://example.com/image.jpg')
    ->get()
    ->request();

$response->saveToFile('/path/to/save/image.jpg');
```

### Progress Tracking

```php
$response = HttpClient::create()
    ->url('https://example.com/large-file.zip')
    ->progress(function ($progress) {
        // $progress->getDownloaded() - bytes downloaded
        // $progress->getTotal() - total bytes (if known)
        echo "Downloaded {$progress->getDownloaded()} of {$progress->getTotal()} bytes\n";
    })
    ->request();
```

## Caching Responses

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter();

$response = HttpClient::create()
    ->url('https://api.example.com/data')
    ->cacheResponse($cache, 3600) // Cache for 1 hour
    ->request();
```

## Error Handling

```php
use SolidWorx\SimpleHttp\HttpClient;
use Http\Client\Exception\HttpException;

try {
    $response = HttpClient::create()
        ->url('https://api.example.com/users')
        ->get()
        ->request();
} catch (HttpException $e) {
    // Get the error response
    $errorResponse = $e->getResponse();

    // Get the status code
    $statusCode = $errorResponse->getStatusCode();

    // Get the error message
    $errorMessage = $e->getMessage();

    // Get the response body
    $errorBody = $errorResponse->getBody()->getContents();
}
```

## Advanced Usage

### Using with a Base URL

```php
$client = HttpClient::createForBaseUrl('https://api.example.com');

// Now you can make requests to endpoints relative to the base URL
$response = $client->path('/users')->get()->request();
$response = $client->path('/posts')->get()->request();
```

### Immutability

All methods in the SimpleHttp client return a new instance, making the client immutable:

```php
$client = HttpClient::create()->url('https://api.example.com');

// These create new instances, they don't modify the original $client
$jsonClient = $client->header('Accept', 'application/json');
$authClient = $client->bearerToken('your-token');

// Original client remains unchanged
$response = $client->request();
```

This allows you to create reusable client configurations:

```php
$baseClient = HttpClient::createForBaseUrl('https://api.example.com')
    ->bearerToken('your-token')
    ->header('Accept', 'application/json');

// Reuse the base client for different endpoints
$usersResponse = $baseClient->path('/users')->get()->request();
$postsResponse = $baseClient->path('/posts')->get()->request();
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
