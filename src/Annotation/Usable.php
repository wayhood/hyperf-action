<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\UsableCollector;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Usable extends AbstractAnnotation
{
    /**
     * @var bool
     */
    public $ok = true;
    
    public function __construct($value = null)
    {
        parent::__construct($value);
        if (!is_null($value)) {
            $this->bindMainProperty('ok', $value);
        }
    }


    public function collectClass(string $className): void
    {
        UsableCollector::collectClass($className, static::class, $this->ok);
    }

}