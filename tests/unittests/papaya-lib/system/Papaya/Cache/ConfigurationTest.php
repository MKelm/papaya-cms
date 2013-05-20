<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Cache/Configuration.php');

class PapayaCacheConfigurationTest extends PapayaTestCase {

  public function testConstructor() {
    $configuration = new PapayaCacheConfiguration();
    $this->assertEquals(
      array(
        'SERVICE' => 'file',
        'FILESYSTEM_PATH' => '/tmp',
        'FILESYSTEM_NOTIFIER_SCRIPT' => '',
        'FILESYSTEM_DISABLE_CLEAR' => FALSE,
        'MEMCACHE_SERVERS' => ''
      ),
      iterator_to_array($configuration)
    );
  }

}