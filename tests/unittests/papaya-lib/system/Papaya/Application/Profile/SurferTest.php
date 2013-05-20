<?php

require_once(substr(__FILE__, 0, -60).'/Framework/PapayaTestCase.php');
PapayaTestCase::defineConstantDefaults(
  array(
    'PAPAYA_DB_TBL_SURFER',
    'PAPAYA_DB_TBL_SURFERGROUPS',
    'PAPAYA_DB_TBL_SURFERPERM',
    'PAPAYA_DB_TBL_SURFERACTIVITY',
    'PAPAYA_DB_TBL_SURFERPERMLINK',
    'PAPAYA_DB_TBL_SURFERCHANGEREQUESTS',
    'PAPAYA_DB_TBL_TOPICS'
  )
);
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Surfer.php');

class PapayaApplicationProfileSurferTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfileSurfer::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfileSurfer();
    $this->assertEquals(
      'Surfer',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfileSurfer::createObject
  */
  public function testCreateObject() {
    $profile = new PapayaApplicationProfileSurfer();
    $surferOne = base_surfer::getInstance(FALSE);
    $surferTwo = $profile->createObject($application = NULL);
    $this->assertSame(
      $surferOne,
      $surferTwo
    );
  }
}
?>
