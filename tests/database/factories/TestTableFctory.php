<?php

use Faker\Generator as Faker;
use Ariby\tests\database\Models\TestTable;

$factory->define(TestTable::class, function (Faker $faker) {
    date_default_timezone_set('Asia/Taipei');

    $ran = rand(1,3);

    // before假資料
    if($ran == 1){
        $start_at = $faker->dateTimeInInterval('-3 days', 'now', 'Asia/Taipei');
        $end_at = $faker->dateTimeInInterval('-1 days', 'now', 'Asia/Taipei');
    }else if($ran == 2){
    // now 假資料
        $start_at = $faker->dateTimeInInterval('-3 days', 'now', 'Asia/Taipei');
        $end_at = $faker->dateTimeInInterval('now', '+3 days', 'Asia/Taipei');
    }else{
    // after 假資料
        $start_at = $faker->dateTimeInInterval('+1days', 'now', 'Asia/Taipei');
        $end_at = $faker->dateTimeInInterval('now', '+3 days', 'Asia/Taipei');
    }

    return [
        'id' => $faker->uuid,
        'name' => $faker->realText(10),
        'status' => $faker->randomElement(['未上映', '上映中', '已下映']),
        'test1' => $faker->randomElement(['t1','t2','t3']),
        'test2' => $faker->randomElement(['t1','t2','t3']),
        'test3' => $faker->randomElement(['t1','t2','t3']),
        'start_at' => $start_at,
        'end_at' => $end_at,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
});
