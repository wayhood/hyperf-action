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

use Wayhood\HyperfAction\Contract\SignInterface;

class SignService implements SignInterface
{
    public function verify(string $secret, array $request, string $sign)
    {
        return true;
    }
}
