<?php


namespace Wayhood\HyperfAction\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Wayhood\HyperfAction\Collector\RequestValidateCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class RequestValidate extends AbstractAnnotation
{
    /**
     * 验证器
     * @var string
     */
    public $validate;

    /**
     * 场景
     * @var string
     */
    public $scene = null;

    /**
     * 是否开启严格模式
     * @var boolean
     */
    public $safe_mode = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                switch ($key) {
                    case 'v':
                        $this->validate = $val;
                        break;
                    case 's':
                        $this->scene = $val;
                        break;
                    case 'm':
                        $this->safe_mode = $val;
                        break;
                }
            }
        }
    }
    public function collectClass(string $className): void
    {
        RequestValidateCollector::collectClass($className, static::class, $this);
    }
}