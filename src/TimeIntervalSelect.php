<?php

namespace ariby\TimeIntervalSelect;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * 遊戲站錢包管理（服務）器，提供方法執行錢包相關操作，餘額操作需套用交易模式
 */
class TimeIntervalSelect
{
    /**
     * 遊戲站連結器集合
     *
     * @var array
     */
    protected $connectors = [];

    /**
     * constructor.
     */
    public function __construct()
    {

    }

    public static function test()
    {
        return 'ok';
    }

    /**
     * @param $tableName
     * @param array $tag
     * @param array $columnName
     * @param $function
     * @return array
     */
    public static function checkBefore($tableName, $tag = [], $columnName = ['primaryKey' => 'id', 'start_at' => 'start_at', 'end_at' => 'end_at'], $function)
    {
        /* check Input != null */
        if(is_null($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName IS NULL');
        }
        if(is_null($tag)){
            return array('ok' => 'false', 'msg' => 'Input tag IS NULL');
        }
        if(is_null($columnName)){
            return array('ok' => 'false', 'msg' => 'Input columnName IS NULL');
        }
        /* check tableName exist */
        if(!Schema::hasTable($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName DO NOT EXIST');
        }
        /* check Input */
        if(!is_array($columnName)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!is_array($tag)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!array_key_exists("primaryKey", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "primaryKey"');
        }
        if(!array_key_exists("start_at", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "start_at"');
        }
        /* check DB column */
        if(!Schema::hasColumn($tableName, $columnName['primaryKey'])){
            return array('ok' => 'false', 'msg' => 'Column primaryKey "'.$columnName['primaryKey'].'" DO NOT EXIST');
        }
        if(!Schema::hasColumn($tableName, $columnName['start_at'])){
            return array('ok' => 'false', 'msg' => 'Column start_at "'.$columnName['start_at'].'" DO NOT EXIST');
        }
        foreach(array_keys($tag) as $fake_key => $real_key){
            if(!Schema::hasColumn($tableName, $real_key)){
                return array('ok' => 'false', 'msg' => 'Column in $tag "'.$real_key.'" DO NOT EXIST');
            }
        }

        /* set */
        $pk  = $columnName['primaryKey'];
        $start_at = $columnName['start_at'];
        date_default_timezone_set('Asia/Taipei');
        $now = $datetime= date("Y-m-d H:i:s");

        /* 基本select */
        $sql_string = "SELECT {$pk} FROM {$tableName} WHERE {$start_at} < '{$now}'";
        /* 加入tag限制 */
        foreach($tag as $key => $item){
            if(is_array($item)){
                /* tag為複合陣列 表單一鍵值有多個條件 */
                foreach($item as $column => $value){
                    $sql_string = $sql_string." AND {$column} != '{$value}'";
                }
            }else{
                /* tag為字串 表單一鍵值只有一個條件 */
                $sql_string = $sql_string." AND {$key} != '{$tag[$key]}'";
            }
        }
        $sql_result = DB::select($sql_string);
        $answerIDs = array_column($sql_result, $pk);

        $resultArray = array('ok' => 'false', 'msg' => '', 'data' => $answerIDs);

        $resultArray['ok'] = 'true';
        $function($answerIDs);

        return $resultArray;
    }

    /**
     * @param $tableName
     * @param array $tag
     * @param array $columnName
     * @param $function
     * @return array
     */
    public static function checkNow($tableName, $tag = [], $columnName = ['primaryKey' => 'id', 'start_at' => 'start_at', 'end_at' => 'end_at'], $function)
    {
        /* check Input != null */
        if(is_null($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName IS NULL');
        }
        if(is_null($tag)){
            return array('ok' => 'false', 'msg' => 'Input tag IS NULL');
        }
        if(is_null($columnName)){
            return array('ok' => 'false', 'msg' => 'Input columnName IS NULL');
        }
        /* check tableName exist */
        if(!Schema::hasTable($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName DO NOT EXIST');
        }
        /* check Input */
        if(!is_array($columnName)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!is_array($tag)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!array_key_exists("primaryKey", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "primaryKey"');
        }
        if(!array_key_exists("start_at", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "start_at"');
        }
        if(!array_key_exists("end_at", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "end_at"');
        }
        /* check DB column */
        if(!Schema::hasColumn($tableName, $columnName['primaryKey'])){
            return array('ok' => 'false', 'msg' => 'Column primaryKey "'.$columnName['primaryKey'].'" DO NOT EXIST');
        }
        if(!Schema::hasColumn($tableName, $columnName['start_at'])){
            return array('ok' => 'false', 'msg' => 'Column start_at "'.$columnName['start_at'].'" DO NOT EXIST');
        }
        if(!Schema::hasColumn($tableName, $columnName['end_at'])){
            return array('ok' => 'false', 'msg' => 'Column end_at "'.$columnName['end_at'].'" DO NOT EXIST');
        }
        foreach(array_keys($tag) as $fake_key => $real_key){
            if(!Schema::hasColumn($tableName, $real_key)){
                return array('ok' => 'false', 'msg' => 'Column in $tag "'.$real_key.'" DO NOT EXIST');
            }
        }

        /* set */
        $pk  = $columnName['primaryKey'];
        $start_at = $columnName['start_at'];
        $end_at = $columnName['end_at'];
        date_default_timezone_set('Asia/Taipei');
        $now = $datetime= date("Y-m-d H:i:s");

        /* 基本select */
        $sql_string = "SELECT {$pk} FROM {$tableName} WHERE {$start_at} < '{$now}' AND {$end_at} > '{$now}'";
        /* 加入tag限制 */
        foreach($tag as $key => $item){
            if(is_array($item)){
                /* tag為複合陣列 表單一鍵值有多個條件 */
                foreach($item as $column => $value){
                    $sql_string = $sql_string." AND {$column} != '{$value}'";
                }
            }else{
                /* tag為字串 表單一鍵值只有一個條件 */
                $sql_string = $sql_string." AND {$key} != '{$tag[$key]}'";
            }
        }
        $sql_result = DB::select($sql_string);
        $answerIDs = array_column($sql_result, $pk);

        $resultArray = array('ok' => 'false', 'msg' => '', 'data' => $answerIDs);

        $resultArray['ok'] = 'true';
        $function($answerIDs);

        return $resultArray;
    }

    /**
     * @param $tableName
     * @param array $tag
     * @param array $columnName
     * @param $function
     * @return array
     */
    public static function checkAfter($tableName, $tag = [], $columnName = ['primaryKey' => 'id', 'start_at' => 'start_at', 'end_at' => 'end_at'], $function)
    {
        /* check Input != null */
        if(is_null($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName IS NULL');
        }
        if(is_null($tag)){
            return array('ok' => 'false', 'msg' => 'Input tag IS NULL');
        }
        if(is_null($columnName)){
            return array('ok' => 'false', 'msg' => 'Input columnName IS NULL');
        }
        /* check tableName exist */
        if(!Schema::hasTable($tableName)){
            return array('ok' => 'false', 'msg' => 'Input tableName DO NOT EXIST');
        }
        /* check Input */
        if(!is_array($columnName)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!is_array($tag)){
            return array('ok' => 'false', 'msg' => 'Second Input is not Array');
        }
        if(!array_key_exists("primaryKey", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "primaryKey"');
        }
        if(!array_key_exists("end_at", $columnName)){
            return array('ok' => 'false', 'msg' => 'Input Array DO NOT HAS "end_at"');
        }
        /* check DB column */
        if(!Schema::hasColumn($tableName, $columnName['primaryKey'])){
            return array('ok' => 'false', 'msg' => 'Column primaryKey "'.$columnName['primaryKey'].'" DO NOT EXIST');
        }
        if(!Schema::hasColumn($tableName, $columnName['end_at'])){
            return array('ok' => 'false', 'msg' => 'Column end_at "'.$columnName['end_at'].'" DO NOT EXIST');
        }
        foreach(array_keys($tag) as $fake_key => $real_key){
            if(!Schema::hasColumn($tableName, $real_key)){
                return array('ok' => 'false', 'msg' => 'Column in $tag "'.$real_key.'" DO NOT EXIST');
            }
        }

        /* set */
        $pk  = $columnName['primaryKey'];
        $end_at = $columnName['end_at'];
        date_default_timezone_set('Asia/Taipei');
        $now = $datetime= date("Y-m-d H:i:s");

        /* 基本select */
        $sql_string = "SELECT {$pk} FROM {$tableName} WHERE {$end_at} < '{$now}'";
        /* 加入tag限制 */
        foreach($tag as $key => $item){
            if(is_array($item)){
                /* tag為複合陣列 表單一鍵值有多個條件 */
                foreach($item as $column => $value){
                    $sql_string = $sql_string." AND {$column} != '{$value}'";
                }
            }else{
                /* tag為字串 表單一鍵值只有一個條件 */
                $sql_string = $sql_string." AND {$key} != '{$tag[$key]}'";
            }
        }
        $sql_result = DB::select($sql_string);
        $answerIDs = array_column($sql_result, $pk);

        $resultArray = array('ok' => 'false', 'msg' => '', 'data' => $answerIDs);

        $resultArray['ok'] = 'true';
        $function($answerIDs);

        return $resultArray;
    }

}