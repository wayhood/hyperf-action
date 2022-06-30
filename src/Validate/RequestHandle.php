<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Validate;

use DeathSatan\Hyperf\Validate\Lib\AbstractValidate;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;

class RequestHandle implements \DeathSatan\Hyperf\Validate\Contract\CustomHandle
{
    /**
     * {@inheritDoc}
     */
    public function provide(object $current, AbstractValidate $validate, string $scene = null): array
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        return $request->getAttribute('actionRequest')['params'] ?? [];
    }
}
