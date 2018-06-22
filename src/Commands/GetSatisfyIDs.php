<?php

namespace Ariby\UpdateStatusByTime\Commands;

use Illuminate\Console\Command;
use Ariby\UpdateStatusByTime\UpdateStatusByTime;

class GetSatisfyIDs extends Command
{
    // 命令名稱
    protected $signature = 'UpdateStatusByTime:get-satisfy-ids {tableName} {method} {primaryKey} {beforeTimeKey} {afterTimeKey} {tagArray*}';

    // 說明文字
    protected $description =
        '參數說明如下：'."\n".
        '{tableName : The name of table in the database.}'."\n".
        '{method : 1:before, 2:between, 3:after}'."\n".
        '{primaryKey : The column you want to return after search.}'."\n".
        '{beforeTimeKey : The column name in the database.}'."\n".
        '{afterTimeKey : The column name in the database.}'."\n".
        '{beforeTimeKey : The column name in the database.}'."\n".
        '{afterTimeKey : The column name in the database.}'."\n".
        '{tagArray* : The rule that you want to set. The first parameter is key, and after is value. e.g. send "status false tag error" means ["staus" => "false", "tag" => "error"]}';

    public function __construct()
    {
        parent::__construct();
    }

    // Console 執行的程式
    public function handle()
    {
        $arguments = $this->arguments();
        $tableName = $arguments['tableName'];
        $method = $arguments['method'];
        $keyArray = [
            'primaryKey' => $arguments['primaryKey'],
            'start_at' => $arguments['beforeTimeKey'],
            'end_at' => $arguments['afterTimeKey']
        ];
        $tag = $arguments['tagArray'];
        $tagArray = array();
        for($i = 0; $i < count($tag) ; $i+=2){
            $tagArray[$tag[$i]] = $tag[$i+1];
        }

        switch($method){
            case '1':
                $result = UpdateStatusByTime::checkBefore($tableName,$tagArray, $keyArray, function(){});
                break;
            case '2':
                $result = UpdateStatusByTime::checkBetween($tableName,$tagArray, $keyArray, function(){});
                break;
            case '3':
                $result = UpdateStatusByTime::checkAfter($tableName,$tagArray, $keyArray, function(){});
                break;
        }

        if($result['ok'] != 'true'){
            $this->error($result['msg']);
        }else{
            $this->line("Number: ".count($result['data']));
            $this->line("result: ".implode(" ,", $result['data']));
        }

    }
}