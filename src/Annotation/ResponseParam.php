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
use Wayhood\HyperfAction\Collector\ResponseParamCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ResponseParam extends AbstractAnnotation
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
     * @var string
     */
    public $example = '无';

    /**
     * @var string
     */
    public $description = '无';

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                switch ($key) {
                    case 'n':
                        $this->name = $val;
                        break;
                    case 't':
                        $this->type = $val;
                        break;
                    case 'e':
                        $this->example = $val;
                        break;
                    case 'd':
                        $this->description = $val;
                        break;
                }
            }
        }
    }

    public function collectClass(string $className): void
    {
        ResponseParamCollector::collectClass($className, static::class, $this);
    }
}
