<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Xml/Element.php');

class PapayaXmlElementTest extends PapayaTestCase {

  /**
  * @covers PapayaXmlElement::append
  */
  public function testAppend() {
    $document = new PapayaXmlDocument();
    $element = $document->appendElement('sample');
    $element->append(new PapayaXmlAppendable_TestImplementation());
    $this->assertEquals(
      '<sample><success/></sample>',
      $document->saveXml($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendElement
  */
  public function testAppendElement() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $document->appendChild($element);
    $element->appendElement('test', array('attribute' => 42), 'content');
    $this->assertEquals(
      '<sample><test attribute="42">content</test></sample>',
      $document->saveXml($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendText
  */
  public function testAppendText() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $document->appendChild($element);
    $element->appendText('content');
    $this->assertEquals(
      '<sample>content</sample>',
      $document->saveXml($element)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithDocumentTarget() {
    $target = new DOMDocument('1.0', 'UTF-8');
    $document = new PapayaXmlDocument();
    $element = $document->createElement('sample');
    $element->appendTo($target);
    $this->assertEquals(
      '<sample/>',
      $target->saveXml($target->documentElement)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithElementTarget() {
    $document = new PapayaXmlDocument();
    $target = $document->createElement('sample');
    $document->appendChild($target);
    $element = $document->createElement('test');
    $element->appendTo($target);
    $this->assertEquals(
      '<sample><test/></sample>',
      $document->saveXml($target)
    );
  }

  /**
  * @covers PapayaXmlElement::appendTo
  */
  public function testAppendToWithNodeTargetExpectingException() {
    $document = new PapayaXmlDocument();
    $element = $document->createElement('test');
    $this->setExpectedException('InvalidArgumentException');
    $element->appendTo($document->createTextNode('_'));
  }

  /**
  * @covers PapayaXmlElement::appendXml
  */
  public function testAppendXml() {
    $document = new PapayaXmlDocument();
    $target = $document->createElement('sample');
    $document->appendChild($target);
    $target->appendXml('<element/>text<element attribute="value"/>');
    $this->assertEquals(
      '<sample><element/>text<element attribute="value"/></sample>',
      $document->saveXml($target)
    );
  }

  /**
  * @covers PapayaXmlElement::saveXml
  */
  public function testSaveXml() {
    $document = new PapayaXmlDocument();
    $document->appendChild($document->createElement('sample'));
    $target = $document->createElement('test');
    $document->documentElement->appendChild($target);
    $this->assertEquals(
      '<test/>',
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::saveFragment
  */
  public function testSaveFragment() {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->appendElement('element', array('attribute' => 42));
    $target->appendText('text');
    $this->assertEquals(
      '<element attribute="42"/>text',
      $target->saveFragment()
    );
  }

  /**
  * @covers PapayaXmlElement::setAttribute
  * @dataProvider provideAttributeValues
  */
  public function testSetAttribute($expected, $value) {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('attribute', $value);
    $this->assertEquals(
      $expected,
      $target->saveXml()
    );
  }

  /**
  * @covers PapayaXmlElement::setAttribute
  * @dataProvider provideAttributeIgnoredValues
  */
  public function testSetAttributewithEmptyValueExpectingNoAttribute($value) {
    $document = new PapayaXmlDocument();
    $target = $document->appendElement('test');
    $target->setAttribute('attribute', '');
    $this->assertEquals(
      '<test/>',
      $target->saveXml()
    );
  }

  public static function provideAttributeValues() {
    return array(
      array('<test attribute="42"/>', 42),
      array('<test attribute="value"/>', 'value'),
      array('<test attribute=" "/>', ' '),
      array('<test attribute="0"/>', 0),
      array('<test attribute=""/>', FALSE)
    );
  }

  public static function provideAttributeIgnoredValues() {
    return array(
      array(NULL),
      array('')
    );
  }
}

class PapayaXmlAppendable_TestImplementation implements PapayaXmlAppendable {
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement('success');
  }
}
