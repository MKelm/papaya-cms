<?php
require_once(substr(dirname(__FILE__), 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/BackgroundImageHeadline/Box/Base.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/BackgroundImageHeadline/Box.php');

class BackgroundImageHeadlineBoxBaseTest extends PapayaTestCase {
  /**
  * Get the BackgroundImageHeadlineBoxBaseTest object to be tested
  */
  private function getBaseObjectFixture() {
    return new BackgroundImageHeadlineBoxBase();
  }

  /**
  * Get a mock owner object
  *
  * @return BackgroundImageHeadlineBox mock object
  */
  private function getOwnerObjectFixture() {
    return $this->getMock(
      'BackgroundImageHeadlineBox',
      array(),
      array(),
      'Mock_'.md5(__CLASS__.  microtime()),
      FALSE
    );
  }

  /**
  * @covers BackgroundImageHeadlineBoxBase::setOwner
  */
  public function testSetOwner() {
    $baseObject = $this->getBaseObjectFixture();
    $ownerObject = $this->getOwnerObjectFixture();
    $baseObject->setOwner($ownerObject);
    $this->assertAttributeSame($ownerObject, '_owner', $baseObject);
  }

  /**
  * @covers BackgroundImageHeadlineBoxBase::setBoxData
  */
  public function testSetBoxData() {
    $baseObject = $this->getBaseObjectFixture();
    $data = array('headline' => 'ACME Corporation Online');
    $baseObject->setBoxData($data);
    $this->assertAttributeEquals($data, '_data', $baseObject);
  }

  /**
  * @covers BackgroundImageHeadlineBoxBase::getBoxXml
  */
  public function testGetBoxXml() {
    $baseObject = $this->getBaseObjectFixture();
    $ownerObject = $this->getOwnerObjectFixture();
    $ownerObject
      ->expects($this->once())
      ->method('getWebMediaLink')
      ->will($this->returnValue('image.png'));
    $baseObject->setOwner($ownerObject);
    $data = array(
      'headline' => 'ACME Corporation online',
      'image' => 'b5e2491a1632156d6b798f033b04b10a',
      'alt' => 'ACME Corporation'
    );
    $baseObject->setBoxData($data);
    $expectedXml =  '<logo>
<headline>ACME Corporation online</headline>
<image src="image.png" alt="ACME Corporation" />
</logo>
';
    $this->assertEquals($expectedXml, $baseObject->getBoxXml());
  }
}
?>
