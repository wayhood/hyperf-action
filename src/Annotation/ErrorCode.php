<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ErrorCode extends AbstractAnnotation
{
    
    public function __construct(public int $code = 1999, public string $message = '未知')
    {


    }


    public function collectClass(string $className): void
    {
        ErrorCodeCollector::collectClass($className, static::class, $this);
    }

}