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

class ActionCollector extends MetadataCollector
{
    protected static array $container = [];

    protected static array $result = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        if (isset(static::$container[$value])) {
            if (static::$container[$value] != $class) {
                $msg = "Duplicate definition Action(\"{$value}\") in " . static::$container[$value] . ',' . $class;
                throw new \Exception($msg);
            }
        }
        static::$container[$value] = $class;
    }

    public static function result()
    {
        if (count(static::$result) == 0) {
            foreach (static::$container as $key => $value) {
                static::$result[$value] = $key;
            }
        }
        return static::$result;
    }
}
