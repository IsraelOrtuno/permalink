<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Support\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => ''
    ];
});