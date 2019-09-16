# Advanced Laravel Permalinks and SEO Management from Database

[![Build Status](https://travis-ci.com/IsraelOrtuno/permalink.svg?branch=master)](https://travis-ci.com/IsraelOrtuno/permalink) [![Latest Stable Version](https://poser.pugx.org/devio/permalink/version)](https://packagist.org/packages/devio/permalink)

This package allows to create dynamic routes right from database, just like WordPress and other CMS do.

## Roadmap
- [ ] [Resources for visual SEO management](https://github.com/IsraelOrtuno/permalink-form) (in progress)

## Documentation)
- [Getting the route for a resource](#getting-the-route-for-a-resource)
- [Automatic SEO generation](#automatic-seo-generation)

- [Installation](#installation)
- [Getting Started](#getting-started)
- [Usage](#usage)
	- [Replacing the Default Router](#replacing-the-default-router)
	- [Creating a Permalink](#creating-a-permalink)
	- [Binding Models to Permalinks](#binding-models-to-permalinks)
	- [Automatically Handling Permalinks](#automatically-handling-permalinks)
	- [Nesting Permalinks](#nesting-permalinks)
- [Caching Permalinks](#caching.-permalinks)

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

## Usage

### Replacing the Default Router
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

### Creating a Permalink

That's pretty much it for setting up the dynamic routing system. Let's create a Permalink record and test it out!

```php
Permalink::create([
    'slug' => 'home',
    'action' => 'App\Http\Controllers\HomeController@index'
]);
// Then visit /home
```

If your permalink is bound to a model (read next section), you may create your permalink record like this:

```php
$user = User::create([
    'name' => 'israel',
    'permalink' => [
        'slug' => 'israel-ortuno',
        'action' => 'user.show',
        'seo' => [...] // omit this attribute until you read more about it
    ]
]);
```

Any existing key in the `permalink` array will override its default value when creating the permalink.

**NOTE:** This will only work if `permalinkHandling` has not been disabled, read more about it below.

```

### Binding Models to Permalinks

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

### Automatically Handling Permalinks

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

### Nesting Permalinks

You may want to have a nested permalink structure, let's say, for your blog. Parent will be `/blog` and every post should be inside this path, so you can do things like:

```
/blog           -> Blog index, show all blog posts
/blog/post-1
/blog/post-2
...
```

This package handles this for you out of the box:

#### Automatic Permalink Nesting

The `permalinks` table has a column for automatically nesting models: `parent_for`. This attribute should contain the FQN class name of the model you want it to be parent for. Once set, when you create a new permalink for the specified model, it will automatically nested to the given parent.

This will usually be a manual procedure you will do in you database so it may look like like the [example above](#example).

#### Disable Automatic Nesting

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

#### Manually Nesting

If you wish to nest a permalink to other manually, all you have to do is to set the `id` of the parent permalink to the `parent_id` attribute on the child permalink:

```php
Permalink::create(['slug' => 'my-article', 'parent_id' => 1, 'action' => '...']);
```

### Permalink Actions

The `action` attribute on your permalink record will be providing the information about what's going to handle the request when that permalink matches the current request URI.

#### Controllers as Actions

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

#### Views as Actions

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

#### Default Actions (in Models)

If you have a model bound to a permalink, you may define a default action in your model like this:

```php
public function permalinkAction()
{
    return UserController::class . '@show';
}
```

This method is mandatory once you implement the `Permalinkable` interface.

#### Overriding the Default Action

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

### Caching Permalinks (Read Carefully!)

As mentioned above, this package will perform a single SQL query on every request in order to find a matching permalink for the current URI. This is quite performant and should be ok for most use cases. This query may also be cached for super-fast access if needed.

You may cache your permalink routes into the default Laravel Route Caching system, but be aware that it will generate a route for every single record in your `permalinks` table, so I **DO NOT** recommend it if you have a large amount of permalinks, as you may end up with a huge base64 encoded string in your `bootstrap/cache/routes.php` which may really slow down your application bootstrapping. Perform some tests to know if you are really improving performance for the amount of routes you pretend to cache.

In order to cache you permalinks, all you have to do is to load the entire `permalinks` dataset into the Router and then run the Route Caching command:

```php
Router::loadPermalinks();
Artisan::call('route:cache');
```

You could create a command to perform this two actions or whatever you consider. From now on, you will have to manually update this cache every time a permalink record has been updated.

---

WORK IN PROGRESS...
