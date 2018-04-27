<?php

namespace ariby\TimeIntervalSelect\Commands;

use Illuminate\Console\Command;
use ariby\TimeIntervalSelect\TimeIntervalSelect;

class GetIDs extends Command
{
    // 命令名稱
    protected $signature = 'TimeSelect:getIDs {tableName} {method} {primaryKey} {beforeTimeKey} {afterTimeKey} {tagArray*}';

    // 說明文字
    protected $description = '{tableName : The name of table.}
                       {method : 1:before, 2:now, 3:after}
                       {primaryKey : The column you want to return after search.}
                       {beforeTimeKey : The column name in the database.}
                       {afterTimeKey : The column name in the database.}
                       {tagArray* : The rule that you want to set. The first parameter is key, and after is value. e.g. send "status false tag error" means ["staus" => "false", "tag" => "error"]}';

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
                $result = TimeIntervalSelect::checkBefore($tableName,$tagArray, $keyArray, function(){});
                break;
            case '2':
                $result = TimeIntervalSelect::checkNow($tableName,$tagArray, $keyArray, function(){});
                break;
            case '3':
                $result = TimeIntervalSelect::checkAfter($tableName,$tagArray, $keyArray, function(){});
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