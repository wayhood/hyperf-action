<?php


namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\RequestRuleCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RequestRule extends AbstractAnnotation
{
    public $rule;

    public $message;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value))
        {
            foreach ($value as $key =>$val)
            {
                switch ($key)
                {
                    case 'r':
                        $this->rule = $val;
                        break;
                    case 'm':
                        $this->message = $val;
                }
            }
        }
    }

    public function collectClass(string $className): void
    {
        RequestRuleCollector::collectClass($className, static::class, $this);
    }
}