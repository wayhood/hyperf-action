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

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Introduction extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $name = '无说明';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('name', $value);
    }

    public function collectClass(string $className): void
    {

    }
}
