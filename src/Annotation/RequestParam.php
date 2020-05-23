<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\RequestParamCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RequestParam extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $type = 'string';

    /**
     * @var bool
     */
    public $require = true;

    /**
     * @var mixed
     */
    public $example = '无';

    /**
     * @var string
     */
    public $description = '无';

    /**
     * 示例是否使用base64
     * @var bool
     */
    public $base64 = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                switch($key) {
                    case "b":
                        $this->base64 = $val;
                        break;
                    case "n":
                        $this->name = $val;
                        break;
                    case "t":
                        $this->type = $val;
                        break;
                    case "r":
                        $this->require = $val;
                        break;
                    case "e":
                        $this->example = $val;
                        break;
                    case "d":
                        $this->description = $val;
                        break;
                }
            }
        }

    }


    public function collectClass(string $className): void
    {
        RequestParamCollector::collectClass($className, static::class, $this);
    }

}