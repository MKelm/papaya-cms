<?php
/**
* generic tests for our unittest extensions
*
* PHP version 5
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    weisseliste
* @subpackage unittest
* @category   unittest
* @version    SVN: $Id: &
*
*/

/**
* test cases for our unittest extensions
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    weisseliste
* @subpackage unittest
* @category   unittest
*
*/

class genericExtensionTests extends papayaUnitTests {

  /**
  * check handling of trigger_error events
  */
  public function test_errorHandlerTriggerError() {

    // set our simple error handler
    set_error_handler(array($this, 'errorHandler'));

    // produce an trigger_error event
    $this->generateTriggerError();

    // restore old error handler
    restore_error_handler();

    // check if the error was really registered
    $this->assertNotNull($this->errorCode, "no expected error catched");

    // debug message, may be omitted
    /*printf(
      "SUCCESS:\n errorCode: %s\n errorMessage: %s\n errorFile: %s\n errorLine: %s",
      $this->errorCode,
      $this->errorMessage,
      $this->errorFile,
      $this->errorLine
    );*/
  }

  /**
  * check handling of exceptions
  * here is no error handler needed
  */
  public function test_errorHandlerExceptions() {

    // generate an exception
    try{
      $this->generateException();
    } catch (Exception $e) {
      $this->errorCode = $e->getCode();
      $this->errorMessage = $e->getMessage();
      $this->errorFile = $e->getFile();
      $this->errorLine = $e->getLine();
    }

    // check if the error was really registered
    $this->assertNotNull($this->errorCode, "no expected error catched");

    // debug message, may be omitted
    /*printf(
      "SUCCESS:\n errorCode: %s\n errorMessage: %s\n errorFile: %s\n errorLine: %s",
      $this->errorCode,
      $this->errorMessage,
      $this->errorFile,
      $this->errorLine
    );*/
  }

  /**
  * check http client, incl. backend login and logout
  */
  public function test_httpClient() {
    include_once("extensions/papayaHttpClient.php");

    $client = new papayaHttpClient("http://papaya5.paptester.papaya.local");

    // check html content
    $htmlContent = $this->stripRandomPapayaValues($client->query(36));
    $expectedHtml = $this->touchFile("index.36.html", $htmlContent);
    $this->assertEquals($expectedHtml, $htmlContent);

    // check login, logout and xml content
    $this->assertTrue($client->backendLogin("admin", "pass"));

    $xmlContent = $this->stripRandomPapayaValues($client->query("index.36.xml.preview"));

    $this->assertTrue($client->backendLogout());

    $expectedXml = $this->touchFile("index.36.xml", $xmlContent);
    $this->assertEquals($expectedXml, $xmlContent);
  }

  /**
  * ////////////// PRIVATE METHODS /////////////
  */

  /**
  * error generator for an trigegr_error event
  * @see test_errorHandlerTriggerError()
  */
  private function generateTriggerError() {
    trigger_error("TRIGGER_ERROR EVENT", E_USER_ERROR);
  }

  /**
  * error generator for an exception
  * @see test_errorHandlerExceptions()
  */
  private function generateException() {
    throw new Exception("EXCEPTION EVENT");
  }

}
?>