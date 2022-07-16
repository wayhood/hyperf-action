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
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;

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
