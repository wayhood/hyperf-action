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

use Wayhood\HyperfAction\Contract\SignInterface;

class SignService implements SignInterface
{
    public function verify(string $secret, array $request, string $sign)
    {
        return true;
    }
}
