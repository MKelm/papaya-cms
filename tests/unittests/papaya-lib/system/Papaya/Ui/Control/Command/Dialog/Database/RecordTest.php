<?php
require_once(substr(__FILE__, 0, -74).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaUiControlCommandDialogDatabaseRecordTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::__construct
  */
  public function testConstructor() {
    $record = $this->getMock('PapayaDatabaseInterfaceRecord');
    $command = new PapayaUiControlCommandDialogDatabaseRecord($record);
    $this->assertSame($record, $command->record());
  }

  /**
  * @covers PapayaUiControlCommandDialogDatabaseRecord::record
  */
  public function testRecordGetAfterSet() {
    $command = new PapayaUiControlCommandDialogDatabaseRecord(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->record($record = $this->getMock('PapayaDatabaseInterfaceRecord'));
    $this->assertSame($record, $command->record());
  }
}