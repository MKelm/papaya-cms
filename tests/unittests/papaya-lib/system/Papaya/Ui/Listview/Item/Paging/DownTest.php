<?php
require_once(substr(__FILE__, 0, -62).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Listview/Item/Paging/Down.php');

class PapayaUiListviewItemPagingDownTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemPagingDown::getPages
  * @dataProvider provideDataForPageCalculations
  */
  public function testGetPages($expected, $currentPage, $itemsPerPage) {
    $item = new PapayaUiListviewItemPagingDown('page', $currentPage, $itemsPerPage);
    $this->assertEquals(
      $expected,
      $item->getPages()
    );
  }

  /**
  * @covers PapayaUiListviewItemPagingDown::getImagePage
  */
  public function testGetImagePage() {
    $item = new PapayaUiListviewItemPagingDown('page', 5, 500);
    $this->assertEquals(
      4,
      $item->getImagePage()
    );
  }

  /**
  * @covers PapayaUiListviewItemPagingDown::getImagePage
  */
  public function testGetImagePageExpectingDefault() {
    $item = new PapayaUiListviewItemPagingDown('page', 0, 500);
    $this->assertEquals(
      1,
      $item->getImagePage()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function provideDataForPageCalculations() {
    return array(
      array(
        array(),
        5,
        2
      ),
      array(
        array(),
        1,
        20
      ),
      array(
        array(1, 2),
        3,
        40
      ),
      array(
        array(7, 8, 9),
        10,
        100
      )
    );
  }
}