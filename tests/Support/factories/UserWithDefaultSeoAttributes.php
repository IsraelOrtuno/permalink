<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Support\Models\UserWithDefaultSeoAttributes;

$factory->define(UserWithDefaultSeoAttributes::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => ''
    ];
});