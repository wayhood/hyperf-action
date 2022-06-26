<?php


namespace Wayhood\HyperfAction\Collector;


use Hyperf\Di\MetadataCollector;

class RequestRuleCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * @var array
     */
    protected static $result = [];

    public static function collectClass(string $class, string $annotation, $value): void
    {
        static::$container[$class][] = $value;
    }

    public static function result() {
        if (count(static::$result) == 0) {
            static::parseRules();
        }
        return static::$result;
    }

    public static function parseRules() {
        foreach(static::list() as $class => $requesRules) {
            $result = [
                'rules'=>[],
                'messages'=>[]
            ];
            foreach($requesRules as $requestRule) {

                $result['rules'] =
                    array_merge(
                        $result['rules'],
                        static::parse_rule($requestRule->rule)
                    );

                $result['messages'] =
                    array_merge(
                        $result['messages'],
                        static::parse_message($requestRule->message)
                    );
            }
            static::$result[$class] = $result;
        }
    }

    protected static function parse_rule(string $rule)
    {
        $rules = [];
        $rule = explode(';',$rule);
        foreach ($rule as $value)
        {
            $val = explode('.',$value,2);
            $rules[$val[0]] = $val[1];
        }
        return $rules;
    }

    protected static function parse_message(string $message)
    {
        $message = explode(';',$message);
        $messages = [];
        foreach ($message as $value)
        {
            $val = explode(':',$value);
            $messages[$val[0]] = $val[1];
        }
        return $messages;
    }
}