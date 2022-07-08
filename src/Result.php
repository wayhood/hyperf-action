<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;

class Result
{
    protected static $response;

    public static function convertArray($data): array
    {
        if (! is_array($data)) {
            if (is_object($data) && method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
        }
        return $data;
    }
    /**
     * 系统内置json方式.
     * @param mixed $data
     */
    public static function systemReturn($data = [], string $message = 'success', int $code = 0, int $deviation = 0): \Psr\Http\Message\ResponseInterface
    {
        $data = self::convertArray($data);

        $response_data = compact('code', 'deviation', 'message');
        $response_data['response'] = $data;
        return self::getResponse()
            ->json($response_data);
    }

    /**
     * success.
     */
    public static function success(array $data = [], string $message = 'success'): \Psr\Http\Message\ResponseInterface
    {
        return self::systemReturn($data, $message);
    }

    /**
     * error.
     */
    public static function error(array $data = [], string $message = 'error', int $code = 1): \Psr\Http\Message\ResponseInterface
    {
        return self::systemReturn($data, $message, $code);
    }

    protected static function getResponse()
    {
        if (! (self::$response instanceof ResponseInterface)) {
            self::$response = ApplicationContext::getContainer()
                ->get(ResponseInterface::class);
        }
        return self::$response;
    }
}
