<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ResponseParam extends AbstractAnnotation
{
    public function __construct(
        public string $name = '',
        public string $type = 'string',
        public string $example = '无',
        public string $description = '无'
    )
    {

    }


    public function collectClass(string $className): void
    {
        ResponseParamCollector::collectClass($className, static::class, $this);
    }

}