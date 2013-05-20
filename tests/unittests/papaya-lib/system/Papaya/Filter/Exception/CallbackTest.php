<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Filter/Exception/Callback.php');

class PapayaFilterExceptionCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaFilterExceptionCallback::__construct
  */
  public function testConstructor() {
    $e = new PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertAttributeEquals(
      'function', '_callback', $e
    );
  }

  /**
  * @covers PapayaFilterExceptionCallback::getCallback
  */
  public function testGetCallback() {
    $e = new PapayaFilterExceptionCallback_TestProxy('', 'function');
    $this->assertEquals(
      'function', $e->getCallback()
    );
  }

  /**
  * @covers PapayaFilterExceptionCallback::callbackToString
  * @dataProvider provideCallbacks
  */
  public function testCallbackToString($expected, $callback) {
    $e = new PapayaFilterExceptionCallback_TestProxy('', $callback);
    $this->assertEquals(
      $expected, $e->callbackToString($callback)
    );
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideCallbacks() {
    return array(
      array('strpos', 'strpos'),
      array('function() {...}', create_function('$value', '')),
      array(
        'PapayaFilterExceptionCallback_SampleCallback->sample',
        array(new PapayaFilterExceptionCallback_SampleCallback(), 'sample')
      ),
      array(
        'PapayaFilterExceptionCallback_SampleCallback::sample',
        array('PapayaFilterExceptionCallback_SampleCallback', 'sample')
      )
    );
  }
}

class  PapayaFilterExceptionCallback_SampleCallback {
  public function sample() {
  }
}

class PapayaFilterExceptionCallback_TestProxy extends PapayaFilterExceptionCallback {
  public function callbackToString($callback) {
    return parent::callbackToString($callback);
  }
}
