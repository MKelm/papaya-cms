<?php

require_once(substr(__FILE__, 0, -58).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profiles/Cms.php');

class PapayaApplicationProfilesCmsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilesCms::getProfiles
  */
  public function testGetProfiles() {
    $application = $this->getMock('PapayaApplication');
    $profiles = new PapayaApplicationProfilesCms();
    $list = $profiles->getProfiles($application);
    $this->assertEquals(
      array(
        new PapayaApplicationProfileLanguages(),
        new PapayaApplicationProfileMessages(),
        new PapayaApplicationProfileOptions(),
        new PapayaApplicationProfilePageReferences(),
        new PapayaApplicationProfilePlugins(),
        new PapayaApplicationProfileRequest(),
        new PapayaApplicationProfileDatabase(),
        new PapayaApplicationProfileSession(),
        new PapayaApplicationProfileSurfer(),
        new PapayaApplicationProfileProfiler(),
        new PapayaApplicationProfileAdministrationUser(),
        new PapayaApplicationProfileAdministrationLanguage()
      ),
      $list
    );
  }
}
?>