<?php

namespace Devio\Permalink\Tests\other;

use Devio\Permalink\HasPermalinks;
use Illuminate\Database\Eloquent\Model;
use Devio\Permalink\Contracts\Permalinkable;

class DummyUser extends Model implements Permalinkable
{
    use HasPermalinks;

    protected $guarded = [];

    public $table = 'users';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->email = rand() . 'foo@foo.com';
            $model->password = 'foo';
        });
    }

    /**
     * Get the permalink action for the model.
     *
     * @return string
     */
    public function permalinkAction()
    {
        return 'UserController@index';
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