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
* @version    $Id: statisticReferer.php 32064 2009-08-17 07:58:01Z kersken $
*
*/

include_once(PAPAYA_INCLUDE_PATH.'modules/commercial/statistics/base_statistic_referer.php');

/**
* test cases for our unittest extensions
*
* @author     David Rekowski <info@papaya-cms.com>
* @package    papaya5
* @subpackage unittest
* @category   unittest
*
*/
class statisticReferer extends papayaUnitTests {

  var $newGoogleURL = "http://www.google.com/url?sa=t&source=web&ct=res&cd=7&url=http%3A%2F%2Fwww.example.com%2Fmypage.htm&ei=0SjdSa-1N5O8M_qW8dQN&rct=j&q=flowers&usg=AFQjCNHJXSUh7Vw7oubPaO3tZOzz-F-u_w&sig2=X8uCFh6IoPtnwmvGMULQfw";

  var $testURLs = array(
    array(
      'url' => 'http://www.bing.com/search?q=eine+testsuche&FORM=SSRE',
      'engine' => 'bing.com',
      'query' => 'eine testsuche',
    ),
    array(
      'url' => "http://www.google.com/url?sa=t&source=web&ct=res&cd=7&url=http%3A%2F%2Fwww.example.com%2Fmypage.htm&ei=0SjdSa-1N5O8M_qW8dQN&rct=j&q=flowers&usg=AFQjCNHJXSUh7Vw7oubPaO3tZOzz-F-u_w&sig2=X8uCFh6IoPtnwmvGMULQfw",
      'engine' => 'google',
      'query' => 'flowers',
    ),
  );

  public function test_isRobot() {
    $refererObj = base_statistic_referer::getInstance();
    $this->say('Checking search strings in URLs.');
    foreach ($this->testURLs as $entry) {
      $refererObj->parse($entry['url']);
      $this->assertEquals($refererObj->engine, $entry['engine']);
      $this->assertEquals(urldecode($refererObj->query), $entry['query']);
    }
  }

  private function say($string) {
    if (is_array($string)) {
      var_dump($string);
    } elseif (!is_object($string)) {
      echo '<br />';
      echo $string;
    }
  }

}
?>
