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

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\CodeGen\Project;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GeneratorCURDCommand extends Command
{
    protected $attributes = [];

    public function __construct(string $name = null)
    {
        parent::__construct('gen:curd-action');
        $this->setDescription('One click curd');
        $this->setHelp(
            <<<'HELP'
Generate actions, models, and services based on the provided table name
The generated file contains some common operations
HELP
        );
    }

    public function handle()
    {
        $this->initAttributes();
        if ($this->attributes === false) {
            return;
        }
        $service = $this->genService($this->attributes);
        $this->attributes['service'] = $service;
        $this->genActions($this->attributes);
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('force', 'f', InputOption::VALUE_OPTIONAL, 'Whether to force overwrite', false);
        $this->addUsage('--force');
        $this->addArgument('model', InputArgument::REQUIRED, 'Model class');
        $this->addUsage('--model App\\Model\\User');
        $this->addOption('namespace', 'name', InputOption::VALUE_OPTIONAL, 'Action and Service Namespace');
        $this->addUsage('--namespace User');
    }

    protected function initAttributes()
    {
        $force = (bool) $this->input->getOption('force');
        $model = $this->input->getArgument('model');
        $namespace = $this->input->getOption('namespace');
        if (! empty($this->attributes)) {
            return $this->attributes;
        }
        if (! class_exists($model)) {
            $this->error('NotFound Model Class:[' . $model . ']');
            return false;
        }
        $model_class = $this->getModelClass($model);
        $model = make($model);
        $table = $model->getTable();
        $databases = config('databases.default.database');
        $table_comment_sql = <<<SQL
select `information_schema`.`TABLES`.TABLE_COMMENT FROM `information_schema`.`TABLES` WHERE `information_schema`.`TABLES`.`TABLE_SCHEMA` = '{$databases}' AND `information_schema`.`TABLES`.`TABLE_NAME`= '{$table}';
SQL;
        $table_comment = Db::selectOne($table_comment_sql);
        $table_comment = empty($table_comment->TABLE_COMMENT) ? $table : $table_comment->TABLE_COMMENT;

        $table_fields_comment_sql = <<<SQL
SELECT
`cl`.DATA_TYPE as type,`cl`.COLUMN_NAME as name,`cl`.COLUMN_COMMENT as comment,
`cl`.IS_NULLABLE as is_null
FROM `information_schema`.`COLUMNS` as cl WHERE `cl`.TABLE_SCHEMA = '{$databases}' and `cl`.TABLE_NAME = '{$table}'
SQL;
        $table_fields_comment = Db::select($table_fields_comment_sql);

        $this->attributes = compact('force', 'model', 'namespace', 'model_class', 'table_fields_comment', 'table_comment');
        return $this->attributes;
    }

    protected function getModelClass(string $model)
    {
        return substr($model, strrpos($model, '\\') + 1);
    }

    protected function genService(array $attributes)
    {
        $name = $this->qualifyClass($attributes['model_class'], 'service');
        $path = $this->getPath($name);
        [$input,$output] = [$this->input, $this->output];
        if (($input->getOption('force') === false) && $this->alreadyExists($attributes['model_class'], 'service')) {
            $output->writeln(sprintf('<fg=red>%s</>', $name . ' already exists!'));
            return 0;
        }
        $this->makeDirectory($path);

        file_put_contents($path, $this->buildClass($name, 'service'));

        $output->writeln(sprintf('<info>%s</info>', $name . ' created successfully.'));

        $this->openWithIde($path);
        return $name;
    }

    protected function genActions(array $attributes)
    {
        $name = $this->qualifyClass($attributes['model_class'], 'action') . '\\';
        $action_list = ['GetAction', 'SetAction', 'DelAction', 'SearchAction'];
        foreach ($action_list as $action) {
            $this->attributes['action_current'] = $action;
            $action_class = $name . $action;
            $action_path = $this->getPath($action_class);
            [$input,$output] = [$this->input, $this->output];
            if (($input->getOption('force') === false) && is_file($action_path)) {
                $output->writeln(sprintf('<fg=red>%s</>', $action_class . ' already exists!'));
                continue;
            }
            $this->makeDirectory($action_path);
            file_put_contents($action_path, $this->buildClass($action_class, 'action', $action));

            $output->writeln(sprintf('<info>%s</info>', $action_class . ' created successfully.'));

            $this->openWithIde($action_path);
        }
    }

    /**
     * Parse the class name and format according to the root namespace.
     */
    protected function qualifyClass(string $name, string $build_type): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $namespace = $this->build_namespace($build_type);
        return rtrim($namespace, '\\') . '\\' . $name;
    }

    protected function build_namespace(string $name): string
    {
        $namespace = $this->input->getOption('namespace');
        switch ($name) {
            case 'service':
                return 'App\\Service' . (is_null($namespace) ? $namespace : '\\' . $namespace);
            case 'action':
                return 'App\\Action' . (is_null($namespace) ? $namespace : '\\' . $namespace);
        }
        throw new \Exception($name . 'NotFound Namespace ');
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     * @param mixed $build_type
     */
    protected function alreadyExists($rawName, $build_type): bool
    {
        return is_file($this->getPath($this->qualifyClass($rawName, $build_type)));
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     */
    protected function getPath($name): string
    {
        $project = new Project();
        return BASE_PATH . '/' . $project->path($name);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     */
    protected function makeDirectory($path): string
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     * @return string
     */
    protected function buildClass($name, string $type, ?string $ext = null)
    {
        $stub = file_get_contents($this->getStub($type, $ext));
        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name, $type);
    }

    protected function getStub(string $type, ?string $ext)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'curd' . DIRECTORY_SEPARATOR;
        if ($type == 'action') {
            return $path . $ext . '.stub';
        }
        return $path . $type . '.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['%NAMESPACE%'],
            [$this->getNamespace($name)],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @param mixed $type
     * @return string
     */
    protected function replaceClass($stub, $name, $type)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);
        if ($type == 'service') {
            $model = $this->attributes['model'];
            $stub = str_replace('%MODEL%', '\\' . get_class($model), $stub);
        }
        if ($type == 'action') {
            $stub = $this->replaceActionStub($stub, $name);
        }
        return str_replace('%CLASS%', $class, $stub);
    }

    protected function replaceActionStub($stub, $name)
    {
        $action = str_replace('App\\Action\\', '', $name);
        $action = str_replace('Action', '', $action);
        $action = str_replace('\\', '.', $action);
        $stub = str_replace('%ACTION%', $action, $stub);
        $stub = str_replace('%SERVICE%', $this->attributes['service'], $stub);
        $request_params = $this->build_request_params();
        // 填充request params注解
        $stub = str_replace('%REQUESTPARAM%', $request_params, $stub);

        // 填充分类名
        $stub = str_replace('%CATEGORY%', $this->attributes['table_comment'], $stub);

        $response_params = $this->build_response_params();

        // 填充response params
        return str_replace('%RESPONSEPARAM', $response_params, $stub);
    }

    // 将表字段映射到注解内
    protected function formatRequest(bool $is_not_need_key_name = false, bool $is_not_need_created_and_updated = false)
    {
        /**
         * @var Model $model
         */
        $model = $this->attributes['model'];
        $except_fields = [];

        // 是否不需要映射主键
        if ($is_not_need_key_name) {
            $except_fields[] = $model->getKeyName();
        }

        // 是否不需要映射 时间维护字段
        if ($is_not_need_created_and_updated && $model->timestamps) {
            $except_fields[] = $model::UPDATED_AT;
            $except_fields[] = $model::CREATED_AT;
        }
        //是否是set action
        $is_set_action = $this->attributes['action_current']=='SetAction';
        $table_comment = $this->attributes['table_comment'];
        $fields_comment = $this->attributes['table_fields_comment'];
        $types[] = [
            'tinyint', 'smallint',
            'mediumint', 'int', 'integer', 'bigint', 'bool', 'boolean',
            'format' => $is_set_action?'int':'array',
            'example' => $is_set_action?1:'[\'>|<|!=|=\',123]',
        ];
        $types[] = [
            'float', 'double', 'decimal',
            'format' => $is_set_action?'float':'array',
            'example' => $is_set_action?10.24:'[\'>|<|!=|=\',10.24]',
        ];
        $types[] = [
            'varchar', 'char', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set',
            'format' => $is_set_action?'string':'array',
            'example' => $is_set_action?'xxxx':'[\'like|=\',\'xxx\']',
        ];
        $types[] = [
            'time', 'date', 'datetime', 'timestamp', 'year',
            'format' => $is_set_action?'string':'array',
            'example' => $is_set_action?'2022-07-07 16:48:27':'[\'start_date\',\'end_date\']',
        ];
        $result = [];
        // 转换类型
        foreach ($fields_comment as $index => $item) {
            // 去除掉不需要的字段
            if (in_array($item->name, $except_fields)) {
                continue;
            }

            // 转换type
            foreach ($types as $type) {
                if (in_array($item->type, $type)) {
                    $result[$index]['type'] = $type['format'];
                    $result[$index]['example'] = $type['example'];
                    break;
                }
            }

            // 判断是否必须传值
            $result[$index]['require'] = ($item->is_null === 'NO') ? 'true' : 'false';

            // 字段名
            $result[$index]['name'] = $item->name;
            // 如果没有注释就 拼接表的注释 + 字段名
            $result[$index]['description'] = empty($item->comment) ? $table_comment . $item->name : $item->comment;
        }
        return $result;
    }

    // 将表字段映射到response注解内
    protected function formatResponse(bool $is_not_need_key_name = false, bool $is_not_need_created_and_updated = false)
    {
        /**
         * @var Model $model
         */
        $model = $this->attributes['model'];
        $except_fields = [];

        // 是否不需要映射主键
        if ($is_not_need_key_name) {
            $except_fields[] = $model->getKeyName();
        }

        // 是否不需要映射 时间维护字段
        if ($is_not_need_created_and_updated && $model->timestamps) {
            $except_fields[] = $model::UPDATED_AT;
            $except_fields[] = $model::CREATED_AT;
        }

        $table_comment = $this->attributes['table_comment'];
        $fields_comment = $this->attributes['table_fields_comment'];
        $types[] = [
            'tinyint', 'smallint',
            'mediumint', 'int', 'integer', 'bigint', 'bool', 'boolean',
            'format' => 'int',
            'example' => '1',
        ];
        $types[] = [
            'float', 'double', 'decimal',
            'format' => 'float',
            'example' => '10.24',
        ];
        $types[] = [
            'varchar', 'char', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set',
            'format' => 'string',
            'example' => 'xxx',
        ];
        $types[] = [
            'time', 'date', 'datetime', 'timestamp', 'year',
            'format' => 'string',
            'example' => '2020-07-01 12:00:00',
        ];
        $result = [];
        // 转换类型
        foreach ($fields_comment as $index => $item) {
            // 去除掉不需要的字段
            if (in_array($item->name, $except_fields)) {
                continue;
            }

            // 转换type
            foreach ($types as $type) {
                if (in_array($item->type, $type)) {
                    $result[$index]['type'] = $type['format'];
                    $result[$index]['example'] = $type['example'];
                    break;
                }
            }

            // 字段名
            $result[$index]['name'] = $item->name;
            // 如果没有注释就 拼接表的注释 + 字段名
            $result[$index]['description'] = empty($item->comment) ? $table_comment . $item->name : $item->comment;
        }
        return $result;
    }

    protected function build_request_params()
    {
        $current_action = $this->attributes['action_current'];
        $table_comment = $this->attributes['table_comment'];
        switch ($current_action) {
            case 'DelAction':
                return $this->build_request_comment([[
                    'name' => 'ids',
                    'type' => 'array',
                    'require' => 'true',
                    'example' => '[1,2,3]',
                    'description' => $table_comment . ' ids',
                ]]);
            case 'SetAction':
                $format_request = $this->formatRequest(true, true);
                $format_request[] = [
                    'name' => 'ids',
                    'type' => 'array',
                    'require' => 'true',
                    'example' => '[1,2,3]',
                    'description' => $table_comment . ' ids',
                ];
                $format_request = $this->format_set_action($format_request);
                return $this->build_request_comment($format_request);
            case 'GetAction':
            case 'SearchAction':
                return $this->build_request_comment(
                    $this->build_search_request()
                );
        }
        return ' ';
    }

    protected function build_response_params()
    {
        $current_action = $this->attributes['action_current'];
        switch ($current_action) {
            case 'DelAction':
                return $this->build_response_comment([[
                    'name' => 'status',
                    'type' => 'bool',
                    'example' => 'true',
                    'description' => '是否成功',
                ]]);
            case 'SetAction':
                $format_request = $this->formatResponse(true, true);
                $format_request[] = [
                    'name' => 'status',
                    'type' => 'bool',
                    'example' => 'true',
                    'description' => '是否成功',
                ];
                $format_request = $this->format_set_action($format_request);
                return $this->build_response_comment($format_request);
            case 'GetAction':
            case 'SearchAction':
                return $this->build_response_comment(
                    $this->build_detail_response(true)
                );
        }
        return ' ';
    }

    // 转换set
    protected function format_set_action(array $data)
    {
        foreach ($data as &$datum) {
            if ($datum['name'] !== 'ids') {
                $datum['require'] = 'false';
            } else {
                $datum['require'] = 'true';
            }
        }
        unset($datum);
        return $data;
    }

    // 转换搜索action所需要的注释
    protected function build_search_request()
    {
        $data = $this->formatRequest(false, true);
        array_unshift($data,[
            'name'=>'search',
            'type'=>'array',
            'require'=>'false',
            'description'=>'搜索条件',
            'example'=>'search[]'
        ]);
        foreach ($data as &$datum) {
            if ($datum['name'] == 'search')
            {
                continue;
            }
            $datum['name'] = 'search.' . $datum['name'];
            $datum['require'] = 'false';
        }
        $data[] = [
            'name'=>'per_page',
            'type'=>'int',
            'require'=>'false',
            'description'=>'每页数量',
            'example'=>'10'
        ];
        $data[] = [
            'name'=>'page',
            'type'=>'int',
            'require'=>'false',
            'description'=>'当前多少页',
            'example'=>'1'
        ];
        unset($datum);
        return $data;
    }

    // 转换详情action所需要的注释
    protected function build_detail_response(bool $is_detail)
    {
        // 表注释
        $table_comment = $this->attributes['table_comment'];
        $data = $this->formatResponse();
        if (! $is_detail) {
            array_unshift($data, [
                'name' => 'list.0',
                'type' => 'map',
                'example' => '{}',
                'description' => $table_comment . '数据对象',
            ]);
            array_unshift($data, [
                'name' => 'list',
                'type' => 'array',
                'example' => 'list[]',
                'description' => $table_comment . '数据列表',
            ]);
            $data[] = [
                'name' => 'current_page',
                'type' => 'int',
                'example' => '1',
                'description' => '当前页数',
            ];
            $data[] = [
                'name' => 'total',
                'type' => 'int',
                'example' => '10',
                'description' => '总数量',
            ];
            foreach ($data as &$datum) {
                if (in_array($datum['name'], ['current_page', 'total', 'list.0', 'list'])) {
                    continue;
                }
                $datum['name'] = 'list.0.' . $datum['name'];
            }
            unset($datum);
        }
        return $data;
    }

    protected function build_request_comment(array $data)
    {
        $str = '';
        foreach ($data as $index => $datum) {
            $str .= <<<REQUEST
 * @RequestParam(n="{$datum['name']}",t="{$datum['type']}",r={$datum['require']},e="{$datum['example']}",d="{$datum['description']}")\n
REQUEST;
        }
        return $str;
    }

    protected function build_response_comment(array $data)
    {
        $str = '';
        foreach ($data as $index => $datum) {
            $str .= <<<RESPONSE
 * @ResponseParam(n="{$datum['name']}",                t="{$datum['type']}",    e="{$datum['example']}",     d="{$datum['description']}")\n
RESPONSE;
        }
        return $str;
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->input->getArgument('name'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    /**
     * Get the editor file opener URL by its name.
     */
    protected function getEditorUrl(string $ide): string
    {
        switch ($ide) {
            case 'sublime':
                return 'subl://open?url=file://%s';
            case 'textmate':
                return 'txmt://open?url=file://%s';
            case 'emacs':
                return 'emacs://open?url=file://%s';
            case 'macvim':
                return 'mvim://open/?url=file://%s';
            case 'phpstorm':
                return 'phpstorm://open?file=%s';
            case 'idea':
                return 'idea://open?file=%s';
            case 'vscode':
                return 'vscode://file/%s';
            case 'vscode-insiders':
                return 'vscode-insiders://file/%s';
            case 'vscode-remote':
                return 'vscode://vscode-remote/%s';
            case 'vscode-insiders-remote':
                return 'vscode-insiders://vscode-remote/%s';
            case 'atom':
                return 'atom://core/open/file?filename=%s';
            case 'nova':
                return 'nova://core/open/file?filename=%s';
            case 'netbeans':
                return 'netbeans://open/?f=%s';
            case 'xdebug':
                return 'xdebug://%s';
            default:
                return '';
        }
    }

    /**
     * Open resulted file path with the configured IDE.
     */
    protected function openWithIde(string $path): void
    {
        $ide = (string) $this->getContainer()->get(ConfigInterface::class)->get('devtool.ide');
        $openEditorUrl = $this->getEditorUrl($ide);

        if (! $openEditorUrl) {
            return;
        }

        $url = sprintf($openEditorUrl, $path);
        switch (PHP_OS_FAMILY) {
            case 'Windows':
                exec('explorer ' . $url);
                break;
            case 'Linux':
                exec('xdg-open ' . $url);
                break;
            case 'Darwin':
                exec('open ' . $url);
                break;
        }
    }
}
