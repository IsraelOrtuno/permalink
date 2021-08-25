# Advanced Laravel Permalinks and SEO Management from Database

[![Build Status](https://travis-ci.com/IsraelOrtuno/permalink.svg?branch=master)](https://travis-ci.com/IsraelOrtuno/permalink) [![Latest Stable Version](https://poser.pugx.org/devio/permalink/version)](https://packagist.org/packages/devio/permalink)

## 2021-08-25: Looking for maintainer

Despite it's pretty stable, I do not have time to keep maintaining this package for future Laravel releases and add more features so I am looking for anyone who found this useful and would like to maintain it. Feel free to contact!
israel@devio.es

-------


This package allows to create dynamic routes right from database, just like WordPress and other CMS do.

**IMPORTANT** Despite the functionality of this package is not complex at all, there are a few things and good practices to consider. I really recommend to carefully read the entire documentation to deeply understand how this package works as you will be replacing the default Laravel Routing System and do not want to mess up with your URLs and SEO!

## Roadmap
- [ ] [Resources for visual SEO management](https://github.com/IsraelOrtuno/permalink-form) (in progress)

## Documentation
- [Getting the route for a resource](#getting-the-route-for-a-resource)
- [Automatic SEO generation](#automatic-seo-generation)

* [Installation](#installation)
* [Getting Started](#getting-started)
* [Replacing the Default Router](#replacing-the-default-router)
* [Creating a Permalink](#creating-permalinks)
* [Updating a Permalink](#updating-permalinks)
* [Binding Models to Permalinks](#binding-models-to-permalinks)
* [Automatically Handling Permalinks](#automatically-handling-permalinks)
* [Nesting Permalinks](#nesting-permalinks)
* [Deleting Permalinks](#deleting-permalinks)
* [Caching Permalinks](#caching.-permalinks)
* [Handling SEO Attributes](#handling-seo-attributes)

## Installation

### Install the package

```shell
composer require devio/permalink
```

### Run the migrations

```shell
php artisan migrate
```

## Getting started (PLEASE READ)

This package handles dynamic routing directly from our database. Nested routes are also supported, so we can easily create routes like this `/jobs/frontend-web-developer`.

Most of the solutions out there are totally bound to models with polymorphic relationships, however that's not flexible at all when dealing with routes without models. This package supports both, routes with bound models and regular routes.

Basically, the package stores routes in a `permalinks` table which contains information about every route: 
- Slug
- Parent (parent route for nesting)
- Model (if any)
- Action (controller action or model default action)
- SEO options (title, metas...)

By default, this package will try to find if there's a a permalink in the `permalinks` table matching the current request path in a single SQL query. This is ok for most of the use cases. If for some reason you want to cache your permalinks information into the Laravel Routing Cache, please refer to the [Caching Permalinks](#caching) section.

### Example

Let check out a very basic example to understand how it internally works:

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

## Replacing the Default Router
This package has it's own router which extends the default Laravel router. To replace the default router for the one included in this package you have two options:

```shell
php artisan permalink:install {--default}
```

The console will propmpt you with 2 options:
```shell
  [0] Http/Kernel.php (Default & Recommended)
  [1] bootstrap/app.php (Advanced)
```

Select the one that fits your needs. For most cases I recommend going through `Http\Kernel.php`. Use the `--default` option to avoid blocking prompts (could also use the default Laravel command's flag `--no-interaction`).

Both of these methods will replace the default Laravel Router by an extended version provided by this package which contains the Permalink management logic.

**IMPORTANT:** Use either `Http\Kernel.php` or `bootstrap/app.php`. **Do not** use both as it may cause unexpected behaviour.

## Creating Permalinks

That's pretty much it for setting up the dynamic routing system. Let's create a Permalink record and test it out!

```php
Permalink::create([
    'slug' => 'home',
    'action' => 'App\Http\Controllers\HomeController@index'
]);
// Then visit /home
```

If your permalink is bound to a model (read next section), you may create your permalink record as follows:

```php
// Note: when using the User::create method, even if permalinkHandling (read more about it below)
// is disabled, it will create the permalink record.
$user = User::create([
    'name' => 'israel',
    'permalink' => [
        'slug' => 'israel-ortuno',
        'action' => 'user.show',
        'seo' => [...] // omit this attribute until you read more about it
    ]
]);

// Or

$user->createPermalink([...);
```

If you do not provide any data to the `permalink` key when using `User::create` or `createPermalink`, it will automatcally use the default data. Any existing key in the data array will override its default value when creating the permalink.

**NOTE:** This will only work if `permalinkHandling` has not been disabled, read more about it below.

## Updating Peramlinks

You can easily update a permalink just like any other Eloquent model. **BE CAREFUL** when updating a permalink slug as the previous URL won't be available anymore and this package does not handle 301/302 redirections.

### Rebuilding Final Path (PLEASE READ)

When updating a slug, the package will recursively update its nested permalinks `final_url` attribute reemplacing the previous slug semgment with the new one. You can control this behaviour from the `rebuild_children_on_update` option in your `config/permalink.php` config file. Disable this option if you wish to handle this task manually (NOT RECOMMENDED).

Check out `Devio\Permalink\Services\PathBuilder` class to discover the methods available for performing the manual update.

**NOTE:** Make sure to rebuild childen's final path in the current request lifecycle.

## Binding Models to Permalinks

You may want to bind a permalink to a model resource, so you can create a unique URL to access that particular resource. If you want to do so, you just have to use the tait `HasPermalinks` and implement the contract `Permalinkable` to your model.

```php
class User extends Model implements \Devio\Permalink\Contracts\Permalinkable;
{
    use \Devio\Permalink\HasPermalinks;
    
    public function permalinkAction()
    {
        return UserController::class . '@show';
    }

    public function permalinkSlug(): array 
    {
        return ['entity.name'];
    }
}
```

Once you have this setup, this package will generate a permalink for every new record of this model automatically.

Also, the `Permalinkable` interface will force you to define two simple methods:

**permalinkAction()**

This method will return the default controller action responsible for handling the request for this particular model. The model itself will be injected into the action (as Laravel usually does for route model binding).

```php 
public function show($user)
{
    return view('users.show', $user);
}
```

**NOTE:** This action will be overwritten by any existing value on the `action` column in your permalink record, so you could have multiple actions for the same model in case you need them. 

**permalinkSlug()**

This method is a bit more tricky. Since all the slugging task is being handled by the brilliant [Sluggable](https://github.com/cviebrock/eloquent-sluggable) package, we do have to provide the info this package requires on its [sluggable](https://github.com/cviebrock/eloquent-sluggable#updating-your-eloquent-models) method.

The permalink model will expose an `entity` polymorphic relationship to this model. Since the slugging occurs in the `Permalink` model class, we do have to specify which is going to be the source for our slug. You can consider `entity` as `$this`, so in this case `entity.name` would be equivalent to `$this->name`. Return multiple items if you would like to concatenate multiple properties:

```
['entity.name', 'entity.city']
```

**NOTE:** This method should return an array compatible with the Sluggable package, please [check the package documentation](https://github.com/cviebrock/eloquent-sluggable#updating-your-eloquent-models) if you want to go deeper.

## Automatically Handling Permalinks

By default, this package takes care of creating/updating/deleting your permalinks based on the actions performed in the bound model. If you do not want this to happen and want to decide when decide the precise moment the permalink has to be created/updated/deleted for this particular model. You can disable the permalink handling in two ways:

```php

// Temporally disable/enable:
$model->disablePermalinkHandling();
$model->enablePermalinkHandling();

// Permanent disable or return a condition.
// Create this method in you model:
public function permalinkHanlding()
{
    return false;
}
```

### Creating

A permalink will be created automatically when your resource fires a `saved` event. It will be populate with the default data unless you have provided a `peramlink` key array to the creation array or used the `setPermalinkAttribute` mutator.

```php
User::create(['name' => 'israel', 'permalink' => ['slug' => 'israel']]);
//
$user = new User;
$user->permalink = ['slug' => 'israel'];
$user->save();
```

If `permalinkHandling` is disabled, you will be able to decide when to create the permalink:

```php
// Assume permalinkHanlding() returns false
$user = User::create(['name' => 'israel']);
// Perform other tasks...
$user->createPermalink(); // Array is optional, provide data to override default values
```

**NOTE:** Be aware that the permalink record will be still created if the data provided for creation contains a `permalink` key.

### Updating

You can update your permalink right like creating:

```php
$user = User::find(1);

$user->updatePermalink(['seo' => ['title' => 'changed']]);
```

**NOTE:** By default, if you update a permalink's slug, it will recursively update all its nested elements with the new segment. Read more about [updating permalinks](#updating-permalinks).

### Deleting

If you delete a resource which is bound to a permalink record, the package will automatically destroy the permalink for us. Again, if you do not want this to happen and want to handle this yourself, disable the permalink handling in your model.

### Support for SoftDeleting

SoftDeleting support comes out of the box, so if your resource is soft deleted, the permalink will be soft deleted too. If you restore your resource, it will be restored automatically too. Disable handling for dealing with this task manually.

**NOTE:** If you `forceDelete()` your resource, the permalink will also be deleted permanently.

## Nesting Permalinks

You may want to have a nested permalink structure, let's say, for your blog. Parent will be `/blog` and every post should be inside this path, so you can do things like:

```
/blog           -> Blog index, show all blog posts
/blog/post-1
/blog/post-2
...
```

This package handles this for you out of the box:

### Automatic Permalink Nesting

The `permalinks` table has a column for automatically nesting models: `parent_for`. This attribute should contain the FQN class name of the model you want it to be parent for. Once set, when you create a new permalink for the specified model, it will automatically nested to the given parent.

This will usually be a manual procedure you will do in you database so it may look like like the [example above](#example).

### Disable Automatic Nesting

If you are deep into this package and want to manage the nesting of your permalinks manually (why would you do so? but just in case...), feel free to disable this feature from the config:

```php
// Globally disable this feature for all models in your permalink.php config file
'nest_to_parent_on_create' => false
// or
config()->set('permalink.nest_to_parent_on_create', false);

// Disable this feature for a particular model. Define this method in your model class:
public function permalinkNestToParentOnCreate()
{
    return false;
}
```

### Manually Nesting

If you wish to nest a permalink to other manually, all you have to do is to set the `id` of the parent permalink to the `parent_id` attribute on the child permalink:

```php
Permalink::create(['slug' => 'my-article', 'parent_id' => 1, 'action' => '...']);
```

## Permalink Actions

The `action` attribute on your permalink record will be providing the information about what's going to handle the request when that permalink matches the current request URI.

### Controllers as Actions

Every permalink should have a action, specifically those which are not bound to models. You should specify a `controller@action` into the `action` column of your permalink record.

If there's a model bound to the permalink (entity), it will be passed as parameter to the controller action:

```php
class UserController {
    public function show($user)
    {
        return view('users.show', compact('user'));
    }
}
```

### Views as Actions

For simple use cases you could simply specify a view's path as an action for your permalink. The permalink entity (if bound to a model) will also be available in this view as mentioned above:

```php
Permalink::create(['slug' => 'users', 'action' => 'users.index']);
```

If bound to a model...

```php
Permalink::create(['slug' => 'israel-ortuno', 'entity_type' => User::class, 'entity_id' => 1, 'action' => 'users.show']);

// And then in users/show.blade.php
<h1>Welcome {{ $user->name }}</h1>
```

#### Using a Custom Controller for View Actions

Under the hood, view actions are handled by a controller provided by this package `Devio\Permalink\Http\PermalinkController`. You can update this controller with your own implementation if needed. Maybe you want to apply some middleware, or resolve views in a different way...

All you have to do is to bind your implementation to the container in your `AppServiceProvider` (or other):

```php
// In your AppServiceProvider.php
public function register()
{
    $this->bind('Devio\Permalink\Http\PermalinkController', YourController::class);
}

// And then...
class YourController 
{
    use Devio\Permalink\Http\ResolvesPermalinkView;

    public function __construct()
    {
        // Do your stuff.
    }
}
```

This way, Laravel will now resolve your implementation out of the container.

If you wish to have your own implementation for resolving the views, do not use the `Devio\Permalink\Http\ResolvesPermalinkView` trait and create your own `view()` method.

### Default Actions (in Models)

If you have a model bound to a permalink, you may define a default action in your model like this:

```php
public function permalinkAction()
{
    return UserController::class . '@show'; // Or a view
}
```

This method is mandatory once you implement the `Permalinkable` interface.

### Overriding the Default Action

By default, the permalink will resolve the action based on the `permlainkAction` method of the permalink entity. However, if you specifiy a value to the `action` column in the permalink record, it will override the default action. For example:

```php
class User extends Model
{
    use HasPermalinks;
    ...
    public function permalinkAction()
    {
        return UserController::class . '@index';
    }
    ...
}

// And then...
$user = User::create([
    'name' => 'israel',
    'permalink' => [
        'action' => 'user.show'
    ]
]);
// Or just update the action attribute as you like
```

When accessing the permalink for this particular entity, `user/show.blade.php` will be responsible for handling the request rather than the default controller. Isn't it cool?

## Deleting Permalinks

By default, and if

### Support for SoftDeleting

## Caching Permalinks (Read Carefully!)

As mentioned above, this package will perform a single SQL query on every request in order to find a matching permalink for the current URI. This is quite performant and should be ok for most use cases. This query may also be cached for super-fast access if needed.

You may cache your permalink routes into the default Laravel Route Caching system, but be aware that it will generate a route for every single record in your `permalinks` table, so I **DO NOT** recommend it if you have a large amount of permalinks, as you may end up with a huge base64 encoded string in your `bootstrap/cache/routes.php` which may really slow down your application bootstrapping. Perform some tests to know if you are really improving performance for the amount of routes you pretend to cache.

In order to cache you permalinks, all you have to do is to load the entire `permalinks` dataset into the Router and then run the Route Caching command:

```php
Router::loadPermalinks();
Artisan::call('route:cache');
```

You could create a command to perform this two actions or whatever you consider. From now on, you will have to manually update this cache every time a permalink record has been updated.

## Handling SEO Attributes

This package wouldn't be complete if you could not configure your SEO attributes for every single permalink record, it would have been almost useless!

## Automatic SEO generation

For SEO tags generation [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper) is being used. This package offers a powerful set of tools to manage your SEO meta tags.

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

**NOTE:** This is just an example of the most common tags but you could any kind of tag supported (index, noindex...) by [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper), just make sure to nest it correctly.

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

### Understanding How it Works

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

This structure will allow you to set a base value for the `title` in all the builders plus changing exclusively the title for the _Meta_ section. Same with the image, Twitter and OpenGraph will inherit the parent image but OpenGraph will replace its for the one on its builder. This way you will be able to display different information on every section!

This will call [setTitle](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoMeta.php#L127) from the `SeoMeta` helper and [setImage](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoOpenGraph.php#L78) from the `SeoOpenGraph` helper. Same would happen with Twitter. Take some time to review these three contracts in order to know all the methods available:

- [Metas](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoMeta.php)
- [OpenGraph](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoOpenGraph.php)
- [Twitter](https://github.com/ARCANEDEV/SEO-Helper/blob/master/src/Contracts/SeoTwitter.php)

In order to match any of the helper methods, every JSON option will be transformed to `studly_case` prefixed by `set` and `add`, so `title` will be converted to `setTitle` and `google_analytics` to `setGoogleAnalytics`. How cool is that?

All methods are called via `call_user_func_array`, so if an option contains an array, every key will be pased as parameter to the helper method. See `setTitle` or `addWebmaster` which allows multiple parameters.

### Populating SEO Attributes

You can specify the SEO attributes for your permalink by just passing an array of data to the `seo` attribute:

```php
Peramlink::create([
  'slug' => 'foo',
  'seo' => [
    'title' => 'this is a title',
    'description' => 'this is a description',
    'opengraph' => [
      'title' => 'this is a custom title for og:title'
    ]
  ]
);
```

#### Populating SEO Attributes with Default Content

You will usually want to automatically populate your SEO information directly from your bound model information. You can do so by creating fallback methods in you model as shown below:

```php
public function getPeramlinkSeoTitleAttribute() 
{
  return $this->name;
}
  
public function getPermalinkSeoOpenGraphTitleAttribute()
{
  return $this->name . ' for OpenGraph';
}
```

This fallbacks will be used if they indeed exist and the value for that field has not been provided when creating the permalink. Note that these methods should be called as an Eloquent accessor. Use the _permalinkSeo_ prefix and then the path to the default value in a _StudlyCase_, for example:

```
seo.title                   => getPermalinkSeoTitleAttribute()
seo.description             => getPermalinkSeoDescriptionAttribute()
seo.twitter.title           => getPermalinkSeoTwitterTitleAttribute()
seo.twitter.description     => getPermalinkSeoTwitterDescriptionAttribute()
seo.opengraph.title         => getPermalinkSeoTwitterOpenGraphAttribute()
seo.opengraph.description   => getPermalinkSeoOpenGraphDescriptionAttribute()
```

The package will look for any matching method, so you can create as many methods as your seo set-up may need, even if you are just creating custom meta tags so `getPermalinkMyCustomMetaDescriptionAttribute` would match if there's a `seo.my.custom.meta.description` object.

### SEO Builders

To provide even more flexibility, the method calls are piped through 3 classes (one for each helper) called [Builders](https://github.com/IsraelOrtuno/permalink/tree/master/src/Builders). These builders are responsible for calling the right method on the [ARCANDEV/SEO-Helper](https://github.com/ARCANEDEV/SEO-Helper) package.

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

If you wish to use other package for generating the SEO meta tags, extending and modifying the builders will do the trick.

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
