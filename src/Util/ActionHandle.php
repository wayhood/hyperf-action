<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Util;

use DeathSatan\Hyperf\Validate\Contract\CustomHandle;
use DeathSatan\Hyperf\Validate\Lib\AbstractValidate;
use Hyperf\HttpServer\Contract\RequestInterface;

class ActionHandle implements CustomHandle
{
    public function __construct(protected RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function provide(object $current, AbstractValidate $validate, string $scene = null): array
    {
        $actionRequest = $this->request->getAttribute('actionRequest');
        return $actionRequest['params'] ?? [];
    }
}
