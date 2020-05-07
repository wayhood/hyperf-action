<?php


namespace Wayhood\HyperfAction\Middleware;


use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
//        echo "enter CorsMiddleware\n";
//        $response = Context::get(ResponseInterface::class);
//        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
//            ->withHeader('Access-Control-Allow-Credentials', 'true')
//            // Headers 可以根据实际情况进行改写。
//            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');
//
//        Context::set(ResponseInterface::class, $response);
//        var_dump("AAAAAAA");
//        var_dump($request->getAttribute("A"));
//        var_dump("BBBBBB");
//        if ($request->getMethod() == 'OPTIONS') {
//            return $response;
//        }
//        echo "2enter CorsMiddleware\n";
//
//        $re = $handler->handle($request);
//        var_dump("out cors");
//        var_dump($re->getStatusCode());
//        return $re;
        return $handler->handle($request);
    }
}