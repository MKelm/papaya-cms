<?php
require_once(substr(__FILE__, 0, -53).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Toolbar/Elements.php');

class PapayaUiToolbarElementsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarElements::__construct
  * @covers PapayaUiToolbarElements::owner
  */
  public function testConstructor() {
    $menu = $this->getMock('PapayaUiMenu');
    $elements = new PapayaUiToolbarElements($menu);
    $this->assertSame(
      $menu, $elements->owner()
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsAllowed() {
    $elements = new PapayaUiToolbarElements($this->getMock('PapayaUiMenu'));
    $elements->allowGroups = TRUE;
    $group = $this->getMock('PapayaUiToolbarGroup', array(), array('caption'));
    $elements->add($group);
    $this->assertEquals(
      $group, $elements[0]
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsNotAllowedExpectingException() {
    $elements = new PapayaUiToolbarElements($this->getMock('PapayaUiMenu'));
    $elements->allowGroups = FALSE;
    $group = new PapayaUiToolbarGroup('caption');
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Invalid item class "PapayaUiToolbarGroup".'
    );
    $elements->add($group);
  }
}