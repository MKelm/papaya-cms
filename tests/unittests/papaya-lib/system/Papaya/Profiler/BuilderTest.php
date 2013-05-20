<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Profiler/Builder.php');

class PapayaProfilerBuilderTest extends PapayaTestCase {

  /**
  * @covers PapayaProfilerBuilder::createCollector
  */
  public function testCreateCollector() {
    $builder = new PapayaProfilerBuilder();
    $this->assertInstanceOf('PapayaProfilerCollectorXhprof', $builder->createCollector());
  }

  /**
  * @covers PapayaProfilerBuilder::createStorage
  */
  public function testCreateStorageExpectFile() {
    $builder = new PapayaProfilerBuilder();
    $builder->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_PROFILER_STORAGE_DIRECTORY' => $this->createTemporaryDirectory())
          )
        )
      )
    );
    $storage = $builder->createStorage();
    $this->removeTemporaryDirectory();
    $this->assertInstanceOf('PapayaProfilerStorageFile', $storage);
  }

  /**
  * @covers PapayaProfilerBuilder::createStorage
  */
  public function testCreateStorageExpectXhgui() {
    $builder = new PapayaProfilerBuilder();
    $builder->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_PROFILER_STORAGE' => 'xhgui'
            )
          )
        )
      )
    );
    $storage = $builder->createStorage();
    $this->assertInstanceOf('PapayaProfilerStorageXhgui', $storage);
  }
}