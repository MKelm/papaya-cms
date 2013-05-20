<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Dialog/Field/Select/Radio.php');

class PapayaUiDialogFieldSelectRadioTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectRadio
  */
  public function testAppendTo() {
    $select = new PapayaUiDialogFieldSelectRadio(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectRadio" error="yes" mandatory="yes">'.
        '<select name="name" type="radio">'.
          '<option value="1">One</option>'.
          '<option value="2">Two</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }
}
