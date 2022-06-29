<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Action extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $mapping = '';

    public function __construct($value)
    {
        parent::__construct($value);
        $this->bindMainProperty('mapping', $value);
    }

    public function collectClass(string $className): void
    {
        ActionCollector::collectClass($className, static::class, $this->mapping);
    }
}