<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Util;

use Exception;
use Hyperf\Database\Model\Model;

trait BaseService
{
    // 可以匹配的类型
    protected $op_condition = [
        '=', '<>', '!=', '>', '<', '>=', '<=',
    ];

    // 获取模型
    protected function model(): Model
    {
        if (empty($this->model)) {
            throw new \Exception(static::class . ' NotFound property model');
        }
        $model = $this->model;
        return new $model();
    }

    // 获取查询条件
    protected function getWhere(Model $query, $params)
    {
        foreach ($params as $key => $value) {
            $query = $query->query()->where(...($this->buildWhere($key, $value)));
        }
        return $query;
    }

    // 根据key和value生成单条查询条件
    protected function buildWhere($key, $value): array
    {
        if (is_string($value)) {
            return [$key, '=', $value];
        }
        if (is_array($value) && count($value) == 2) {
            return $this->parseWhere($key, $value[0], $value[1]);
        }
        throw new Exception('Where Format error');
    }

    // 根据key,type,以及value解析查询条件
    protected function parseWhere($key, $type, $value): array
    {
        switch ($type) {
            case 'like':
                $value = (string) $value;
                return [function ($query) use ($value, $key) {
                    return $query->where($key, 'like', '%' . $value . '%')
                        ->orWhere($key, 'like', '%' . $value)
                        ->orWhere($key, 'like', $value . '%');
                }];
            case 'between':
                return [function ($query) use ($key, $value) {
                    return $query->whereBetween($key, $value);
                }];
            case 'in':
                return [function ($query) use ($value, $key) {
                    return $query->whereIn($key, $value);
                }];
        }
        if (in_array($type, $this->op_condition)) {
            return [$key, $type, $value];
        }
        throw new Exception('Where Type Format Error');
    }

    // 批量插入的方法
    protected function add(array $data)
    {
        $model = $this->model();
        $result = [];
        foreach ($data as $datum) {
            $result[] = $model::create($datum);
        }
        return $result;
    }

    // 单条或者批量删除的方法
    protected function del(array $ids, bool $is_soft_delete = false)
    {
        $model = $this->model();
        if ($is_soft_delete) {
            $delete_at = method_exists($model, 'getDeletedAtColumn') ?
                $model->getDeletedAtColumn()
                : 'deleted_at';
            $time = $model->freshTimestamp();
            $columns = [$delete_at => $model->fromDateTime($time)];
            return $model->newQuery()->whereIn($model->getKeyName(), $ids)->update($columns);
        }
        return $model->newQuery()->whereIn($model->getKeyName(), $ids)->delete();
    }

    // 批量修改的方法
    protected function update(array $ids, array $data)
    {
        $model = $this->model();
        if (count($ids) == 0 && $model->newQuery()->whereIn($model->getKeyName(), $ids)->count() == 0) {
            return $model->newQuery()
                ->insert($data);
        }
        return $model
            ->newQuery()
            ->whereIn($model->getKeyName(), $ids)
            ->updateOrCreate($data);
    }
}
