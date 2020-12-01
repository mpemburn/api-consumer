<?php
/**
 Example:
    'shopify' => [
        'username' => '5e4c0acb3bd00f1a6ac6aa3b155a65c5',
        'password' => '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'
    ]
For greater security, define these values in your .env file:
    SHOPIFY_PASSWORD=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
    ...replaces the above with:
    'password' => env('SHOPIFY_PASSWORD')

You would then add 'shopify' as the return value of your endpoint's getApiName() method

 */
 return [

 ];
