<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class ActionCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var array
     */
    protected static $result = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$value] = $class;
    }

    public static function result() {
        if (count(static::$result) == 0) {
            foreach(static::$container as $key => $value) {
                static::$result[$value] = $key;
            }
        }
        return static::$result;
    }
}