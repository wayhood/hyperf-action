<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Contract;

interface TokenInterface
{

    /**
     * 验证token
     * @param string $token
     * @return mixed
     */
    public function verify(string $token);

    /**
     * @param string $token
     * @return mixed
     */
    public function has(string $token);

    /**
     * 生成token
     * @param mixed $value
     * @return string
     */
    public function generator(array $value);

    /**
     * 更新token
     * @param mixed $value
     * @return string
     */
    public function set(string $token);

    /**
     * 获得token内容
     * @param mixed $value
     * @return string
     */
    public function get(string $token);
}