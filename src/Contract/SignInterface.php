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

interface SignInterface
{
    /**
     * @param string $secret 秘钥
     * @param array $request request
     * @return mixed
     */
    public function verify(string $secret, array $request, string $sign);
}
