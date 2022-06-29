<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Attribute;

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