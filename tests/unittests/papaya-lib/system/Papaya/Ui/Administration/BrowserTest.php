<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Administration/Browser.php');

class PapayaUiAdministrationBrowserTest extends PapayaTestCase {

  /**
  * @covers PapayaUiAdministrationBrowser::__construct
  */
  public function testConstructor() {
    $owner = 'any value';
    $params = array(1, 2, 3);
    $paramName = 'abc';
    $fieldName = 'browser';
    $hiddenFields = array('theme' => 'theme1');
    $browserObject = new PapayaUiAdministrationBrowser(
      $owner, $params, $paramName, array(), $fieldName, $hiddenFields
    );
    $this->assertAttributeSame($owner, 'owner', $browserObject);
    $this->assertAttributeSame($params, 'params', $browserObject);
    $this->assertAttributeSame($paramName, 'paramName', $browserObject);
    $this->assertAttributeSame(array(), 'data', $browserObject);
    $this->assertAttributeSame($fieldName, 'fieldName', $browserObject);
    $this->assertAttributeSame($hiddenFields, 'hiddenFields', $browserObject);
  }

  /**
  * @covers PapayaUiAdministrationBrowser::getXml
  */
  public function testGetXml() {
    $browserObject = new PapayaUiAdministrationBrowser(
      new stdClass, array(), 'group'
    );
    $this->assertSame('', $browserObject->getXml());
  }

  /**
  * @covers PapayaUiAdministrationBrowser::getLink
  */
  public function testGetLink() {
    $browserObject = new PapayaUiAdministrationBrowser(
      new stdClass, array('foo' => 'bar'), 'group'
    );
    $browserObject->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      'test.html?group[foo]=bar&group[bar]=foo',
      $browserObject->getLink(array('bar' => 'foo'))
    );
  }
}