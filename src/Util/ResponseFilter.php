<?php

namespace Wayhood\HyperfAction\Util;


use Wayhood\HyperfAction\Collector\ResponseParamCollector;

class ResponseFilter
{
    public static function processResponseData($data, $className)
    {
        if (is_array($data) && count($data) == 0) {
            $data = new \stdClass();
            return $data;
        }
        //根据配置，过滤响应参数
        $data = self::filterData($data, ResponseParamCollector::result()[$className]??[]);
        return $data;
    }

    public static function filterArrayData($data, $mapData)
    {
        $newData = [];
        foreach ($data as $k => $v) {
            $newV = [];
            foreach ($mapData as $key => $value) {
                if (array_key_exists($key, $v)) {
                    $newV[$key] = self::processVarType($value['type'], $v[$key]);
                    if (array_key_exists('children', $value)) {
                        $newV[$key] = self::filterData($newV[$key], $value['children']);
                    }
                }
            }
            $newData[$k] = $newV;
        }
        return $newData;
    }

    public static function processVarType($type, $value) {
        if ($type == 'string') {
            if (is_null($value)) {
                $value = '';
            }
            if (!is_string($value)
                && is_scalar($value)
            ) {
                $value = strval(trim($value));
            }
        } else if ($type == 'int') {
            if (is_null($value)) {
                $value = 0;
            }
            if (!is_int($value)
                && is_scalar($value)
            ) {
                $value = intval($value);
            }
        } else if ($type == 'float') {
            if (is_null($value)) {
                $value = 0.0;
            }
            if (!is_float($value)
                && is_scalar($value)
            ) {
                $value = floatval($value);
            }
        } else if ($type == 'array') {
            if (!is_array($value)) {
                $value = [$value];
            }
        }
        return $value;
    }

    public static function filterData($data, $mapData)
    {
        $newData = [];
        foreach ($mapData as $key => $value) {
            if (is_numeric($key)) {
                $newData = self::filterArrayData($data, $value['children']);
                return $newData;
            }
            if (array_key_exists($key, $data)) {
                $newData[$key] = self::processVarType($value['type'], $data[$key]);
                if (array_key_exists('children', $value)) {
                    $newData[$key] = self::filterData($newData[$key], $value['children']);
                }
            }
        }

        return $newData;
    }

}