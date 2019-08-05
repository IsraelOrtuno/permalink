# Advanced Laravel Permalinks and SEO Management from Database

[![Build Status](https://travis-ci.com/IsraelOrtuno/permalink.svg?branch=master)](https://travis-ci.com/IsraelOrtuno/permalink) [![Latest Stable Version](https://poser.pugx.org/devio/permalink/version)](https://packagist.org/packages/devio/permalink)

This package allows to create dynamic routes right from database, just like WordPress and other CMS do.

## Roadmap
- [ ] [Resources for visual SEO management](https://github.com/IsraelOrtuno/permalink-form) (in progress)

## Documentation
- [Installation](#installation)
- [Getting started](#getting-started)
- [Usage](#usage)
    - [Replace the default Router]()
    - [Creating a permalink]()
- [Route names](#route-names)
- [Getting the route for a resource](#getting-the-route-for-a-resource)
- [Routes and route groups](#routes-and-route-groups)
- [Nesting routes](#nesting-routes)
- [Creating/updating permalinks manually](#creatingupdating-permalinks-manually)
- [Overriding the default action](#overriding-the-default-action)
- [Support for morphMap & actionMap](#support-for-morphmap--actionmap)
- [Automatic SEO generation](#automatic-seo-generation)

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

This package handles dynamic routing directly from our database. Nested routes are also supported, so we can easily create routes like this `/jobs/frontend-web-developer`.

Most of the solutions out there are totally bound to models with polymorphic relationships, however that's not flexible at all when dealing with routes without models. This package supports both, routes with bound models and regular routes.

Basically, the package stores routes in a `permalinks` table which contains information about every route: 
- Slug
- Parent (parent route for nesting)
- Model (if any)
- Action (controller action or model default action)
- SEO options (title, metas...)

### Example

Let's review a very basic example to understand how it works:

| id | slug          | parent_id | parent_for | entity_type        | entity_id        | action               | final_path            |
| -- | ------------- | --------- | ---------- | ------------------ | ---------------- | -------------------- | --------------------- |
| 1  | users         | NULL      | App\User   | NULL               | NULL             | UserController@index | users
| 2  | israel-ortuno | 1         | NULL       | App\User           | 1                | UserController@show  | users/israel-ortuno

It will run the following (this example tries to be as explicit as possible, internally it uses eager loading and some other performance optimizations):

```php
$router->get('users', 'UserController@index');
$router->get('users/israel-ortuno', 'UserController@show');

// Which will produce:
//    /users                UserController@index
//    /users/israel-ortuno  
```

**NOTE:** The `show` method will receive the user as parameter `App\User::find(1)` the route is bound to that model.

## Usage

### Replace default Router
This package has it's own router which extends the default Laravel router. To replace the default router for the one included in this package you have two options:

##### 1. Replace the router in Http\Kernel.php (recommended)
Bind the router in the default `app/Http/Kernel.php` file. Add the trait This will add the trait `Devio\Permalink\Routing\ReplacesRouter` to the `Kernel` class or run the following command:

```shell
php artisan permalink:replace-router
```

 This will override the Laravel Router bound by default by the one provided by this package.

##### 2. Replace the router before bootstrapping the app (bootstrap/app.php) (only advanced)
If you are hacking Laravel's routing behaviour, you may want to bind the router at bootstrap. Update the default Router class in `bootstrap/app.php` by `Devio\Permalink\Routing\Router` or run this command:

```shell
php artisan permalink:bind-router
```

**IMPORTANT:** Use either `Kernel.php` or `bootstrap/app.php`. **Do not** use both as it may cause unexpected behaviour.

That's pretty much it for setting up the dynamic routing system, feel free to test it out:

```php
Permalink::create([
    'slug' => 'home',
    'action' => 'App\Http\Controllers\HomeController@index'
]);
// Then visit /home
```

### Creating Permalinks
### Manual creation
### Automatic creation

// Then navigate to /home
```

---

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

However, if the permalink action is an "alias" ([see Support for morphMap & actionMap](#support-for-morphmap-and-actionmap)), the action name itself will be used to name the route.

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


## Creating/updating permalinks

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

The values for the newly created or updated permalink will be extracted from:

- A `permalink` key passed into the creation array.
- A `permalink` key from the current request.

## Overriding the default action

When a permalink is binded to a model, we will guess which action it points to the `permalinkAction` method defined in our `Permalinkable` model. However, we can override this action for a certain model by just specifiying a value into the `action` column of the permalink record:

| id | slug   | permalinkable_type | permalinkable_id | action              
| -- | ------ | ------------------ | ---------------- | --------------------
| 1  | madrid | App\City           | 1                | App\Http\Controllers\CityController@show

You could update your model via code as any other normal relationship, for example:

```php
$city = City::find(1);

$city->permalink->update(['action' => 'App\Http\Controllers\CityController@show']);
```

*NOTE:* The action namespace should always be a fully qualified name unless you are using the `actionMap` explained below.

## Support for morphMap & actionMap

This package provides support for `morphMap`. As you may know, Laravel ships with a `morphMap` method to where you can define a relationship "morph map" to instruct Eloquent to use a custom name for each model instead of the class name.

In adition to the `morphMap` method, this package includes an `actionMap` static method under the `Permalink` model where you can also define a relationship "action map" just like the "morph map" but for the permalink actions:

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'users' => 'App\User',
    'posts' => 'App\Post',
]);

use Devio\Permalink\Permalink;

Permalink::actionMap([
    'user.index'  => 'App\Http\Controllers\UserController@index',
    'user.show'   => 'App\Http\Controllers\UserController@show',
]);
```

You can register these maps in the boot method of your `AppServiceProvider`. The example above will make the `permalinkable_type` and `action` columns look much more readable than showing fully qualified class names.


| id | ... | permalinkable_type | ... | action              
| -- | --- | ------------------ | --- | --------------------
| 1  |     | user               |     | user.show

## Automatic SEO generation

For SEO tags generation [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper) is being used. This package offers a powerful set of tools to manage your SEO meta tags.

Permalink package provides content for [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper) form a specific `seo` column in the permalinks table. This column is supposed to store all the SEO related data for a given permalink in a JSON format:

```
{
  "meta": {
    "title": "Specific title",                  // The <title>
    "description": "The meta description",      // The page meta description
    "robots": "noindex,nofollow"                // Robots control
  },
  "opengraph":{
    "title": "Specific OG title",               // The og:title tag
    "description": "The og description",        // The og:description tag
    "image": "path/to/og-image.jpg"             // The og:image tag
  },
  "twitter":{
    "title": "Specific Twitter title",          // The twitter:title tag
    "description": "The twitter description",   // The twitter:description tag
    "image": "path/to/og-image.jpg"             // The twitter:image tag
  }
}
```

In order to have all this content rendered in your HTML you should add the following you your `<meta>`:

```blade
<head>
    {!! seo_helper()->render() !!}
</head>
```

##### OR

```blade
<head>
    {{ seo_helper()->renderHtml() }}
</head>
```

Plase visit [SEO-Helper – Laravel Usage](https://github.com/ARCANEDEV/SEO-Helper/blob/master/_docs/3-Usage.md#4-laravel-usage) to know more about what and how to render.

Under the hood, this JSON structure is calling to the different SEO helpers (meta, opengraph and twitter). Let's understand:

```json
{ 
  "title": "Generic title",
  "image": "path/to/image.jpg",
  "description": "Generic description",
  
  "meta": {
    "title": "Default title",
  },
  "opengraph": {
    "image": "path/to/og-image.jpg"
  }
}
```

This structure will allow you to set a base value for the `title` in all the builders plus changing exclusively the title for the _Meta_ builder. Same with the image, Twitter and OpenGraph will inherit the parent image but OpenGraph will replace its for the one on its builder.

This will call [setTitle](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoMeta.php#L127) from the `SeoMeta` helper and [setImage](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoOpenGraph.php#L78) from the `SeoOpenGraph` helper. Same would happen with Twitter. Take some time to review these three contracts in order to know all the methods available:

- [Metas](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoMeta.php)
- [OpenGraph](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoOpenGraph.php)
- [Twitter](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoTwitter.php)

In order to match any of the helper methods, every JSON option will be transformed to `studly_case` prefixed by `set` and `add`, so `title` will be converted to `setTitle` and `google_analytics` to `setGoogleAnalytics`. How cool is that?

All methods are called via `call_user_func_array`, so if an option contains an array, every key will be pased as parameter to the helper method. See `setTitle` or `addWebmaster` which allows multiple parameters.

### Populate SEO with default content

If you wish that your newly created permalinks get some default value rather than having to specify it, you may define some default fallback methods in your "Permalinkable" entity.

```php
class User extends Model {
  use HasPermalinks;
  
  // ...
  public function getSeoTitleAttribute() 
  {
    return $this->name;
  }
  
  public function getSeoOpenGraphTitleAttribute()
  {
    return $this->name . ' for OpenGraph';
  }
  // ...
}
```

This fallbacks will be used if they indeed exist and the value for that field has not been provided when creating the permalink. Note that these methods should be called as an Eloquent accessor. Use the "seo" prefix and then the path to the default value in a _StudlyCase_, for example:

```
seo.title                   => getSeoTitleAttribute()
seo.description             => getSeoDescriptionAttribute()
seo.twitter.title           => getSeoTwitterTitleAttribute()
seo.twitter.description     => getSeoTwitterDescriptionAttribute()
seo.opengraph.title         => getSeoTwitterOpenGraphAttribute()
seo.opengraph.description   => getSeoOpenGraphDescriptionAttribute()
```

### Builders

To provide even more flexibility, the method calls are piped through 3 classes (one for each helper) called [Builders](https://github.com/IsraelOrtuno/permalink/tree/master/src/Builders). These builders are responsible for calling the right method from the [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper) package.

If there is a method in this builders matching any of the JSON options, the package will execute that method instead of the default behaviour, which would be calling the method (if exists) from the *SEO-Helper* package.

Review the [MetaBuilder](https://github.com/IsraelOrtuno/permalink/blob/master/src/Builders/MetaBuilder.php) as example. This builder contains a `setCanonical` method which is basically used as an alias for `setUrl` (just to be more explicit).

#### Extending Builders

In order to modify the behaviour of any of these builders, you can create your own Builder which should extend the `Devio\Permalink\Contracts\SeoBuilder` interface or inherit the `Devio\Permalink\Builders\Builder` class.

Once you have created your own Builder, just replace the default one in the Container. Add the following to the `register` method of any Service Provider in your application:

```php
// Singleton or not, whatever you require
$this->app->singleton("permalink.meta", function ($app) { // meta, opengraph, twitter or base
  return new MyCustomBuilder;
  
  // Or if you are inheriting the default builder class
  
  return (new MyCustomBuilder($app->make(SeoHelper::class)));
});
```

### Disabling SEO generation

If you wish to prevent the rendering of any of the three Builders (meta, OpenGraph or Twitter), just set its JSON option to false:

```json
{
  "meta": { },
  "opengraph": false,
  "twitter": false
}
```

This will disable the execution of the OpenGraph and Twitter builders.
