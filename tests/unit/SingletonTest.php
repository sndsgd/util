<?php

namespace sndsgd;

class ExampleSingleton extends Singleton
{
}

class SingletonTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $ex = ExampleSingleton::getInstance();
        $rc = new \ReflectionClass($ex);

        $method = $rc->getMethod("__clone");
        $method->setAccessible(true);
        $method->invoke($ex);

        $method = $rc->getMethod("__wakeup");
        $method->setAccessible(true);
        $method->invoke($ex);
    }
}
