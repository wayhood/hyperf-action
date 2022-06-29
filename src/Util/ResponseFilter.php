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
namespace Wayhood\HyperfAction\Util;

use Wayhood\HyperfAction\Collector\ResponseParamCollector;

class ResponseFilter
{
    public static function processResponseData($data, $className)
    {
        if (is_array($data) && count($data) == 0) {
            return new \stdClass();
        }
        // 根据配置，过滤响应参数
        return self::filterData($data, ResponseParamCollector::result()[$className] ?? [], 'array');
    }

    public static function filterArrayData($data, $mapData)
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $newV = [];
            foreach ($mapData as $key => $value) {
                if (array_key_exists($key, $v)) {
                    $newV[$key] = self::processVarType($value['type'], $v[$key], $value['example']);
                    if (array_key_exists('children', $value)) {
                        $newV[$key] = self::filterData($newV[$key], $value['children'], $value['type']);
                    }
                }
            }
            $newData[$k] = $newV;
        }
        return $newData;
    }

    public static function processVarType($type, $value, $example)
    {
        if ($type == 'string') {
            if (is_null($value)) {
                $value = '';
            }
            if (! is_string($value)
                && is_scalar($value)
            ) {
                // float 转 string 丢失精度 获取$exmaple的精度
                if (is_float($value)) {
                    $tmp = explode('.', $example);
                    if (count($tmp) == 2) {
                        $decimal = strlen($tmp[1]);
                        $value = number_format($value, $decimal, '.', '');
                    } else {
                        $decimal = 0;
                        $value = strval(trim($value));
                    }
                } else {
                    $value = strval(trim($value));
                }
            }
        } elseif ($type == 'int') {
            if (is_null($value)) {
                $value = 0;
            }
            if (! is_int($value)
                && is_scalar($value)
            ) {
                $value = intval($value);
            }
        } elseif ($type == 'float') {
            if (is_null($value)) {
                $value = 0.0;
            }
            if (! is_float($value)
                && is_scalar($value)
            ) {
                $value = floatval($value);
            }
        } elseif ($type == 'array') {
            if (! is_array($value)) {
                $value = [$value];
            }
        }
        return $value;
    }

    public static function filterData($data, $mapData, $type)
    {
        $newData = [];
        foreach ($mapData as $key => $value) {
            if (is_numeric($key)) {
                if (isset($value['children'])) {
                    $newData = self::filterArrayData($data, $value['children']);
                } else {
                    $newData = $data;
                }
                return $newData;
            }
            if (array_key_exists($key, $data)) {
                $newData[$key] = self::processVarType($value['type'], $data[$key], $value['example']);
                if (array_key_exists('children', $value)) {
                    $newData[$key] = self::filterData($newData[$key], $value['children'], $value['type']);
                }
            }
        }

        if ($type == 'map' || $type == 'object') {
            if (empty($newData)) {
                $newData = new \stdClass();
            }
        }

        return $newData;
    }
}
