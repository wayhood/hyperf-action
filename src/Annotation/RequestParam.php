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
use Wayhood\HyperfAction\Collector\RequestParamCollector;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class RequestParam extends AbstractAnnotation
{
    public function __construct(
        public string $name = '',
        public string $type = 'string',
        public bool $require = true,
        public string $example = '无',
        public string $description = '无',
        public bool $base64 = false
    ) {
    }

    public function collectClass(string $className): void
    {
        RequestParamCollector::collectClass($className, static::class, $this);
    }
}
