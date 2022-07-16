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
use Wayhood\HyperfAction\Collector\ResponseParamCollector;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ResponseParam extends AbstractAnnotation
{
    public function __construct(
        public string $name = '',
        public string $type = 'string',
        public string $example = '无',
        public string $description = '无'
    ) {
    }

    public function collectClass(string $className): void
    {
        ResponseParamCollector::collectClass($className, static::class, $this);
    }
}
