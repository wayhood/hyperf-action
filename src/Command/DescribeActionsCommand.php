<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wayhood\HyperfAction\Collector\ActionCollector;

class DescribeActionsCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        parent::__construct('describe:actions');
        $this->container = $container;
        $this->config = $config;
    }

    public function handle()
    {
        $path = $this->input->getOption('dispatch');
        $actions = ActionCollector::list();
        $this->show(
            $this->analyzeAction($actions, $path),
            $this->output
        );
    }

    protected function configure()
    {
        $this->setDescription('Describe the actions information.')
            ->addOption('dispatch', 'd', InputOption::VALUE_OPTIONAL, 'Get the detail of the specified action information by dispatch');
    }

    protected function analyzeAction($data, ?string $dispatch)
    {
        if (! is_null($dispatch)) {
            if (isset($data[$dispatch])) {
                return [
                    $dispatch => $data[$dispatch],
                ];
            }
            return [];
        }
        return $data;
    }

    private function show(array $data, OutputInterface $output)
    {
        $rows = [];
        foreach ($data as $action => $class) {
            $dispatch['class'] = $class;
            $dispatch['dispatch'] = $action;
            $rows[] = $dispatch;
            $rows[] = new TableSeparator();
        }
        $rows = array_slice($rows, 0, count($rows) - 1);
        $table = new Table($output);
        $table
            ->setHeaders(['Class', 'Dispatch'])
            ->setRows($rows);
        $table->render();
    }
}
