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
        if (isset($search['relation'])) {
            $query = $query->with($search['relation']);
            unset($search['relation']);
        }
        foreach ($this->convertWhere($params) as $key => $value) {
            $query = $this->buildWhere($query, $key, $value);
        }
        return $query;
    }

    // 解析参数
    protected function convertWhere(array $params)
    {
        foreach ($params as $index => $value) {
            foreach ($value as $i => $v) {
                if (is_integer($i)) {
                    continue;
                }
                $params[$index . '.' . $i] = $v;
                unset($params[$index]);
            }
        }
        return $params;
    }

    // 根据key和value生成单条查询条件
    protected function buildWhere($query, $key, $value)
    {
        if (is_string($value)) {
            return [$key, '=', $value];
        }
        if (is_array($value) && count($value) == 2) {
            $type = $value[0];
            $value = $value[1];
            switch ($type) {
                case 'like':
                    $value = (string) $value;
                    return $query->where($key, 'like', '%' . $value . '%');
                case 'between':
                    return $query->whereBetween($key, $value);
                case 'in':
                    return $query->whereIn($key, $value);
            }
            if (in_array($type, $this->op_condition)) {
                return $query->where($key, $type, $value);
            }
        }
        throw new Exception('Where Format error');
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
                ->create($data);
        }
        return $model
            ->newQuery()
            ->whereIn($model->getKeyName(), $ids)
            ->updateOrCreate($data);
    }
}
