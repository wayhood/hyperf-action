# hyperf-action

背景
=====
原来项目都是这种结构，准备换swoole，借助hyperf 还在修改中。。。

配置
=====
```
composer require hyperf-action
```


说明
=====
全局使用一个Controller，（其实可以没有controller，直接挂在router上，
配置 config/routers.php
```
Router::addRoute(['POST'], '/', 'Wayhood\HyperfAction\Controller\MainController@index');
//Action调度都在MainController@index
```

修改callback事件  config/autoload/server.php 修改SwooleEvent::ON_BEFORE_START
这里用于收集Action注解，（暂时没找个最合适的位置）
```
SwooleEvent::ON_BEFORE_START => [Wayhood\HyperfAction\Bootstrap\ServerStartCallback::class, 'beforeStart'],
```

创建Action
=====

在App下创建Action目录，并创建Action, 类上加入Action注解，注意不要有重复的值
Action和Controller类似，可以使用$this->request $this->response
```
<?php

declare(strict_types=1);
namespace App\Action;


use Wayhood\HyperfAction\Annotation\Action;
use Wayhood\HyperfAction\Action\AbstractAction;
use \Hyperf\DB\DB;

/**
 * Class IndexAction
 * @package App\Action
 * @Action("index.list")
 */
class IndexAction extends AbstractAction
{
    public function run() {
        $res = DB::query("SELECT * FROM `banner` where id=2");
        return $res;
    }
}
```

请求参数 POST
=====

```
{
     "extras:": { //附加字段，用于用户追踪
        "uuid": "xxxxxxxxx",                 //设备Id
        "device_token": "xxxxxxxxx",         //推送id
        "idfa": "xxxxxxxxx",                 //苹果广告id
        "mac": "xxxxxx",                     //mac地址
        "os": "android",                     //操作系统 android ios
        "app_version": "1.2.0",              //app版本号
        "screen": "1024x768",                //屏幕宽x高
     },
     "timestamp": "xxxxxx",  //当前时间戳 字符串或数字都可以, 注意时间戳允许与服务器时间误差正负600秒
     "signature": "xxxxxxxxxxxxxxxxxxx",   //签名 暂时未使用
     "requests":[  //开始
        //第一个调用开始
        {
          "params":{    // 这是请求参数
               "test": "Hello"
          },
          "dispatch":"test"  //调用名 dispatch对应一个Action
        }
        //第一个调用结束

        //这里可以有多个调用, 最多5个, 也就是说, 如果多个调用之前没有关连, 可并行调用, 如果requests有多个将开启协程 调用Action
        //这里都会
    ]
}
```




