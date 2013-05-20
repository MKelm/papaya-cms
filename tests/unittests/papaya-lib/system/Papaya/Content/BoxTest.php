<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Box.php');

class PapayaContentBoxTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBox::load
  */
  public function testLoad() {
    $translations = $this->getMock('PapayaContentBoxTranslations', array('load'));
    $translations
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(42));
    $record = array(
      'box_id' => 42,
      'boxgroup_id' => 21,
      'box_name' => 'Box Name',
      'box_created' => 1,
      'box_modified' => 2,
      'box_cachemode' => PapayaContentOptions::CACHE_SYSTEM,
      'box_cachetime' => 0,
      'box_unpublished_languages' => 0
    );
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue($record));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('box')
      ->will($this->returnValue('papaya_box'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_box', 42))
      ->will($this->returnValue($databaseResult));
    $box = new PapayaContentBox_TestProxy();
    $box->setDatabaseAccess($databaseAccess);
    $box->translations($translations);
    $this->assertTrue(
      $box->load(42)
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'group_id' => 21,
        'name' => 'Box Name',
        'created' => 1,
        'modified' => 2,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'unpublished_translations' => 0
      ),
      '_values',
      $box
    );
  }

  /**
  * @covers PapayaContentBox::load
  */
  public function testLoadFailedExpectingFalse() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('box')
      ->will($this->returnValue('papaya_box'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_box', 42))
      ->will($this->returnValue(FALSE));
    $box = new PapayaContentBox_TestProxy();
    $box->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $box->load(42)
    );
  }

  /**
  * @covers PapayaContentBox::translations
  */
  public function testTranslationsSet() {
    $translations = $this->getMock('PapayaContentBoxTranslations');
    $box = new PapayaContentBox_TestProxy();
    $box->translations($translations);
    $this->assertAttributeSame(
      $translations, '_translations', $box
    );
  }

  /**
  * @covers PapayaContentBox::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->getMock('PapayaContentBoxTranslations');
    $box = new PapayaContentBox_TestProxy();
    $box->translations($translations);
    $this->assertSame(
      $translations, $box->translations()
    );
  }

  /**
  * @covers PapayaContentBox::translations
  */
  public function testTranslationsGetImplicitCreate() {
    $box = new PapayaContentBox_TestProxy();
    $this->assertInstanceOf(
      'PapayaContentBoxTranslations', $box->translations()
    );
  }
}

class PapayaContentBox_TestProxy extends PapayaContentBox {

}