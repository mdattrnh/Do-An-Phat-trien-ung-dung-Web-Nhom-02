<?php
/**
 * Application Routes
 * Format: $routes['METHOD /path'] = 'ControllerName@methodName'
 */

$routes = [
    // Frontend SPA
    'GET /' => 'HomeController@index',
    'GET /productdetail/{id}' => 'ProductController@detail',
    'POST /productdetail/{id}/review' => 'ProductController@submitReview',
    
    // API
    'GET /api/products' => 'ApiController@getProducts',
    'GET /api/cart' => 'ApiController@getCart',
    'POST /api/cart/add' => 'ApiController@addToCart',
    'POST /api/cart/update' => 'ApiController@updateCartItem',
    'POST /api/cart/remove' => 'ApiController@removeFromCart',
    'POST /api/checkout' => 'OrderController@processCheckout',
    'POST /api/auth/login' => 'UserController@loginApi',
    
    // Admin Login / Auth
    'GET /admin/login' => 'AdminController@login',
    'POST /admin/login' => 'AdminController@login',
    'GET /admin/logout' => 'AdminController@logout',
    'GET /admin' => 'AdminController@dashboard',
    
    // Admin Dashboard
    'GET /admin/dashboard' => 'AdminController@dashboard',
    
    // Admin Products
    'GET /admin/products' => 'AdminController@products',
    'POST /admin/products/save' => 'AdminController@saveProduct',
    'GET /admin/products/delete' => 'AdminController@deleteProduct',

    // Admin Users
    'GET /admin/users' => 'AdminController@users',
    'GET /admin/users/delete' => 'AdminController@deleteUser',

    // User Registration
    'GET /register' => 'UserController@register',
    'POST /register' => 'UserController@register',
    'GET /logout' => 'UserController@logout',
    
    // Checkout & Payment
    'GET /pay' => 'OrderController@checkout',
    'POST /api/payment' => 'OrderController@processPayment',
];

return $routes;
