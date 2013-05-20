<style type="text/css" >
  * {
    font-family: DejaVu Mono, Courier new, monospace;
  }

</style>
<?php
/**
* phpunit test suite runner
*
* PHP version 5
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    module_weisseListe
* @subpackage unittest
* @category   testsuite
* @version    SVN: $Id: &
*
*/

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once(dirname(__FILE__).'/papayaUnitTests.php');

/**
* generic test suite class
* may be used for simple test cases that do not rely on common sharedFixture setup
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    weisseliste
* @subpackage unittest
* @category   unittest
*
*/
class genericTestSuite extends PHPUnit_Framework_TestSuite {

  protected function setUp() {
  }

  protected function tearDown() {
  }
}

/**
* main function to run a test suite
* @param STRING $suiteClassName - name of the test suite class
* @param ARRAY $testcases - array of one or more test case classes
* - array $testcases may be of structure array( testCaseClass1 => testCaseFile1, ...)
*   or array(testCaseClass1, testCaseClass2, ...) , or both mixed
*   in the second case the testCaseFile will be testCaseClass.php
* - it is also possible to run only one test case from the list,
*   please use CGI GET variable __test := _array_key_ of the expected test case
*/
function runSuite($suiteClassName, $testcases) {
  global $docRoot, $HTTP_SERVER_VARS;

  // INITIALISATION
  $docRoot = $HTTP_SERVER_VARS['DOCUMENT_ROOT'];
  include_once($docRoot.'/conf.inc.php');

  //define('PAPAYA_INCLUDE_PATH',$docRoot.'/papaya-lib/');
  define('PAPAYA_MODULES_PATH',$docRoot.'/papaya-lib/modules/');
  define('PAPAYA_PATH_TEMPLATES',$docRoot.'/papaya-data/templates/weisseliste/xslt/');
  define('PAPAYA_PATH_WEB','../../');
  define('PAPAYA_LAYOUT_THEME','weisseliste');
  define('PAPAYA_XSLT_EXTENSION','xslt');
  //define('PAPAYA_DBG_DATABASE_EXPLAIN', TRUE);
  define('PHPUNIT_TEST_CASE_DIR', $docRoot."/testing/tests-unittests/cases/");
  define('PHPUNIT_FIXTURES_DIR', $docRoot."/testing/tests-unittests/data/");
  define('PHPUNIT_DB_DUMPS_DIR', $docRoot."/testing/tests-unittests/db-dumps/");

  // CREATE TEST SUITE
  $suite = new $suiteClassName('Test Suite '.$suiteClassName);
  if (isset($_GET['__test']) && isset($testcases[$_GET['__test']])) {
    // run a single test suite
    $case = $_GET['__test'];
    $file = PHPUNIT_TEST_CASE_DIR.$testcases[$case].".php";
    include_once($file);
    $suite->addTestSuite(is_numeric($case) ? $testcases[$case] : $case);
  } else {

    // run all test suites
    foreach ($testcases as $case => $fileName){
      if (is_numeric($case)) {
        $case = $fileName;
      }
      $file = PHPUNIT_TEST_CASE_DIR.$fileName.".php";
      include_once($file);
      $suite->addTestSuite($case);
    }
  }

  // RUN PHPUNIT TESTS
  ob_start();
  PHPUnit_TextUI_TestRunner::run($suite);
  print nl2br(ob_get_clean());
}

?>