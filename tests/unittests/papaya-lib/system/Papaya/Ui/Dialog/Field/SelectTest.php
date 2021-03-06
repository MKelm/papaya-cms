<?php
require_once(substr(__FILE__, 0, -55).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select.php');

class PapayaUiDialogFieldSelectTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelect::__construct
  * @covers PapayaUiDialogFieldSelect::setValues
  * @covers PapayaUiDialogFieldSelect::setValueMode
  * @covers PapayaUiDialogFieldSelect::_createFilter
  */
  public function testConstructor() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $this->assertEquals('Caption', $select->getCaption());
    $this->assertEquals('name', $select->getName());
    $this->assertAttributeEquals(
      array(21 => 'half', 42 => 'full'), '_values', $select
    );
    $this->assertEquals(
      new PapayaFilterListKeys(array(21 => 'half', 42 => 'full')), $select->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::__construct
  * @covers PapayaUiDialogFieldSelect::setValues
  * @covers PapayaUiDialogFieldSelect::setValueMode
  * @covers PapayaUiDialogFieldSelect::_createFilter
  */
  public function testConstructorWithTraversable() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', $iterator = new ArrayIterator(array(21 => 'half', 42 => 'full'))
    );
    $this->assertAttributeSame(
      $iterator, '_values', $select
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::setValues
  * @covers PapayaUiDialogFieldSelect::getValues
  */
  public function testGetValuesAfterSetValues() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array()
    );
    $select->setValues(array(21 => 'half', 42 => 'full'));
    $this->assertEquals(array(21 => 'half', 42 => 'full'), $select->getValues());
  }

  /**
  * @covers PapayaUiDialogFieldSelect::setValueMode
  * @covers PapayaUiDialogFieldSelect::getValueMode
  */
  public function testGetValueModeAfterSetValueMode() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array()
    );
    $select->setValueMode(PapayaUiDialogFieldSelect::VALUE_USE_CAPTION);
    $this->assertEquals(PapayaUiDialogFieldSelect::VALUE_USE_CAPTION, $select->getValueMode());
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendTo() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option value="21">half</option>'.
          '<option value="42">full</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendToWithEmptyValue() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array('' => 'empty', 'some' => 'filled')
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option selected="selected">empty</option>'.
          '<option value="some">filled</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  * @covers PapayaUiDialogFieldSelect::_createFilter
  */
  public function testAppendToUsingCaptionsAsOptionValues() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption',
      'name',
      array(21 => 'half', 42 => 'full'),
      TRUE,
      PapayaUiDialogFieldSelect::VALUE_USE_CAPTION
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option value="half">half</option>'.
          '<option value="full">full</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendToWithIterator() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', new ArrayIterator(array(21 => 'half', 42 => 'full'))
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option value="21">half</option>'.
          '<option value="42">full</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptionGroup
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  * @covers PapayaUiDialogFieldSelect::_createFilter
  */
  public function testAppendToWithRecursiveIterator() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption',
      'name',
      new PapayaIteratorTreeGroupsRegex(
        array('foo', 'bar', 'foobar'), '(^foo)'
      ),
      TRUE,
      PapayaUiDialogFieldSelect::VALUE_USE_CAPTION
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<group caption="foo">'.
            '<option value="foo">foo</option>'.
            '<option value="foobar">foobar</option>'.
          '</group>'.
          '<option value="bar">bar</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  * @covers PapayaUiDialogFieldSelect::_isOptionSelected
  */
  public function testAppendToWithDefaultValue() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->any())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('truth' => 42))));
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $select->papaya($application);
    $select->collection($this->getCollectionMock($dialog));
    $select->appendTo($node);
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="no" mandatory="yes">'.
        '<select name="truth" type="dropdown">'.
          '<option value="21">half</option>'.
          '<option value="42" selected="selected">full</option>'.
        '</select>'.
      '</field>',
      $dom->saveXml($node->firstChild)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendToWithOptionCaptionCallback() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array(21 => array('title' => 'half'), 42 =>  array('title' => 'full'))
    );
    $select->callbacks()->getOptionCaption = array($this, 'callbackGetOptionCaption');
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option value="21">mapped: half</option>'.
          '<option value="42">mapped: full</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionCaption($context, $data, $index) {
    return 'mapped: '.$data['title'];
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendToWithOptionDataAttributesCallback() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'name', array(21 => 'half', 42 => 'full')
    );
    $select->callbacks()->getOptionData = array($this, 'callbackGetOptionDataAttributes');
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<option value="21" data-title="half" data-index="21" data-json="[21,42]">half</option>'.
          '<option value="42" data-title="full" data-index="42" data-json="[21,42]">full</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionDataAttributes($context, $data, $index) {
    return array('title' => $data, 'index' => $index, 'json' => array(21, 42));
  }

  /**
  * @covers PapayaUiDialogFieldSelect::appendTo
  * @covers PapayaUiDialogFieldSelect::_appendSelect
  * @covers PapayaUiDialogFieldSelect::_appendOption
  * @covers PapayaUiDialogFieldSelect::_appendOptionGroup
  * @covers PapayaUiDialogFieldSelect::_appendOptions
  */
  public function testAppendToWithOptionGroupCallback() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption',
      'name',
      new PapayaIteratorTreeGroupsRegex(
        array('foo', 'bar', 'foobar'), '(^foo)'
      ),
      TRUE,
      PapayaUiDialogFieldSelect::VALUE_USE_CAPTION
    );
    $select->callbacks()->getOptionGroupCaption = array($this, 'callbackGetOptionGroupCaption');
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelect" error="yes" mandatory="yes">'.
        '<select name="name" type="dropdown">'.
          '<group caption="Group: foo">'.
            '<option value="foo">foo</option>'.
            '<option value="foobar">foobar</option>'.
          '</group>'.
          '<option value="bar">bar</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }

  public function callbackGetOptionGroupCaption($context, $data, $index) {
    return 'Group: '.$data;
  }

  /**
  * @covers PapayaUiDialogFieldSelect::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this
      ->getMockBuilder('PapayaUiDialogFieldSelectCallbacks')
      ->disableOriginalConstructor()
      ->getMock();
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $this->assertSame(
      $callbacks, $select->callbacks($callbacks)
    );
  }

  /**
  * @covers PapayaUiDialogFieldSelect::callbacks
  */
  public function testCallbacksGetImpliciteCreate() {
    $select = new PapayaUiDialogFieldSelect(
      'Caption', 'truth', array(21 => 'half', 42 => 'full')
    );
    $callbacks = $select->callbacks();
    $this->assertInstanceOf(
      'PapayaObjectCallbacks', $callbacks
    );
  }

  /*************************
  * Mocks
  *************************/

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
