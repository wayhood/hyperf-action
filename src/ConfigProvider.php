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

use Wayhood\HyperfAction\Collector\ActionCollector;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Wayhood\HyperfAction\Collector\DescriptionCollector;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;
use Wayhood\HyperfAction\Collector\RequestValidateCollector;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Wayhood\HyperfAction\Command\ActionCommand;
use Wayhood\HyperfAction\Command\DescribeActionsCommand;
use Wayhood\HyperfAction\Command\GeneratorCURDCommand;
use Wayhood\HyperfAction\Command\ServiceCommand;
use Wayhood\HyperfAction\Contract\TokenInterface;
use Wayhood\HyperfAction\Service\TokenService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                TokenInterface::class => TokenService::class,
            ],
            'commands' => [
                ActionCommand::class,
                ServiceCommand::class,
                DescribeActionsCommand::class,
                GeneratorCURDCommand::class
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
                        RequestValidateCollector::class,
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
