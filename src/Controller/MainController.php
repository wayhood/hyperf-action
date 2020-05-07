<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Wayhood\HyperfAction\Collector\ActionCollector;
class MainController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    public function index()
    {
        $requestMapping = ['index.list', 'test.list'];

        $parallel = new Parallel(count($requestMapping));
        for($i=0; $i<count($requestMapping); $i++) {
            $mapping = $requestMapping[$i];
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