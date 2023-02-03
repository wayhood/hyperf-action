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
    public function verify(string $token): bool
    {
        return true;
    }

    public function has(string $token): bool
    {
        return true;
    }

    public function generator(array $value): string
    {
        return '';
    }

    public function set(string $token): void
    {
    }

    public function get(string $token): string
    {
        return '';
    }
}
