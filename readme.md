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

It will run the following (this example tries to be as explicit as possible, internally it uses eager loading and some other performance optimizations):

```php
$router->get('users', 'UserController@index');

$router->group(['prefix' => 'users'], function() {
  $user->get('israel-ortuno', User::find(1)->permalinkAction())
});

// Which will produce:
//    /users                UserController@index
//    /users/israelOrtuno   Whatever action configured into the permalinkAction method
```

### Usage

The configuration is pretty simple, simply use the `HasPermalinks` trait and implement the `Permalinkable` interface in the models you want to provide the permalink functionality and implement the following methods:

```php
class Product extends Model implements \Devio\Permalink\Contracts\Permalinkable {
  use \Devio\Permalink\HasPermalinks;

  /**
   * Get the model action.
   *
   * @return string
   */
  public function permalinkAction()
  {
    return UserController::class . '@index';
  }

  /**
   * Get the options for the sluggable package.
   *
   * @return array
   */
  public function slugSource(): array
  {
    return ['source' => 'permalinkable.name'];
  }
}
```

This model is now fully ready to work with. 

The package uses [cviebrock/eloquent-sluggable](https://github.com/cviebrock/eloquent-sluggable) for the automatic slug generation, so the `slugSource` method should return an array of options compatible with the `eloquent-sluggable` options. By just providing the `source` key should be enough for most cases, but in case you want to update other options, here you can do so. Basically we are pointing that the slug will be generated from the `name` field of the `permalinkable` relationship of the permalink model, which will be the current model.

The `permalinkAction` method should return the default action hanlder for this model, just like if we were setting a route here: `Controller@action`. You could even return a `Closure`.

**NOTE:** Be aware that Laravel cannot cache `Closure` based routes.

We are now ready to create a new `Product` and the permalink will be automatically generated for us.

```php
$product = Product::create(['name' => 'Laravel Sticker']);

// $permalink = [
//    'slug'      => 'laravel-sticker',
//    'parent_id' => NULL,

```
