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

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpBasicMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config, protected HttpResponse $response, protected RequestInterface $request)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$username, $password] = $this->getAuthCredentials($request->getHeaders());
        if ($username != $this->config->get('wayhood.doc_auth_user') || $password != $this->config->get('wayhood.doc_auth_pass')) {
            $response = Context::get(ResponseInterface::class);
            $response = $response->withHeader('Server', $this->config->get('app_name'))
                ->withHeader('WWW-Authenticate', 'Basic realm="auth api"');
            Context::set(ResponseInterface::class, $response);
            return $response->withStatus(401)->withBody(new SwooleStream('Your request was made with invalid credentials.'));
        }
        return $handler->handle($request);
    }

    public function getAuthCredentials(array $headers): array
    {
        if (isset($headers['authorization'])) {
            $auth_token = $headers['authorization'][0];
            if ($auth_token !== null && strncasecmp($auth_token, 'basic', 5) === 0) {
                $parts = array_map(function ($value) {
                    return strlen($value) === 0 ? null : $value;
                }, explode(':', base64_decode(mb_substr($auth_token, 6)), 2));

                if (count($parts) < 2) {
                    return [$parts[0], null];
                }

                return $parts;
            }
        }

        return ['', ''];
    }
}
