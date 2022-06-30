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

class ErrorCodeCollector extends MetadataCollector
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
        static::$container[$class][] = $value;
    }

    public static function result()
    {
        if (count(static::$result) == 0) {
            static::parseParams();
        }
        return static::$result;
    }

    public static function parseParams()
    {
        foreach (static::list() as $class => $errorCodes) {
            $result = [];
            foreach ($errorCodes as $errorCode) {
                $result[$errorCode->code] = [
                    'code' => $errorCode->code,
                    'message' => $errorCode->message,
                ];
            }
            static::$result[$class] = $result;
        }
    }
}
