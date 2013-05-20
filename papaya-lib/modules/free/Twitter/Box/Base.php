<?php
/**
* Twitter Statuses Box, base class
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
* @version $Id: Base.php 37990 2013-01-18 19:15:32Z weinert $

/**
* Twitter Statuses Box Module
*
* This box module displays twitter statuses of a specified twitter user. The number of
* displayed statuses can also be speciefied in the content section of each box.
*
* @package Papaya-Modules
* @subpackage Free-Twitter
*/
class PapayaModuleTwitterBoxBase extends PapayaObject {
  /**
  * Twitter API URL
  * @var string
  */
  protected $_apiUrl = 'http://api.twitter.com/1/statuses/user_timeline.xml';

  /**
  * HTTP Client object
  * @var HTTPClient
  */
  protected $_httpClient = NULL;

  /**
  * papaya Cache object
  * @var PapayaCache service
  */
  protected $_cacheService = NULL;

  /**
  * Owner object
  * @var TwitterBox
  */
  protected $_owner = NULL;

  /**
  * Page configuration data
  * @var array
  */
  protected $_data = array();

  /**
  * Set owner object
  *
  * @param TwitterBox $owner
  */
  public function setOwner($owner) {
    $this->_owner = $owner;
  }

  /**
  * Set page configuration data
  *
  * @param array $data
  */
  public function setBoxData($data) {
    $this->_data = $data;
  }

  /**
  * Get box output XML
  *
  * @return string XML
  */
  public function getBoxXml() {
    $result = '';
    if (!empty($this->_data['title'])) {
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($this->_data['title'])
      );
    }
    $result .= sprintf(
      '<follow-link href="%s%s">%s</follow-link>',
      'http://twitter.com/',
      urlencode($this->_data['screen_name']),
      papaya_strings::escapeHTMLChars($this->_data['follow_caption'])
    );
    $apiXml = $this->getApiXml();
    if (!empty($apiXml)) {
      $dom = DOMDocument::loadXml($apiXml);
      if (isset($dom) && isset($dom->documentElement) &&
          $dom->documentElement->hasChildNodes()) {
        $statusesNode = $dom->getElementsByTagName("status");
        foreach ($statusesNode as $statusNode) {
          $created = strtotime(
            $statusNode->getElementsByTagName('created_at')->item(0)->nodeValue);
          $result .= sprintf(
            '<status id ="%s" created="%s">',
            papaya_strings::escapeHTMLChars(
              $statusNode->getElementsByTagName('id')->item(0)->nodeValue),
            papaya_strings::escapeHTMLChars(date('Y-m-d H:i:s', $created))
          );
          $text = $statusNode->getElementsByTagName('text')->item(0)->nodeValue;

          $result .= sprintf(
            '<text>%s</text>',
            $this->_owner->getXHTMLString($this->_addTwitterLinks($text))
          );
          $result .= sprintf(
            '<source>%s</source>',
            $this->_owner->getXHTMLString(
              $statusNode->getElementsByTagName('source')->item(0)->nodeValue
            )
          );
          if ($statusNode->getElementsByTagName('in_reply_to_user_id')->item(0)->nodeValue != '') {
            $result .= sprintf(
              '<reply-to user-id="%s" status-id="%s" screen-name="%s" />',
              papaya_strings::escapeHTMLChars(
                $statusNode->getElementsByTagName('in_reply_to_user_id')->item(0)->nodeValue
              ),
              papaya_strings::escapeHTMLChars(
                $statusNode->getElementsByTagName('in_reply_to_status_id')->item(0)->nodeValue
              ),
              papaya_strings::escapeHTMLChars(
                $statusNode->getElementsByTagName('in_reply_to_screen_name')->item(0)->nodeValue
              )
            );
          }
          $result .= '</status>';
        }
      }
    }
    return sprintf(
      '<twitter screen-name="%s">%s</twitter>',
      $this->_data['screen_name'],
      $result
    );
  }

  /**
  * Get API XML
  *
  * Looks for cached response xml. If the cached data is out of date, a new API request will be
  * sent, and the retrieved data will be added to the cache. If the request fails too, we'll try to
  * get older cache data as a fallback. The maximum time for the regular cache is defined in the
  * edit fields with the key <var>cache_time</var>. The fallback cache time is set to three weeks.
  *
  * @return string API XML
  */
  protected function getApiXml() {
    $cache = $this->getCacheService();
    $arrParams = array(
      'screen_name' => $this->_data['screen_name'],
      'count' => $this->_data['count'],
      'include_rts' => $this->_data['include_rts']
    );
    if (
      $xml = $cache->read(
        'twitter',
        $this->_data['screen_name'],
        $arrParams,
        $this->_data['cache_time']
      )
    ) {
      return $xml;
    }
    $client = $this->getHttpClient();
    $client->addRequestData($arrParams);
    if ($client->send() && $client->getResponseStatus() == 200) {
      $xml = $client->getResponseData();
    }
    // Fallback for broken twitter API
    if (!$xml) {
      $xml = $cache->read('twitter', $this->_data['screen_name'], $arrParams, 604800);
    }
    // Refresh cache
    if ($xml) {
      $cache->write('twitter', $this->_data['screen_name'], $arrParams, $xml);
    }
    return $xml;
  }

  /**
  * Return a HTTP client object instance
  *
  * @return PapayaHttpClient HTTP Client
  */
  protected function getHttpClient() {
    if ($this->_httpClient === NULL) {
      $this->setHttpClient(new PapayaHttpClient($this->_apiUrl));
    }
    return $this->_httpClient;
  }

  /**
  * Set the HTTP client instance
  */
  public function setHttpClient($client) {
    $this->_httpClient = $client;
  }

  /**
  * Return a cache service instance
  *
  * @return PapayaCacheService Cache Service
  */
  protected function getCacheService() {
    if ($this->_cacheService === NULL) {
      $this->setCacheService(
        PapayaCache::getService($this->papaya()->options)
      );
    }
    return $this->_cacheService;
  }

  /**
  * Set the cache service object instanz
  *
  * @return void Nothing!
  */
  public function setCacheService($service) {
    $this->_cacheService = $service;
  }

  /**
  * Add Twitter Links
  *
  * Parse a twitter status message and add links for replys, hashtags and urls.
  * What should be linked, can be configured in the Edit fields...
  *
  * @param string Text
  * @return string Text with links
  */
  protected function _addTwitterLinks($text) {
    $pattern = array();
    $replacement = array();
    if ($this->_data['link_replies'] == 1) {
      $pattern[] = "((^|[\\s])@([a-zA-Z0-9_]{2,15}))";
      $replacement[] = '$1<a class="twitterReply"'.
        ' target="_blank" href="http://twitter.com/$2">@$2</a>';
    }
    if ($this->_data['link_tags'] == 1) {
      $pattern[] = "((^|[\\s])#([^\\s.,;!?]+))";
      $replacement[] = '$1<a class="twitterHashtag"'.
        ' target="_blank" href="http://search.twitter.com/search?tag=$2">#$2</a>';
    }
    if ($this->_data['link_urls'] == 1) {
      // matches an "xxxx://yyyy" URL at the start of a line, or after a space.
      // xxxx can only be alpha characters.
      // yyyy is anything up to the first space, newline, comma, double quote or <
      $pattern[] = "(
        (^|[\\s])
        ([\w]+?://)
        (([\w\#$%&~.\-;:=,?@\[\]+]*)
        (/?)
        ([\w\#$%&~.\-;:=,?@\[\]+/]*))
        )x";
      if ($this->_data['remove_link_protocols'] == 1) {
        $replacement[] = '$1<a class="twitterLink" href="$2$3" target="_blank">$3</a>';
      } else {
        $replacement[] = '$1<a class="twitterLink" href="$2$3" target="_blank">$2$3</a>';
      }
      // matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
      // Must contain at least 2 dots. xxxx contains either alphanum, or "-"
      // zzzz is optional.. will contain everything up to the first space, newline,
      // comma, double quote or <.
      $pattern[] = "(
        (^|[\\s])
        ((www|ftp)\.[\w\#$%&~.\-;:=,?@\[\]+]*)
        (/?)
        ([\w\#$%&~.\-;:=,?@\[\]+/]*)
        )x";
      $replacement[] = '$1<a class="twitterLink" href="http://$2" target="_blank">$2</a>';
    }
    if ($this->_data['link_mailaddresses'] == 1) {
      // matches an email@domain type address at the start of a line, or after a space.
      // Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
      $pattern[] = '((^|[\\s])((?:[a-z0-9&_.-]+?)@(?:[\\pL-]+\\.)+[a-zA-Z]{2,}))u';
      $replacement[] = '$1<a class="twitterMail" href="mailto:$2">$2</a>';
    }
    return preg_replace($pattern, $replacement, $text);
  }
}