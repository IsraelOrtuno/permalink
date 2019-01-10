<?php

use Faker\Generator as Faker;
use Devio\Permalink\Tests\Support\Models\Company;

$factory->define(Company::class, function (Faker $faker) {
    return [
        'name'     => $faker->name,
    ];
});