<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
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
     * validate class.
     * @var string
     */
    public $validate;

    /**
     * 场景.
     * @var null|string
     */
    public $scene;

    public function __construct($value = null)
    {
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
                }
            }
        }
    }

    public function collectClass(string $className): void
    {
        RequestValidateCollector::collectClass($className, static::class, $this);
    }
}
