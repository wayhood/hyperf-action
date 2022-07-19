<?php


namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class ValidateCollector extends MetadataCollector
{
    protected static array $container = [];

    protected static $result = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class][] = $value;
    }

    public static function result()
    {
        if (count(static::$result) == 0) {
            static::parseValidate();
        }
        return static::$result;
    }

    public static function parseValidate()
    {
        foreach (static::list() as $class => $validates) {
            $result = [];
            foreach ($validates as $validate)
            {
                $result[] = [
                    'validate'=>$validate->validate,
                    'scene'=>$validate->scene,
                ];
            }
            self::$result[$class] = $result;
        }
    }
}