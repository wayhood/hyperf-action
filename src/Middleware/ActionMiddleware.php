<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wayhood\HyperfAction\Contract\SignInterface;
use Wayhood\HyperfAction\Result;

class ActionMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ConfigInterface $config, ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->config = $config->get('wayhood');
    }

    public function systemError(int $code, string $message): ResponseInterface
    {
        return Result::systemReturn([], $message, $code);
    }

    // fix 时间戳
    public function fixTimestamp($timestamp)
    {
        if (is_numeric($timestamp)) {
            $timestamp = intval($timestamp);
            if (strlen(strval($timestamp)) == 13) { // 有微秒 去掉微秒
                $timestamp = intval($timestamp / 1000);
            }
        }
        return $timestamp;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->request->getMethod() != 'POST') {
            return $this->systemError(9001, '请求方法不对 必须是post请求');
        }

        $body = $request->getBody()->getContents();
        $body = @json_decode($body, true);

        if (is_null($body)) {
            return $this->systemError(9001, 'payloads结构有误');
        }

        $verifyTimestamp = $this->config['verify_timestamp'];

        $old_timestamp = 0;
        if ($verifyTimestamp) {
            if (! isset($body['timestamp'])) {
                return $this->systemError(9005, 'timestamp无效');
            }
            $old_timestamp = intval($body['timestamp']);
            $timestamp = $this->fixTimestamp($old_timestamp);
            $deviation = $timestamp - time();
            if (abs($deviation) > 60 * 10) {
                $message = '手机时间与服务器时间误差: ' . $deviation . '秒' . "\n";
                if ($deviation > 0) {
                    $message .= '当前手机时间过快' . "\n";
                } else {
                    $message .= '当前手机时间过慢' . "\n";
                }
                $message .= '本软件允许误差在 ±600秒' . "\n";
                $message .= '请将手机时间调成自动后再试';
                return $this->systemError(9006, $message);
            }
        }

        // 分析设备信息
        $extras = [];
        if (isset($body['extras']) && is_array($body['extras'])) {
            $extras = $body['extras'];
        }

        // 多请求处理
        if (! isset($body['request'])) {
            return $this->systemError(9002, 'request无效');
        }

        if (! is_array($body['request'])) {
            return $this->systemError(9003, 'request结构有误');
        }

        // 验证签名
        $verifySign = $this->config['verify_sign'];
        if ($verifySign) {
            if (! isset($body['signature'])) {
                return $this->systemError(9008, 'signature结构有误');
            }
            $sign = $this->container->get(SignInterface::class);
            if (! $sign->verify(strval($old_timestamp), $body['request'], $body['signature'])) {
                return $this->systemError(9007, 'signature无效');
            }
        }

        $request = $request->withAttribute('actionRequest', $body['request']);
        $request = $request->withAttribute('extras', $extras);
        return $handler->handle($request);
    }
}
