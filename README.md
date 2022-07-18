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
Router::addRoute(['GET', 'POST'], "/",
    'Wayhood\HyperfAction\Controller\MainController@index',
    ['middleware' => [\Wayhood\HyperfAction\Middleware\ActionMiddleware::class]]);

Router::get('/doc',
    'Wayhood\HyperfAction\Controller\MainController@doc');
```

命令
=====

显示指定dispatch对应的类，显示所有action
```shell
php bin/hyperf.php describe:actions  -d study.list
php bin/hyperf.php describe:actions
```

生成命令
```shell
php bin/hyperf.php gen:action --namespace 'App\Action\User' LoginAction
php bin/hyperf.php gen:service --namespace 'App\Service\Test' TestService
```

创建Action
=====

在App下创建Action目录，并创建Action, 类上加入Action注解，注意不要有重复的值
Action和Controller类似，可以使用$this->request $this->response
```php
<?php

declare(strict_types=1);
namespace App\Action\Test;

use Hyperf\DB\DB;
use Wayhood\HyperfAction\Annotation\Action;
use Wayhood\HyperfAction\Annotation\Category;
use Wayhood\HyperfAction\Annotation\Description;
use Wayhood\HyperfAction\Annotation\RequestParam;
use Wayhood\HyperfAction\Annotation\ResponseParam;
use Wayhood\HyperfAction\Annotation\Usable;
use Wayhood\HyperfAction\Annotation\ErrorCode;
use Wayhood\HyperfAction\Annotation\Token;
use Wayhood\HyperfAction\Action\AbstractAction;

/**
 * @Action("test.get")
 *
 * 以下注解用于生成文档校验数据类型和过滤响应输出
 *
 * 分类
 * @Category("测试")
 *
 * 描述
 * @Description("测试请求")
 *
 * 请求参数
 * 格式:  name="名称",  type="类型", require=是否必须, example=示例值, description="描述"
 * 简写:  n="名称",  t="类型", r=是否必须, e=示例值, d="描述"
 * @RequestParam(name="nick", type="string", require=true, example="test", description="用户昵称")
 * @RequestParam(n="a",       t="string", r=true, e="a",  d="请求参数a")
 * @RequestParam(n="b",       t="int",   r=true, e=1,   d="请求参数b")
 * @RequestParam(n="c",       t="float", r=true, e=0.1, d="请求参数c")
 *
 * 响应参数
 * 格式:  name="名称",  type="类型", example=示例值, description="描述"
 * 简写:  n="名称",  t="类型", e=示例值, d="描述"
 * @ResponseParam(n="user",           t="map",      e="无",          d="返回用户信息")
 * @ResponseParam(n="user.name",      t="string",   e="syang",       d="返回用户名称")
 * @ResponseParam(n="user.age",       t="int",      e=40,            d="返回用户年龄")
 * @ResponseParam(n="user.tel",       t="string",   e="12345789001", d="返回用户电话")
 *
 * 错误代码
 * 格式: code=错误代码, message="描述"
 * 简写: c=错误代码, m="描述"
 * @ErrorCode(code=1000, message="不知道")
 *
 * 是否可用 true可用 false不可用
 * @Usable(true)
 *
 * 是否需要Token 必须传Token false不做要求
 * @Token(false)
 */
class GetAction extends AbstractAction
{
    public function run($params, $extras, $headers) {
        return $this->successReturn([
            'user' => [
                'name' => 'syang',
                'age' => 40,
                'tel' => '1234567890'
            ]
        ]);
    }
}
```

请求参数 POST
=====

```json
{
     "extras:": { //附加字段
     },
     "timestamp": "xxxxxx",  //当前时间戳 字符串或数字都可以, 注意时间戳允许与服务器时间误差正负600秒
     "signature": "xxxxxxxxxxxxxxxxxxx",   //签名 暂时未使用
     "request": {
          "params":{    // 这是请求参数
             "nick":"test",
             "a":"a",
             "b":1,
             "c":0.1
          },
          "dispatch":"test"  //调用名 dispatch对应一个Action
     }
}
```

响应格式
=====
响应格式如下
```json
{
   "code": 0,    //最外层的code，0是成功  非0失败  是说明这个请求正确（如，请求方法post，请求格式，即json，等等，但不代表具体的请求接口）
   "message": "成功",   //描述，非0会有具体面描述
   "timestamp": 1458291720, //服务器时间戳
   "deviation": 8, //误差, 即请求的时间戳 与服务器时间的误差
   "response": {    //响应
         "code": 0,   //0是成功，非0失败
         "message": "成功"， //描述，非0会有具本描述
         "data": {   //响应数据，非0没有
           "success": "true"
         },
         "dispatch": "test"   //对应的调用方式
   }
}
```


