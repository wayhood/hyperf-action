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
    public function verify(string $token): bool;

    public function has(string $token): bool;

    /**
     * 生成token.
     * @param array $value
     * @return string
     */
    public function generator(array $value): string;

    /**
     * 更新token.
     * @param string $token
     * @return string
     */
    public function set(string $token): void;

    /**
     * 获得token内容.
     * @param string $token
     * @return string
     */
    public function get(string $token): string;
}
