<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Action extends AbstractAnnotation
{
    public function __construct(public string $mapping = '')
    {
    }

    public function collectClass(string $className): void
    {
        ActionCollector::collectClass($className, static::class, $this->mapping);
    }
}