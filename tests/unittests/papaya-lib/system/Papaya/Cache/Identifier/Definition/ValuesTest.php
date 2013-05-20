<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaCacheIdentifierDefinitionValuesTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionValues
   */
  public function testGetStatus() {
    $definition = new PapayaCacheIdentifierDefinitionValues('21', '42');
    $this->assertEquals(
      array('PapayaCacheIdentifierDefinitionValues' => array('21', '42')),
      $definition->getStatus()
    );
  }


  /**
   * @covers PapayaCacheIdentifierDefinitionValues
   */
  public function testGetStatusWithoutValuesExpectingTrue() {
    $definition = new PapayaCacheIdentifierDefinitionValues();
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionValues
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionValues();
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}