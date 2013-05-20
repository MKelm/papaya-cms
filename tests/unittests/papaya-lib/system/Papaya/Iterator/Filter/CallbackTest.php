<?php
require_once(substr(__FILE__, 0, -57).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Iterator/Filter/Callback.php');

class PapayaIteratorFilterCallbackTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorFilterCallback::__construct
  * @covers PapayaIteratorFilterCallback::setCallback
  * @covers PapayaIteratorFilterCallback::getCallback
  */
  public function testConstructor() {
    $filter = new PapayaIteratorFilterCallback(
      new EmptyIterator(), array($this,  'callbackAssertInteger')
    );
    $this->assertEquals(
      array($this,  'callbackAssertInteger'), $filter->getCallback()
    );
  }

  /**
  * @covers PapayaIteratorFilterCallback::setCallback
  */
  public function testSetCallbackWithInvalidCallbackExpectingException() {
    $this->setExpectedException('UnexpectedValueException');
    $filter = new PapayaIteratorFilterCallback(new EmptyIterator(), NULL);
  }

  /**
  * @covers PapayaIteratorFilterCallback::accept
  */
  public function testAccept() {
    $data = array(
      'ok' => 42,
      'fail' => 'wrong'
    );
    $filter = new PapayaIteratorFilterCallback(
      new ArrayIterator($data), array($this,  'callbackAssertInteger')
    );
    $this->assertEquals(
      array('ok' => 42),
      iterator_to_array($filter, TRUE)
    );
  }

  public function callbackAssertInteger($element, $key) {
    return is_int($element);
  }
}