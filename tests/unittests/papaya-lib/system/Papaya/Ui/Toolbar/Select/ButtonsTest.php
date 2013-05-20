<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Toolbar/Select/Buttons.php');

class PapayaUiToolbarSelectButtonsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSelectButtons::appendTo
  */
  public function testAppendToWithCurrentValue() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelectButtons('foo', array(10 => '10', 20 => '20', 50 => '50'));
    $select->papaya($this->getMockApplicationObject());
    $select->currentValue = 20;
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<button href="http://www.test.tld/test.html?foo=10" title="10"/>'.
        '<button href="http://www.test.tld/test.html?foo=20" title="20" down="down"/>'.
        '<button href="http://www.test.tld/test.html?foo=50" title="50"/>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarSelectButtons::appendTo
  */
  public function testAppendToWithAdditionalParameters() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelectButtons(
      'foo/size', array(10 => '10', 20 => '20', 50 => '50')
    );
    $select->papaya($this->getMockApplicationObject());
    $select->reference->setParameters(array('page' => 3), 'foo');
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10" title="10"/>'.
        '<button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=20" title="20"/>'.
        '<button href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=50" title="50"/>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }
}