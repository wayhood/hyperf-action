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
namespace Wayhood\HyperfAction\Service;

use Wayhood\HyperfAction\Contract\TokenInterface;

/**
 * Class TokenService.
 */
class TokenService implements TokenInterface
{
    public function verify(string $token)
    {
        return true;
        // TODO: Implement verify() method.
    }

    public function has(string $token)
    {
        // TODO: Implement has() method.
    }

    public function generator(array $value)
    {
        // TODO: Implement generator() method.
    }

    public function set(string $token)
    {
        // TODO: Implement set() method.
    }

    public function get(string $token)
    {
        // TODO: Implement get() method.
    }
}
