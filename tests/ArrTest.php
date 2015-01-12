<?php

use \sndsgd\Arr;


class ArrTest extends PHPUnit_Framework_TestCase
{
   public function testIsIndexed()
   {
      $test = ['one', 'two', 'three'];
      $this->assertTrue(Arr::isIndexed($test));

      $test = ['one', 'key' => 'val', 'two'];
      $this->assertFalse(Arr::isIndexed($test));
   }

   public function testCast()
   {
      $this->assertTrue(is_array(Arr::cast(null)));
      $this->assertTrue(is_array(Arr::cast(1)));
      $this->assertTrue(is_array(Arr::cast([1,2,3])));
   }

   public function testAddValue()
   {
      $arr = [];
      Arr::addValue($arr, 'odd', 1);
      $this->assertEquals($arr, ['odd' => 1]);
      Arr::addValue($arr, 'even', 2);
      $this->assertEquals($arr, ['odd' => 1, 'even' => 2]);
      Arr::addValue($arr, 'odd', 3);
      $this->assertEquals($arr, ['odd' => [1, 3], 'even' => 2]);

      Arr::addValue($arr, 'brackets[]', 'value');
      $this->assertEquals($arr, [
         'odd' => [1, 3], 
         'even' => 2,
         'brackets' => ['value']
      ]);
   }

   public function testRequireKeys()
   {
      $req = ['a', 'b', 'c'];

      $test = ['a' => 1, 'b' => 1, 'c' => 1];
      $this->assertTrue(Arr::requireKeys($test, $req));

      $test = ['a' => 1, 'b' => 1];
      $this->assertTrue(is_string(Arr::requireKeys($test, $req)));
   }

   public function testDefaults()
   {
      $arr = ['a' => 1, 'b' => 1, 'c' => 1];
      $defaults = ['d' => 2];
      $expect = ['a' => 1, 'b' => 1, 'c' => 1, 'd' => 2];
      $this->assertEquals($expect, Arr::defaults($arr, $defaults));


      $arr = null;
      $defaults = ['one'=>1, 'two'=>2];
      $this->assertEquals($defaults, Arr::defaults($arr, $defaults));
   }

   public function testFlatten()
   {
      $test = [1,2,[3,4],5,[6,7],8,9,0];
      $result = Arr::flatten($test);
      $this->assertEquals([1,2,3,4,5,6,7,8,9,0], $result);
   }

   public function testImplode()
   {
      $delim = ', ';
      $arr = ['a', 'b', 'c'];
      $prelast = 'and ';
      $expect = 'a, b, and c';
      $this->assertEquals($expect, Arr::implode($delim, $arr, $prelast));

      $arr = ['a'];
      $expect = 'a';
      $this->assertEquals($expect, Arr::implode($delim, $arr, $prelast));
   }

   public function testWithout()
   {
      $test = [
         'id' => 1,
         'page' => 3,
         'pageSize' => 100
      ];

      $expect = ['id' => 1];
      $result = Arr::without($test, 'page', 'pageSize');
      $this->assertEquals($expect, $result);

      $expect = ['id' => 1, 'page' => 3];
      $result = Arr::without($test, 'pageSize');
      $this->assertEquals($expect, $result);
   }

   public function testOnly()
   {
      $test = [
         'id' => 1,
         'page' => 3,
         'pageSize' => 100
      ];
      $expect = ['id' => 1];
      $result = Arr::only($test, 'id');
      $this->assertEquals($expect, $result);

      $expect = ['id' => 1, 'page' => 3];
      $result = Arr::only($test, 'id', 'page');
      $this->assertEquals($expect, $result);
   }

   public function testFilter()
   {
      $arr = ['odd' => 1, 'even' => 2, 'three' => 3, 'four' => 4];
      $filter = function($value, $key) {
         return (strlen($key) % 2 === 0);
      };

      $expect = ['even' => 2, 'four' => 4];
      $this->assertEquals($expect, Arr::filter($arr, $filter));
   }

   public function testPopValues()
   {
      $test = [1,2,null,false,null];

      # pop falsy values without strict matching
      $expect = [1,2];
      $this->assertEquals($expect, Arr::popValues($test));

      # pop null-y values without strict matching
      $expect = [1,2];
      $this->assertEquals($expect, Arr::popValues($test, null));

      # pop null values with strict matching
      $expect = [1,2,null,false];
      $this->assertEquals($expect, Arr::popValues($test, null, true));

      # pop true values without strict matching
      $expect = $test;
      $this->assertEquals($expect, Arr::popValues($test, true));

      # all values match
      $test = [null, null, null];
      $expect = [];
      $this->assertEquals($expect, Arr::popValues($test, null));
   }

   public function testShiftValues()
   {
      $test = [null, false, null, 1, 2];

      $expect = [1,2];
      $this->assertEquals($expect, Arr::shiftValues($test, null));

      $expect = [false,null,1,2];
      $this->assertEquals($expect, Arr::shiftValues($test, null, true));

      $test = [null, false, null];
      $expect = [];
      $this->assertEquals($expect, Arr::shiftValues($test, null));
   }

   public function testTestValueByKey()
   {
      $test = [
         'string' => 'str',
         'integer' => 100,
         'float' => 1.1,
         'array' => [1,2,3],
         'object' => (new StdClass()),
         'boolean' => true,
         'null' => null
      ];

      $this->assertTrue(Arr::testValueByKey($test, 'string', 'is_string'));
      $this->assertTrue(Arr::testValueByKey($test, 'integer', 'is_int'));
      $this->assertTrue(Arr::testValueByKey($test, 'float', 'is_float'));
      $this->assertTrue(Arr::testValueByKey($test, 'array', 'is_array'));
      $this->assertTrue(Arr::testValueByKey($test, 'object', 'is_object'));
      $this->assertTrue(Arr::testValueByKey($test, 'boolean', 'is_bool'));
      $this->assertTrue(Arr::testValueByKey($test, 'null', 'is_null'));

      $this->assertFalse(Arr::testValueByKey($test, 'not-here', 'is_null'));
      $this->assertFalse(Arr::testValueByKey($test, 'string', 'is_null'));

      $this->assertTrue(Arr::testValueByKey($test, 'integer', function($v) {
         return ($v > 99);
      }));

      $test = array_values($test);
      $this->assertTrue(Arr::testValueByKey($test, 0, 'is_string'));
      $this->assertTrue(Arr::testValueByKey($test, 1, 'is_int'));
      $this->assertTrue(Arr::testValueByKey($test, 2, 'is_float'));
      $this->assertTrue(Arr::testValueByKey($test, 3, 'is_array'));
      $this->assertTrue(Arr::testValueByKey($test, 4, 'is_object'));
      $this->assertTrue(Arr::testValueByKey($test, 5, 'is_bool'));
      $this->assertTrue(Arr::testValueByKey($test, 6, 'is_null'));
   }

   public function testExport()
   {
      $arr = [
         'no-index',
         'one' => 1,
         'two' => [
            'first',
            'second'
         ]
      ];

      // var_dump(var_export($arr, true));
      // array (
      //   0 => 'no-index',
      //   'one' => 1,
      //   'two' => 
      //   array (
      //     0 => 'first',
      //     1 => 'second',
      //   ),
      // )

      $expect = implode("\n", [
         "array(",
         "  0 => 'no-index',",
         "  'one' => 1,",
         "  'two' => array(",
         "    0 => 'first',",
         "    1 => 'second',",
         "  ),",
         ")"
      ]);

      $this->assertEquals($expect, Arr::export($arr));
   }
}

