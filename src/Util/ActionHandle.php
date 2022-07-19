<?php


namespace Wayhood\HyperfAction\Util;


use DeathSatan\Hyperf\Validate\Lib\AbstractValidate;
use Hyperf\HttpServer\Contract\RequestInterface;

class ActionHandle implements \DeathSatan\Hyperf\Validate\Contract\CustomHandle
{
    public function __construct(protected RequestInterface $request){}

    /**
     * @inheritDoc
     */
    public function provide(object $current, AbstractValidate $validate, string $scene = null): array
    {
        $actionRequest = $this->request->getAttribute('actionRequest');
        return $actionRequest['params']??[];
    }
}