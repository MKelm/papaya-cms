<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select/Grouped.php');

class PapayaUiDialogFieldSelectGroupedTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectGrouped::setValues
  */
  public function testSetValues() {
    $select = new PapayaUiDialogFieldSelectGrouped(
      'Caption', 'name', array('Group Caption' => array(21 => 'half', 42 => 'full'))
    );
    $this->assertAttributeEquals(
      array('Group Caption' => array(21 => 'half', 42 => 'full')), '_values', $select
    );
    $this->assertAttributeEquals(
      new PapayaFilterList(array(21, 42)), '_filter', $select
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectGrouped::setValues
  */
  public function testSetValuesComplex() {
    $select = new PapayaUiDialogFieldSelectGrouped(
      'Caption',
      'name',
      array(
        array(
          'caption' => 'Group Caption',
          'options' => array(21 => 'half', 42 => 'full')
        )
      )
    );
    $this->assertAttributeEquals(
      new PapayaFilterList(array(21, 42)), '_filter', $select
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectGrouped::appendTo
  * @covers PapayaUiDialogFieldSelectGrouped::_appendOptionGroups
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $select = new PapayaUiDialogFieldSelectGrouped(
      'Caption', 'name', array('Group Caption' => array(21 => 'half', 42 => 'full'))
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $select->appendTo($node);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectGrouped" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<group caption="Group Caption">'.
            '<option value="21">half</option>'.
            '<option value="42">full</option>'.
          '</group>'.
        '</select>'.
      '</field>',
      $dom->saveXml($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelectGrouped::appendTo
  * @covers PapayaUiDialogFieldSelectGrouped::_appendOptionGroups
  */
  public function testAppendToWithComplexLabel() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $select = new PapayaUiDialogFieldSelectGrouped(
      'Caption',
      'name',
      array(
        array(
          'caption' => new PapayaUiString('Group Caption'),
          'options' => array(21 => 'half', 42 => 'full')
        )
      )
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $select->appendTo($node);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectGrouped" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<group caption="Group Caption">'.
            '<option value="21">half</option>'.
            '<option value="42">full</option>'.
          '</group>'.
        '</select>'.
      '</field>',
      $dom->saveXml($node->firstChild)
    );
  }
}
