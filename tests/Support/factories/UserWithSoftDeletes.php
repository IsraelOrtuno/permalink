<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Support\Models\UserWithSoftDeletes;

$factory->define(UserWithSoftDeletes::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => ''
    ];
});