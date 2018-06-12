<?php

use Faker\Generator as Faker;

$factory->define(\Devio\Permalink\Tests\DummyUser::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
        'email'    => $faker->email,
        'password' => '123123123'
    ];
});