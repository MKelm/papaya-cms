<?php
require_once(substr(__FILE__, 0, -67).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaCacheIdentifierDefinitionSurferTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetStatus() {
    $surfer = new stdClass();
    $surfer->isValid = TRUE;
    $surfer->id = '012345678901234567890123456789ab';
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $definition->papaya(
      $this->getMockApplicationObject(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertEquals(
      array('PapayaCacheIdentifierDefinitionSurfer' => '012345678901234567890123456789ab'),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetStatusForPreviewExpectingFalse() {
    $surfer = new stdClass();
    $surfer->isValid = FALSE;
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $definition->papaya(
      $this->getMockApplicationObject(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }
}