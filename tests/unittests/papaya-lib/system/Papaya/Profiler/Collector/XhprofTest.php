<?php
require_once(substr(__FILE__, 0, -59).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Profiler/Collector/Xhprof.php');

class PapayaProfilerCollectorXhprofTest extends PapayaTestCase {

  /**
  * @covers PapayaProfilerCollectorXhprof::enable
  */
  public function testEnable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new PapayaProfilerCollectorXhprof();
    $this->assertTrue($collector->enable());
  }

  /**
  * @covers PapayaProfilerCollectorXhprof::disable
  */
  public function testDisable() {
    $this->skipIfNotExtensionLoaded('xhprof');
    $collector = new PapayaProfilerCollectorXhprof();
    $collector->enable();
    $this->assertInternalType(
      'array',
      $collector->disable()
    );
  }

  /**
  * @covers PapayaProfilerCollectorXhprof::disable
  */
  public function testDisableNoEnabled() {
    $collector = new PapayaProfilerCollectorXhprof();
    $this->assertNull(
      $collector->disable()
    );
  }

  private function skipIfNotExtensionLoaded($extension) {
    if (!extension_loaded($extension)) {
      $this->markTestSkipped('Extension "'.$extension.'" not loaded.');
    }
  }
}