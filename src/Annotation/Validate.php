<?php


namespace Wayhood\HyperfAction\Annotation;

use Attribute;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class Validate extends \Hyperf\Di\Annotation\AbstractAnnotation
{
    #[Inject]
    protected ContainerInterface $container;

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
        $this->validate = $this->container->make($this->validate);
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
    }
}