<?php


class TestErrorTrait
{
    use \sndsgd\ErrorTrait;

    public function fail()
    {
        $this->error = "failed";
        $this->errorDetail = "detail";
        return false;
    }

    public function forceSetError()
    {
        @trigger_error("test", E_USER_NOTICE);
        $this->setError("forced");
    }
}


class ErrorTraitTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $test = new TestErrorTrait;
        $this->assertNull($test->getError());
        $this->assertNull($test->getErrorDetail());

        $test->fail();
        $this->assertEquals("failed", $test->getError());
        $this->assertEquals("detail", $test->getErrorDetail());

        $test->clearError();
        $this->assertNull($test->getError());
        $this->assertNull($test->getErrorDetail());

        $test->forceSetError();
        $this->assertEquals("forced", $test->getError());
        $expect = "'test' in ".__FILE__." on line 17";
        $this->assertEquals($expect, $test->getErrorDetail());
    }
}