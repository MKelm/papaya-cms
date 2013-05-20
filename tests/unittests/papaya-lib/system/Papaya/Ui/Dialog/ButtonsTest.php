<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Buttons.php');

class PapayaUiDialogButtonsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogButtons::add
  */
  public function testAdd() {
    $button = $this->getMock('PapayaUiDialogButton', array('owner', 'appendTo'));
    $buttons = new PapayaUiDialogButtons();
    $buttons->add($button);
    $this->assertAttributeEquals(
      array($button), '_items', $buttons
    );
  }
}