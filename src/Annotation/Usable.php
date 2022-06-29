<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Usable extends AbstractAnnotation
{
    public function __construct(public bool $usable = true)
    {
    }


    public function collectClass(string $className): void
    {
        UsableCollector::collectClass($className, static::class, $this->usable);
    }

}