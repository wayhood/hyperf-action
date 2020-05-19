<?php
namespace Wayhood\HyperfAction\Action;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
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

    protected function getTokenByHeader($headers) {
        if (isset($headers['authorization'][0])) {
            return $headers['authorization'][0];
        }
        return "";
    }

    abstract public function beforeRun($params, $extras, $headers);

    abstract public function run($params, $extras, $headers);

    protected function errorReturn(int $errorCode, string $message = "", array $replace = []) {
        $errorCodes = ErrorCodeCollector::result()[get_called_class()] ?? "";

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
            'data' => new \stdClass()
        ];
    }

    protected function successReturn($data = [])
    {
        if (is_array($data) && count($data) == 0) {
            $data = new \stdClass();
        }

        $data = ResponseFilter::processResponseData($data, get_called_class());
        return [
            'code' => 0,
            'message' => '成功',
            'data' => $data,
        ];
    }

}