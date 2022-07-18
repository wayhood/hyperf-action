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
use Wayhood\HyperfAction\Collector\ActionCollector;

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
