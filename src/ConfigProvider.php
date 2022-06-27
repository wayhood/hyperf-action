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

use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Wayhood\HyperfAction\Collector\DescriptionCollector;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\RequestValidateCollector;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Service\TokenService;
use Wayhood\HyperfAction\Command\ActionCommand;
use Wayhood\HyperfAction\Command\ServiceCommand;
use Wayhood\HyperfAction\Command\DescribeActionsCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TokenInterface::class => TokenService::class
            ],
            'commands' => [
                ActionCommand::class,
                ServiceCommand::class,
                DescribeActionsCommand::class
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        ActionCollector::class,
                        CategoryCollector::class,
                        DescriptionCollector::class,
                        ErrorCodeCollector::class,
                        RequestParamCollector::class,
                        ResponseParamCollector::class,
                        TokenCollector::class,
                        UsableCollector::class,
                        RequestValidateCollector::class
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'wayhood',
                    'description' => 'The config for hyperf-wayhood.',
                    'source' => __DIR__ . '/../publish/wayhood.php',
                    'destination' => BASE_PATH . '/config/autoload/wayhood.php',
                ],
            ],
        ];
    }
}
