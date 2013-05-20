<?php

require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'modules/free/forum/connector_forum.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/forum/base_forum.php');
require_once(PAPAYA_INCLUDE_PATH.'modules/free/forum/admin_forum.php');

PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_URI',
  'PAPAYA_DB_TABLEPREFIX',
  'PAPAYA_SEARCH_BOOLEAN',
  'PAPAYA_PAGE');

class PapayaLibModulesFreeForumConnectorTest extends PapayaTestCase {

  /**
  * Instanctiate of the forum connector
  *
  * @return connector_forum
  */
  public function getForumConnectorObject($ownerObject = NULL) {
    return new connector_forum($ownerObject);
  }


  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Category
  **********************/

  /**
  * @covers connector_forum::getCategoryIdByMode
  */
  public function testGetCategoryIdByModeCategoryMode() {
    $connector = $this->getForumConnectorObject();
    $mode = array(
      'mode' => 'categ',
      'id' => 1
    );

    $this->assertSame(1, $connector->getCategoryIdByMode($mode));
  }

  /**
  * @covers connector_forum::getCategoryIdByMode
  */
  public function testGetCategoryIdByModeForumMode() {
    $connector = $this->getForumConnectorObject();
    $mode = array(
      'mode' => 'forum',
      'id' => 1
    );
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('loadBoard')
      ->will($this->returnValue(array('forumcat_id' => 100)));
    $connector->setBaseForumObject($forum);
    $this->assertSame(100, $connector->getCategoryIdByMode($mode));
  }


  /**
  * Forum
  **********************/

  /**
  * @dataProvider addForumDataProvider
  * @covers connector_forum::addForum
  */
  public function testAddForum($data, $expected, $actual) {
    $categoryId = $data['category'];
    $title = $data['title'];
    $description = $data['description'];
    $uri = $data['uri'];

    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('addForum')
      ->will($this->returnValue(1));

    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame(1, $connector->addForum($categoryId, $title, $description, $uri));
  }

  /**
  * @covers connector_forum::getForumByURI
  */
  public function testGetForumByURI() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('getForumByPageId')
      ->will($this->returnValue(100));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame(100, $connector->getForumByURI(1, '2'));
  }

  /**
  * @covers connector_forum::loadBoard
  */
  public function testLoadBoard() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('loadBoard')
      ->will($this->returnValue(100));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame(100, $connector->loadBoard(1));
  }


  /**
  * Entry
  **********************/

  /**
  * @covers connector_forum::getEntries
  */
  public function testGetEntries() {

    $expected = array(
      'entries' => array(
        1 => array(
          'entry_id' => 1,
          'entry_username' => 'static test username',
          'entry_userhandle' => 'MvE',
          'entry_subject' => 'static test value',
          'entry_text' => 'static Test text',
          'entry_created' => '1260194400',
        )
      ),
      'abs_count' => 1
    );
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('loadLastEntriesByForum')
      ->will($this->returnValue($expected));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);

    $this->assertSame($expected, $connector->getEntries(1, 'page:1'));
  }

  /**
  * @dataProvider addEntryDataProvider
  * @covers connector_forum::addEntry
  */
  public function testAddEntry($data, $expected, $actual) {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('getCurrentSurfer')
      ->will($this->returnValue(TRUE));
    $forum
      ->expects($this->once())
      ->method('addEntry')
      ->will($this->returnValue($actual));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame($expected, $connector->addEntry($data, FALSE));
  }

  /**
  * @covers connector_forum::getThreadByURI
  */
  public function testGetThreadByURI() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('getThreadByPageId')
      ->will($this->returnValue(1));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame(1, $connector->getThreadByURI(1, '1'));
  }

  /**
  * @covers connector_forum::blockEntry
  */
  public function testBlockEntry() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('blockEntry')
      ->will($this->returnValue(TRUE));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertTrue($connector->blockEntry(1));
  }

  /**
  * @covers connector_forum::unblockEntry
  */
  public function testUnblockEntry() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('unblockEntry')
      ->will($this->returnValue(TRUE));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertTrue($connector->unblockEntry(1));
  }


  /**
  * Surfer Methods
  **********************/

  /**
  * @covers connector_forum::getCurrentSurfer
  */
  public function testGetCurrentSurfer() {

    PapayaTestCase::defineConstantDefaults(
      'PAPAYA_DB_TBL_SURFER',
      'PAPAYA_DB_TBL_SURFERGROUPS',
      'PAPAYA_DB_TBL_SURFERPERM',
      'PAPAYA_DB_TBL_SURFERACTIVITY',
      'PAPAYA_DB_TBL_SURFERPERMLINK',
      'PAPAYA_DB_TBL_SURFERCHANGEREQUESTS',
      'PAPAYA_DB_TBL_TOPICS'
    );

    $connector = new connector_forumProxy($this);
    $baseObject = new base_forumProxy;

    $connector->setBaseForumObject($baseObject);
    $surferObject = $connector->getCurrentSurfer();
    $this->assertAttributeSame($surferObject, '_surfer', $connector);
  }


  /**
  * Administration
  **********************/

  /**
  * @covers connector_forum::getForumCategoryCombo
  */
  public function testGetForumCategoryCombo() {
    $connector = $this->getForumConnectorObject();

    $adminObject = $this->getMock('admin_forum', array('getForumCombo'));
    $adminObject
      ->expects($this->once())
      ->method('getForumCombo')
      ->will($this->returnValue(TRUE));
    $connector->setAdminObject($adminObject);
    $this->assertTrue($connector->getForumCategoryCombo('paramName', 'name', array()));
  }


  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * @covers connector_forum::checkSpam
  */
  public function testCheckSpam() {
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('checkSpam')
      ->will($this->returnValue(1));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $this->assertSame(1, $connector->checkSpam('Test Text'));
  }

  /**
  * @dataProvider decodeUriDataProvider
  * @covers connector_forum::decodeURI
  */
  public function testDecodeURI($uri, $expected) {
    $owner = NULL;
    $connector = new connector_forumProxy($owner);
    $this->assertEquals($expected, $connector->decodeURI($uri));
  }

  /**
  * @covers connector_forum::getBaseForumObject
  */
  public function testGetBaseForumObject() {
    $connector = $this->getForumConnectorObject();
    $baseObject = $connector->getBaseForumObject();
    $this->assertAttributeSame($baseObject, '_baseForum', $connector);
  }

  /**
  * @covers connector_forum::setBaseForumObject
  */
  public function testSetBaseForumObject() {
    $connector = $this->getForumConnectorObject();
    $baseForumObject = $this->getMock('base_forum');
    $connector->setBaseForumObject($baseForumObject);
    $this->assertAttributeSame($baseForumObject, '_baseForum', $connector);
  }

  /**
  * @covers connector_forum::getAdminObject
  */
  public function testGetAdminObject() {
    $connector = $this->getForumConnectorObject();
    $adminObject = $connector->getAdminObject();
    $this->assertAttributeSame($adminObject, '_administration', $connector);
  }

  /**
  * @covers connector_forum::setAdminObject
  */
  public function testSetAdminObject() {
    $connector = $this->getForumConnectorObject();
    $adminObject = $this->getMock('admin_forum');
    $connector->setAdminObject($adminObject);
    $this->assertAttributeSame($adminObject, '_administration', $connector);
  }

  /**
  * @covers connector_forum::setOwnerObject
  */
  public function testSetOwnerObject() {
    $owner = new stdClass();
    $forum = $this->getMock('base_forum');
    $forum
      ->expects($this->once())
      ->method('setOwnerObject')
      ->with($this->equalTo($owner));
    $connector = $this->getForumConnectorObject();
    $connector->setBaseForumObject($forum);
    $connector->setOwnerObject($owner);
  }

  /***************************************************************************/
  /** DataProvider                                                           */
  /***************************************************************************/

  public static function decodeUriDataProvider() {
    return array(
      'URI is integer' => array(1 , array('page_id' => 1, 'page_prefix' => '')),
      'URI has mode: page' => array('page:1', array('page_id' => 1, 'page_prefix' => 'page:')),
      'URI has mode: module' => array(
        'module:12345/1',
        array('page_id' => 1, 'page_prefix' => 'module:12345')
      ),
    );
  }
  public static function addForumDataProvider() {
    return array(
      'Succedd to add a Forum' => array (
        array(
          'category' => 1,
          'title' => 'Test Title',
          'description' => 'Test description',
          'uri' => '1'
        ),
        10,
        10
      ),
      'FAILED due to not existing category' => array (
        array(
          'category' => -1,
          'title' => 'Test Title',
          'description' => 'Test description',
          'uri' => '1'
        ),
        FALSE,
        FALSE
      ),
    );
  }

  public static function addEntryDataProvider() {
    return array(
      'Succeed to add an entry' => array(
        array (
          'entry_text' => 'Entry Text',
          'entry_subject' => 'Subtext',
          'entry_strip' => 'Strip',
          'entry_path' => ';Path;',
          'entry_pid' => 12
        ),
        10,
        10
      ),
      'Failed, due to missing entry_text' => array(
        array (
          'entry_subject' => 'Subtext',
          'entry_strip' => 'Strip',
          'entry_path' => ';Path;',
          'entry_pid' => 12
        ),
        FALSE,
        FALSE
      ),
      'Failed, due to missing entry_subject' => array(
        array (
          'entry_text' => 'Entry Text',
          'entry_strip' => 'Strip',
          'entry_path' => ';Path;',
          'entry_pid' => 12
        ),
        FALSE,
        FALSE
      ),
      'Failed, due to missing entry_strip' => array(
        array (
          'entry_text' => 'Entry Text',
          'entry_subject' => 'Subtext',
          'entry_path' => ';Path;',
          'entry_pid' => 12
        ),
        FALSE,
        FALSE
      ),
    );
  }


}

/**
* Proxy object of connector_forum to be able to mock deeper layers
*/
class connector_forumProxy extends connector_forum {

  public $_surfer = NULL;

  public function decodeURI($uri) {
    return parent::decodeURI($uri);
  }
}

/**
* Proxy object of base_forum to be able to mock deeper layers
*/
class base_forumProxy extends base_forum {

  public $surferObj = NULL;

  public function getCurrentSurfer() {
    return $this->surferObj = TRUE;
  }
}
?>
