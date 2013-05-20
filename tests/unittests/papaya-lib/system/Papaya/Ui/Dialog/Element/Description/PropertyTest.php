<?php
require_once(substr(__FILE__, 0, -71).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Element/Description/Property.php');

class PapayaUiDialogElementDescriptionPropertyTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogElementDescriptionProperty::__construct
  * @covers PapayaUiDialogElementDescriptionProperty::setName
  */
  public function testConstructor() {
    $property = new PapayaUiDialogElementDescriptionProperty('foo', 'bar');
    $this->assertEquals(
      'foo', $property->name
    );
    $this->assertEquals(
      'bar', $property->value
    );
  }

  /**
  * @covers PapayaUiDialogElementDescriptionProperty::appendTo
  */
  public function testAppendTo() {
    $property = new PapayaUiDialogElementDescriptionProperty('foo', 'bar');
    $this->assertEquals(
      '<property name="foo" value="bar"/>', $property->getXml()
    );
  }
}