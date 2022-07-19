<?php


namespace Wayhood\HyperfAction\Annotation;

use Attribute;
use Hyperf\Utils\ApplicationContext;

#[Attribute(Attribute::TARGET_CLASS)]
class Validate extends \Hyperf\Di\Annotation\AbstractAnnotation
{
    /**
     * Validate constructor.
     * @param string $validate 验证器
     * @param string|array $scene 验证场景
     */
    public function __construct(public string $validate,public null|string|array $scene = null){
        $this->validate();
    }

    protected function validate()
    {
        $this->validate = ApplicationContext::getContainer()->make($this->validate);
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
    }
}