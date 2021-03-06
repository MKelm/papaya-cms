<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Confirmation.php');

class PapayaUiDialogConfirmationTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogConfirmation::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'));
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
    $this->assertEquals(
      array('sample' => 'foo'), $dialog->hiddenFields()->toArray()
    );
  }

  /**
  * @covers PapayaUiDialogConfirmation::__construct
  */
  public function testConstructorWithParameterGroup() {
    $owner = new stdClass();
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'), 'group');
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
    $this->assertEquals(
      'group', $dialog->parameterGroup()
    );
    $this->assertEquals(
      array('sample' => 'foo'), $dialog->hiddenFields()->toArray()
    );
  }

  /**
  * @covers PapayaUiDialogConfirmation::setMessageText
  */
  public function testSetMessageText() {
    $owner = new stdClass();
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'), 'group');
    $dialog->setMessageText('Message text');
    $this->assertAttributeEquals(
      'Message text', '_message', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogConfirmation::setButtonCaption
  */
  public function testSetButtonCaption() {
    $owner = new stdClass();
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'), 'group');
    $dialog->setButtonCaption('Button caption');
    $this->assertAttributeEquals(
      'Button caption', '_button', $dialog
    );
  }

  /**
  * @covers PapayaUiDialogConfirmation::isSubmitted
  */
  public function testIsSubmittedExpectingTrue() {
    $request = $this->getMock('PapayaRequest', array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new PapayaUiDialogConfirmation(new stdClass(), array('sample' => 'foo'));
    $dialog->papaya($this->getMockApplicationObject(array('Request' => $request)));
    $dialog->parameters(
      new PapayaRequestParameters(array('confirmation' => 'a9994ecdd4cc99b5ac3b59272afa0d47'))
    );
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialogConfirmation::isSubmitted
  */
  public function testIsSubmittedExpectingFalse() {
    $request = $this->getMock('PapayaRequest', array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new PapayaUiDialogConfirmation(new stdClass(), array('sample' => 'foo'));
    $dialog->papaya($this->getMockApplicationObject(array('Request' => $request)));
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialogConfirmation::execute
  */
  public function testExecuteExpectingTrue() {
    $owner = new stdClass();
    $request = $this->getMock('PapayaRequest', array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->getMock('PapayaUiTokens', array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->getMockApplicationObject(array('Request' => $request)));
    $dialog->tokens($tokens);
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => 'a9994ecdd4cc99b5ac3b59272afa0d47',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogConfirmation::execute
  */
  public function testExecuteExpectingFalse() {
    $owner = new stdClass();
    $request = $this->getMock('PapayaRequest', array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->getMockApplicationObject(array('Request' => $request)));
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogConfirmation::execute
  */
  public function testExecuteCachesResultExpectingFalse() {
    $owner = new stdClass();
    $request = $this->getMock('PapayaRequest', array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new PapayaUiDialogConfirmation($owner, array('sample' => 'foo'));
    $dialog->papaya($this->getMockApplicationObject(array('Request' => $request)));
    $dialog->execute();
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialogConfirmation::appendTo
  */
  public function testAppendTo() {
    $owner = new stdClass();
    $tokens = $this->getMock('PapayaUiTokens', array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('create')
      ->with($this->equalTo($owner))
      ->will($this->returnValue('TOKEN_STRING'));
    $dialog = new PapayaUiDialogConfirmation(
      $owner,
      array('sample' => 'foo'),
      'group'
    );
    $dialog->papaya($this->getMockApplicationObject());
    $dialog->tokens($tokens);
    $this->assertEquals(
      '<confirmation-dialog action="http://www.test.tld/test.html" method="post">'.
      '<input type="hidden" name="group[sample]" value="foo"/>'.
      '<input type="hidden" name="group[confirmation]" value="a9994ecdd4cc99b5ac3b59272afa0d47"/>'.
      '<input type="hidden" name="group[token]" value="TOKEN_STRING"/>'.
      '<message>Confirm action?</message>'.
      '<dialog-button type="submit" caption="Yes"/>'.
      '</confirmation-dialog>',
      $dialog->getXml()
    );
  }
}