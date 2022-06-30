<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Event;

class BeforeAction
{
    public $action;

    public $params = [];

    public $headers = [];

    public $extras = [];

    public function __construct(string $action, array $params = [], array $headers = [],array $extras = [])
    {
        $this->action = $action;
        $this->params = $params;
        $this->headers = $headers;
        $this->extras = $extras;
    }
}
