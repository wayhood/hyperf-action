<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
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
