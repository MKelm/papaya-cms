<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Hidden.php');

class PapayaUiDialogFieldHiddenTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldHidden::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldHidden('name', 'default');
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
    $this->assertAttributeEquals(
      'default', '_defaultValue', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldHidden::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $input = new PapayaUiDialogFieldHidden('name', 'value', $filter);
    $this->assertAttributeSame(
      $filter, '_filter', $input
    );
  }

  /**
  * @covers PapayaUiDialogFieldHidden::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldHidden('name', 'default');
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->getMock('PapayaUiDialogFields'));
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogFieldHidden">'.
        '<input type="hidden" name="name">default</input>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldHidden::appendTo
  */
  public function testAppendToWithId() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');

    $input = new PapayaUiDialogFieldHidden('name', 'default');
    $input->setId('id');
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->getMock('PapayaUiDialogFields'));
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogFieldHidden" id="id">'.
        '<input type="hidden" name="name">default</input>'.
        '</field>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }
}
