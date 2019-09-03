<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Support\Models\UserWithDisabledPermalinkHandling;

$factory->define(UserWithDisabledPermalinkHandling::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => ''
    ];
});
