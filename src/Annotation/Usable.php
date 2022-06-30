<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
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
