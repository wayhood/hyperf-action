<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class CategoryCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static array $container = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class] = $value;
    }
}