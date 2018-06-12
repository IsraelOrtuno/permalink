# Laravel Permalinks and SEO

This package allows to create dynamic routes right from database, just like WordPress and other CMS do.

### Installation

#### Install the package

```shell
composer require devio/permalink
```

#### Run the migrations

```shell
php artisan migrate
```

### Getting Started

This package handles dynamic routing directly from our database. It also supports slug inheritance so we can easily create routes like this `jobs/frontend-web-developer`.

Most of the solutions out there are totally binded to models with polymorphic relationships, however that's not flexible at all when dealing with routes without models. This package supports both, model binded routes and plain routes.

Basically, the package stores routes in a `permalinks` table which contains information about every route: 
- Slug
- Parent slug
- Model (if any)
- Action
- SEO

#### Example

Let's review a very basic example to understand how it works:

| id | slug          | parent_id | parent_for | permalinkable_type | permalinkable_id | action              
| -- | ------------- | --------- | ---------- | ------------------ | ---------------- | --------------------
| 1  | users         | NULL      | App\User   | NULL               | NULL             | UserController@index
| 2  | israel-ortuno | 1         | NULL       | App\User           | 1                | NULL

This will run the following:

```php
$router->get('users', 'UserController@index');

$router->group(['prefix' => 'users'], function() {
  $user->get('israel-ortuno', User::find(1)->permalinkAction())
});

// Which will produce:
//    /users                UserController@index
//    /users/israelOrtuno   Whatever action configured into the permalinkAction method
```

### 
