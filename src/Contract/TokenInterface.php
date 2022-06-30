<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace Wayhood\HyperfAction\Contract;

interface TokenInterface
{
    /**
     * 验证token.
     */
    public function verify(string $token);

    /**
     * @return mixed
     */
    public function has(string $token);

    /**
     * 生成token.
     */
    public function generator(array $value);

    /**
     * 更新token.
     */
    public function set(string $token);

    /**
     * 获得token内容.
     * @return string
     */
    public function get(string $token);
}
