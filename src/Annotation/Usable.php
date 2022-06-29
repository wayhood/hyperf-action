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
use Wayhood\HyperfAction\Collector\UsableCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Usable extends AbstractAnnotation
{
    /**
     * @var bool
     */
    public $ok = true;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (! is_null($value)) {
            $this->bindMainProperty('ok', $value);
        }
    }

    public function collectClass(string $className): void
    {
        UsableCollector::collectClass($className, static::class, $this->ok);
    }
}
