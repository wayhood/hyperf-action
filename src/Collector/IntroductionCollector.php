<?php

namespace Wayhood\HyperfAction\Collector;

use Hyperf\Di\MetadataCollector;

class IntroductionCollector extends MetadataCollector
{
    protected static array $container = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class][] = $value;
    }

}