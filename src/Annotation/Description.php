<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\DescriptionCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Description extends AbstractAnnotation
{
    public function __construct(public string $name = '无描述')
    {
    }


    public function collectClass(string $className): void
    {
        DescriptionCollector::collectClass($className, static::class, $this->name);
    }

}