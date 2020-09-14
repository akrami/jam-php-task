<?php


use App\Service\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        $this->config = new Config();
        parent::setUp();
    }

    public function testGet(){
        $this->assertEquals('Invite', $this->config->get('APP_NAME'));
    }

    public function testEnv(){
        $this->assertEquals('development', $this->config->mode());
    }
}
