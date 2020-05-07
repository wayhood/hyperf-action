<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Bootstrap;


use App\Annotation\Action;
use App\Collector\ActionCollector;
use Hyperf\Di\Annotation\AnnotationCollector;

class ServerStartCallback extends \Hyperf\Framework\Bootstrap\ServerStartCallback
{
    protected $actions = [];
    public function beforeStart()
    {
        $this->initAnnotationDispatch(AnnotationCollector::list());
        parent::beforeStart();
    }

    public function initAnnotationDispatch(array $collector): void {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][Action::class])) {
                ActionCollector::set($metadata['_c'][Action::class]->mapper, $className);
            }
        }
    }
}