<?php
/**
* simple tests for module xml output fixtures
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
* base class
*/
require_once("extensions/papayaHttpClient.php");

/**
* simple tests for module xml output fixtures
*
* ATTENTION: those tests rely on an special content setup,
* to adapt to your own please change settings in test suite "papayaModules.php"
* please set PAPAYA_LOGIN_CHECKTIME to minimum if you want test community login failures
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    weisseliste
* @subpackage unittest
* @category   unittest
*
*/

class papayaModulesXmlFixtures extends papayaUnitTests {

  /**
  * start page
  */
  public function test_startPage() {

    $client = new papayaHttpClient($this->sharedFixture["papaya_base_url"]);

    $this->assertTrue(
      $client->backendLogin(
        $this->sharedFixture["papaya_backend_user"],
        $this->sharedFixture["papaya_backend_password"]
      )
    );

    $this->assertTrue((bool)$client->query(
        $this->sharedFixture["pages"]["start"],
        NULL,
        "get",
        "xml.preview"
      )
    );

    $content = $this->getContentXml($client);

    $this->assertTrue($client->backendLogout());

    $expected = $this->touchFile("startPage.xml", $content);
    $this->assertEquals($expected, $content);
  }

  /**
  * community login page
  */
  public function test_communityLoginPage() {

    $client = new papayaHttpClient($this->sharedFixture["papaya_base_url"]);

    $this->assertTrue(
      $client->backendLogin(
        $this->sharedFixture["papaya_backend_user"],
        $this->sharedFixture["papaya_backend_password"]
      )
    );

    // login page
    $this->assertTrue((bool)$client->query(
        $this->sharedFixture["pages"]["communityLogin"],
        NULL,
        "get",
        "xml.preview"
      )
    );
    $loginContent = $this->getContentXml($client);
    $loginExpected = $this->touchFile("communityLogin.xml", $loginContent);

    // password request page
    $this->assertTrue((bool)$client->query(
        $this->sharedFixture["pages"]["communityLogin"],
        array("surf[newpwd]" => 1),
        "get",
        "xml.preview"
      )
    );
    $sendPwdContent = $this->getContentXml($client);
    $sendPwdExpected = $this->touchFile("communitySendPwd.xml", $sendPwdContent);

    $this->assertTrue($client->backendLogout());

    $this->assertEquals($loginExpected, $loginContent);
    $this->assertEquals($sendPwdExpected, $sendPwdContent);
  }

  /**
  * login garbage provider
  */
  public static function loginGarbage() {
    return array(
      array("WrongUser", "scacd", ""),
      array("WrongPassword", "", "wqeq"),
    );
  }

  /**
  * failed community login
  * @dataProvider loginGarbage
  */
  public function test_communityLoginFailure($type, $userGarbage, $passGarbage) {
    $client = new papayaHttpClient($this->sharedFixture["papaya_base_url"]);

    $this->assertTrue(
      $client->backendLogin(
        $this->sharedFixture["papaya_backend_user"],
        $this->sharedFixture["papaya_backend_password"]
      )
    );

    // login failure
    $this->assertTrue((bool)$client->query(
        $this->sharedFixture["pages"]["communityLogin"],
        array(
          "surf" => array(
            "email" => $this->sharedFixture["papaya_frontend_user_email"].$userGarbage,
            "password" => $this->sharedFixture["papaya_frontend_password"].$passGarbage
          )
        ),
        "post",
        "xml.preview"
      )
    );

    $failedLoginContent = $this->getContentXml($client);
    $failedLoginExpected = $this->touchFile("communityLogin$type.xml", $failedLoginContent);

    $this->assertTrue($client->backendLogout());

    $this->assertEquals($failedLoginExpected, $failedLoginContent);
  }

  /**
  * successfull community login
  */
  public function test_communityLoginSuccess() {

    $client = new papayaHttpClient($this->sharedFixture["papaya_base_url"]);

    $this->assertTrue(
      $client->backendLogin(
        $this->sharedFixture["papaya_backend_user"],
        $this->sharedFixture["papaya_backend_password"]
      )
    );

    // login success
    $this->assertTrue((bool)$client->query(
        $this->sharedFixture["pages"]["communityLogin"],
        array(
          "surf" => array(
            "email" => $this->sharedFixture["papaya_frontend_user_email"],
            "password" => $this->sharedFixture["papaya_frontend_password"]
          )
        ),
        "post",
        "xml.preview"
      )
    );

    $successLoginContent = $this->getContentXml($client);
    $successLoginExpected = $this->touchFile("communityLoginSuccess.xml", $successLoginContent);

    $this->assertTrue($client->backendLogout());

    $this->assertEquals($successLoginExpected, $successLoginContent);
  }

  /**
  * method handler to get papaya content xml
  */
  public function getContentXml($client) {
    $subtree = $client->domQuery("//content", $client->getDom());
    if ($subtree->length == 0) {
      return "";
    }
    return $this->stripRandomPapayaValues(
      $client->savePrettyXML($subtree->item(0))
    );
  }
}
?>