<?php


namespace Wayhood\HyperfAction\Command;


use Hyperf\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GeneratorCURDCommand extends Command
{

    public function __construct(string $name = null)
    {
        parent::__construct('gen:curd-action');
        $this->setDescription('One click curd');
        $this->setHelp(<<<HELP
Generate actions, models, and services based on the provided table name
The generated file contains some common operations
HELP
);
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('force','f',InputOption::VALUE_OPTIONAL,'Whether to force overwrite',false);
        $this->addUsage('--force');
        $this->addArgument('model',InputArgument::REQUIRED,'Model class');
        $this->addUsage('--model App\\Model\\User');
        $this->addOption('namespace','name',InputOption::VALUE_OPTIONAL,'Action and Service Namespace');
        $this->addUsage('--namespace User');
    }


    public function handle()
    {
        $attributes = $this->getAttributes();
        if ($attributes === false)
        {
            return;
        }
        $this->genService($attributes);
    }

    protected function getAttributes()
    {
        $force = (boolean)$this->input->getOption('force');
        $model = $this->input->getArgument('model');
        $namespace = $this->input->getOption('namespace');
        if (!class_exists($model))
        {
            $this->error('NotFound Model Class:['.$model.']');
            return false;
        }
        $model_class = $this->getModelClass($model);
        $model = make($model);
        return compact('force','model','namespace','model_class');
    }

    protected function getModelClass(string $model)
    {
        return substr($model,strrpos($model,'\\')+1);
    }

    protected function genService(array $attributes)
    {
        
    }

}