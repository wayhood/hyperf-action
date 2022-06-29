<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\TokenCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Token extends AbstractAnnotation
{
    public function __construct(
        public bool $token = true,
    )
    {

    }

    public function collectClass(string $className): void
    {
        TokenCollector::collectClass($className, static::class, $this->token);
    }

}