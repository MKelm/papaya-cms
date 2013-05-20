<?php

require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MODULES'
);
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application/Profile/Plugins.php');

class PapayaApplicationProfilePluginsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilePlugins::getIdentifier
  */
  public function testGetIdentifier() {
    $profile = new PapayaApplicationProfilePlugins();
    $this->assertEquals(
      'Plugins',
      $profile->getIdentifier()
    );
  }

  /**
  * @covers PapayaApplicationProfilePlugins::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfilePlugins();
    $plugins = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaPluginLoader',
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
?>
