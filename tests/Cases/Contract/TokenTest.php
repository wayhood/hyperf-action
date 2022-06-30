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

use Wayhood\HyperfAction\Contract\TokenInterface;

/**
 * @internal
 * @coversNothing
 */
class TokenTest extends \HyperfTest\Cases\AbstractTestCase
{
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testVerify()
    {
        $token_success = 'token_success';
        $token_fail = 'token_fail';
        $token_kick = 'token_kick';

        $token = $this->genToken();

        $token->allows()
            ->verify($token_success)
            ->andReturn(1);
        $this->assertEquals(1, $token->verify($token_success));

        $token->allows()
            ->verify($token_fail)
            ->andReturn(0);
        $this->assertEquals(0, $token->verify($token_fail));

        $token->allows()
            ->verify($token_kick)
            ->andReturn(-1);
        $this->assertEquals(-1, $token->verify($token_kick));
    }

    public function testGenerator()
    {
        $token_data = ['token'];
        $token = $this->genToken();

        $token->allows()
            ->generator($token_data)
            ->andReturn('token');

        $this->assertIsString($token->generator($token_data));
    }

    public function testSet()
    {
        $token_data = 'token_string';
        $token = $this->genToken();

        $token->allows()
            ->set($token_data)
            ->andReturn('token');

        $this->assertIsString($token->set($token_data));
    }

    public function testGet()
    {
        $token_data = 'token_string';
        $token = $this->genToken();

        $token->allows()
            ->get($token_data)
            ->andReturn('token');

        $this->assertIsString($token->get($token_data));
    }

    protected function genToken()
    {
        return \Mockery::mock(TokenInterface::class);
    }
}
