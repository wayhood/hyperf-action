<?php


namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\ApplicationContext;

class RequestValidateCollector extends MetadataCollector
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

    public static function result(): array
    {
        if (count(static::$result) == 0) {
            static::parseValidates();
        }
        return static::$result;
    }

    public static function parseValidates()
    {
        foreach(static::list() as $class => $requestValidates) {
            $result = [];
            foreach($requestValidates as $validate) {
                $result[] = [
                    'validate' => static::makeValidate($validate->validate),
                    'scene' => $validate->scene,
                ];
            }
            static::$result[$class] = $result;
        }
    }

    public static function makeValidate(string $validate)
    {
        return ApplicationContext::getContainer()->make($validate);
    }
}