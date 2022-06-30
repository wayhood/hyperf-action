<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
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
        return 1;
        // TODO: Implement verify() method.
    }

    public function has(string $token)
    {
        // TODO: Implement has() method.
    }

    public function generator(array $value)
    {
        return '';
    }

    public function set(string $token)
    {
        return '';
    }

    public function get(string $token)
    {
        return '';
    }
}
