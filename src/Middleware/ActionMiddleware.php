<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function responsesReturn(int $code, string $message) {
        $data = [
            'code' => $code,
            'timestamp' => time(), //服务器时间
            'deviation' => 0, //误差
            'message' => $message,
            'responses' => []
        ];
        return $this->response->json($data);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->request->getMethod() != 'POST') {
            return $this->responsesReturn(9001, '请求方法不对 必须是post请求');
        }

        $body = $request->getBody()->getContents();
        $body = @json_decode($body, true);

        if (is_null($body)) {
            return $this->responsesReturn(9001, 'payloads结构有误');
        }

        //分析设备信息
        $extras = [];
        if (array_key_exists('extras', $body) && is_array($body['extras'])) {
            $extras = $body['extras'];
        }

        //多请求处理
        if (!array_key_exists('requests', $body)) {
            return $this->responsesReturn(9002, 'requests无效');
        }

        if (!is_array($body['requests']) || count($body['requests']) == 0) {
            return $this->responsesReturn(9003, 'requests结构有误');
        }

        $request = $request->withAttribute('actionRequests', $body['requests']);
        $request = $request->withAttribute('extras', $extras);
        return $handler->handle($request);
    }
}