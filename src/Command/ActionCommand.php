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
namespace Wayhood\HyperfAction\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Devtool\Generator\GeneratorCommand;
/**
 * @Command
 */
#[Command]
class ActionCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:action');
        $this->setDescription('Create a new action class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/action.stub';
    }

    protected function buildClass($name)
    {
        $stub = file_get_contents($this->getStub());
        $action = str_replace('App\\Action\\', '', $name);
        $action = str_replace('Action', '', $action);
        $action = str_replace('\\', '.', $action);
        $stub = str_replace('%ACTION%', strtolower($action), $stub);
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Action';
    }
}
