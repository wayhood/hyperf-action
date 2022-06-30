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
