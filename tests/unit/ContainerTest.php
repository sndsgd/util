<?php

namespace sndsgd;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAndResetSingleton()
    {
        $container = new \ContainerMock();

        # first call creates the instance, second call retrieves the instance
        $container->getConfig();
        $container->getConfig();

        # the unset callback will echo the key and the instance classname
        $this->expectOutputString("config sndsgd\\Config");

        # first call nukes the instance, second call to test unset scenario
        $container->resetConfig();
        $container->resetConfig();
    }
}
