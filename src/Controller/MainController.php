<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Wayhood\HyperfAction\Annotation\Category;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Wayhood\HyperfAction\Collector\DescriptionCollector;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Util\DocHtml;

/**
 * Class MainController
 * @package Wayhood\HyperfAction\Controller
 */
class MainController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @Inject()
     * @var TokenInterface
     */
    protected $token;

    public function systemExceptionReturn(int $errorCode, string $message, string $actionName)
    {
        $responses = Context::get(__CLASS__ .':responses',  []);
        $ret = [
            'code' => $errorCode,
            'message' => $message,
            'data' => new \stdClass()
        ];

        if (!is_null($actionName)) {
            $ret['dispatch'] = $actionName;
        }
        $responses[] = $ret;
        Context::set(__CLASS__ .':responses',  $responses);
    }

    public function systemReturn(array $responseResults) {
        $responses = Context::get(__CLASS__ .':responses',  []);
        foreach($responseResults as $mapping => $ret) {
            $ret['dispatch'] = $mapping;
            $responses[] = $ret;
        }
        Context::set(__CLASS__ .':responses',  $responses);
    }

    public function validateParam($requestParam, $params, $actionMapping) {
        $key = $requestParam['name'];
        $require = $requestParam['require'];
        if ($require == 'true') {
            if (!isset($params[$key])) {
                $this->systemExceptionReturn(9901, "缺少参数: ". $key, $actionMapping);
                return false;
            }
            //判断类型
            $type = $requestParam['type'];
            $value = $params[$key];
            if ($type == 'string') {
                if (!is_string($value)) {
                    $this->systemExceptionReturn(9902, $key ." 类型不匹配，请查看文档", $actionMapping);
                    return false;
                }
            }

            if ($type == 'int') {
                if (!is_int($value)) {
                    $this->systemExceptionReturn(9902, $key ." 类型不匹配，请查看文档", $actionMapping);
                    return false;
                }
            }

            if ($type == 'float') {
                if (!is_float($value)) {
                    $this->systemExceptionReturn(9902, $key ." 类型不匹配，请查看文档", $actionMapping);
                    return false;
                }
            }

            if ($type == 'array') {
                if (!is_array($value)) {
                    $this->systemExceptionReturn(9902, $key ." 类型不匹配，请查看文档", $actionMapping);
                    return false;
                }
            }
            return true;
        } else {
            return true;
        }
    }

    public function index()
    {
        $this->responses = [];
        $actionRequests = $this->request->getAttribute("actionRequests");
        $extras = $this->request->getAttribute("extras");
        $headers = $this->request->getHeaders();

        $okRequest = [];
        foreach($actionRequests as $actionRequest) {
            if (!is_array($actionRequest)) {
                continue;
            }
            $actionMapping = array_key_exists('dispatch', $actionRequest) ? $actionRequest['dispatch'] : null;
            if (is_null($actionMapping)) {
                $this->systemExceptionReturn(8003, '请求参数有误', $actionMapping);   //请求参数有误
                continue;
            }

            $actionName = ActionCollector::list()[$actionMapping]?? null;
            if (is_null($actionName)) {
                $this->systemExceptionReturn(8001, '调度不可用', $actionMapping); //调度名不可用
                continue;
            }

            $usable = UsableCollector::list()[$actionName]?? false;
            if ($usable == false) {
                $this->systemExceptionReturn(8002, '调度暂停使用', $actionMapping); //调度名不可用
                continue;
            }

            $defineRequestParam = RequestParamCollector::result()[$actionName]?? [];
            $validateParam = true;
            foreach($defineRequestParam as $params) {
                if (!$this->validateParam($params, $actionRequest['params'], $actionMapping)) {
                    $validateParam = false;
                    break;
                }
            }
            if ($validateParam == false) {
                break;
            }


            $okRequest[$actionMapping] = [
                'container' => $this->container->get($actionName),
                'params' => $actionRequest['params'] ?? [],
                'hasToken' => TokenCollector::list()[$actionName]? true : false
            ];
        }

        //开始处理
        $responseResults = [];
        $actionRequesCount = count($okRequest);
        if ($actionRequesCount == 1) {
            foreach($okRequest as $mapping => $value) {
                if ($value['hasToken'] == true) {
                    $token = $this->getTokenByHeader($headers);
                    if (!$this->token->verify($token)) {
                        $ret = [
                            'code' => 8005,
                            'message' => 'token失效',
                            'data' => new \stdClass(),
                        ];
                        return $ret;
                    }
                    $this->token->set($token);
                }
                $beforeResult = $value['container']->beforeRun($value['params'], $extras, $headers);
                if ($beforeResult === true) {
                    $responseResults[$mapping] = $value['container']->run($value['params'], $extras, $headers);
                } else {
                    $responseResults[$mapping] = $beforeResult;
                }
            }
        } else if (count($okRequest) > 1) {
            //开启协程
            $parallel = new Parallel($actionRequesCount);
            foreach($okRequest as $mapping => $value) {
                $parallel->add(function () use ($value, $extras, $headers) {
                    Context::copy(\Swoole\Coroutine::getPcid());
                    if ($value['hasToken'] == true) {
                        $token = $this->getTokenByHeader($headers);
                        if (!$this->token->verify($token)) {
                            $ret = [
                                'code' => 8005,
                                'message' => 'token失效',
                                'data' => new \stdClass(),
                            ];
                            return $ret;
                        }
                        $this->token->set($token);
                    }
                    $beforeResult = $value['container']->beforeRun($value['params'], $extras, $headers);
                    if ($beforeResult === true) {
                        $result = $value['container']->run($value['params'], $extras, $headers);
                    } else {
                        $result = $beforeResult;
                    }
                    //\Swoole\Coroutine::sleep(1);
                    return $result;
                }, $mapping);
            }

            try {
                $responseResults = $parallel->wait();
            }  catch(ParallelExecutionException $e){
                //var_dump(1);
                echo $e->getMessage();
                //var_dump($e->getResults()); // 获取协程中的返回值。
                //var_dump($e->getThrowables()); // 获取协程中出现的异常。
            }
        }

        $this->systemReturn($responseResults);
        $responses = Context::get(__CLASS__ .':responses',  []);
        return $this->response->json([
            'code' => 0,
            'timestamp' => time(),
            'deviation' => 0,
            'message' => '成功',
            'response' => $responses
        ]);
    }

    public function getTokenByHeader($headers) {
        if (isset($headers['authorization'][0])) {
            return $headers['authorization'][0];
        }
        return "";
    }

    public function doc() {
        $response = Context::get(\Psr\Http\Message\ResponseInterface::class);
        $response = $response->withHeader('Content-Type', 'text/html;charset=utf-8');
        Context::set(\Psr\Http\Message\ResponseInterface::class, $response);
        $action = $this->request->input("dispatch", "");
        if ($action == "") {
            return $this->response->raw(DocHtml::getIndexHtml($this->request->getUri(), $this->request->getPathInfo()));
        }

        return $this->response->raw(DocHtml::getActionHtml($action, $this->request->getPathInfo()));
    }


}
