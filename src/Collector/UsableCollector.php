<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Collector;

use Hyperf\Di\MetadataCollector;

class UsableCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class] = $value;
    }
}
