<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Control/Command/Condition.php');

class PapayaUiControlCommandConditionTest extends PapayaTestCase {

  /**
  * @covers PapayaUiControlCommandCondition::command
  */
  public function testCommandGetAfterSet() {
    $application = $this->getMockApplicationObject();
    $command = $this->getMock('PapayaUiControlCommand');
    $command
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($application));
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $condition->papaya();
    $this->assertSame($command, $condition->command($command));
    $this->assertEquals($application, $condition->papaya());
  }

  /**
  * @covers PapayaUiControlCommandCondition::command
  */
  public function testCommandGetExpectingException() {
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $this->setExpectedException(
      'LogicException',
      'LogicException:'.
        ' Instance of "PapayaUiControlCommandCondition_TestProxy" has no command assigned.'
    );
    $command = $condition->command();
  }

  /**
  * @covers PapayaUiControlCommandCondition::hasCommand
  */
  public function testHascommandExpectingTrue() {
    $command = $this->getMock('PapayaUiControlCommand');
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $condition->command($command);
    $this->assertTrue($condition->hasCommand());
  }

  /**
  * @covers PapayaUiControlCommandCondition::hasCommand
  */
  public function testHasCommandExpectingFalse() {
    $condition = new PapayaUiControlCommandCondition_TestProxy();
    $this->assertFalse($condition->hasCommand());
  }
}

class PapayaUiControlCommandCondition_TestProxy extends PapayaUiControlCommandCondition {

  public function validate() {

  }
}