<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Controller;

use Hyperf\Utils\Context;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Wayhood\HyperfAction\Collector\ActionCollector;
class MainController
{
    public function index()
    {
        $requestMapper = ['index.list', 'test.list'];

        $parallel = new Parallel(count($requestMapper));
        for($i=0; $i<count($requestMapper); $i++) {
            $mapping = $requestMapper[$i];
            $actionName = ActionCollector::get($mapping);
            $action = $this->container->get($actionName);
            $parallel->add(function() use ($action) {
                Context::copy(\Swoole\Coroutine::getPcid());
                $ret = $action->run();
                //\Swoole\Coroutine::sleep(1);
                return $ret;
            }, $mapping);
        }

        try {
            $results = $parallel->wait();
            return $this->response->json($results);
        }  catch(ParallelExecutionException $e){
            //echo $e->getMessage();
            //var_dump($e->getResults()); // 获取协程中的返回值。
            //var_dump($e->getThrowables()); // 获取协程中出现的异常。
        }
    }
}