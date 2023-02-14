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
use Wayhood\HyperfAction\Collector\IntroductionCollector;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Introduction extends AbstractAnnotation
{
    /**
     * @param string $introduction 接口文档描述
     */
    public function __construct(public string $introduction)
    {
    }

    public function collectClass(string $className): void
    {
        IntroductionCollector::collectClass($className,static::class,$this);
    }
}
