<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\CategoryCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class Category extends AbstractAnnotation
{
    public function __construct(public string $name = '未分类')
    {
    }

    public function collectClass(string $className): void
    {
        CategoryCollector::collectClass($className, static::class, $this->name);
    }
}
