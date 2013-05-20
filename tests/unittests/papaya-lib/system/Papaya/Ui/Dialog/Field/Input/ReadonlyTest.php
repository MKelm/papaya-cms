<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Input/Readonly.php');

class PapayaUiDialogFieldInputReadonlyTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputReadonly::__construct
   */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldInputReadonly('Caption', 'name');

    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputReadonly::__construct
   */
  public function testConstructorWithAllParameters() {
    $input = new PapayaUiDialogFieldInputReadonly('Caption', 'name', 'default');

    $this->assertAttributeEquals(
      'default', '_defaultValue', $input
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputReadonly::appendTo
   */
  public function testStandardAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);

    $input = new PapayaUiDialogFieldInputReadonly('Caption', 'name');
    $input->appendTo($node);

    $this->assertXmlStringEqualsXmlString(
      $dom->saveXml($node),
      '<sample>
        <field caption="Caption" class="DialogFieldInputReadonly" error="no">
          <input type="text" name="name" readonly="yes"></input>
        </field>
      </sample>'
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputReadonly::appendTo
   */
  public function testWithDefaultAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);

    $input = new PapayaUiDialogFieldInputReadonly('Caption', 'name', 'default');
    $input->appendTo($node);

    $this->assertXmlStringEqualsXmlString(
      $dom->saveXml($node),
      '<sample>
        <field caption="Caption" class="DialogFieldInputReadonly" error="no">
          <input type="text" name="name" readonly="yes">default</input>
        </field>
      </sample>'
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputReadonly::getCurrentValue
   */
  public function testGetCurrentValue() {
    $input = new PapayaUiDialogFieldInputReadonly('Caption', 'name', 'default');

    $this->assertEquals(
      'default',
      $input->getCurrentValue()
    );
  }
}
