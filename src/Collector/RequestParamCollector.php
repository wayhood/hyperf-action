<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class RequestParamCollector extends MetadataCollector
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

    public static function result() {
        if (count(static::$result) == 0) {
            static::parseParams();
        }
        return static::$result;
    }

    public static function parseParams() {
        foreach(static::list() as $class => $requestParams) {
            $result = [];
            foreach($requestParams as $requetParam) {
                $result[] = [
                    'name' => $requetParam->name,
                    'type' => $requetParam->type,
                    'require' => $requetParam->require,
                    'example' => $requetParam->example,
                    'desc' => $requetParam->description,
                ];
            }
            static::$result[$class] = $result;
        }
    }
}