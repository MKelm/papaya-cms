<?php
/**
* Base class for rss reader module.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
* GPL License:
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Rss
* @version $Id: Reader.php 37760 2012-11-30 16:34:25Z weinert $
*/

/**
* Base class for rss reader module.
*
* @package Papaya-Modules
* @subpackage Free-Rss
*/
class RssReader extends PapayaObject {

  /**
  * Configuration
  * @var PapayaConfiguration
  */
  private $_configuration = NULL;

  /**
  * Papaya Http Client
  * @var PapayaHttpClient
  */
  private $_httpClient = NULL;

  /**
  * Html purifier object.
  * @var base_htmlpurifier
  */
  private $_htmlPurifier = NULL;

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Returns the parsed rss feed as array structure.
  *
  * @param string $feedUrl
  * @return array
  */
  public function parseFeed($feedUrl) {
    $httpClient = $this->getHttpClient($feedUrl);
    $httpResult = $httpClient->send();
    if ($httpResult) {
      if ($httpClient->getResponseStatus() == 200) {
        $result = array();
        $document = new DOMDocument('1.0', 'UTF-8');
        $responseData = $httpClient->getResponseData();
        $document->loadXML($responseData);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('papaya-atom', 'http://www.w3.org/2005/Atom');
        $result['title'] = $xpath->evaluate('string(/papaya-atom:feed/papaya-atom:title)');
        $result['logo'] = $xpath->evaluate('string(/papaya-atom:feed/papaya-atom:logo)');
        $result['updated'] = $this->translateDateTime(
          $xpath->evaluate('string(/papaya-atom:feed/papaya-atom:updated)')
        );
        $result['linkPrefix'] = $xpath->evaluate(
          'string(/papaya-atom:feed/papaya-atom:link[1]/@href)'
        );
        $entryCount = $xpath->evaluate('count(/papaya-atom:feed/papaya-atom:entry)');
        if ((int)$entryCount > 0) {
          foreach ($xpath->evaluate('//papaya-atom:entry') as $entryNode) {
            $entryId = $xpath->evaluate('string(papaya-atom:id)', $entryNode);
            $result['entries'][$entryId]['title'] = trim(
              $xpath->evaluate('string(papaya-atom:title)', $entryNode)
            );
            $result['entries'][$entryId]['link'] = $this->validateURL(
              $xpath->evaluate(
                'string(papaya-atom:link[@rel="alternate" and @type="text/html"][1]/@href)',
                $entryNode
              ),
              $result['linkPrefix']
            );
            $result['entries'][$entryId]['published'] = $this->translateDateTime(
              $xpath->evaluate('string(papaya-atom:published)', $entryNode)
            );
            $result['entries'][$entryId]['author'] = trim(
              $xpath->evaluate('string(papaya-atom:author/papaya-atom:name)', $entryNode)
            );
            $result['entries'][$entryId]['content'] = $this->verifySimpleHTMLInput(
              $this->validateLinksInContent(
                $xpath->evaluate(
                  'string(papaya-atom:content)',
                  $entryNode
                ),
                $result['linkPrefix']
              )
            );
          }
          return $result;
        }
      } else {
        // HTTP response was not ok
        throw new LogicException('Invalid feed response received.');
      }
    } else {
      // HTTP request error
      throw new LogicException('Request to feed failed.');
    }
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * Set the current configuration
  *
  * @param object PapayaConfiguration instance
  */
  public function setConfiguration($configuration) {
    $this->_configuration = $configuration;
  }

  /**
  * Returns the papaya http client.
  *
  * @param string $url optional
  * @return PapayaHttpClient
  */
  public function getHttpClient($url = '') {
    if (!is_object($this->_httpClient)) {
      $this->_httpClient = new PapayaHttpClient($url);
      $this->_httpClient->setHeader('User-Agent', 'Mozilla');
    }
    return $this->_httpClient;
  }

  /**
  * Sets the http client object to be used instead of real one.
  *
  * @param object $httpClient
  */
  public function setHttpClient($httpClient) {
    $this->_httpClient = $httpClient;
  }

  /**
  * Returns the html purifier object.
  *
  * @return base_htmlpurifier
  */
  public function getHtmlPurifierObject() {
    if (is_null($this->_htmlPurifier) || !is_object($this->_htmlPurifier)) {
      $this->_htmlPurifier = new base_htmlpurifier();
      $this->_htmlPurifier->papaya($this->papaya());
      $this->_htmlPurifier->setUp(
        array(
          'HTML:Doctype' => 'XHTML 1.0 Transitional',
          'HTML:Allowed' => 'h1,h2,h3,ol,li,ul,a[title|href|name|target],em,strong,p,b,i,br,'.
                            'span,div,img[width|height|src],blockquote,pre',
          'HTML:DefinitionID' => 'papaya-rss-atom-reader'
        )
      );
      $this->_htmlPurifier->addAttribute('a', 'name', 'Text');
    }
    return $this->_htmlPurifier;
  }

  /**
  * Sets the html purifier object to be used instead of real one.
  *
  * @param object $htmlPurifierObject
  */
  public function setHtmlPurifierObject($htmlPurifierObject) {
    $this->_htmlPurifier = $htmlPurifierObject;
  }

  /**
  * Adds a domain/url prefix to given url if needed.
  *
  * @param string $content
  * @param string $urlPrefix
  * @return string
  */
  public function validateURL($url, $urlPrefix) {
    return preg_replace('(^/)', $urlPrefix, $url);
  }

  /**
  * Adds a domain/url prefix to all links and image source attributes in given content.
  *
  * @param string $content
  * @param string $urlPrefix
  * @return string
  */
  public function validateLinksInContent($content, $urlPrefix) {
    return preg_replace(
      '((href|src)="/)',
      '$1="'.$urlPrefix,
      $content
    );
  }

  /**
  * Returns the purified text.
  * @param string $text
  * @return string
  */
  public function verifySimpleHTMLInput($text) {
    $htmlPurifier = $this->getHtmlPurifierObject();
    return $htmlPurifier->purifyInput($text);
  }

  /**
  * Converts date time from iso 8601 to iso.
  * @param string $datetime
  * @return string
  */
  public function translateDateTime($datetime) {
    $timestamp = PapayaUtilDate::iso8601ToTimestamp($datetime);
    $result = ((int)$timestamp > 0) ? date('Y-m-d H:i:s', $timestamp) : '';
    return $result;
  }
}
?>