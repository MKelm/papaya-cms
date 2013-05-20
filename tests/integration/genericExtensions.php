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

class extensionTestSuite extends PHPUnit_Framework_TestSuite {

  protected function setUp() {
  }

  protected function tearDown() {
  }
}

runSuite('extensionTestSuite', array(
    'genericExtensionTests',
  )
);

?>