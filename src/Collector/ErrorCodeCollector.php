<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class ErrorCodeCollector extends MetadataCollector
{
    protected static array $container = [];

    protected static array $result = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class][] = $value;
    }

    public static function result() {
        if (count(static::$result) == 0) {
            static::parseParams();
        }
        return static::$result;
    }

    public static function parseParams() {
        foreach(static::list() as $class => $errorCodes) {
            $result = [];
            foreach($errorCodes as $errorCode) {
                $result[$errorCode->code] = [
                    'code' => $errorCode->code,
                    'message' => $errorCode->message,
                ];
            }
            static::$result[$class] = $result;
        }
    }
}