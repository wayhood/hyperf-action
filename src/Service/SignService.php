<?php


namespace Wayhood\HyperfAction\Service;

use Wayhood\HyperfAction\Contract\SignInterface;

class SignService implements SignInterface
{
    public function verify(string $secret, array $request, string $sign) {
        return true;
    }
}