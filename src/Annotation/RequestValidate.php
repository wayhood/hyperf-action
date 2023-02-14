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
use Wayhood\HyperfAction\Collector\RequestValidateCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class RequestValidate extends AbstractAnnotation
{
    /**
     * Validate constructor.
     * @param string $validate 验证器
     * @param null|array|string $scene 验证场景
     * @param bool $safeMode 严格模式，默认false
     */
    public function __construct(public string $validate, public null|string|array $scene = null, public bool $safeMode = false)
    {
    }

    public function collectClass(string $className): void
    {
        RequestValidateCollector::collectClass($className, static::class, $this);
    }
}
