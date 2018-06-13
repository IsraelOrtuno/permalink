<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Dummy\DummyUser;

$factory->define(DummyUser::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => '123123123'
    ];
});