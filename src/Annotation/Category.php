<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\CategoryCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Category extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '未分类';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('name', $value);
    }
    

    public function collectClass(string $className): void
    {
        CategoryCollector::collectClass($className, static::class, $this->name);
    }

}