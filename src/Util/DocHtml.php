<?php
declare(strict_types=1);
namespace Wayhood\HyperfAction\Util;

use Hyperf\Utils\ApplicationContext;
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
    public static $tableOfContentHtml = [];

    /**
     * 目录内容
     * @var array
     */
    public static $tableOfContent = [];

    /**
     * 请求参数Html
     * @var array
     */
    public static $requestParamHtmls = [];

    /**
     * 请求参数示例Html
     * @var array
     */
    public static $requestParamExampleHtmls = [];

    /**
     * 响应参数Html
     * @var array
     */
    public static $responseParamHtmls = [];

    /**
     * 响应参数示例Html
     * @var array
     */
    public static $responseParamExampleHtml = [];

    /**
     * 错误代码html
     * @var array
     */
    public static $errorCodeHtml = [];

    /**
     * 左侧html模版
     * @var string
     */
    public static $leftHtml = <<<EOF
    <div class="book-summary">
      <div class="search-box form-group">
          <input type="text" class="form-control" id="inputSearch" placeholder="搜索接口">
          <span class="glyphicon glyphicon-search form-control-feedback" aria-hidden="true"></span>
      </div>

      <div id="accordion" class="catalog">
        {{tableOfContent}}
      </div>
    </div>
EOF;

    /**
     * 右侧html模版
     * @var string
     */
    public static $rightHtml = <<<EOF
    <div class="book-body">
        <div class="body-inner">
            <div class="book-header">
                <div class="d-flex justify-content-between">
                    <a class="header-menu toggle-catalog" href="javascript:void(0)"><i
                                class="glyphicon glyphicon-align-justify"></i> 目录</a>
                </div>
            </div>
            <div class="page-wrapper">
                <div class="page-inner">
                    <div class="main-content">
                        <div class="col-lg-11" style="padding:5px;">
                            <h1 style="{{style}}">{{dispatch}} ({{desc}})</h1>
                            {{token}}
                            <h3>请求参数</h3>
                            <table class="table table-bordered">
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
                            <table class="table table-bordered">
                                <tr>
                                    <th width="15%">名称</th>
                                    <th width="10%">类型</th>
                                    <th width="35%">示例值</th>
                                    <th width="40%">描述</th>
                                </tr>
                                {{responseParams}}
                            </table>
                    
                            <h3>请求示例</h3>
                            <div id="requestCanvas"></div>
                    
                            <h3>响应示例</h3>
                            <div id="responseCanvas"></div>
                    
                            <h3>错误代码</h3>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">代码</th>
                                    <th width="70%">描述</th>
                                </tr>
                                {{errorCodes}}
                            </table>
                        </div>
                        <script>
                            var responseJsonText = '{{responseExampleParams}}';
                            var requestJsonText = {{requestExampleParams}};
                            requestJsonText["timestamp"] = new Date().getTime();
                            Process(responseJsonText, "responseCanvas");
                            Process(JSON.stringify(requestJsonText), "requestCanvas");
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

EOF;


    /**
     * 底部Html模版
     * @var string
     */
    public static $footerHtml = <<<EOF
    </div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.jquery.min.js"></script>
<script>

    var search_source_data = {{searchData}};


    $('.toggle-catalog').click(function () {
        $('.book').toggleClass('with-summary');
    });

    $('#inputSearch').autocomplete({hint: false}, [
        {
            source: function (query, callback) {
                var result = [];
                for(var i = 0; i !== search_source_data.length; i++){
                    if(search_source_data[i].name.indexOf(query) !== -1){
                        result.push(search_source_data[i]);
                    }
                }
                callback(result);
            },
            displayKey: 'name',
            templates: {
                suggestion: function (suggestion) {
                    return suggestion.name;
                }
            }
        }
    ]).on('autocomplete:selected', function (event, suggestion, dataset, context) {
        self.location = suggestion.url;
    });
</script>

    </body>
</html>
EOF;

    /**
     * 头部Html模版
     * @var string
     */
    public static $headerHtml = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{name}} 接口文档</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <style>
    pre {
        display: block;
        padding: 9.5px;
        margin: 0 0 10px;
        font-size: 13px;
        line-height: 1.42857143;
        color: #333;
        word-break: break-all;
        word-wrap: break-word;
        background-color: #fff;
        /* border: 1px solid #ccc; */
        border-radius: 4px;
        /* font-family:'consolas'; */
    }
    .Canvas {
        font:14px/18px 'consolas';
        background-color: #ECECEC;
        color: #000000;
        border: solid 1px #CECECE;
    }
    .ObjectBrace {
        color: #00AA00;
        font-weight: bold;
    }
    .ArrayBrace {
        color: #0033FF;
        font-weight: bold;
    }
    .PropertyName {
        color: #CC0000;
        font-weight: bold;
    }
    .String {
        color: #007777;
    }
    .Number {
        color: #AA00AA;
    }
    .Boolean {
        color: #0000FF;
    }
    .Function {
        color: #AA6633;
        text-decoration: italic;
    }
    .Null {
        color: #0000FF;
    }
    .Comma {
        color: #000000;
        font-weight: bold;
    }
    PRE.CodeContainer {
        margin-top: 0px;
        margin-bottom: 0px;
    }
    </style>
        <script>
    var formatJson = function (json, options) {
        var reg = null,
                formatted = '',
                pad = 0,
                PADDING = '    ';
        options = options || {};
        options.newlineAfterColonIfBeforeBraceOrBracket = (options.newlineAfterColonIfBeforeBraceOrBracket === true) ? true : false;
        options.spaceAfterColon = (options.spaceAfterColon === false) ? false : true;
        if (typeof json !== 'string') {
            json = JSON.stringify(json);
        } else {
            json = JSON.parse(json);
            json = JSON.stringify(json);
        }
        reg = /([\{\}])/g;
        json = json.replace(reg, '\\r\\n$1\\r\\n');
        reg = /([\[\]])/g;
        json = json.replace(reg, '\\r\\n$1\\r\\n');
        reg = /(\,)/g;
        json = json.replace(reg, '$1\\r\\n');
        reg = /(\\r\\n\\r\\n)/g;
        json = json.replace(reg, '\\r\\n');
        reg = /\\r\\n\,/g;
        json = json.replace(reg, ',');
        if (!options.newlineAfterColonIfBeforeBraceOrBracket) {
            reg = /\:\\r\\n\{/g;
            json = json.replace(reg, ':{');
            reg = /\:\\r\\n\[/g;
            json = json.replace(reg, ':[');
        }
        if (options.spaceAfterColon) {
            reg = /\:/g;
            json = json.replace(reg, ':');
        }
        (json.split('\\r\\n')).forEach(function (node, index) {
            //console.log(node);
            var i = 0,
                indent = 0,
                padding = '';
    
            if (node.match(/\{$/) || node.match(/\[$/)) {
                indent = 1;
            } else if (node.match(/\}/) || node.match(/\]/)) {
                if (pad !== 0) {
                    pad -= 1;
                }
            } else {
                    indent = 0;
            }
    
            for (i = 0; i < pad; i++) {
                padding += PADDING;
            }
    
            formatted += padding + node + '\\r\\n';
            pad += indent;
        });
        return formatted;
    };

    
    window.TAB = "    ";
    function IsArray(obj) {
      return obj &&
          typeof obj === 'object' &&  typeof obj.length === 'number' && !(obj.propertyIsEnumerable('length'));
    }
    function Process(json, domName) {
        document.getElementById(domName).style.display = "block";
        var html = "";
        try {
            if (json == "") {
                json = '""';
            }
            var obj = eval("[" + json + "]");
            html = ProcessObject(obj[0], 0, false, false, false);
            document.getElementById(domName).innerHTML = "<PRE class='CodeContainer'>" + html + "</PRE>";
        } catch(e) {
            alert("json error:\\n" + e.message);
            document.getElementById(domName).innerHTML = "";
        }
    }
    function ProcessObject(obj, indent, addComma, isArray, isPropertyContent) {
        var html = "";
        var comma = (addComma) ? "<span class='Comma'>,</span> ": "";
        var type = typeof obj;
        if (IsArray(obj)) {
            if (obj.length == 0) {
                html += GetRow(indent, "<span class='ArrayBrace'>[ ]</span>" + comma, isPropertyContent);
            } else {
                html += GetRow(indent, "<span class='ArrayBrace'>[</span>", isPropertyContent);
                for (var i = 0; i < obj.length; i++) {
                    html += ProcessObject(obj[i], indent + 1, i < (obj.length - 1), true, false);
                }
                html += GetRow(indent, "<span class='ArrayBrace'>]</span>" + comma);
            }
        } else {
            if (type == "object" && obj == null) {
                html += FormatLiteral("null", "", comma, indent, isArray, "Null");
            } else {
                if (type == "object") {
                    var numProps = 0;
                    for (var prop in obj) {
                        numProps++;
                    }
                    if (numProps == 0) {
                        html += GetRow(indent, "<span class='ObjectBrace'>{ }</span>" + comma, isPropertyContent)
                    } else {
                        html += GetRow(indent, "<span class='ObjectBrace'>{</span>", isPropertyContent);
                        var j = 0;
                        for (var prop in obj) {
                            html += GetRow(indent + 1, '<span class="PropertyName">"' + prop + '"</span>: ' + ProcessObject(obj[prop], indent + 1, ++j < numProps, false, true))
                        }
                        html += GetRow(indent, "<span class='ObjectBrace'>}</span>" + comma);
                    }
                } else {
                    if (type == "number") {
                        html += FormatLiteral(obj, "", comma, indent, isArray, "Number");
                    } else {
                        if (type == "boolean") {
                            html += FormatLiteral(obj, "", comma, indent, isArray, "Boolean");
                        } else {
                            if (type == "function") {
                                obj = FormatFunction(indent, obj);
                                html += FormatLiteral(obj, "", comma, indent, isArray, "Function");
                            } else {
                                if (type == "undefined") {
                                    html += FormatLiteral("undefined", "", comma, indent, isArray, "Null");
                                } else {
                                    html += FormatLiteral(obj, '"', comma, indent, isArray, "String");
                                }
                            }
                        }
                    }
                }
            }
        }
        return html;
    };
    
    function FormatLiteral(literal, quote, comma, indent, isArray, style) {
        if (typeof literal == "string") {
            literal = literal.split("<").join("&lt;").split(">").join("&gt;");
        }
        var str = "<span class='" + style + "'>" + quote + literal + quote + comma + "</span>";
        if (isArray) {
            str = GetRow(indent, str);
        }
        return str;
    }
    function FormatFunction(indent, obj) {
        var tabs = "";
        for (var i = 0; i < indent; i++) {
            tabs += window.TAB;
        }
        var funcStrArray = obj.toString().split("\\n");
        var str = "";
        for (var i = 0; i < funcStrArray.length; i++) {
            str += ((i == 0) ? "": tabs) + funcStrArray[i] + "\\n";
        }
        return str;
    }
    function GetRow(indent, data, isPropertyContent) {
        var tabs = "";
        for (var i = 0; i < indent && !isPropertyContent; i++) {
            tabs += window.TAB;
        }
        if (data != null && data.length > 0 && data.charAt(data.length - 1) != "\\n") {
            data = data + "\\n";
        }
        return tabs + data;
    };
    </script>
    <style>
body, html {
    height: 100%;
    overflow-y:hidden;
}

.book{
    position: relative;
    width: 100%;
    height: 100%;
}

.book.with-summary .book-summary {
    left: 0;
}

.book-summary {
    position: absolute;
    top: 0;
    left: -350px;
    bottom: 0;
    z-index: 1;
    overflow-y: auto;
    width: 350px;
    color: #364149;
    background: #fafafa;
    border-right: 1px solid rgba(0,0,0,.07);
    -webkit-transition: left 250ms ease;
    -moz-transition: left 250ms ease;
    -o-transition: left 250ms ease;
    transition: left 250ms ease;
}

.book-body {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    color: #333;
    background: #fff;
    -webkit-transition: left 250ms ease;
    -moz-transition: left 250ms ease;
    -o-transition: left 250ms ease;
    transition: left 250ms ease;
}

.book-body .body-inner {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    padding-top: 10px;
}

.book-header {
    overflow: visible;
    height: 50px;
    z-index: 2;
    font-size: .85em;
    color: #7e888b;
    background: 0 0;
}

.book-header a.header-menu{
    font-size: 18px;
    color: #555555;
    padding: 10px;
    text-decoration: none;
}

.book-header a.header-menu:hover{
    text-decoration: none;
    color: #000;
}

.page-wrapper {
    position: relative;
    outline: 0;
}

.book .book-body .page-wrapper .page-inner {
    position: relative;
    left: 0px;
    transition: 300ms ease left;
}

.page-inner {
    position: relative;
    margin: 0 auto;
    padding: 20px 15px 40px 15px;
}

@media (min-width: 600px){
    .book.with-summary .book-body {
        left: 350px;
    }
}

@media (max-width: 600px){
    .book-summary {
        width: calc(100% - 60px);
        bottom: 0;
        left: -100%;
    }
    .book.with-summary .book-body {
        -webkit-transform: translate(calc(100% - 60px),0);
        -moz-transform: translate(calc(100% - 60px),0);
        -ms-transform: translate(calc(100% - 60px),0);
        -o-transform: translate(calc(100% - 60px),0);
        transform: translate(calc(100% - 60px),0);
    }
}

@media (max-width: 1240px){
    .book-body {
        -webkit-transition: -webkit-transform 250ms ease;
        -moz-transition: -moz-transform 250ms ease;
        -o-transition: -o-transform 250ms ease;
        transition: transform 250ms ease;
        padding-bottom: 20px;
    }
}

@media (max-width: 1240px){
    .book-body .body-inner {
        position: static;
        min-height: calc(100% - 50px);
    }
}

.navbar{
    background: #F7931E;
    color: #FFF;
}

.navbar a{
    color: #FFF;
}
.navbar-brand{
    font-weight: 600;
}

@media (min-width: 768px){
    .navbar {
        border-radius: 0;
    }
}

.catalog .panel{
    margin-bottom: 0;
    border-radius: 0;
    border: none;
    box-shadow: none;
    -webkit-box-shadow: none;
}

.catalog .catalog-title {
    border-bottom: 1px solid #EAEAEA;
    padding: 1rem 1.25rem;
    background: rgba(0, 0, 0, .03);
    cursor: pointer;
    color: #333;
    font-weight: 600;
    font-size: 16px;
}

.catalog-item{
    padding: 8px 15px;
    margin-left: 15px;
    color: #888;
    border-bottom: solid #EEE 1px;
    display: block;
}

.active {
    background-color:#f3f3f3;
}

.action-item h2 a{
    color: #888;
    text-decoration: none;
}

a:hover{
    color: #888;
    text-decoration: none;
}

.search-box{
    position: relative;
    margin: 10px;
}

.navbar{
    margin-bottom: 0;
}

.main-content{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

.text-bold{
    font-weight: bold;
}

/**third part*/

.algolia-autocomplete {
    width: 100%;
}
.algolia-autocomplete .aa-input, .algolia-autocomplete .aa-hint {
    width: 100%;
}
.algolia-autocomplete .aa-hint {
    color: #888;
}
.algolia-autocomplete .aa-dropdown-menu {
    width: 100%;
    background-color: #ccc;
    border: 1px solid #EEE;
    border-top: none;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion {
    cursor: pointer;
    padding: 5px 4px;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion.aa-cursor {
    background-color: #F7931E;
    color: #FFF;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion em {
    font-weight: bold;
    font-style: normal;
}

a {
    text-decoration: none;
}
</style>
</head>
<body>
<nav class="navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{index}}" style="text-decoration:none;color:#fff;">
                {{name}} 接口文档
            </a>
        </div>
    </div>
</nav>
<div class="book with-summary">
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
        if (isset(static::$requestParamHtmls[$actionName])) {
            $html = str_replace("{{requestParams}}", static::$requestParamHtmls[$actionName], static::$rightHtml);
        } else {
            $html = str_replace("{{requestParams}}", "", static::$rightHtml);
        }

        $html = str_replace("{{responseParams}}", static::$responseParamHtmls[$actionName], $html);
        if (isset(static::$requestParamExampleHtmls[$actionName])) {
            $html = str_replace("{{requestExampleParams}}", static::$requestParamExampleHtmls[$actionName], $html);

        } else {
            $html = str_replace("{{requestExampleParams}}", static::getEmptyRequestParamExampleHtmls($mapping), $html);
        }
        $html = str_replace("{{responseExampleParams}}", static::$responseParamExampleHtml[$actionName], $html);
        if (isset(static::$errorCodeHtml[$actionName])) {
            $html = str_replace("{{errorCodes}}", static::$errorCodeHtml[$actionName], $html);
        } else {
            $html = str_replace("{{errorCodes}}", "", $html);
        }

        $html = str_replace("{{dispatch}}", $mapping, $html);
        $html = str_replace("{{desc}}", DescriptionCollector::list()[$actionName], $html);
        if (isset(UsableCollector::list()[$actionName])) {
            $html = str_replace("{{style}}", UsableCollector::list()[$actionName] == false ? "text-decoration: line-through;" : "", $html);
        } else {
            $html = str_replace("{{style}}", "text-decoration: line-through;", $html);
        }
        if (isset(TokenCollector::list()[$actionName])) {
            $html = str_replace("{{token}}", TokenCollector::list()[$actionName]== true ? static::getTokenHtml() : "", $html);
        } else {
            $html = str_replace("{{token}}", "", $html);
        }
        return $html;
    }

    public static function getFooterHtml(string $mapping, string $actionName, string $pathInfo) {
        if (count(static::$tableOfContentHtml) == 0) {
            static::getTableOfContentHtml($pathInfo);
        }
        $search_data = json_encode(static::getSearchData($pathInfo));
        $html = str_replace("{{searchData}}", $search_data, static::$footerHtml);
        return $html;

    }

    public static function getEmptyRequestParamExampleHtmls($mapping) {
        $params = new \stdClass();
        $request['request'] = [
            'dispatch' => $mapping,
            'params' => $params
        ];
        $result_pretty = json_encode($request, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $result_pretty;
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
        $config = ApplicationContext::getContainer()->get(\Hyperf\Contract\ConfigInterface::class);
        $html = str_replace("{{name}}", $config->get('app_name'), $html);
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
            ksort($tableOfContent, SORT_STRING);
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
        $i = 0;
        foreach(ActionCollector::list() as $mapping => $class) {
            $html = "";
            foreach($tableOfContent as $category => $data) {
                $i++;
                $html .= <<<EOF
<div class="panel">
        <div id="heading{$i}" data-parent="#accordion" class="catalog-title" data-toggle="collapse"
             aria-expanded="true" data-target="#collapse{$i}" aria-controls="collapse{$i}">
            <i class="glyphicon glyphicon-th-list"></i> {$category}
        </div>
EOF;
                $in = "";
                foreach($data as $line) {
                    if ($line['dispatch'] == $mapping) {
                        $in = ' in';
                        break;
                    }
                }
                $html .= <<<EOF
        <div id="collapse{$i}" class="collapse {$in} " aria-labelledby="heading{$i}">
EOF;

                foreach($data as $line) {
                    $active = '';
                    if ($line['dispatch'] == $mapping) {
                        $active = ' active';
                    }
                    $token = '';
                    if ($line['token'] == true) {
                        $token = '<span class="label label-warning"><span class="glyphicon glyphicon-user"></span></span>';
                    }
                    $text = $line['name'];
                    if ($line['usable'] != true) {
                        $text = "<span style='text-decoration: line-through;'>". $text .'</span>';
                    }

                    $dispatch = '<span class="label label-default">' . $line['dispatch'] .'</span>';
                    $text = $text. " ". $dispatch . ' '. $token;

                    $html .= '<a class="catalog-item '. $active .'" href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'">'. $text ."</a>";
                    //$html .= '         <a href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'" class="list-group-item '. $active .'">'. $text ."</a>";
                }
                $html .= '      </div>    </div>';
            }
            static::$tableOfContentHtml[$class] = $html;
        }
        $html = "";
        $i = 0;
        foreach($tableOfContent as $category => $data) {
            $i++;
            $html .= <<<EOF
<div class="panel">
        <div id="heading{$i}" data-parent="#accordion" class="catalog-title collapsed" data-toggle="collapse"
             aria-expanded="false" data-target="#collapse{$i}" aria-controls="collapse{$i}">
            <i class="glyphicon glyphicon-th-list"></i> {$category}
        </div>
EOF;
            $html .= <<<EOF
        <div id="collapse{$i}" class="collapse" aria-labelledby="heading{$i}">
EOF;
            foreach($data as $line) {
                $active = '';

                $token = '';
                if ($line['token'] == true) {
                    $token = '<span class="label label-warning"><span class="glyphicon glyphicon-user"></span></span>';
                }
                $text = $line['name'];
                if ($line['usable'] != true) {
                    $text = "<span style='text-decoration: line-through;'>". $text .'</span>';
                }

                $dispatch = '<span class="label label-default">' . $line['dispatch'] .'</span>';
                $text = $text. " ". $dispatch . ' '. $token;

                $html .= '<a class="catalog-item '. $active .'" href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'">'. $text ."</a>";
                //$html .= '         <a href="'. $pathInfo .'?dispatch=' . $line['dispatch'].'" class="list-group-item '. $active .'">'. $text ."</a>";
            }
            $html .= '      </div>    </div>';
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
    if (!isset($actionMap[$action])) {
            return "Not Found Dispatch";
        }
        $actionName = $actionMap[$action];

        $html = static::getHeaderHtml($action, $actionName, $pathInfo) .
            static::getLeftHtml($action, $actionName, $pathInfo) .
            static::getRightHtml($action, $actionName, $pathInfo) .
            static::getFooterHtml($action, $actionName, $pathInfo);
        return $html;
    }

    public static $searchData = [];

    public static function getSearchData($pathInfo) {
        if (count(static::$searchData) > 0) {
            return static::$searchData;
        }
        $tableOfContent = static::getTableOfContent();
        foreach($tableOfContent as $category => $data) {
            $name = $category;
            foreach ($data as $line) {
                static::$searchData[] = [
                    'name' => $name .'.'.$line['name']." ".$line['dispatch'],
                    'url' => $pathInfo ."?dispatch=" .$line['dispatch']
                ];
            }
        }
        return static::$searchData;
//    ];
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
        $search_data = json_encode(static::getSearchData($pathInfo));
        $html =<<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{name}} 接口文档</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <style>
body, html {
    height: 100%;
    overflow-y:hidden;
}

.book{
    position: relative;
    width: 100%;
    height: 100%;
}

.book.with-summary .book-summary {
    left: 0;
}

.book-summary {
    position: absolute;
    top: 0;
    left: -350px;
    bottom: 0;
    z-index: 1;
    overflow-y: auto;
    width: 350px;
    color: #364149;
    background: #fafafa;
    border-right: 1px solid rgba(0,0,0,.07);
    -webkit-transition: left 250ms ease;
    -moz-transition: left 250ms ease;
    -o-transition: left 250ms ease;
    transition: left 250ms ease;
}

.book-body {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    color: #333;
    background: #fff;
    -webkit-transition: left 250ms ease;
    -moz-transition: left 250ms ease;
    -o-transition: left 250ms ease;
    transition: left 250ms ease;
}

.book-body .body-inner {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    padding-top: 10px;
}

.book-header {
    overflow: visible;
    height: 50px;
    z-index: 2;
    font-size: .85em;
    color: #7e888b;
    background: 0 0;
}

.book-header a.header-menu{
    font-size: 18px;
    color: #555555;
    padding: 10px;
    text-decoration: none;
}

.book-header a.header-menu:hover{
    text-decoration: none;
    color: #000;
}

.page-wrapper {
    position: relative;
    outline: 0;
}

.book .book-body .page-wrapper .page-inner {
    position: relative;
    left: 0px;
    transition: 300ms ease left;
}

.page-inner {
    position: relative;
    margin: 0 auto;
    padding: 20px 15px 40px 15px;
}

@media (min-width: 600px){
    .book.with-summary .book-body {
        left: 350px;
    }
}

@media (max-width: 600px){
    .book-summary {
        width: calc(100% - 60px);
        bottom: 0;
        left: -100%;
    }
    .book.with-summary .book-body {
        -webkit-transform: translate(calc(100% - 60px),0);
        -moz-transform: translate(calc(100% - 60px),0);
        -ms-transform: translate(calc(100% - 60px),0);
        -o-transform: translate(calc(100% - 60px),0);
        transform: translate(calc(100% - 60px),0);
    }
}

@media (max-width: 1240px){
    .book-body {
        -webkit-transition: -webkit-transform 250ms ease;
        -moz-transition: -moz-transform 250ms ease;
        -o-transition: -o-transform 250ms ease;
        transition: transform 250ms ease;
        padding-bottom: 20px;
    }
}

@media (max-width: 1240px){
    .book-body .body-inner {
        position: static;
        min-height: calc(100% - 50px);
    }
}

.navbar{
    background: #F7931E;
    color: #FFF;
}

.navbar a{
    color: #FFF;
}
.navbar-brand{
    font-weight: 600;
}

@media (min-width: 768px){
    .navbar {
        border-radius: 0;
    }
}

.catalog .panel{
    margin-bottom: 0;
    border-radius: 0;
    border: none;
    box-shadow: none;
    -webkit-box-shadow: none;
}

.catalog .catalog-title {
    border-bottom: 1px solid #EAEAEA;
    padding: 1rem 1.25rem;
    background: rgba(0, 0, 0, .03);
    cursor: pointer;
    color: #333;
    font-weight: 600;
    font-size: 16px;
}

.catalog-item{
    padding: 8px 15px;
    margin-left: 15px;
    color: #888;
    border-bottom: solid #EEE 1px;
    display: block;
}

.action-item h2 a{
    color: #888;
    text-decoration: none;
}

a:hover{
    color: #888;
    text-decoration: none;
}

.search-box{
    position: relative;
    margin: 10px;
}

.navbar{
    margin-bottom: 0;
}

.main-content{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
}

.text-bold{
    font-weight: bold;
}

/**third part*/

.algolia-autocomplete {
    width: 100%;
}
.algolia-autocomplete .aa-input, .algolia-autocomplete .aa-hint {
    width: 100%;
}
.algolia-autocomplete .aa-hint {
    color: #888;
}
.algolia-autocomplete .aa-dropdown-menu {
    width: 100%;
    background-color: #ccc;
    border: 1px solid #EEE;
    border-top: none;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion {
    cursor: pointer;
    padding: 5px 4px;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion.aa-cursor {
    background-color: #F7931E;
    color: #FFF;
}
.algolia-autocomplete .aa-dropdown-menu .aa-suggestion em {
    font-weight: bold;
    font-style: normal;
}

a {
    text-decoration: none;
}

</style>
</head>
<body>
<nav class="navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="{$pathInfo}" style="text-decoration:none;color:#fff;">
                {{name}} 接口文档
            </a>
        </div>
    </div>
</nav>
<div class="book with-summary">
    <div class="book-summary">
      <div class="search-box form-group">
          <input type="text" class="form-control" id="inputSearch" placeholder="搜索接口">
          <span class="glyphicon glyphicon-search form-control-feedback" aria-hidden="true"></span>
      </div>

      <div id="accordion" class="catalog">
        {$tableOfContent}
      </div>
    </div>
    <div class="book-body">
        <div class="body-inner">
            <div class="book-header">
                <div class="d-flex justify-content-between">
                    <a class="header-menu toggle-catalog" href="javascript:void(0)"><i
                                class="glyphicon glyphicon-align-justify"></i> 目录</a>
                </div>
            </div>
            <div class="page-wrapper">
                <div class="page-inner">
                    <div class="main-content">
                        <div class="col-lg-11" style="padding:5px;">
        <h3>所有接口请求方式为POST, 接口地址: {$url}</h3><h5>请求格式如下</h5><pre>
{
     "timestamp": "xxxxxx",  //当前时间戳 字符串或数字都可以, 注意时间戳允许与服务器时间误差正负600秒
     "signature": "xxxxxxxxxxxxxxxxxxx",   //签名 暂时未使用
     "request":{
          "params":{    // 这是请求参数
               "test": "Hello"
          },
          "dispatch":"test"  //调用名
     }
}
</pre>    </pre><h5>响应格式如下 </h5><pre>
{
   "code": 0,    //最外层的code，0是成功  非0失败  是说明这个请求正确（如，请求方法post，请求格式，即json，等等，但不代表具体的请求接口）
   "message": "成功",   //描述，非0会有具体面描述
   "timestamp": 1458291720, //服务器时间戳
   "deviation": 8 //误差, 即请求的时间戳 与服务器时间的误差
   "response": {
         "code": 0,   //0是成功，非0失败
         "message": "成功"， //描述，非0会有具本描述
         "data": {   //响应数据，非0没有
           "success": "true"
         },
         "dispatch": "test"   //对应的调用方式
   }
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
        <table class="table table-bordered">
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
                <td>request无效 没有request段</td>
            </tr>
            <tr>
                <td>9003</td>
                <td>request结构有误 request段没有具体请求方法或结构不对</td>
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
        <table class="table table-bordered">
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
获取request中的params
params的key:value，按key排序
组成
key1:value2,key2:value2
注意：如果value2非标量值 即：不是 integer、float、double, string 或 boolean 这些值，则忽略掉这对key:value
然后通过|与dispatch的值字符串连接
例如：
sys.launch.get|height:2208,width:1242


第三步：
计算签名

通过HmacSHA1 计算值（字节或binary)
再md5加密生成16进制字符串， 即为签名，大小写均可

第四步:
将上面的签名值，放到最外层
例如
{
    "request": {
        "dispatch": "sys.launch.get",
        "params": {
            "width": 1242,
            "height": 2208
        }
    },
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
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/google-code-prettify@1.0.5/bin/prettify.min.js"></script>
<script>
    var search_source_data = {$search_data};

    $('.toggle-catalog').click(function () {
        $('.book').toggleClass('with-summary');
    });

    $('#inputSearch').autocomplete({hint: false}, [
        {
            source: function (query, callback) {
                var result = [];
                for(var i = 0; i !== search_source_data.length; i++){
                    if(search_source_data[i].name.indexOf(query) !== -1){
                        result.push(search_source_data[i]);
                    }
                }
                callback(result);
            },
            displayKey: 'name',
            templates: {
                suggestion: function (suggestion) {
                    return suggestion.name;
                }
            }
        }
    ]).on('autocomplete:selected', function (event, suggestion, dataset, context) {
        self.location = suggestion.url;
    });
</script>
</body>
</html>
EOF;
        $config = ApplicationContext::getContainer()->get(\Hyperf\Contract\ConfigInterface::class);
        $html = str_replace("{{name}}", $config->get('app_name'), $html);
        return $html;

    }

    /**
     * 获取token header html
     * @return string
     */
    public static function getTokenHtml() {
        $html =<<<EOF
        <h3>Header参数</h3>
        <table class="table table-bordered">
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
        $map = ActionCollector::result();
        foreach($requestParamCollector as $class => $requestParams) {
            $requestParamHtml = "";
            foreach($requestParams as $requestParam) {
                $requestParamHtml .= "<tr><td>" . $requestParam->name . "</td>\n";
                $requestParamHtml .= "<td style=\"word-break:break-all;\">" . $requestParam->type . "</td>\n";
                $requestParamHtml .= "<td style=\"word-break:break-all;\">" . ($requestParam->require == true ? "true" : "false") . "</td>\n";
                if ($requestParam->base64 == true) {
                    $requestParamHtml .= "<td style=\"word-break:break-all;\">" . htmlentities(base64_decode(strval($requestParam->example))) . "</td>\n";
                } else {
                    $requestParamHtml .= "<td style=\"word-break:break-all;\">" . htmlentities(strval($requestParam->example)) . "</td>\n";
                }
                $requestParamHtml .= "<td style=\"word-break:break-all;\">" . $requestParam->description . "</td></tr>\n";
            }
            static::$requestParamHtmls[$class] = $requestParamHtml;
            static::$requestParamExampleHtmls[$class] = static::getRequestParamExampleHtml($map[$class], $requestParams);
        }
    }

    /**
     * 获取请求参数示例Html
     * @param array $requestParams
     * @return string
     */
    public static function getRequestParamExampleHtml(string $mapping, array $requestParams) {
        $req = [
            'dispatch' => $mapping
        ];

        $params = [];
        foreach($requestParams as $requestParam) {
            if ($requestParam->type == 'string') {
                $params[$requestParam->name] = strval($requestParam->example);
            } else if ($requestParam->type == 'int') {
                $params[$requestParam->name] = intval($requestParam->example);
                //$html .= intval($requestParam->example);
            } else if ($requestParam->type == 'float') {
                $params[$requestParam->name] = floatval($requestParam->example);
            } else if ($requestParam->type == 'bool') {
                $params[$requestParam->name] = boolval($requestParam->example);
            } else if ($requestParam->type == 'array') {
                $example = $requestParam->example;
                if ($requestParam->base64 == true) {
                    $example = base64_decode($example);
                }
                $example = @json_decode($example, true);
                if (!is_array($example)) {
                    $example = [];
                }
                $params[$requestParam->name] = $example;
            } else if ($requestParam->type == 'object') {
                $example = $requestParam->example;
                if ($requestParam->base64 == true) {
                    $example = base64_decode($example);
                }
                $example = @json_decode($example, true);
                if (!is_array($example)) {
                    $example = new \stdClass();
                }
                $params[$requestParam->name] = $example;
            } else {
                $params[$requestParam->name] = $requestParam->example;
            }
        }
        if (count($params) == 0) {
            $params = new \stdClass();
        }
        $req["params"] = $params;
        $request['request'] = $req;
        $result_pretty = json_encode($request, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return $result_pretty;
    }


    /**
     * 获取响应参数html
     * @param $action
     */
    public static function genResponseParamHtml($action) {
        $responseParamCollector = ResponseParamCollector::result();
        $map = ActionCollector::result();
        foreach($responseParamCollector as $class => $responseParams) {
            static::$responseParamHtmls[$class] = static::getResponseParamHtmlOne($responseParams, 0);
            static::$responseParamExampleHtml[$class] = static::getResponseParamsPreHtml($map[$class], $responseParams);
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
                $html .= "<td style=\"word-break:break-all;\">" . $responseParam['type'] . "</td>\n";
                $html .= "<td style=\"word-break:break-all;\">" . $responseParam['example'] . "</td>\n";
                $html .= "<td style=\"word-break:break-all;\">" . $responseParam['desc'] . "</td></tr>\n";
            }
            if (isset($responseParam['children'])) {
                if (!is_numeric($key)) {
                    $indent = $indent + 2;
                }
                $html .= static::getResponseParamHtmlOne($responseParam['children'], $indent);
            }
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
        $ret = [
            'code' => 0,
            'message' => '成功',
            'dispatch' => $mapping
        ];
        $ret['data'] = static::getResponseParamExampleHtml($paramData);

        $response["response"] = $ret;
        //$result_pretty = json_encode($response,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $result_pretty = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
