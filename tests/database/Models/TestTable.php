<?php

namespace ariby\TimeIntervalSelect\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 遊戲站錢包
 *
 * @package SuperPlatform\StationWallet\Models
 */
class TestTable extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
    protected $primaryKey = 'id';

    protected $table = 'test_table';
}
