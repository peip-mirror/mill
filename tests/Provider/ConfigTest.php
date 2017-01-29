<?php
namespace Mill\Tests\Provider;

use League\Flysystem\Memory\MemoryAdapter;
use Mill\Provider\Config;
use Mill\Provider\Filesystem;
use Pimple\Container;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $container = new Container;

        $filesystem = new Filesystem();
        $filesystem->register($container);

        // Overwrite the stock library local filesystem with an in-memory file system we'll use for testing.
        $container->extend('filesystem', function ($filesystem, $c) {
            return new \League\Flysystem\Filesystem(new MemoryAdapter);
        });

        $container['filesystem']->write(
            'mill.xml',
            file_get_contents(__DIR__ . '/../../tests/_fixtures/mill.test.xml')
        );

        $container['config.path'] = 'mill.xml';
        $container['config.load_bootstrap'] = false;

        $config = new Config();
        $config->register($container);

        $this->assertInstanceOf('Mill\Config', $container['config']);
    }
}
