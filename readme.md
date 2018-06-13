# Advanced Laravel Permalinks And SEO Management

[![Build Status](https://travis-ci.com/IsraelOrtuno/permalink.svg?branch=master)](https://travis-ci.com/IsraelOrtuno/permalink)

This package allows to create dynamic routes right from database, just like WordPress and other CMS do.

## Work In Progress

This package is currently being developed and tested. Would love feedback ❤️.

## Roadmap
- [ ] Add docs about how to handle automatic SEO tags.
- [ ] [Resources for visual SEO management](https://github.com/IsraelOrtuno/permalink-form) (in progress)

## Documentation
- [Installation](#installation)
- [Getting started](#getting-started)
- [Usage](#usage)
- [Route names](#route-names)
- [Getting the route for a resource](#getting-the-route-for-a-resource)
- [Routes and route groups](#routes-and-route-groups)
- [Nesting routes](#nesting-routes)
- [Creating/updating permalinks manually](#creatingupdating-permalinks-manually)
- [Overriding the default action](#overriding-the-default-action)
- [Support for morphMap & aliasMap](#support-for-morphmap-and-aliasmap)

## Installation

### Install the package

```shell
composer require devio/permalink
```

### Run the migrations

```shell
php artisan migrate
```

## Getting started

This package handles dynamic routing directly from our database. It also supports slug inheritance so we can easily create routes like this `jobs/frontend-web-developer`.

Most of the solutions out there are totally binded to models with polymorphic relationships, however that's not flexible at all when dealing with routes without models. This package supports both, model binded routes and plain routes.

Basically, the package stores routes in a `permalinks` table which contains information about every route: 
- Slug
- Parent slug
- Model (if any)
- Action
- SEO

### Example

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

## Usage

Call the permalink route loader from the `boot` method from any of your application service providers: `AppServiceProvider` or `RouteServiceProviderp` are good examples:

```php
class AppServiceProvider extends ServiceProvider {
    public function boot()
    {
        $this->app->make('permalink')->load();
    }
}
```

The configuration in the models is pretty simple, simply use the `HasPermalinks` trait and implement the `Permalinkable` interface in the models you want to provide the permalink functionality and implement the following methods:

```php
class User extends Model implements \Devio\Permalink\Contracts\Permalinkable {
  use \Devio\Permalink\HasPermalinks;

  /**
   * Get the model action.
   *
   * @return string
   */
  public function permalinkAction()
  {
    return UserController::class . '@show';
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

We are now ready to create a new `User` and the permalink will be automatically generated for us.

```php
$user = User::create(['name' => 'Israel Ortuño']);

$route = $user->route; // 'http://localhost/israel-ortuno

// Permalink (
//  slug:               israel-ortuno
//  parent_id:          NULL
//  permalinkable_type: App\User
//  permalinkable_id:   1
//  action:             NULL
// )
```

Whenever we visit `/israel-ortuno` we will be executing `UserController@show` action. This action will receive the binded model as parameter:

```php
<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class UserController
{
    public function show(Request $request, User $user)
    {
        return $user;
    }
}
```

## Route names

When routes are loaded, they will be named based on their related model or action.

### Route names for permalinks with models

If the route is linked to a model, the route name will be generated by appending the permalink `id` to the string provided by the `permalinkRouteName()` method included in the `HasPermalinks` trait. By default this method returns `permalink` so all routes will be named as `peramlink.{id}`. Feel free to override this method and provide any other name you like.

### Route names for permalinks without models (only action)

Static permalinks which are not linked to models will receive their names by extracting the controller name and the action from their fully qualified action names: `UserController@index` will be transformed into `user.index`.

However, if the permalink action is an alias ([see Support for morphMap & aliasMap](#support-for-morphmap-and-aliasmap)), the alias name itself will be used to name the route.

## Getting the route for a resource

Routes can be resolved as any other Laravel route but just taking into account that they will cannot be resolved by route parameters, they have to be appended nevertheless.

```php
route('permalink.1'); // Get the route of the permalink with id = 1
route('user.5'); // Get the route of the permalink with id = 1
```

**NOTE:** The `id` of the resource is appended to the route name `route.{id}` (note the `.`), be careful to NOT pass the key as parameter `route('permalink', $id)`.

When we are manipulating our model, we do not really want to care about the key of its permalink. Despite we could resolve routes just like in the previous example, `HasPermalinks` trait includes a `route` accessor which will resolve the fully qualified route for the current entity permalink:

```php
$route = $user->route;
// this is just an alias for:
route('permalink.' . $user->permalink->id);
```

Cool and neat!

## Routes and route groups

If a route is not binded to a model and its action is also `NULL`, it will be threated as a route group but won't be registered:

| id | slug          | parent_id | parent_for | permalinkable_type | permalinkable_id | action              
| -- | ------------- | --------- | ---------- | ------------------ | ---------------- | --------------------
| 1  | users         | NULL      | App\User   | NULL               | NULL             | NULL
| 2  | israel-ortuno | 1         | NULL       | App\User           | 1                | NULL

The example above will not generate a `/users` route, `users` will only act as parent of other routes but won't be registered.

## Nesting Routes

At this point, you may be wondering why do we need a `parent_for` column if there's already a `parent_id` being used as foreign key for parent child nesting.

`parent_for` is used in order to automatically discover which route will be the parent of a new stored permalink. Using the table above this section, whenever we store a permalink for a `App\User` model, it will be automatically linked to the permalink `1`.

The `parent_for` will be `NULL` in most cases.


## Creating/updating permalinks manually

By default, this package comes with an observer class which is linked to the `saved` event of your model. Whenever a model is saved, this package will create/update accordingly.

**NOTE:** By default, slugs are only set when creating. They won't be modified when updating unless you explicitly configured the `slugSource` options to do so.

To disable the automatic permalink management you can simply set the value of the property `managePermalinks` of your model to `false`:

```php
class User ... {
    public $managePermalinks = false;
}
```

This will disable any permalink creation/update and you will be then responisble of doing this manually. As the `Permalink` model is actually a polymorphic relationship, you can just create or update the permalink as any other relationship:

```php
$user = User::create(['name' => 'Israel Ortuño']);

$user->permalink()->create(); // This is enough unless you want to manually specify an slug or other options

// or if the permalink exists...
$user->permalink->update([...]);
```

## Overriding the default action

When a permalink is binded to a model, we will guess which action it points to the `permalinkAction` method defined in our `Permalinkable` model. However, we can override this action for a certain model by just specifiying a value into the `action` column of the permalink record:

| id | slug          | permalinkable_type | permalinkable_id | action              
| -- | ------------- | ------------------ | ---------------- | --------------------
| 1  | israel-ortuno | App\User           | 1                | OtherController@action

You could update your model via code as any other normal relationship, for example:

```php
$user = User::find(1);

$user->permalink->update(['action' => 'OtherController@action']);
```

## Support for morphMap & aliasMap

This package provides support for `morphMap`. As you may know, Laravel ships with a `morphMap` method to where you can define a relationship "morph map" to instruct Eloquent to use a custom name for each model instead of the class name.

In adition to the `morphMap` method, this package includes a `aliasMap` static method under the `Permalink` model where you can also define a relationship "alias map" just like the "morph map" but for the permalink actions:

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'users' => 'App\User',
    'posts' => 'App\Post',
]);

use Devio\Permalink\Permalink;

Permalink::aliasMap([
    'user.index'  => 'App\Http\Controllers\UserController@index',
    'user.show'   => 'App\Http\Controllers\UserController@show',
]);
```

You can register these maps in the boot method of your `AppServiceProvider`. The example above will make the `permalinkable_type` and `action` columns look much more readable than showing fully qualified class names.


| id | ... | permalinkable_type | ... | action              
| -- | --- | ------------------ | --- | --------------------
| 1  |     | user               |     | user.show
