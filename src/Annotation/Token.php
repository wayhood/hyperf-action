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
use Wayhood\HyperfAction\Collector\TokenCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class Token extends AbstractAnnotation
{
    public function __construct(
        public bool $token = true,
    ) {
    }

    public function collectClass(string $className): void
    {
        TokenCollector::collectClass($className, static::class, $this->token);
    }
}
