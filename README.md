## API Consumer
[![Latest Version](https://img.shields.io/github/release/mpemburn/api-consumer.svg?style=flat-square)](https://github.com/spatie/laravel-analytics/releases)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/mpemburn/api-consumer.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-analytics)

### About
The `api-consumer` package allows you to create simple endpoint wrappers for RESTful API's.


### Installation

You can install this package via composer:

`composer require mpemburn/api-consumer`

The package will automatically register the service provider `ApiConsumerProvider`.

Next, you will need to publish the package in order to copy `api-consumer.php` into your `config` directory:
```
php artisan vendor:publish --provider="Mpemburn\ApiConsumer\ApiConsumerProvider"
``` 

### How to use
#### Configuration
The package will add the file `config/api-consumer.php` to your project.  This returns an array containing the keys and variables used by the endpoint classes you'll create to consume one or more RESTful APIs.  The format will look like this:

    'shopify' => [
        'base_uri' => 'https://mystore.myshopify.com/admin/api/2020-10',
        'username' => '5ac3bd00f1ebc6a65caa4c0a6a3b1555',
        'password' => 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
    ],
 
To make this more secure, you should store the actual variables in your `.env` file:

    SHOPIFY_USERNAME=5ac3bd00f1ebc6a65caa4c0a6a3b1555
    SHOPIFY_PASSWORD=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
 
...and reference them like this:

    'shopify' => [
        'base_uri' => 'https://mystore.myshopify.com/admin/api/2020-10',
        'username' => env('SHOPIFY_USERNAME'),
        'password' => env('SHOPIFY_PASSWORD')
    ],
    
You can add as many API's as you need to the config file, as long as each has the API name (i.e., 'shopify' in this instance) at top-level of the array.

**NOTE**: After making changes to a this config file, it's important to run:
```
php artisan config:cache
``` 
#### Class Structure
While there's no absolute requirement to structure your class files this way, the suggested heirarchy is:
```
project
│   
└───Api
│   │   
│   └───Shopify
│       │   ShopifyEndpoint
│       │   CreateProduct
│       │   GetProducts
│       │   ...
│       Discourse
│       │   DiscourseEndpoint
│       │   CreateUser
│       │   GetUsers
│       │   ...
```

#### Parente Endpoint Classes
Each parent endpoint class (e.g., `ShopifyEndpoint`, `DiscourseEndpoint` above) needs to extend this package's `AbstractEndpoint` class. Individual endpoints then extends its primary class.  For example:
```php
<?php

namespace App\Api\Shopify;

use Mpemburn\ApiConsumer\Endpoints\AbstractEndpoint;

class ShopifyEndpoint extends AbstractEndpoint
{
    public function __construct()
    {
        parent::__construct();

        $this->addHeader('Content-Type', 'application/json');
    }

    public function getApiName(): string
    {
        return 'shopify';
    }
}
```
It's important to return the exact same name from `getApiName()` that you specified in the `api-consumer` config file.

**NOTE**: No headers are assumed, so you should add them in the constructor as shown above.

#### Endpoint Classes
An individual endpoint should be structured like this:
```php
<?php

namespace App\Api\Shopify;

class GetProducts extends ShopifyEndpoint
{
    public function getRequestType(): ?string
    {
        return 'GET';
    }

    public function getEndpoint(): ?string
    {
        return '/products.json';
    }

    public function getRequestName(): ?string
    {
        return 'Get Products';
    }
}
```
### Making Requests

A simple example of making a request with a `GET` endpoint:

```php
Route::get('get_products', function () {
    $requestManager = RequestManager::make();
    $products = new Products();
    if ($requestManager) {
        return $requestManager->send($products)
            ->getResponse();
    }
});

```
### POST Requests
To create a `POST` endpoint:

```php
<?php

namespace App\Api\Shopify;

class CreateProduct extends ShopifyEndpoint
{
    public function getRequestType(): ?string
    {
        return 'POST';
    }

    public function getEndpoint(): ?string
    {
        return '/products.json';
    }

    public function getRequestName(): ?string
    {
        return 'Create Product';
    }

    public function create(array $product): void
    {
        $this->setParams($product);
    }
}
```
The request might look like this:
```php
Route::post('create_product', function (Request $request) {
    $requestManager = RequestManager::make();
    $createProduct = new CreateProduct();
    $createProduct->create($request->toArray());

    if ($requestManager) {
        return $requestManager->send($createProduct)
            ->getResponse();
    }
});
```
### URL's with variables
Some API endpoints require variable parts in the URL string. For example, if you need to update a user, the API endpoint might include the user's ID as part of the URL:
```
https://roster.org/api/v1/users/update/123
```

In this case, you would set up your `PUT` endpoint something like this:
```php
<?php

namespace App\Api\Roster;

class UpdateUser extends RosterEndpoint
{
    public function getRequestType(): ?string
    {
        return 'PUT';
    }

    public function getEndpoint(): ?string
    {
        return $this->hydrateUrlParams('/users/update/{user_id}', $this->getUrlParams());
    }

    public function getRequestName(): ?string
    {
        return 'Update User';
    }

    public function update(int $userId, array $userData): void
    {
        $this->setParams($userData);
        $this->addUrlParam('user_id', $userId);
    }
}
```
Here, the `update` method takes the `$userId` and array of the data to be updated (`$userData`).  The `setParams` method will add all of the `$userData` to a Larvel Collection, and the `addUrlParam` method adds `$memberId` to a similar collection.

Next, in the `getEndpoint` method, we can pass the URL string with `user_id` enclosed in curly braces, which causes it to be seen as a variable. Passing this into the `hydrateUrlParams` method along with a call to `getUrlParams` will replace `{user_id}` with whatever was passed into the `update` method.

**NOTE**: You can pass as many variables as needed using this method.

### API's That Use Authorization (auth) Tokens
Some API's require you to use an auth token for each endpoint call. The typical pattern is to fetch the auth token by sending a `GET` request to the API with a secret key provided to by the API's developer's console. Once you have the auth token, you add it to the request parameters for each subsequent call.

This package supports this model by allowing you to create a special endpoint for the purpose.  For example:
```
https://roster.org/api/v1/get_auth?key=V2hhdCBpcyB0aGF0IHN0cmFuZ2Ugc291bmQ
```
To call this, your `GetAuthToken` endpoint should look like this:
```php
<?php

namespace App\Api\Roster;

class GetAuthToken extends RosterEndpoint
{
    protected ?string $authTokenFetchKeyName = 'key';
    protected ?string $authTokenResponseName = 'auth_token';

    public function getRequestType(): ?string
    {
        return 'GET';
    }

    public function getEndpoint(): ?string
    {
        return '/get_auth';
    }

    public function getRequestName(): ?string
    {
        return 'Get Auth Token';
    }
}
```
Here the `$authTokenFetchKeyName` property refers to the parameter name of the API key, and the `$authTokenResponseName` refers to the name that the API assigns to the auth token.  The response might look like this:
```
{
  "auth_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdXRoX2tleSI6IlYyaGhkQ0JwY3lCMGFHRjBJSE4wY21GdVoyVWdjMjkxYm1RIiwibm93IjoxNjA3NDM1MTQ2fQ.DKgv6rTKnRa2k_WOT5LbvNRUhgSt6uRAnnO84Weka0CVifs6tZhkDHAXQQJibJYQVjWmYooCLtFQfNkFc4oS-z3X-rgj80qpjh8dFFfq3mM5zBvbbyhxWFKzhLmownsOJZCjOiJE5nGTazenMH-0bc5CjWW8SzlXPgksIRRK8bg"
}
```

In addition, your parent endpoint needs to include a reference to the `GetAuthToken` class:
```php
...
class RosterEndpoint extends AbstractEndpoint
{
    protected ?string $authTokenEndpoint = GetAuthToken::class;
...
```

By default, the token will be discarded after each request.  This is usually a good idea since many/most auth tokens are time limited.  In the case where you need to make a series of requests in rapid succession, you can use the `preserveAuthToken` method of the `RequestManager`:
```php
$requestManager = RequestManager::make();
$members = new GetMembers();

if ($requestManager) {
return $requestManager->preserveAuthToken()
    ->send($members)
    ->getResponse();
}
```
**NOTE**: The `preserveAuthToken` call must come before call to `send`.
