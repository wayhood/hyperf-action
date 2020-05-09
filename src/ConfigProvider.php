<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Wayhood\HyperfAction;

use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Service\TokenService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TokenInterface::class => TokenService::class
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ]
        ];
    }
}
