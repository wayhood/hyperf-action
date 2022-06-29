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

class ResponseParamCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

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
        foreach (static::list() as $class => $responseParams) {
            $result = [];
            foreach ($responseParams as $responseParam) {
                static::checkHasChildrenKey($result, $responseParam->name, [
                    'type' => $responseParam->type,
                    'example' => $responseParam->example,
                    'desc' => $responseParam->description,
                ], false);
            }
            static::$result[$class] = $result;
        }
    }

    private static function checkHasChildrenKey(&$params, $key, $data, $hasChild)
    {
        if (strpos($key, '.') !== false) {
            $hasChild = true;
            // 有多级key
            $keys = explode('.', $key);
            $first_key = array_shift($keys);
            $nextKey = join('.', $keys);
            $secondKey = array_shift($keys);
            if (! isset($params[$first_key])) {
                $params[$first_key] = [];
            }
            if (! isset($params[$first_key]['children'])) {
                $params[$first_key]['children'] = [];
            }
            static::checkHasChildrenKey($params[$first_key]['children'], $nextKey, $data, $hasChild);
        } else {
            $data['name'] = $key;
            if (! isset($params[$key])) {
                $params[$key] = [];
                $params[$key] = $data;
            }
        }
    }
}
