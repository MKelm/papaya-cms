<?php
require_once(substr(__FILE__, 0, -69).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaCacheIdentifierDefinitionBooleanTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForBooleanReturningTrue() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(TRUE);
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForBooleanReturningFalse() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForCallableReturningTrue() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(array($this, 'callbackReturnTrue'));
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForCallableReturningFalse() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(array($this, 'callbackReturnFalse'));
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(TRUE);
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }

  public function callbackReturnTrue() {
    return TRUE;
  }

  public function callbackReturnFalse() {
    return FALSE;
  }
}