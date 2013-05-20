<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/String/Translated.php');

class PapayaUiStringTranslatedListTest extends PapayaTestCase {

  /**
   * @covers PapayaUiStringTranslatedList::__construct
   */
  public function testConstructorWithArray() {
    $list = new PapayaUiStringTranslatedList(array('foo'));
    $this->assertInstanceOf('PapayaIteratorTraversable', $list->getInnerIterator());
  }

  /**
   * @covers PapayaUiStringTranslatedList
   */
  public function testIterationCallsTranslation() {
    $phrases = $this->getMock('base_phrases', array('getText'));
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $list = new PapayaUiStringTranslatedList(array('foo'));
    $list->papaya(
      $this->getMockApplicationObject(array('Phrases' => $phrases))
    );
    $this->assertEquals(
      array('bar'),
      iterator_to_array($list)
    );
  }

  /**
  * @covers PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetUsingSingleton() {
    $list = new PapayaUiStringTranslatedList(array());
    $this->assertInstanceOf(
      'PapayaApplication', $list->papaya()
    );
  }

  /**
  * @covers PapayaUiStringTranslatedList::papaya
  */
  public function testPapayaGetAfterSet() {
    $list = new PapayaUiStringTranslatedList(array());
    $application = $this->getMock('PapayaApplication');
    $this->assertSame($application, $list->papaya($application));
  }
}

