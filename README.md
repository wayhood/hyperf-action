# hyperf-action

背景
=====
原来项目都是这种结构，准备换swoole，借助hyperf 还在修改中。。。

配置
=====
```shell
composer require hyperf-action
```


说明
=====
全局使用一个Controller，（其实可以没有controller，直接挂在router上，
配置 config/routers.php
```php
Router::addRoute(['POST'], '/', 'Wayhood\HyperfAction\Controller\MainController@index');
//Action调度都在MainController@index
```

修改callback事件  config/autoload/server.php 修改SwooleEvent::ON_BEFORE_START
这里用于收集Action注解，（暂时没找个最合适的位置）
```php
SwooleEvent::ON_BEFORE_START => [Wayhood\HyperfAction\Bootstrap\ServerStartCallback::class, 'beforeStart'],
```

创建Action
=====

在App下创建Action目录，并创建Action, 类上加入Action注解，注意不要有重复的值
Action和Controller类似，可以使用$this->request $this->response
```php
<?php

declare(strict_types=1);
namespace App\Action;

use Wayhood\HyperfAction\Annotation\Action;
use Wayhood\HyperfAction\Annotation\RequestParam;
use Wayhood\HyperfAction\Annotation\ResponseParam;
use Wayhood\HyperfAction\Annotation\Category;
use Wayhood\HyperfAction\Annotation\Description;
use Wayhood\HyperfAction\Annotation\ErrorCode;
use Wayhood\HyperfAction\Annotation\Usable;
use Wayhood\HyperfAction\Action\AbstractAction;
use \Hyperf\DB\DB;

/**
 * 操作Mapping
 * @Action("index.list")
 *
 * 以下注解用于生成文档, 校验请求数据类型，以及过滤响应输出
 *
 * 分类
 * @Category("首页")
 *
 * 描述
 * @Description("首页列表")
 *
 * 请求参数
 * 格式:  name="名称",  type="类型", require=是否必须, example=示例值, description="描述"
 * 简写:  n="名称",  t="类型", r=是否必须, e=示例值, d="描述"
 * @RequestParam(name="start", type="int", require=false, example=0, description="记录起始位置, 默认从0开始")
 * @RequestParam(n="limit",       t="int", r=false, e=0,  d="获取记录条数, 默认10条")
 * @RequestParam(n="category_id", t="int", r=false, e=10, d="分类ID")
 *
 * 响应参数
 * 格式:  name="名称",  type="类型", example=示例值, description="描述"
 * 简写:  n="名称",  t="类型", e=示例值, d="描述"
 * @ResponseParam(name="a", type="string", example="1", description="abcdefg")
 * @ResponseParam(n="b", t="string", e="1", d="abcdefg")
 *
 * 错误代码
 * 格式: code=错误代码, message="描述"
 * 简写: c=错误代码, m="描述"
 * @ErrorCode(code=1001, message="分类不存在")
 *
 * 是否可用 true可用 false不可用
 * @Usable(false)
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

```json
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

响应格式
=====
响应格式如下
注意: responses数组的顺序通常与requests一致, 但也可能通过responses里的dispatch知道是哪个响应
```json
{
   "code": 0,    //最外层的code，0是成功  非0失败  是说明这个请求正确（如，请求方法post，请求格式，即json，等等，但不代表具体的请求接口）
   "message": "成功",   //描述，非0会有具体面描述
   "timestamp": 1458291720, //服务器时间戳
   "deviation": 8 //误差, 即请求的时间戳 与服务器时间的误差
   "responses": [    //响应，对应请求的requests部份
       { //第一个请求响应
         "code": 0,   //0是成功，非0失败
         "message": "成功"， //描述，非0会有具本描述
         "data": {   //响应数据，非0没有
           "success": "true"
         },
         "dispatch": "test"   //对应的调用方式
       }
   ]
}
```


