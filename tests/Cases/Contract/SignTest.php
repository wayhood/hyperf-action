<?php

declare(strict_types=1);
/**
 * This is an extension of hyperf
 * Name hyperf action
 *
 * @link     https://github.com/wayhood
 * @license  https://github.com/wayhood/hyperf-action
 */
namespace HyperfTest\Cases\Contract;

use HyperfTest\Cases\AbstractTestCase;
use Wayhood\HyperfAction\Contract\SignInterface;

/**
 * @internal
 * @coversNothing
 */
class SignTest extends AbstractTestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testVerify()
    {
        $sign_service = $this->genSignService();
        $secret = 'xxx';
        $request = ['sign_body' => [
            'aaa' => 'ccc',
        ]];
        $sign = 'xxxaaa';
        $sign_service->allows()
            ->verify($secret, $request, $sign)
            ->andReturnTrue();

        $this->assertTrue($sign_service->verify($secret, $request, $sign));
    }

    protected function genSignService()
    {
        return \Mockery::mock(SignInterface::class);
    }
}
