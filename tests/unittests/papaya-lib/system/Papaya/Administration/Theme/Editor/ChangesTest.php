<?php
require_once(substr(__FILE__, 0, -68).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaAdministrationThemeEditorChangesTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChanges::appendTo
   */
  public function testAppendTo() {
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $commands
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->commands($commands);
    $this->assertEquals('', $changes->getXml());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::commands
   */
  public function testCommandsGetAfterSet() {
    $commands = $this
      ->getMockBuilder('PapayaUiControlCommandController')
      ->disableOriginalConstructor()
      ->getMock();
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->commands($commands);
    $this->assertSame($commands, $changes->commands());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::commands
   */
  public function testCommandGetImplicitCreate() {
    $changes = new PapayaAdministrationThemeEditorChanges();
    $changes->papaya($this->getMockApplicationObject());
    $this->assertInstanceOf('PapayaUiControlCommandController', $changes->commands());
  }


  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $command->themeSet($themeSet =  $this->getMock('PapayaContentThemeSet'));
    $this->assertSame($themeSet, $command->themeSet());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChanges::themeSet
   */
  public function testThemeSetGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChanges();
    $this->assertInstanceOf('PapayaContentThemeSet', $command->themeSet());
  }
}