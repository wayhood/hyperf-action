<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Action;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Result;
use Wayhood\HyperfAction\Util\ResponseFilter;

abstract class AbstractAction
{
    /**
     * @var array
     */
    protected $errorCodes = [];

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

    public function beforeRun($params, $extras, $headers)
    {
        return true;
    }

    abstract public function run($params, $extras, $headers);

    public function errorReturn(int $errorCode, string $message = '', array $replace = [])
    {
        $errorCodes = ErrorCodeCollector::result()[get_called_class()] ?? '';

        if (isset($errorCodes[$errorCode]) && empty($message)) {
            $message = $errorCodes[$errorCode]['message'];
        }
        if (count($replace) > 0) {
            $message = strtr($message, $replace);
        }
        if (empty($message)) {
            $message = '无';
        }
        if ($errorCode == 0) {
            $errorCode = -1;
            $message = '未知';
        }
        return [
            'code' => $errorCode,
            'message' => $message,
            'data' => new \stdClass(),
        ];
    }

    public function successReturn($data = []): array
    {
        $data = ResponseFilter::processResponseData(Result::convertArray($data), get_called_class());
        if (is_array($data) && count($data) == 0) {
            $data = new \stdClass();
        }
        return [
            'code' => 0,
            'message' => '成功',
            'data' => $data,
        ];
    }

    protected function getTokenByHeader($headers)
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
}
