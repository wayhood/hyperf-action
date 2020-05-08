<?php
declare(strict_types=1);
namespace Wayhood\HyperfAction\Util;

use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Wayhood\HyperfAction\Collector\DescriptionCollector;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;

class DocHtml
{

    /**
     * 目录html
     * @var array
     */
    public static array $tableOfContentHtml = [];

    /**
     * 目录内容
     * @var array
     */
    public static array $tableOfContent = [];

    /**
     * 请求参数Html
     * @var array
     */
    public static array $requestParamHtmls = [];

    /**
     * 请求参数示例Html
     * @var array
     */
    public static array $requestParamExampleHtmls = [];

    /**
     * 响应参数Html
     * @var array
     */
    public static array $responseParamHtmls = [];

    /**
     * 响应参数示例Html
     * @var array
     */
    public static array $responseParamExampleHtml = [];

    /**
     * 错误代码html
     * @var array
     */
    public static array $errorCodeHtml = [];

    /**
     * 左侧html模版
     * @var string
     */
    public static string $leftHtml = <<<EOF
    <!-- 左边内容 -->
    <div class="col-lg-2" style="padding:5px;">
        {{tableOfContent}}
    </div>
EOF;

    /**
     * 右侧html模版
     * @var string
     */
    public static string $rightHtml = <<<EOF
    <div class="col-lg-10" style="padding:5px;">
        <h1 style="{{style}}">{{dispatch}} ({{desc}})</h1>
        {{token}}
        <h3>请求参数</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th width="15%">名称</th>
                <th width="10%">类型</th>
                <th width="10%">必须</th>
                <th width="25%">示例值</th>
                <th width="40%">描述</th>
            </tr>
            {{requestParams}}
        </table>

        <h3>响应参数</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th width="15%">名称</th>
                <th width="10%">类型</th>
                <th width="35%">示例值</th>
                <th width="40%">描述</th>
            </tr>
            {{responseParams}}
        </table>

        <h3>请求示例</h3>
        <code class="language-json" data-lang="json" style="white-space: pre-wrap;">{
        "requests": [{
            "dispatch": "{{dispatch}}",
            "params": {
{{params}}
            }
        }]
}</code>

        <h3>响应示例</h3>
        <pre>{{responseExampleParams}}</pre>

        <h3>错误代码</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th width="30%">代码</th>
                <th width="70%">描述</th>
            </tr>
            {{errorCodes}}
        </table>
    </div>
EOF;


    /**
     * 底部Html模版
     * @var string
     */
    public static string $footerHtml = <<<EOF
    </div>
    </body>
</html>
EOF;

    /**
     * 头部Html模版
     * @var string
     */
    public static $headerHtml = <<<EOF
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>接口文档</title>
    <link href="https://cdn.bootcss.com/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<body>
<div class="container-fluid">
    <h3>接口文档 <a href="{{index}}" class="btn btn-default">返回首页</a></h3>
</div>
<div class="container-fluid">
EOF;

    /**
     * 获取左侧html
     * @param string $mapping
     * @param string $actionName
     * @param string $pathInfo
     * @return string|string[]
     */
    public static function getLeftHtml(string $mapping, string $actionName, string $pathInfo) {
        if (count(static::$tableOfContentHtml) == 0) {
            static::getTableOfContentHtml($pathInfo);
        }
        $html = str_replace("{{tableOfContent}}", static::$tableOfContentHtml[$actionName], static::$leftHtml);
        return $html;
    }

    /**
     * 获取右侧html
     * @param string $mapping
     * @param string $actionName
     * @param string $pathInfo
     * @return string|string[]
     */
    public static function getRightHtml(string $mapping, string $actionName, string $pathInfo) {
        $html = str_replace("{{requestParams}}", static::$requestParamHtmls[$actionName], static::$rightHtml);
        $html = str_replace("{{responseParams}}", static::$responseParamHtmls[$actionName], $html);
        $html = str_replace("{{params}}", static::$requestParamExampleHtmls[$actionName], $html);
        $html = str_replace("{{responseExampleParams}}", static::$responseParamExampleHtml[$actionName], $html);
        $html = str_replace("{{errorCodes}}", static::$errorCodeHtml[$actionName], $html);
        $html = str_replace("{{dispatch}}", $mapping, $html);
        $html = str_replace("{{desc}}", DescriptionCollector::list()[$actionName], $html);
        $html = str_replace("{{style}}", UsableCollector::list()[$actionName] == false ? "text-decoration: line-through;" : "", $html);
        $html = str_replace("{{token}}", TokenCollector::list()[$actionName] == true ? static::getTokenHtml() : "", $html);

        return $html;
    }

    /**
     * 获取头部html
     * @param string $mapping
     * @param string $actionName
     * @param string $pathInfo
     * @return string|string[]
     */
    public static function getHeaderHtml(string $mapping, string $actionName, string $pathInfo) {
        $html = str_replace("{{index}}", $pathInfo, static::$headerHtml);
        return $html;
    }

    /**
     * 获取目录内容
     * @return array
     */
    public static function getTableOfContent() {
        if (count(static::$tableOfContent) == 0) {
            $tableOfContent = [];
            $actions = ActionCollector::list();
            foreach ($actions as $mapping => $class) {
                $category = CategoryCollector::list()[$class];
                if (!isset($tableOfContent[$category])) {
                    $tableOfContent[$category] = [];
                }

                $tableOfContent[$category][] = [
                    'dispatch' => $mapping,
                    'name' => DescriptionCollector::list()[$class],
                    'usable' => UsableCollector::list()[$class] ?? false,
                    'token' => TokenCollector::list()[$class] ?? false,
                ];
            }
            static::$tableOfContent = $tableOfContent;
        }
        return static::$tableOfContent;
    }

    /**
     * 获取目录html
     * @param string $pathInfo
     */
    public static function getTableOfContentHtml(string $pathInfo) {
        $tableOfContent = static::getTableOfContent();
        foreach(ActionCollector::list() as $mapping => $class) {
            $html = "";
            foreach($tableOfContent as $category => $data) {
                $html .= "<h5>". $category ."</h5>";
                $html .= '    <div class="list-group">';
                foreach($data as $line) {
                    $active = '';
                    //if ($a == true) {
                    if ($line['dispatch'] == $mapping) {
                        $active = ' active';
                    }
                    //}

                    $token = '';
                    if ($line['token'] == true) {
                        $token = '<span class="glyphicon glyphicon-user"></span>';
                    }
                    $text = $line['dispatch'];
                    if ($line['usable'] != true) {
                        $text = "<span style='text-decoration: line-through;'>". $text .'</span>';
                    }

                    $text = $text.$token. "<br />". $line['name'];

                    $html .= '         <a href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'" class="list-group-item '. $active .'">'. $text ."</a>";
                }
                $html .= '    </div>';
            }
            static::$tableOfContentHtml[$class] = $html;
        }
        $html = "";
        foreach($tableOfContent as $category => $data) {
            $html .= "<h5>". $category ."</h5>";
            $html .= '    <div class="list-group">';
            foreach($data as $line) {
                $active = '';

                $token = '';
                if ($line['token'] == true) {
                    $token = '<span class="glyphicon glyphicon-user"></span>';
                }
                $text = $line['dispatch'];
                if ($line['usable'] != true) {
                    $text = "<span style='text-decoration: line-through;'>". $text .'</span>';
                }

                $text = $text.$token. "<br />". $line['name'];

                $html .= '         <a href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'" class="list-group-item '. $active .'">'. $text ."</a>";
            }
            $html .= '    </div>';
        }
        static::$tableOfContentHtml['index'] = $html;
    }

    /**
     * 获取其他页html
     * @param string $action
     * @param string $pathInfo
     * @return string
     */
    public static function getActionHtml(string $action, string $pathInfo) {
        if (count(static::$requestParamHtmls) == 0) {
            static::genRequestParamHtml($action);
        }

        if (count(static::$responseParamHtmls) == 0) {
            static::genResponseParamHtml($action);
        }

        if (count(static::$errorCodeHtml) == 0) {
            static::getErrorCodeHtml();
        }

        $actionMap = ActionCollector::list();
        $actionName = $actionMap[$action];
        $requestParamHtml = static::$requestParamHtmls[$actionName];

        $html = static::getHeaderHtml($action, $actionName, $pathInfo) .
            static::getLeftHtml($action, $actionName, $pathInfo) .
            static::getRightHtml($action, $actionName, $pathInfo) .
            static::$footerHtml;
        return $html;
    }

    /**
     * 获得首页html
     * @param $uri
     * @param $pathInfo
     * @return string
     */
    public static function getIndexHtml($uri, $pathInfo) {
        $url = str_replace($pathInfo, "", $uri);
        static::getTableOfContentHtml($pathInfo);
        $tableOfContent = static::$tableOfContentHtml['index'];
        $html =<<<EOF
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>接口文档</title>
    <link href="https://cdn.bootcss.com/twitter-bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<body>
<div class="container-fluid">
    <h3>接口文档</h3>
</div>
<div class="container-fluid">

    <!-- 左边内容 -->
    <div class="col-lg-2" style="padding:5px;">
        {$tableOfContent}
    </div>
    <div class="col-lg-10" style="padding:5px;">
        <h3>所有接口请求方式为POST, 接口地址: {$url}</h3><h5>请求格式如下</h5><pre>
{
     "timestamp": "xxxxxx",  //当前时间戳 字符串或数字都可以, 注意时间戳允许与服务器时间误差正负600秒
     "signature": "xxxxxxxxxxxxxxxxxxx",   //签名 暂时未使用
     "requests":[  //开始
        //第一个调用开始
        {
          "params":{    // 这是请求参数
               "test": "Hello"
          },
          "dispatch":"test"  //调用名
        }
        //第一个调用结束

        //这里可以有多个调用, 最多5个, 也就是说, 如果多个调用之前没有关连, 可并行调用

    ]
}
</pre>    </pre><h5>响应格式如下 </h5><p style="color:red">注意: responses数组的顺序通常与requests一致, 但也可能通过responses里的dispatch知道是哪个响应 </p><pre>
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
</pre><h5>命名规范</h5>
<p>xxx.xxx.add  //表示 添加数据</p>
<p>xxx.xxx.set   //表示 更新数据</p>
<p>xxx.xxx.get  //表示 获取数据</p>
<p>xxx.xxx.del  //表示 删除数据</p>
<p>xxx.xxx.list  //表示 获取列表数据</p>

<p>特殊</p>
<p>没有后标识的，可能即写即读。</p>
<p>比如 user.login wx.login等等。</p>

<h5>Token传入</h5>
<p>需要token的接口，请在请求http中，加入Authorization</p>
<pre>
Authorization: token值
</pre>
        <h3>系统错误代码</h3>
        <table class="table table-striped table-bordered">
            <tbody><tr>
                <th width="30%">代码</th>
                <th width="70%">描述</th>
            </tr>
            <tr>
                <td>9000</td>
                <td>请求方法不对 必须是post请求</td>
            </tr>
            <tr>
                <td>9001</td>
                <td>payloads结构有误 请求结构不对</td>
            </tr>
            <tr>
                <td>9002</td>
                <td>requests无效 没有requests段</td>
            </tr>
            <tr>
                <td>9003</td>
                <td>requests结构有误 requests段没有具体请求方法或结构不对</td>
            </tr>
            <tr>
                <td>9005</td>
                <td>timestamp无效 </td>
            </tr>
            <tr>
                <td>9006</td>
                <td>手机时间与服务器时间误差 不在允许范围</td>
            </tr>
            <tr>
                <td>9007</td>
                <td>signature无效</td>
            </tr>
            <tr>
                <td>9008</td>
                <td>signature结构有误</td>
            </tr>
            <tr>
                <td>9009</td>
                <td>请求来源有误</td>
            </tr>
            <tr>
                <td>8001</td>
                <td>调度不可用 dispatch不正确</td>
            </tr>
            <tr>
                <td>8002</td>
                <td>调度暂停使用 dispatch暂时不可用</td>
            </tr>
            <tr>
                <td>8003</td>
                <td>请求参数有误 request结构中没有指定dispatch</td>
            </tr>
            <tr>
                <td>8005</td>
                <td>token失效 由于token过期</td>
            </tr>
            <tr>
                <td>8006</td>
                <td>token失效 需要token的接口，没接收到token</td>
            </tr>
            </tbody>
        </table>
        <h3>调度错误代码</h3>
        <table class="table table-striped table-bordered">
            <tbody>
                <tr>
                    <th width="30%">代码</th>
                    <th width="70%">描述</th>
                </tr>
                <tr>
                    <td>9901</td>
                    <td>缺少请求参数</td>
                </tr>
                <tr>
                    <td>9902</td>
                    <td>请求参数类型不匹配</td>
                </tr>
            </tbody>
        </table>
        <h3>签名计算</h3>
        <pre>
            签名测试，
            第一步：
            timestamp的值，计算secret_key
            代码示例
            secret_key = substr(md5(timestamp), 0, 16);

            即，取16位md5的值

            第二步：
            获取内容
            循环requests，获取request中的params
            params的key:value，按key排序
            组成
            key1:value2,key2:value2
            注意：如果value2非标量值 即：不是 integer、float、double, string 或 boolean 这些值，则忽略掉这对key:value
            然后通过|与dispatch的值字符串连接
            例如：
            sys.launch.get|height:2208,width:1242

            如果有多个requests，每段request通过/连接，
            例如
            index.list|/sys.launch.get|height:2208,width:1242
            注：index.list如果没有params，则params段的值为空
            格式： dispatch1|params_content1/dispatch2|params_content2

            第三步：
            计算签名

            通过HmacSHA1 计算值（字节或binary)
            再md5加密生成16进制字符串， 即为签名，大小写均可

            第四步:
            将上面的签名值，放到最外层
            例如
{
	"requests": [{
		"dispatch": "sys.launch.get",
		"params": {
			"width": 1242,
			"height": 2208
		}
	}],
	"signature": "ed2eac434ebacdf0d6c3a301cabf6323",
	"timestamp": "1572167919297"
}

            签名计算, 参考

            timestamp = 1572167919297
            secret_key = 2872fe9ea22381dd
            content = sys.launch.get|height:2208,width:1242
            sign = ed2eac434ebacdf0d6c3a301cabf6323

        </pre>
    </div>
</div>
</body>
</html>

EOF;
        return $html;

    }

    /**
     * 获取token header html
     * @return string
     */
    public static function getTokenHtml() {
        $html =<<<EOF
        <h3>Header参数</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th width="30%">key:</th>
                <th width="30%">value</th>
                <th width="40%">说明</th>
            </tr>
            <tr>
                <td width="30%">Authorization:</td>
                <td width="30%">token值</td>
                <td width="40%">本接口必须传token值</td>
            </tr>
        </table>
EOF;

        return $html;
    }

    /**
     * 获取错误代码html
     */
    public static function getErrorCodeHtml() {
        $errorCodeCollector = ErrorCodeCollector::result();
        foreach($errorCodeCollector as $class => $errCode) {
            $html = '';
            foreach($errCode as $error) {
                $html .= "<tr><td>". $error['code'] ."</td>\n";
                $html .= "<td>". $error['message'] ."</td></tr>\n";
            }
            static::$errorCodeHtml[$class] = $html;
        }
    }

    /**
     * 获取请求参数html
     * @param string $action
     */
    public static function genRequestParamHtml(string $action) {
        $requestParamCollector = RequestParamCollector::list();
        foreach($requestParamCollector as $class => $requestParams) {
            $requestParamHtml = "";
            foreach($requestParams as $requestParam) {
                $requestParamHtml .= "<tr><td>" . $requestParam->name . "</td>\n";
                $requestParamHtml .= "<td>" . $requestParam->type . "</td>\n";
                $requestParamHtml .= "<td>" . $requestParam->require == true ? "true" : "false" . "</td>\n";
                $requestParamHtml .= "<td>" . strval($requestParam->example) . "</td>\n";
                $requestParamHtml .= "<td>" . $requestParam->description . "</td></tr>\n";
            }
            static::$requestParamHtmls[$class] = $requestParamHtml;
            static::$requestParamExampleHtmls[$class] = static::getRequestParamExampleHtml($requestParams);
        }
    }

    /**
     * 获取请求参数示例Html
     * @param array $requestParams
     * @return string
     */
    public static function getRequestParamExampleHtml(array $requestParams) {
        $prefix = "                 ";
        $html = "";
        $i = count($requestParams);
        foreach($requestParams as $requestParam) {
            $html .=  $prefix.'"'.$requestParam->name.'":';
            if ($requestParam->type == 'string') {
                $html .= '"'. strval($requestParam->example) .'"';
            } else {
                $html .= strval($requestParam->example);
            }
            $i--;
            if ($i != 0) {
                $html .= ",\n";
            }
        }
        return $html;
    }


    /**
     * 获取响应参数html
     * @param $action
     */
    public static function genResponseParamHtml($action) {
        $responseParamCollector = ResponseParamCollector::result();
        foreach($responseParamCollector as $class => $responseParams) {
            static::$responseParamHtmls[$class] = static::getResponseParamHtmlOne($responseParams, 0);
            static::$responseParamExampleHtml[$class] = static::getResponseParamsPreHtml($action, $responseParams);
        }
    }

    /**
     * 获取响应参数单条html
     * @param array $data
     * @param int $indent
     * @return string
     */
    public static function getResponseParamHtmlOne(array $data, int $indent) {
        $html = '';
        $itString = "";
        for($i=0; $i<=$indent; $i++) {
            $itString .= "&nbsp;&nbsp;";
        }
        foreach($data as $key => $responseParam) {
            if (!is_numeric($key)) {
                $html .= "<tr><td>" . $itString . $key . "</td>\n";
                $html .= "<td>" . $responseParam['type'] . "</td>\n";
                $html .= "<td>" . $responseParam['example'] . "</td>\n";
                $html .= "<td>" . $responseParam['desc'] . "</td></tr>\n";
            }
            if (isset($responseParam['children'])) {
                if (!is_numeric($key)) {
                    $indent = $indent + 2;
                }
                $html .= static::getResponseParamHtmlOne($responseParam['children'], $indent);
            }
            //}
        }
        return $html;
    }

    /**
     * 获取响应参数示例Html
     * @param string $mapping
     * @param array $paramData
     * @return false|string
     */
    public static function getResponseParamsPreHtml(string $mapping, array $paramData) {
        $ret = [];
        $ret[0] = [
            'code' => 0,
            'message' => '成功',
            'dispatch' => $mapping
        ];
        $ret[0]['data'] = static::getResponseParamExampleHtml($paramData);

        $responses["responses"] = $ret;
        $result_pretty = json_encode($responses,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $result_pretty;
    }

    /**
     * 获取响应参数示例数据html
     * @param array $data
     * @return array
     */
    public static function getResponseParamExampleHtml(array $data) {
        $jsonData = [];
        foreach($data as $key => $line) {
            if (is_numeric($key)) {
                $jsonData[] = static::getResponseParamExampleHtml($line['children']);
                return $jsonData;
            }

            if ($line['type'] != 'string') {
                if ($line['type'] != 'map' && $line['type'] != 'array') {
                    if ($line['type'] == 'int') {
                        $jsonData[$line['name']] = @intval($line['example']);
                    }
                    if ($line['type'] == 'boolean') {
                        $jsonData[$line['name']] = @boolval($line['example']);
                    }
                    if ($line['type'] == 'float') {
                        $jsonData[$line['name']] = @floatval($line['example']);
                    }
                }
                if (isset($line['children'])) {
                    $jsonData[$line['name']] = static::getResponseParamExampleHtml($line['children']);
                }
            } else {
                $jsonData[$line['name']] = strval($line['example']);
            }
        }
        return $jsonData;
    }
}