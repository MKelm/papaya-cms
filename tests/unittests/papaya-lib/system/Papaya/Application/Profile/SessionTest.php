<?php
require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaApplicationProfileSessionTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileSession::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileSession();
    $this->assertEquals(
      'Session',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileSession::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileSession();
    $session = $profile->createObject($application = NULL);
    $this->assertInstanceOf(
      'PapayaSession', $session
    );
  }
}
?>
