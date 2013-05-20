<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Listview/Columns.php');

class PapayaUiListviewColumnsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewColumns::__construct
  * @covers PapayaUiListviewColumns::owner
  */
  public function testConstructor() {
    $listview = $this->getMock('PapayaUiListview');
    $columns = new PapayaUiListviewColumns($listview);
    $this->assertSame(
      $listview, $columns->owner()
    );
  }
}