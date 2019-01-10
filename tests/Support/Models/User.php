<?php

namespace Devio\Permalink\Tests\Support\Models;

use Devio\Permalink\HasPermalinks;
use Illuminate\Database\Eloquent\Model;
use Devio\Permalink\Contracts\Permalinkable;
use Devio\Permalink\Tests\Support\Controllers\TestController;

class User extends Model implements Permalinkable
{
    use HasPermalinks;

    /**
     * Get the permalink action for the model.
     *
     * @return string
     */
    public function permalinkAction()
    {
        return TestController::class . '@index';
    }

    /**
     * Get the options for the sluggable package.
     *
     * @return array
     */
    public function permalinkSlug(): array
    {
        return [
            'source' => 'entity.name'
        ];
    }
}