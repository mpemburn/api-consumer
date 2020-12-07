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

#### Primary Classes
Each primary class (e.g., `ShopifyEndpoint`, `DiscourseEndpoint` above) needs to extend this package's `AbstractEndpoint` class. Individual endpoints then extends its primary class.  For example:
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
    $requestManager = RequestHandler::make();
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
    $requestManager = RequestHandler::make();
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
