<?php

declare(strict_types=1);
namespace Wayhood\HyperfAction\Contract;

interface SignInterface
{
    public function verify(string $secret, array $request, string $sign);

}