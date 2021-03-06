<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Box/Work.php');

class PapayaContentBoxWorkTest extends PapayaTestCase {

  /**
  * @covers PapayaContentBoxWork::save
  */
  public function testSaveCreateNew() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'insertRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('box')
      ->will($this->returnValue('papaya_box'));
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with($this->equalTo('papaya_box'), $this->equalTo('box_id'), $this->isType('array'))
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $box = new PapayaContentBoxWork();
    $box->papaya($this->getMockApplicationObject());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 0,
        'modified' => 0,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'unpublished_translations' => 0
      )
    );
    $this->assertEquals(42, $box->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals('Box Name', $data['box_name']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertGreaterThan(0, $data['box_created']);
    $this->assertGreaterThan(0, $data['box_modified']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    $this->assertEquals(0, $data['box_unpublished_languages']);
    return 42;
  }

  /**
  * @covers PapayaContentBoxWork::save
  */
  public function testSaveUpdateExisting() {
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'updateRecord'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->once())
      ->method('getTableName')
      ->with('box')
      ->will($this->returnValue('papaya_box'));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with(
        $this->equalTo('papaya_box'),
        $this->isType('array'),
        $this->equalTo(array('box_id' => 42))
      )
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $box = new PapayaContentBoxWork();
    $box->papaya($this->getMockApplicationObject());
    $box->setDatabaseAccess($databaseAccess);
    $box->assign(
      array(
        'id' => 42,
        'name' => 'Box Name',
        'group_id' => 21,
        'created' => 1,
        'modified' => 1,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'unpublished_translations' => 0
      )
    );
    $this->assertTrue($box->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('Box Name', $data['box_name']);
    $this->assertEquals(21, $data['boxgroup_id']);
    $this->assertEquals(1, $data['box_created']);
    $this->assertGreaterThan(1, $data['box_modified']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['box_cachemode']);
    $this->assertEquals(0, $data['box_cachetime']);
    $this->assertEquals(0, $data['box_unpublished_languages']);
    return 42;
  }

  /**
  * @covers PapayaContentBoxWork::_createPublicationObject
  */
  public function testCreatePublicationObject() {
    $databaseAccess = $this->getMock('PapayaDatabaseAccess', array(), array(new stdClass));
    $box = new PapayaContentBoxWork_TestProxy();
    $box->setDatabaseAccess($databaseAccess);
    $publication = $box->_createPublicationObject();
    $this->assertInstanceOf(
      'PapayaContentBoxPublication', $publication
    );
    $this->assertSame(
      $databaseAccess, $publication->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaContentBoxWork::publish
  */
  public function testPublishWithoutIdExpectingFalse() {
    $box = new PapayaContentBoxWork_TestProxy();
    $this->assertFalse($box->publish());
  }

  /**
  * @covers PapayaContentBoxWork::publish
  * @covers PapayaContentBoxWork::_publishTranslations
  */
  public function testPublishWithoutLanguagesOrPeriod() {
    $box = $this->getContentBoxFixture();
    $publication = $this->getMock('PapayaContentBoxPublication');
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->equalTo($box));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->equalTo(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $box->publicationObject = $publication;
    $this->assertTrue($box->publish());
  }

  /**
  * @covers PapayaContentBoxWork::publish
  */
  public function testPublishFailed() {
    $box = $this->getContentBoxFixture();
    $publication = $this->getMock('PapayaContentBoxPublication');
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->equalTo($box));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->equalTo(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $box->publicationObject = $publication;
    $this->assertFalse($box->publish());
  }

  /**
  * @covers PapayaContentBoxWork::publish
  * @covers PapayaContentBoxWork::_publishTranslations
  */
  public function testPublishWithLanguagesPeriod() {
    $box = $this->getContentBoxFixture();
    $translations = $this->getMock('PapayaContentBoxTranslations', array('count'));
    $translations
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(3));
    $box->translations($translations);

    $publicTranslations = $this->getMock('PapayaContentBoxTranslations', array('count'));
    $publicTranslations
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(2));
    $publication = $this->getMock('PapayaContentBoxPublication');
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf('PapayaContentBoxWork'));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $publication
      ->expects($this->once())
      ->method('translations')
      ->will($this->returnValue($publicTranslations));
    $box->publicationObject = $publication;

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'deleteRecord', 'queryFmt', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('lng_id'), $this->equalTo(array(23, 42)))
      ->will($this->returnValue("lng_id IN ('23', '42')"));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('box_public_trans', array('box_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(2));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with('box', array('box_unpublished_languages' => 1), array('box_id' => 21));
    $box->setDatabaseAccess($databaseAccess);

    $this->assertTrue($box->publish(array(23, 42), 123, 456));
  }

  /**
  * @covers PapayaContentBoxWork::publish
  * @covers PapayaContentBoxWork::_publishTranslations
  */
  public function testPublishTranslationDeletionFailedExpetingFalse() {
    $box = $this->getContentBoxFixture();
    $publication = $this->getMock('PapayaContentBoxPublication');
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf('PapayaContentBoxWork'));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $box->publicationObject = $publication;

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'deleteRecord', 'queryFmt', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('box_public_trans', array('box_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(FALSE));
    $box->setDatabaseAccess($databaseAccess);

    $this->assertFalse($box->publish(array(23, 42), 123, 456));
  }

  /**
  * @covers PapayaContentBoxWork::publish
  * @covers PapayaContentBoxWork::_publishTranslations
  */
  public function testPublishTranslationFailedExpetingFalse() {
    $box = $this->getContentBoxFixture();

    $publication = $this->getMock('PapayaContentBoxPublication');
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf('PapayaContentBoxWork'));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $box->publicationObject = $publication;

    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess',
      array('getTableName', 'getSqlCondition', 'deleteRecord', 'queryFmt', 'updateRecord'),
      array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('lng_id'), $this->equalTo(array(23, 42)))
      ->will($this->returnValue("lng_id IN ('23', '42')"));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('box_public_trans', array('box_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(FALSE));
    $box->setDatabaseAccess($databaseAccess);

    $this->assertFalse($box->publish(array(23, 42), 123, 456));
  }

  public function getContentBoxFixture() {
    $box = new PapayaContentBoxWork_TestProxy();
    $box->assign(
      array(
        'id' => 21,
        'name' => 'Box Name',
        'group_id' => 11,
        'created' => 123,
        'modified' => 456,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'unpublished_translations' => 0
      )
    );
    return $box;
  }
}

class PapayaContentBoxWork_TestProxy extends PapayaContentBoxWork {

  public $publicationObject = NULL;

  public function _createPublicationObject() {
    if (isset($this->publicationObject)) {
      return $this->publicationObject;
    }
    return parent::_createPublicationObject();
  }
}