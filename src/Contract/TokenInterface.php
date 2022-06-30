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
    public function verify(string $token): int;

    /**
     * @return mixed
     */
    public function has(string $token);

    /**
     * 生成token.
     */
    public function generator(array $value): string;

    /**
     * 更新token.
     */
    public function set(string $token): string;

    /**
     * 获得token内容.
     * @param string $token
     * @return string
     */
    public function get(string $token): string;
}
