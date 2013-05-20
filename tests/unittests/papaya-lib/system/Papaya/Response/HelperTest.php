<?php
require_once(substr(__FILE__, 0, -49).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Response/Helper.php');

class PapayaResponseHelperTest extends PapayaTestCase {

  /**
  * @covers PapayaResponseHelper::headersSent
  */
  public function testHeadersSent() {
    $helper = new PapayaResponseHelper();
    $this->assertInternalType(
      'boolean',
      $helper->headersSent()
    );
  }
}