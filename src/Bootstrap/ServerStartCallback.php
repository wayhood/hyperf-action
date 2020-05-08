<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Bootstrap;


use Wayhood\HyperfAction\Annotation\Action;
use Wayhood\HyperfAction\Annotation\ResponseParam;
use Wayhood\HyperfAction\Collector\ActionCollector;
use Hyperf\Di\Annotation\AnnotationCollector;
use Wayhood\HyperfAction\Collector\ResponseParamCollector;
use Wayhood\HyperfAction\Collector\RequestParamCollector;

class ServerStartCallback extends \Hyperf\Framework\Bootstrap\ServerStartCallback
{
    public function beforeStart()
    {
        parent::beforeStart();
    }
}