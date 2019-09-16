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

### Replace default Router
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

By default, this package takes care of creating/updating/deleting your permalinks based on the actions performed in the bound model. If you do not want this to happen and want to decide when decide the precise moment the permalink has to be created/updated/deleted for this particular model. You can disable the permalink handling in multiple two ways:

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

### Automatic Nesting for Bound Models

#### Automatic Permalink Nesting

The `permalinks` table has a column for automatically nesting models: `parent_for`. This attribute should contain the FQN class name of the model you want it to be parent for. Once set, when you create a new permalink for the specified model, it will automatically nested to the given parent.

This will usually be a manual procedure you will do in you database so it may look like like the [example above](#usage).

#### Disable Automatic Nesting

If you are deep into this package and want to manage the nesting of your permalinks manually (why would you do so? but just in case...), feel free to disable this feature from the config:

```php
// permalink.php
'nest_to_parent_on_create' => false
// or
config()->set('permalink.nest_to_parent_on_create', false);
```

---

WORK IN PROGRESS...
