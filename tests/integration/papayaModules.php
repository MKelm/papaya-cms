<?php
/**
* a generic test suite to
* - test papaya phpunit extensions
* - use as a template
*
* PHP version 5
*
* @copyright 2007-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    module_papaya
* @subpackage unittest
* @category   testsuite
* @version    SVN: $Id: &
*
*/

require_once("extensions/suite_runner.php");

class papayaModulesTestSuite extends PHPUnit_Framework_TestSuite {

  protected function setUp() {
    $this->sharedFixture = array(
      "papaya_base_url" => "http://papaya5.paptester.papaya.local",
      "papaya_backend_user" => "admin",
      "papaya_backend_password" => "pass",
      "papaya_frontend_user" => "admin",
      "papaya_frontend_user_email" => "gotwig@papaya-cms.com",
      "papaya_frontend_password" => "pass",
      "pages" => array(
        "start" => 36,
        "communityLogin" => 17,
      )
    );
  }

  protected function tearDown() {
  }
}

runSuite('papayaModulesTestSuite', array(
    'papayaModulesXmlFixtures',
    'statisticUserAgentsTests',
    'statisticReferer',
  )
);

?>