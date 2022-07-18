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
use Wayhood\HyperfAction\Collector\UsableCollector;

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
