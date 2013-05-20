<?php
/**
* This test checks the class base_statistic_useragents in module statistic
*
* PHP version 5
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
*
* @author     David Rekowski <info@papaya-cms.com>
* @package    papaya5
* @subpackage unittest
* @category   unittest
* @version    $Id: statisticUserAgentsTests.php 35763 2011-05-16 11:01:14Z weinert $
*
*/

include_once(PAPAYA_INCLUDE_PATH.'modules/commercial/statistics/base_statistic_useragents.php');

/**
* test cases for our unittest extensions
*
* @author     David Rekowski <info@papaya-cms.com>
* @package    papaya5
* @subpackage unittest
* @category   unittest
*
*/
class statisticUserAgentsTests extends papayaUnitTests {

  private $_clientIds = array(
    'Mozilla/5.0 (X11; U; Linux i686; de; rv:1.9.1b3) Gecko/20090305 Firefox/3.1b3' => array(
      'id' => 109, 'robot' => 0, 'os' => 1, 'name' => 'Firefox', 'os_name' => 'Linux',
      'version' => '3', 'os_version' => FALSE
    ),
    'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; GTB5; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648; .NET CLR 1' => array(
      'id' => 111, 'robot' => 0, 'os' => 2, 'name' => 'MSIE', 'os_name' => 'Windows',
      'version' => '6', 'os_version' => '5.1'
    ),
    'Opera/9.64 (Windows NT 5.1; U; en) Presto/2.1.1' => array(
      'id' => 110, 'robot' => 0, 'os' => 2, 'name' => 'Opera', 'os_name' => 'Windows',
      'version' => '9', 'os_version' => '5.1'
    ),
    'Wget/1.11.4' => array(
      'id' => 100, 'robot' => 1, 'os' => 9, 'name' => 'Wget', 'os_name' => 'Unknown',
      'version' => '1', 'os_version' => FALSE
    ),
    'this-useragent-surely-doesnt-exist' => array(
      'id' => 120, 'robot' => 1, 'os' => 9, 'name' => 'Unknown', 'os_name' => 'Unknown',
      'version' => FALSE, 'os_version' => FALSE
    ),
    'mozilla/5.0 (compatible; googlebot/2.1; +http://www.google.com/bot.html)' => array(
      'id' => 32, 'robot' => 1, 'os' => 9, 'name' => 'Googlebot', 'os_name' => 'Unknown',
      'version' => '2', 'os_version' => FALSE
    ),
    'mozilla/4.0 (compatible; msie 5.5; aol 8.0; windows 98; win 9x 4.90)' => array(
      'id' => 111, 'robot' => 0, 'os' => 2, 'name' => 'MSIE', 'os_name' => 'Windows',
      'version' => '5.5', 'os_version' => '4.1'
    ),
    'mozilla/4.0 (compatible; msie 6.0; windows nt 5.1; de) opera 8.0' => array(
      'id' => 110, 'robot' => 0, 'os' => 2, 'name' => 'Opera', 'os_name' => 'Windows',
      'version' => '8', 'os_version' => '5.1'
    ),
    'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; de-de) AppleWebKit/523.12.2 (KHTML, like Gecko) Version/3.0.4 Safari/523.12.2' => array(
      'id' => 113, 'robot' => 0, 'os' => 3, 'name' => 'Safari', 'os_name' => 'Mac',
      'version' => '3', 'os_version' => 10
    ),
    'Opera/9.80 (X11; Linux i686; U; en) Presto/2.2.15 Version/10.00' => array(
      'id' => 110, 'robot' => 0, 'os' => 1, 'name' => 'Opera', 'os_name' => 'Linux',
      'version' => '10', 'os_version' => FALSE
    ),
  );

  private function init() {
    $this->userAgentObj = base_statistic_useragents::getInstance();
  }

  public function x_test_initialize() {
    $this->init();
    $this->say('Checking Initialization');

    $idStrings = $this->userAgentObj->getUserAgentIdStrings();
    // $this->say($idStrings);

/*
// testing all known useragent strings
    foreach ($idStrings as $string => $client) {
      $this->say('Testing: '.$string);
      $this->say('Robot: '.(($this->userAgentObj->isRobot($string)) ? 'Yes' : 'No'));
      $this->say('Name:    '.$this->userAgentObj->getClientName($string));
      $this->say('Version: '.$this->userAgentObj->getClientVersion($string));
      $this->say('OS:      '.$this->userAgentObj->getOperatingSystemName($string));
      $this->say('OS Ver.: '.$this->userAgentObj->getOperatingSystemVersion($string));
      $this->say('-------');
    }
*/
    $this->assertInternalType('array', $idStrings);
    $this->assertTrue(count($idStrings) > 0);

  }

  public function x_test_checkAllInDB() {
    $this->init();
    $useragents = $this->userAgentObj->getAllUserAgents();
    // asort($useragents);
    // testing all useragents found in the db.
    foreach ($useragents as $string => $count) {
      $id = $this->userAgentObj->getClientId($string);
      if ($id == 120 || $id == 118) {
        if ($i++ > 1000) {
          return;
        }
        $this->say('Testing: '.$string);
        $this->say('Count:   '.$count);
        $this->say('Robot:   '.(($this->userAgentObj->isRobot($string)) ? 'Yes' : 'No'));
        $this->say('Name:    '.$this->userAgentObj->getClientName($string));
        $this->say('Version: '.$this->userAgentObj->getClientVersion($string));
        $this->say('OS:      '.$this->userAgentObj->getOperatingSystemName($string));
        $this->say('OS Ver.: '.$this->userAgentObj->getOperatingSystemVersion($string));
        $this->say('-------');
      }
    }
  }

  public function test_isRobot() {
    $this->init();
    $this->say('Checking Robots');
    foreach ($this->_clientIds as $string => $client) {
/*
      $this->say('Testing: '.$string);
      $this->say('Count:   '.$count);
      $this->say('Robot:   '.(($this->userAgentObj->isRobot($string)) ? 'Yes' : 'No'));
      $this->say('Name:    '.$this->userAgentObj->getClientName($string));
      $this->say('Version: '.$this->userAgentObj->getClientVersion($string));
      $this->say('OS:      '.$this->userAgentObj->getOperatingSystemName($string));
      $this->say('OS Ver.: '.$this->userAgentObj->getOperatingSystemVersion($string));
      $this->say('-------');
*/
      $this->assertEquals($this->userAgentObj->isRobot($string), $client['robot'], $string);
    }
  }

  public function test_getClientId() {
    $this->init();
    $this->say('Checking Client Ids');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getClientId($string), $client['id'], $string);
    }
  }

  public function test_getOperatingSystemId() {
    $this->init();
    $this->say('Checking Operating Sytem Ids');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getOperatingSystemId($string), $client['os'], $string);
    }
  }

  public function test_getClientName() {
    $this->init();
    $this->say('Checking Client Names');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getClientName($string), $client['name'], $string);
    }
  }

  public function test_getOperatingSystemName() {
    $this->init();
    $this->say('Checking Operating System Names');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getOperatingSystemName($string), $client['os_name'], $string);
    }
  }

  public function test_getClientVersion() {
    $this->init();
    $this->say('Checking Client Versions');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getClientVersion($string), $client['version'], $string);
    }
  }

  public function test_getOperatingSystemVersion() {
    $this->init();
    $this->say('Checking Operating System Versions');
    foreach ($this->_clientIds as $string => $client) {
      $this->assertEquals($this->userAgentObj->getOperatingSystemVersion($string), $client['os_version'], $string);
    }
  }

  private function say($value) {
    if (is_array($value)) {
      var_dump($value);
    } elseif (!is_object($value)) {
      echo '<br />';
      echo $value;
    }
  }

}
?>