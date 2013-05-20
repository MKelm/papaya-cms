<?php
require_once(substr(dirname(__FILE__), 0, -25).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();
require_once(PAPAYA_INCLUDE_PATH.'modules/_base/PagesConnector.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

class PagesConnectorTest extends PapayaTestCase {

  private function _getPagesConnectorObject($proxy = FALSE) {
    return $proxy ?
      $this->getProxy('PapayaBasePagesConnector', array('getDatabaseAccessObject')) :
      new PapayaBasePagesConnector();
  }

  private function _getDatabaseResultFixture($methods, $result = NULL) {
    if (!is_array($methods) && is_string($methods) && !empty($methods)) {
      $methods = array($methods);
    }
    $resultObject = $this->getMock(
      'dbresult_mysql', $methods, array(), 'myMock_'.md5('dbresult_mysql'.microtime()), FALSE
    );
    if (isset($result)) {
      if (array_key_exists('fetchRow', array_flip($methods))) {
        $resultObject
          ->expects($this->atLeastOnce())
          ->method('fetchRow')
          ->will(
            $this->onConsecutiveCalls(
              $this->returnValue($result),
              $this->returnValue(FALSE)
            )
          );
      } elseif (array_key_exists('fetchField', array_flip($methods))) {
        $resultObject
          ->expects($this->once())
          ->method('fetchField')
          ->will($this->returnValue($result));
      }
    }
    return $resultObject;
  }

  private function _getPapayaDatabaseAccessWithQueryFixture(
            $databaseResultObject, $additionalMethods = array(), $multiQueries = FALSE
          ) {

    $methods = array('queryFmt');
    if (!is_array($additionalMethods) &&
        is_string($additionalMethods) && !empty($additionalMethods)) {
      $additionalMethods = array($additionalMethods);
    }
    if (is_array($additionalMethods) && count($additionalMethods) > 0) {
      $methods = array_merge($methods, $additionalMethods);
    }
    $databaseObject = $this->getMock(
      'PapayaDatabaseAccess',
      $methods,
      array(new stdClass())
    );
    $databaseObject
      ->expects($this->any())
      ->method('queryFmt')
      ->will($this->returnValue($databaseResultObject));
    return $databaseObject;
  }

  /**
  * @covers PapayaBasePagesConnector::setDatabaseAccessObject
  */
  public function testSetDatabaseAccessObject() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $databaseAccess = new stdClass();
    $pagesConnector->setDatabaseAccessObject($databaseAccess);
    $this->assertAttributeEquals($databaseAccess, '_databaseAccess', $pagesConnector);
  }

  /**
  * @covers PapayaBasePagesConnector::getDatabaseAccessObject
  */
  public function testGetDatabaseAccessObject() {
    $pagesConnector = $this->_getPagesConnectorObject(TRUE);
    $databaseAccess = $pagesConnector->getDatabaseAccessObject();
    $this->assertInstanceOf('PapayaDatabaseAccess', $databaseAccess);
    $this->assertAttributeInstanceOf('PapayaDatabaseAccess', '_databaseAccess', $pagesConnector);
  }

  /**
  * @covers PapayaBasePagesConnector::getTitles
  */
  public function testGetTitles() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $pageIds = array(4, NULL);
    $lngId = 1;
    $pageTitle = 'Title 4';
    $databaseRow = array('topic_id' => $pageIds[0], 'topic_title' => $pageTitle);
    $expectedResult = array($pageIds[0] => $pageTitle);
    $databaseResult = $this->_getDatabaseResultFixture('fetchRow', $databaseRow);
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->once())
      ->method('getSQLCondition')
      ->with($this->equalTo('tt.topic_id'), $this->equalTo(array($pageIds[0])))
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", $pageIds[0])));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult, $pagesConnector->getTitles($pageIds, $lngId));
  }

  /**
  * @covers PapayaBasePagesConnector::getTitles
  */
  public function testGetTitlesWithSinglePage() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $pageId = 4;
    $lngId = 1;
    $pageTitle = 'Title 4';
    $databaseRow = array('topic_id' => $pageId, 'topic_title' => $pageTitle);
    $expectedResult = array($pageId => $pageTitle);
    $databaseResult = $this->_getDatabaseResultFixture('fetchRow', $databaseRow);
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->once())
      ->method('getSQLCondition')
      ->with($this->equalTo('tt.topic_id'), $this->equalTo(array($pageId)))
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", $pageId)));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult, $pagesConnector->getTitles($pageId, $lngId));
  }

  /**
  * @covers PapayaBasePagesConnector::getTitles
  */
  public function testGetTitlesWithMemoryCache() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $lngId = 1;
    $pageTitles = array(
      3 => 'Page Title 3',
      5 => 'Page Title 5',
      6 => 'Page Title 6'
    );
    $pageIds = array(3, 5, 6);
    $furtherPageIds = array(3, 6);

    $databaseRows = array(
      array('topic_id' => $pageIds[0], 'topic_title' => $pageTitles[$pageIds[0]]),
      array('topic_id' => $pageIds[1], 'topic_title' => $pageTitles[$pageIds[1]]),
      array('topic_id' => $pageIds[2], 'topic_title' => $pageTitles[$pageIds[2]])
    );

    $expectedResult1 = array(
      $pageIds[0] => $pageTitles[$pageIds[0]],
      $pageIds[1] => $pageTitles[$pageIds[1]],
      $pageIds[2] => $pageTitles[$pageIds[2]]
    );
    $expectedResult2 = array(
      $pageIds[0] => $pageTitles[$pageIds[0]],
      $pageIds[2] => $pageTitles[$pageIds[2]]
    );

    $databaseResult = $this->_getDatabaseResultFixture('fetchRow');
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue($databaseRows[0]),
          $this->returnValue($databaseRows[1]),
          $this->returnValue($databaseRows[2]),
          $this->returnValue(FALSE)
        )
      );
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->atLeastOnce())
      ->method('getSQLCondition')
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", "123")));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult1, $pagesConnector->getTitles($pageIds, $lngId));
    $this->assertEquals($expectedResult2, $pagesConnector->getTitles($furtherPageIds, $lngId));
    $this->assertAttributeEquals(array($lngId => $expectedResult1), '_pageTitles', $pagesConnector);
  }

  /**
  * @covers PapayaBasePagesConnector::getContents
  */
  public function testGetContents() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $pageIds = array(4, NULL);
    $lngId = 1;
    $pageTitle = 'Title 4';
    $databaseRow = array(
      'topic_id' => $pageIds[0],
      'topic_title' => $pageTitle,
      'topic_content' => '<text>Test</text>'
    );
    $expectedResult = array($pageIds[0] => $databaseRow);
    $databaseResult = $this->_getDatabaseResultFixture('fetchRow', $databaseRow);
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->once())
      ->method('getSQLCondition')
      ->with($this->equalTo('tt.topic_id'), $this->equalTo(array($pageIds[0])))
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", $pageIds[0])));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult, $pagesConnector->getContents($pageIds, $lngId));
  }

  /**
  * @covers PapayaBasePagesConnector::getContents
  */
  public function testGetContentsWithSinglePage() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $pageId = 4;
    $lngId = 1;
    $pageTitle = 'Title 4';
    $databaseRow = array(
      'topic_id' => $pageId,
      'topic_title' => $pageTitle,
      'topic_content' => '<text>Test</text>'
    );
    $expectedResult = array($pageId => $databaseRow);
    $databaseResult = $this->_getDatabaseResultFixture('fetchRow', $databaseRow);
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->once())
      ->method('getSQLCondition')
      ->with($this->equalTo('tt.topic_id'), $this->equalTo(array($pageId)))
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", $pageId)));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult, $pagesConnector->getContents($pageId, $lngId));
  }

  /**
  * @covers PapayaBasePagesConnector::getContents
  */
  public function testGetContentsWithMemoryCache() {
    $pagesConnector = $this->_getPagesConnectorObject();
    $this->defineConstantDefaults(array('PAPAYA_DB_TBL_TOPICS_TRANS', 'DB_FETCHMODE_ASSOC'));

    $lngId = 1;
    $xml = '<text>Test</text>';
    $pageContents = array(
      3 => array('topic_id' => 3, 'topic_title' => 'Page Title 3', 'topic_content' => $xml),
      5 => array('topic_id' => 5, 'topic_title' => 'Page Title 5', 'topic_content' => $xml),
      6 => array('topic_id' => 6, 'topic_title' => 'Page Title 6', 'topic_content' => $xml)
    );
    $pageIds = array(3, 5, 6);
    $furtherPageIds = array(3, 6);

    $databaseRows = array(
      $pageContents[$pageIds[0]],
      $pageContents[$pageIds[1]],
      $pageContents[$pageIds[2]]
    );

    $expectedResult1 = array(
      $pageIds[0] => $pageContents[$pageIds[0]],
      $pageIds[1] => $pageContents[$pageIds[1]],
      $pageIds[2] => $pageContents[$pageIds[2]]
    );
    $expectedResult2 = array(
      $pageIds[0] => $pageContents[$pageIds[0]],
      $pageIds[2] => $pageContents[$pageIds[2]]
    );

    $databaseResult = $this->_getDatabaseResultFixture('fetchRow');
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          $this->returnValue($databaseRows[0]),
          $this->returnValue($databaseRows[1]),
          $this->returnValue($databaseRows[2]),
          $this->returnValue(FALSE)
        )
      );
    $papayaDatabaseAccess = $this->_getPapayaDatabaseAccessWithQueryFixture(
      $databaseResult, array('getSQLCondition')
    );
    $papayaDatabaseAccess
      ->expects($this->atLeastOnce())
      ->method('getSQLCondition')
      ->will($this->returnValue(sprintf("tt.topic_id = '%s'", "123")));
    $pagesConnector->setDatabaseAccessObject($papayaDatabaseAccess);

    $this->assertEquals($expectedResult1, $pagesConnector->getContents($pageIds, $lngId));
    $this->assertEquals($expectedResult2, $pagesConnector->getContents($furtherPageIds, $lngId));
    $this->assertAttributeEquals(
      array($lngId => $expectedResult1),
      '_pageContents',
      $pagesConnector
    );
  }
}
?>