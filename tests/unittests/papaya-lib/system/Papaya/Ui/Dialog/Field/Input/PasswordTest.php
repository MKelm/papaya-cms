<?php
require_once(substr(__FILE__, 0, -63).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiDialogFieldInputPasswordTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldInputPassword::__construct
   */
  public function testConstructorCreatesDefaultFilter() {
    $field = new PapayaUiDialogFieldInputPassword('Caption', 'fieldname');
    $field->setMandatory(TRUE);
    $this->assertInstanceOf('PapayaFilterPassword', $field->getFilter());
  }

  /**
   * @covers PapayaUiDialogFieldInputPassword::__construct
   */
  public function testConstructorAttachingFilter() {
    $filter = $this->getMock('PapayaFilter');
    $field = new PapayaUiDialogFieldInputPassword('Caption', 'fieldname', 42, $filter);
    $field->setMandatory(TRUE);
    $this->assertSame($filter, $field->getFilter());
  }

  /**
   * @covers PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueIgnoresDefaultValue() {
    $field = new PapayaUiDialogFieldInputPassword('Caption', 'fieldname');
    $field->setDefaultValue('not ok');
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueIgnoreData() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(1))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array())));
    $dialog
      ->expects($this->never())
      ->method('data');
    $field = new PapayaUiDialogFieldInputPassword('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueReadParameter() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 'success'))));
    $field = new PapayaUiDialogFieldInputPassword('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEquals('success', $field->getCurrentValue());
  }

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogFields');
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
