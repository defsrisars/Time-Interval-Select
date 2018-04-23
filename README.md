# 資料庫時間間隔查詢器

此package之使用情境為，假設今有一Table movies：

    /*
    |--------------------------------------------------------------------------
    | movies
    |--------------------------------------------------------------------------
    |
    | 記錄目前所有電影的資訊，欄位如下：
    |
    */
    |--------------------------------------------------------------------------
    | id  |   name   |   status    |   start_at     |    end_at ...
    |--------------------------------------------------------------------------
    |  0  | 哈利波特  |    已下檔    |  2018-02-01    |   2018-02-01 ...
    |--------------------------------------------------------------------------
    |  1  | 與神同行  |    上映中    |  2018-04-01    |   2018-04-15 ...
    |--------------------------------------------------------------------------
    |  2  | 玩命關頭  |    上映中    |  2018-04-10    |   2018-04-30 ...
    |--------------------------------------------------------------------------
    |  3  | 全面啟動  |    未上映    |  2018-04-16    |   2018-05-30 ...
    |--------------------------------------------------------------------------
    |  4  | 明天過後  |    未上映    |  2018-06-01    |   2018-08-01 ...
    |--------------------------------------------------------------------------
    | ... |   ...    |    ...       |       ...     |    ...
    且假設有測試欄位test1=t3,test2=t1,test3=t2，完全不符合test1=t1,test2=t2,test3=t3(見下使用方式範例)
    
    假設今天日期是2018-04-16，那麼我們會發現，在資料庫中
    id=1的與神同行的"status"應該被更新為"已下檔"
    id=3的明天過後的"status"應該被更新為"上映中"
    
    這個package的功能就是為你找出那些應該被更新的row id
    你可以依據不同的輸入來做限制
    類似的使用有文章定時發佈與隱藏等...
    
    再配合Laravel的排程使用，即可使程式依自已所需要的頻率自動定期更新維護table資訊

## 安裝
因為此套件是私有版控庫，如果要安裝此 package 的專案，必需在自己的 composer.json 先定義版控庫來源

    // composer.json
    
    ...(略)...
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/defsrisars/Time-Interval-Select.git"
        }
    ],
    ...(略)...

接著就可以透過下列指令進行安裝

    composer require ariby/Time-Interval-Select

## 使用方法

目前的Method一共有3個，以查詢的時間來做區分<br>
分別是checkBefore、checkNow、checkAfter<br>
且都必須傳入四個參數，依序分別是：<br>

$tableName => 欲查詢的table名稱，以上例就是"movies"<br>

$tag => Array，欲做比對的欄位與其值，以movis為例如下<br>
['status' => '上映中']<br>
則會加入status != "上映中"的條件，若呼叫checkBefore便可找出時間應該在"上映中"，
其值卻不為"上映中"的row之primaryKey<br>
且參數值可為Array，便可對單一鍵值做複合查詢，如：<br>
['status' => ['上映中', '已下檔']]，相當於 status != "上映中" && status != "已下檔"

$columnName => Array，存入table中的column欄位名稱，實際格式如下<br>
['primaryKey' => "table中欲回傳的欄位名稱，如id",<br>
 'start_at'   => 'table中記錄判斷起始時間的欄位名稱',<br>
 'end_at'     => 'table中記錄判斷結束時間的欄位名稱'
]<br>
其中checkBefore可不傳入end_at、checkAfter可不傳入start_at<br>
checkNow則兩者皆必須傳入

$function => 為一閉包函式，應接收一陣列參數，會包含查詢結果，可執行使用者想做之事

以下為以movies為使用案例的程式

#### checkBefore
=
=SELECT $primaryKey FROM $tableName WHERE start_at > now() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkBefore("movies",
    ['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3', 'status' => '未上映'], 
    array('primaryKey'=>'id', 'start_at'=>'start_at','status'=>'status'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「未上映」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'未上映'];

即可將符合時間條件與tag條件之資料做修改

#### checkNow
=
=SELECT $primaryKey FROM $tableName WHERE start_at < now() AND end_at > NOW() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkNow("movies",
    ['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3', 'status' => '上映中'], 
    array('primaryKey'=>'id', 'start_at'=>'start_at', 'end_at' => 'end_at', status'=>'status'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(3); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「上映中」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'上映中'];

即可將符合時間條件與tag條件之資料做修改

    
#### checkAfter
=
=SELECT $primaryKey FROM $tableName WHERE end_at < NOW() AND $tag[$key] != $tag[$key]->value...

    $result = TimeIntervalSelect::checkAfter("movies",
    ['test1' => 't1', 'test2'=>'t2', 'test3'=>'t3', 'status' => '已下檔'], 
    array('primaryKey'=>'id', 'end_at' => 'end_at', status'=>'status'),
    function($array){ ...do something });

在function結束時，程式會自動將查詢結果帶入閉包函式執行<br>
回傳結果如：

    array(1); // 以上表movies為例，無欄位應該做修改

使用者應在閉包函式做欲做之事，如將status欄位修改為「上映中」，以Laravel為例

    Movies::whereIn('id', $array)->update['status'=>'已下檔'];

即可將符合時間條件與tag條件之資料做修改
    
#### Artisan 指令集
=
(規劃中)

#### 配合Laravel排程使用
=
(規劃中)
