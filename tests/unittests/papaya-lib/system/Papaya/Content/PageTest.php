<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Page.php');

class PapayaContentPageTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPage::load
  * @covers PapayaContentPage::callbackMapValueFromFieldToProperty
  */
  public function testLoad() {
    $translations = $this->getMock('PapayaContentPageTranslations', array('load'));
    $translations
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(42));
    $record = array(
      'topic_id' => 42,
      'prev' => 21,
      'prev_path' => ';0;11;21;',
      'is_deleted' => FALSE,
      'author_id' => '1234567890...',
      'author_group' => -1,
      'author_perm' => '777',
      'surfer_useparent' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
      'surfer_permids' => '1;2;',
      'topic_created' => 1,
      'topic_modified' => 2,
      'topic_weight' => 0,
      'box_useparent' => FALSE,
      'topic_mainlanguage' => 1,
      'linktype_id' => 1,
      'meta_useparent' => FALSE,
      'topic_changefreq' => 50,
      'topic_priority' => 1,
      'topic_protocol' => PapayaContentOptions::SCHEME_SYSTEM,
      'topic_cachemode' => PapayaContentOptions::CACHE_SYSTEM,
      'topic_cachetime' => 0,
      'topic_expiresmode' => PapayaContentOptions::CACHE_SYSTEM,
      'topic_expirestime' => 0,
      'topic_unpublished_languages' => 0
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
      ->with('topic')
      ->will($this->returnValue('papaya_topic'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_topic'))
      ->will($this->returnValue($databaseResult));
    $page = new PapayaContentPage();
    $page->setDatabaseAccess($databaseAccess);
    $page->translations($translations);
    $this->assertTrue(
      $page->load(42)
    );
    $this->assertAttributeEquals(
      array(
        'id' => 42,
        'parent_id' => 21,
        'is_deleted' => FALSE,
        'owner' => '1234567890...',
        'group' => -1,
        'permissions' => 777,
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
        'created' => 1,
        'modified' => 2,
        'position' => 0,
        'inherit_boxes' => FALSE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => FALSE,
        'change_frequency' => 50,
        'priority' => 1,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0,
        'parent_path' => array(0, 11, 21),
        'visitor_permissions' => array(1, 2)
      ),
      '_values',
      $page
    );
  }

  /**
  * @covers PapayaContentPage::load
  */
  public function testLoadExpectingFalse() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->once())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will($this->returnValue(FALSE));
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('topic')
      ->will($this->returnValue('papaya_topic'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('papaya_topic'))
      ->will($this->returnValue($databaseResult));
    $page = new PapayaContentPage();
    $page->setDatabaseAccess($databaseAccess);
    $this->assertFalse(
      $page->load(42)
    );
  }

  /**
  * @covers PapayaContentPage::translations
  */
  public function testTranslationsSet() {
    $translations = $this->getMock('PapayaContentPageTranslations');
    $page = new PapayaContentPage();
    $page->translations($translations);
    $this->assertAttributeSame(
      $translations, '_translations', $page
    );
  }

  /**
  * @covers PapayaContentPage::translations
  */
  public function testTranslationsGetAfterSet() {
    $translations = $this->getMock('PapayaContentPageTranslations');
    $page = new PapayaContentPage();
    $page->translations($translations);
    $this->assertSame(
      $translations, $page->translations()
    );
  }

  /**
  * @covers PapayaContentPage::translations
  */
  public function testTranslationsGetImplicitCreate() {
    $page = new PapayaContentPage();
    $this->assertInstanceOf(
      'PapayaContentPageTranslations', $page->translations()
    );
  }
}