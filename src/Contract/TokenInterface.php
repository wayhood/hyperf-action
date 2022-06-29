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
namespace Wayhood\HyperfAction\Contract;

interface TokenInterface
{
    /**
     * 验证token.
     * @return mixed
     */
    public function verify(string $token);

    /**
     * @return mixed
     */
    public function has(string $token);

    /**
     * 生成token.
     * @param mixed $value
     * @return string
     */
    public function generator(array $value);

    /**
     * 更新token.
     * @param mixed $value
     * @return string
     */
    public function set(string $token);

    /**
     * 获得token内容.
     * @param mixed $value
     * @return string
     */
    public function get(string $token);
}
