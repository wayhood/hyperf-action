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
use Hyperf\Context\Context;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\RequestValidateCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Result;
use Wayhood\HyperfAction\Util\DocHtml;

/**
 * Class MainController.
 */
class MainController
{
    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected ?RequestInterface $request;

    #[Inject]
    protected ?ResponseInterface $response;

    #[Inject]
    protected TokenInterface $token;

    public function systemExceptionReturn(int $errorCode, string $message, string $actionName)
    {
        $response = [
            'code' => $errorCode,
            'message' => $message,
            'data' => new \stdClass(),
        ];

        if (! is_null($actionName)) {
            $response['dispatch'] = $actionName;
        }
        return $response;
    }

    public function systemReturn($mapping, $response)
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

    public function index()
    {
        try {
            $response = $this->dispatch();
        } catch (\Exception $exception) {
            $response = $this->parseExceptionAndError($exception);
        } catch (\Error $error) {
            $response = $this->parseExceptionAndError($error);
        }
        return $response;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \DeathSatan\Hyperf\Validate\Exceptions\ValidateException
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dispatch()
    {
        $actionRequest = $this->request->getAttribute('actionRequest');
        $extras = $this->request->getAttribute('extras');
        $headers = $this->request->getHeaders();

        $actionMapping = isset($actionRequest['dispatch']) ? $actionRequest['dispatch'] : null;
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
        // 验证器验证 requestValidate注解
        $requestValidates = RequestValidateCollector::result()[$actionName] ?? [];
        foreach ($requestValidates as $requestValidate) {
            $ret = $this->validate($requestValidate, $actionRequest['params'], $actionMapping);
            if ($ret !== true) {
                return $this->response->json([
                    'code' => 0,
                    'timestamp' => time(),
                    'deviation' => 0,
                    'message' => '成功',
                    'response' => $ret,
                ]);
            }
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
                break;
            }
            if (isset($actionRequest['params'][$params['name']])) {
                $filterActionRequestParams[$params['name']] = $actionRequest['params'][$params['name']];
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
                        'data' => new \stdClass(),
                    ];
                } else {
                    $ret = [
                        'code' => 8006,
                        'message' => '当前账号在其他终端登录',
                        'data' => new \stdClass(),
                    ];
                }
                return $this->systemReturn($okRequest['mapping'], $ret);
            }
            $this->token->set($token);
        }
        $beforeResult = $okRequest['container']->beforeRun($okRequest['params'], $extras, $headers);
        if ($beforeResult === true) {
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
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $response->withHeader('Content-Type', 'text/html;charset=utf-8');
        Context::set(\Psr\Http\Message\ResponseInterface::class, $response);

        $action = $this->request->input('dispatch', '');
        if ($action == '') {
            return $response->withBody(new SwooleStream(DocHtml::getIndexHtml((string) $this->request->getUri(), $this->request->getPathInfo())));
        }
        return $response->withBody(new SwooleStream(DocHtml::getActionHtml($action, $this->request->getPathInfo())));
    }

    /**
     * @param \Error|\Exception $except
     */
    protected function parseExceptionAndError($except): \Psr\Http\Message\ResponseInterface
    {
        $actionRequest = $this->request->getAttribute('actionRequest');
        $actionMapping = $actionRequest['dispatch'] ?? null;
        $actionName = ActionCollector::list()[$actionMapping] ?? null;
        $errorCodes = ErrorCodeCollector::result()[$actionName] ?? null;
        $code = $except->getCode();

        // 时间: 2022年7月16日 20:14:15
        // 新增错误日志记录
        $logger = $this->container->get(LoggerFactory::class)->get('action');
        $date = date('Y-m-d H:i:s');
        $logger->error(
            <<<SQL
{$date} ActionError {$except->getMessage()}
{$except->getTraceAsString()}
SQL
        );

        if (array_key_exists($code, $errorCodes)) {
            // 如果已经定义过的error code
            $message = $errorCodes[$code]['message'] ?? null;
            return Result::systemReturn(
                Result::errorReturn($code, $message),
            );
        }

        if (in_array(env('APP_ENV'), ['test', 'demo', 'dev'])) {
            $data = [
                'trace' => $except->getTrace(),
                'message' => $except->getMessage(),
                'exception_file' => $except->getFile(),
                'exception_line' => $except->getLine(),
            ];
            return Result::systemReturn($data, $except->getMessage(), (int) $except->getCode());
        }

        return Result::error([], 'System Errors');
    }

    /**
     * @throws \DeathSatan\Hyperf\Validate\Exceptions\ValidateException
     */
    protected function validate(array $validateConfig, array $params, string $action)
    {

        $safeMode = $validateConfig['safe_mode'];
        $scene = $validateConfig['scene'];
        /**
         * @var AbstractValidate $validate
         */
        $validate = $validateConfig['validate'];
        if ($scene != null) {
            $validate->scene($scene);
        }
        $res = $validate->make($params, $safeMode);
        if ($res instanceof ValidatorInterface) {
            return $this->systemExceptionReturn(9998, $res->errors()->first(), $action);
        }
        return true;
    }
}
