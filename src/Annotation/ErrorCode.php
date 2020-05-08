<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\ErrorCodeCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ErrorCode extends AbstractAnnotation
{
    /**
     * @var int
     */
    public $code = 1999;

    /**
     * @var string
     */
    public $message = '未知';

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                switch($key) {
                    case "c":
                        $this->code = $val;
                        break;
                    case "m":
                        $this->message = $val;
                        break;
                }
            }
        }

    }


    public function collectClass(string $className): void
    {
        ErrorCodeCollector::collectClass($className, static::class, $this);
    }

}