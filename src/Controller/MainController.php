<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Controller;

use DeathSatan\Hyperf\Validate\Lib\AbstractValidate;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\RequestValidateCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Event\BeforeAction;
use Wayhood\HyperfAction\Util\DocHtml;

/**
 * Class MainController.
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
     * @Inject
     * @var TokenInterface
     */
    protected $token;

    /**
     * @Inject
     * @var EventDispatcherInterface
     */
    protected $dispatch;

    public function systemExceptionReturn(int $errorCode, string $message, ?string $actionName = null): array
    {
        $response = [
            'code' => $errorCode,
            'message' => $message,
            'data' => new stdClass(),
        ];

        if (! is_null($actionName)) {
            $response['dispatch'] = $actionName;
        }
        return $response;
    }

    public function systemReturn($mapping, $response): \Psr\Http\Message\ResponseInterface
    {
        $response['dispatch'] = $mapping;
        return $this->response->json([
            'code' => 0,
            'timestamp' => time(),
            'deviation' => 0,
            'message' => '成功',
            'response' => $response,
        ]);
    }

    public function validateParam($requestParam, $params, $actionMapping)
    {
        $key = $requestParam['name'];
        $require = $requestParam['require'];
        if ($require == 'true') {
            if (! isset($params[$key])) {
                return $this->systemExceptionReturn(9901, '缺少参数: ' . $key, $actionMapping);
            }
        }

        // 判断类型
        $type = $requestParam['type'];
        if (isset($params[$key])) {
            $value = $params[$key];
            if ($type == 'string') {
                if (! is_string($value)) {
                    return $this->systemExceptionReturn(9902, $key . ' 类型不匹配，请查看文档', $actionMapping);
                }
            }

            if ($type == 'int') {
                if (! is_int($value)) {
                    return $this->systemExceptionReturn(9902, $key . ' 类型不匹配，请查看文档', $actionMapping);
                }
            }

            if ($type == 'float') {
                if (is_int($value)) {
                    $value = floatval($value);
                }
                if (! is_float($value)) {
                    return $this->systemExceptionReturn(9902, $key . ' 类型不匹配，请查看文档', $actionMapping);
                }
            }

            if ($type == 'array') {
                if (! is_array($value)) {
                    return $this->systemExceptionReturn(9902, $key . ' 类型不匹配，请查看文档', $actionMapping);
                }
            }
        }

        return true;
    }

    protected function checkValidate($validate,$params,$actionMapping)
    {
        /**
         * @var $validate_object AbstractValidate
         */
        $validate_object = $validate['validate'];
        $scene = $validate['scene'];
        $safe_mode = $validate['safe_mode'];
        if ($scene!==null)
        {
            $validate_object = $validate_object->scene($scene);
        }
        $res = $validate_object->make($params,false);
        if ($res instanceof ValidatorInterface)
        {
            return $this->systemExceptionReturn(9998,$res->errors()->first(),$actionMapping);
        }
        return true;
    }

    public function index()
    {
        $actionRequest = $this->request->getAttribute('actionRequest');
        $extras = $this->request->getAttribute('extras');
        $headers = $this->request->getHeaders();

        $actionMapping = $actionRequest['dispatch'] ?? null;
        if (is_null($actionMapping)) {
            $response = $this->systemExceptionReturn(8003, '请求参数有误', is_null($actionMapping) ? '' : $actionMapping);   // 请求参数有误
            return $this->response->json([
                'code' => 0,
                'timestamp' => time(),
                'deviation' => 0,
                'message' => '成功',
                'response' => $response,
            ]);
        }

        $actionName = ActionCollector::list()[$actionMapping] ?? null;
        if (is_null($actionName)) {
            $response = $this->systemExceptionReturn(8001, '调度不可用', $actionMapping); // 调度名不可用
            return $this->response->json([
                'code' => 0,
                'timestamp' => time(),
                'deviation' => 0,
                'message' => '成功',
                'response' => $response,
            ]);
        }

        $usable = UsableCollector::list()[$actionName] ?? false;
        if ($usable == false) {
            $response = $this->systemExceptionReturn(8002, '调度暂停使用', $actionMapping); // 调度名不可用
            return $this->response->json([
                'code' => 0,
                'timestamp' => time(),
                'deviation' => 0,
                'message' => '成功',
                'response' => $response,
            ]);
        }

        $defineRequestParam = RequestParamCollector::result()[$actionName] ?? [];
        $filterActionRequestParams = [];
        foreach ($defineRequestParam as $params) {
            $ret = $this->validateParam($params, $actionRequest['params'], $actionMapping);
            if ($ret !== true) {
                return $this->response->json([
                    'code' => 0,
                    'timestamp' => time(),
                    'deviation' => 0,
                    'message' => '成功',
                    'response' => $ret,
                ]);
            }
            if (isset($actionRequest['params'][$params['name']])) {
                $filterActionRequestParams[$params['name']] = $actionRequest['params'][$params['name']];
            }
        }

        $defineRequestValidate = RequestValidateCollector::result()[$actionName] ?? [];
        foreach ($defineRequestValidate as $validate)
        {
            $ret = $this->checkValidate($validate,$actionRequest['params'],$actionMapping);
            if ($ret !== true)
            {
                return $this->response->json([
                    'code' => 0,
                    'timestamp' => time(),
                    'deviation' => 0,
                    'message' => '成功',
                    'response' => $ret,
                ]);
            }
        }

        $okRequest = [
            'mapping' => $actionMapping,
            'container' => $this->container->get($actionName),
            'params' => $filterActionRequestParams ?? [],
            'hasToken' => TokenCollector::list()[$actionName] ? true : false,
        ];

        // 开始处理
        if ($okRequest['hasToken'] == true) {
            $token = $this->getTokenByHeader($headers);
            $verify = $this->token->verify($token);
            if ($verify != 1) {
                if ($verify == 0) {
                    $ret = [
                        'code' => 8005,
                        'message' => 'token失效',
                        'data' => new stdClass(),
                    ];
                } else {
                    $ret = [
                        'code' => 8006,
                        'message' => '当前账号在其他终端登录',
                        'data' => new stdClass(),
                    ];
                }
                return $this->systemReturn($okRequest['mapping'], $ret);
            }
            $this->token->set($token);
        }
        $beforeResult = $okRequest['container']->beforeRun($okRequest['params'], $extras, $headers);
        if ($beforeResult === true) {
            $this->dispatch->dispatch(new BeforeAction($actionMapping, $okRequest['params'], $headers, $extras));
            $ret = $okRequest['container']->run($okRequest['params'], $extras, $headers);
            return $this->systemReturn($okRequest['mapping'], $ret);
        }
        return $this->systemReturn($okRequest['mapping'], $beforeResult);
    }

    public function getTokenByHeader($headers)
    {
        foreach ($headers as $key => $value) {
            if (strtolower($key) == 'authorization') {
                if (isset($value[0])) {
                    return $value[0];
                }
            }
        }
        return '';
    }

    public function doc()
    {
        $response = Context::get(\Psr\Http\Message\ResponseInterface::class);
        $response = $response->withHeader('Content-Type', 'text/html;charset=utf-8');
        Context::set(\Psr\Http\Message\ResponseInterface::class, $response);
        $action = $this->request->input('dispatch', '');
        if ($action == '') {
            return $this->response->raw(DocHtml::getIndexHtml($this->request->getUri(), $this->request->getPathInfo()));
        }

        return $this->response->raw(DocHtml::getActionHtml($action, $this->request->getPathInfo()));
    }
}
