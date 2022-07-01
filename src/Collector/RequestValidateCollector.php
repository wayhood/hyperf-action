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

    public static function result():array
    {
        if (count(static::$result) == 0)
        {
            static::parseValidate();
        }
        return static::$result;
    }

    public static function parseValidate()
    {
        foreach (static::list() as $class => $requestValidate) {
            $result = [];
            foreach ($requestValidate as $validate)
            {
                $result[] = [
                    'validate'  => static::makeValidate($validate->validate),
                    'scene'     => $validate->scene,
                    'safe_mode' => $validate->safe_mode
                ];
            }
            static::$result[$class] = $result;
        }
    }

    protected static function makeValidate($validate)
    {
        if (!class_exists($validate))
        {
            throw new \RuntimeException('验证类:['.$validate.']不存在');
        }
        return ApplicationContext::getContainer()->make($validate);
    }
}