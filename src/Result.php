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

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Wayhood\HyperfAction\Util\ResponseFilter;

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

    protected static function getRequest():RequestInterface
    {
        return ApplicationContext::getContainer()
                ->get(RequestInterface::class);
    }

    protected static function getResponse()
    {
        return ApplicationContext::getContainer()
                 ->get(ResponseInterface::class);
    }

    public static function successReturn($data = []): array
    {
        $data = ResponseFilter::processResponseData(Result::convertArray($data), get_called_class());
        if (is_array($data) && count($data) == 0) {
            $data = [];
        }
        return [
            'code' => 0,
            'message' => '成功',
            'data' => $data,
        ];
    }

    public static function errorReturn(int $errorCode, string $message = '')
    {
        return [
            'code' => $errorCode,
            'message' => $message,
            'data' => [],
        ];
    }
}
