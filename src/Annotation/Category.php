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
namespace Wayhood\HyperfAction\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\CategoryCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Category extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '未分类';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('name', $value);
    }

    public function collectClass(string $className): void
    {
        CategoryCollector::collectClass($className, static::class, $this->name);
    }
}
