<?php
/**
* Papaya http client
*
* PHP version 5
*
* @copyright 2007-2009 by papaya Software GmbH - All rights reserved.
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
* base client class
*/
require_once('HTTP/Client.php');

/**
* Papaya http client
*
* @author     Viktor Gotwig <info@papaya-cms.com>
* @package    weisseliste
* @subpackage unittest
* @category   unittest
*
*/

class papayaHttpClient {

  /**
  * user agent aliases
  */
  public $userAgentAliases = array(
    "linux" => array(
      "mozilla" => 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4) Gecko/20030624',
      "konqueror" => 'Mozilla/5.0 (compatible; Konqueror/3; Linux)',
    ),
    "windows" => array(
      "ie6" => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
      "mozilla" =>
        'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4b) Gecko/20030516 Mozilla Firebird/0.6',
    ),
    "mac" => array(
      "safari" =>
        'Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en-us) AppleWebKit/85 (KHTML, like Gecko) Safari/85',
      "mozilla" =>
        'Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.4a) Gecko/20030401',
    )
  );

  /**
  * page type - html or xml.preview
  */
  protected $pageType = "html";

  /**
  * base url
  */
  protected $baseUrl = NULL;

  /**
  * events
  */
  protected $events = array(
    "response_code" => array(),
    "headers" => array(),
    "content" => array(),
    "dom" => array()
  );

  /**
  * default properties
  */
  protected $params = NULL;
  protected $defaultHeaders = array();
  protected $defaultRequestParams = array();
  protected $cookieManager = NULL;
  protected $client = NULL;
  protected $nameSpaceURI = NULL;
  protected $nameSpacePrefix = NULL;

  /**
  * dom namespace prefix
  */
  protected $defaultNameSpacePrefix = "test";

  /**
  *   ////////////////////////// HTTP CLIENT FUNCTIONS //////////////////////////
  */

  /**
  * constructor
  *
  * @param STRING $baseUrl - base URL, all following methods use relative links
  * @param ARRAY $params - different parameters for following requests
  * @param OBJECT|NULL $cookieManager - cookie manager object when given, otherwise will be created
  *
  * important parameter:
  *   User-Agent = array(_system_, _browser_) - address a user agent entry in $this->userAgentAliases
  */
  public function __construct($baseUrl, $params = array(), $cookieManager = NULL, $xDebugSession = FALSE) {

    $this->baseUrl = $this->getBaseUrl($baseUrl);
    $this->params = $params;
    $this->cookieManager = is_null($cookieManager)
      ? new HTTP_Client_CookieManager()
      : $cookieManager;

    if ($xDebugSession) {
      $this->cookieManager->addCookie(
        array(
          'name' => "XDEBUG_SESSION",
          'value' => "default",
          'domain' => preg_replace("/^\w+\:\/\//", "", $this->baseUrl),
          'path' => "/",
          'expires' => "0",
          'secure' => "0"
        )
      );
    }

    // set user agent
    list($uaSystem, $uaBrowser) = isset($params["User-Agent"])
      ? $params["User-Agent"]
      : array("linux", "mozilla");

    if (! isset($this->userAgentAliases[$uaSystem][$uaBrowser])) {
      throw new Exception("unknown user agent $uaSystem/$uaBrowser" );
    }
    $this->defaultHeaders["User-Agent"] = $this->userAgentAliases[$uaSystem][$uaBrowser];

    $this->client = new HTTP_Client(
      $this->defaultRequestParams,
      $this->defaultHeaders,
      $this->cookieManager
    );

  }

  /**
  * get base part from an url
  * @param STRING $url - a absolute url
  * @return string url base part
  */
  protected function getBaseUrl($url) {
    if (preg_match("/^((?:http|https|ftp)\:\/\/[\w\.]+)/i", $url, $r)) {
      return strtolower($r[1]);
    } else {
      trigger_error("wrong base url schema", E_USER_ERROR);
      return FALSE;
    }
  }

  /**
  * set global page type
  */
  public function setPageType($type) {
    $this->pageType = $type;
  }

  /**
  * a query to http server
  * @param STRING|NUMBER $url - a relative url or papaya topic id number
  * @param ARRAY $params - array with request values
  * @param STRING $method - get | post , default is "get"
  * @param STRING $type - optional papaya view mode type - html | xml.preview
  * @return STRING - response content
  * $type will be set to value of $this->pageType if omited
  *
  * @see usage example in test_httpClient() , tests-unittests/cases/genericExtensionTests.php
  */
  public function query($url, $params = array(), $method = "get", $type = NULL) {
    if (is_numeric($url)) {
      if (is_null($type)) {
        $type =  $this->pageType;
      }
      // url is a papaya topic id
      $url = "index.$url." . $type;
    }
    $uri = $this->baseUrl . "/" . $url;

    switch ($method) {
    case "get":
      $code = $this->client->get($uri, $params);
    break;
    case "post":
      $code = $this->client->post($uri, $params);
    break;
    default:
      return $this->failure("unknown method '{$method}'");
    }

    if (is_object($code)) {
      if (method_exists($code, "getMessage")) {
        return $this->failure("http request failed, got object " . $code->toString() .
          print_r($code, TRUE)
        );
      } else {
        return $this->failure("http request failed with unknown reason");
      }
    }

    $this->papayaViewmode = $type;
    unset($this->doc);

    return $this->getResponseContent();
  }

  /**
  * get content from last http response, also calls registered event handlers
  * @return STRING response content
  */
  public function getResponseContent() {
    $response = $this->client->currentResponse();
    $code = $response["code"];
    if (! $this->callEventHandlers("response_code", $code)) {
      return FALSE;
    }

    if (! $this->callEventHandlers("headers", $response['headers'])) {
      return FALSE;
    }

    if (! $this->callEventHandlers("content", $response['body'])) {
      return FALSE;
    }

    if (! empty($this->events["dom"])) {
      if (! $this->callEventHandlers("dom", $this->getDom($response['body']))) {
        return FALSE;
      }
    }

    return $response['body'];
  }

  /**
  * central failure routine
  * @param STRING $message - failure message
  * function may be reimplemented in derived classes
  */
  public function failure($message) {
    trigger_error($message, E_USER_ERROR);
    return FALSE;
  }

  /**
  *   ////////////////////////// SPECIAL PAPAYA FUNCTIONS //////////////////////////
  */

  /**
  * papaya backend login
  * @param STRUNG $user - login name
  * @param STRUNG $password - login password
  * @return BOOL status
  */
  public function backendLogin($user, $password) {

    // register event handlers
    $events = array();
    $events[] = $this->setEvent("response_code", array($this, "assertResponseCode"), 200);
    $events[] = $this->setEvent("headers", array($this, "assertContentType"), "text/html");
    $events[] = $this->setEvent("content", array($this, "assertNotEmpty"));

    // a dummy request to create a session
    $status = $this->query("papaya/index.php", NULL, "get", "html");

    if ($status) {
      $events[] = $this->setEvent("dom", array($this, "findLogoutLink"));
      $this->query(
          "papaya/index.php",
          array(
            'usr[login]' => 1,
            'usr[username]' => $user,
            'usr[password]' => $password
          ),
          "post",
          "html"
      );
    }

    $this->resetEventList($events);
    return (bool)$status;
  }

  /**
  * backend logout
  * @return BOOL status
  */
  public function backendLogout() {
    if (! isset($this->logOutUrl) || empty($this->logOutUrl)) {
      return $this->failure('Not loged in');
    }

    $events = array();
    $events[] = $this->setEvent("response_code", array($this, "assertResponseCode"), 200);
    $status = $this->query("papaya/".$this->logOutUrl, NULL, "get", "html");
    $this->resetEventList($events);

    return (bool)$status;
  }

  /**
  * event handler to find an logout link
  * @param DOM $document - dom tree from last request
  */
  public function findLogoutLink($document) {
    $nodeList = $this->domQuery('//a[@title="Abmelden" or @title="Log out"]', $document);
    if ($nodeList->length == 0) {
      $this->failure("no logout link found");
    }
    $this->logOutUrl = $nodeList->item(0)->getAttribute("href");
    return TRUE;
  }

  /**
  *   ////////////////////////// COMMON ASSERTION FUNCTIONS //////////////////////////
  */

  /**
  * assertion event handler for http response code
  * @param NUMBER $code - response code
  * @param MIXED $params - event parameters
  *
  */
  public function assertResponseCode($code, $params) {
    if ($code != $params[0]) {
      return $this->failure(sprintf("assertResponseCode: %d != %d", $code, $params[0]));
    }
    return TRUE;
  }

  /**
  * assertion event handler for http content type header
  * @param ARRAY $headers - response headers
  * @param MIXED $params - event parameters
  *
  */
  public function assertContentType($headers, $params) {
    $contentType = strtolower(preg_replace("/\;.+/", "", $headers['content-type']));

    if (! in_array($contentType, $params)) {
      return $this->failure(
        "Wrong content type {$contentType}, expected are " . implode(", ", $params)
      );
    }
    return TRUE;
  }

  /**
  * content assertion event handler
  * @param STRING $content - response content
  */
  public function assertNotEmpty($content) {
    if (empty($content)) {
      return $this->failure("no content returned");
    }
    return TRUE;
  }

  /**
  *   ////////////////////////// FUNCTIONS FOR EVENT HANDLING //////////////////////////
  */

  /**
  * set an event
  * @param STRING $type - event type, should be a present key in $this->events
  * @param STRING|ARRAY $callback - a function callback
  * @return ARRAY - an event identificator structure, to be used by following event functions
  * - if the function will be called with more than two parameters, the rest will be pushed
  *   as an parameter array to the event handler callback function
  */
  public function setEvent($type, $callback) {
    if (! isset($this->events[$type])) {
      throw new Exception("unknown event type " . $type);
    }
    $params = func_num_args() > 2 ? array_slice(func_get_args(), 2) : array();
    $id =  sizeof($this->events[$type]);
    $this->events[$type][$id] = array($callback, $params);
    return array($type, $id);
  }

  /**
  * reset an event
  * @param ARRAY $struct - an event identificator structure returned by setEvent()
  */
  public function resetEvent($struct) {
    list($type, $id) = $struct;
    if (! isset($this->events[$type][$id])) {
      return $this->failure("unknown event id $type/$id");
    }
    unset($this->events[$type][$id]);
  }

  /**
  * reset a list of events
  * @param ARRAY $list - a list of event identificator structures from setEvent()
  */
  public function resetEventList($list) {
    foreach($list as $event) {
      if (! empty($event)) {
        $this->resetEvent($event);
      }
    }
  }

  /**
  * call event handlers
  * @param STRING $type - event type
  * @param MIXED $value - values generated by the event
  * @return BOOL status
  * - method calls all registered event handlers until end or some of them returns FALSE
  *
  */
  protected function callEventHandlers($type, $value) {
    if (! isset($this->events[$type])) {
      throw new Exception("unknown event type " . $type);
    }
    if (!empty($this->events[$type])) {
      foreach($this->events[$type] as $event) {
        if (! $this->callEvent($event, $value)) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
  * call an event handler
  * @param ARRAY $eventStruct - internal event structure
  * @param MIXED $value - values generated by the event
  * @return BOOL status
  * - method calls a registered event handler with two parameters:
  *   $value - values generated by the event
  *   $params - extra paremeters from the event handler registration
  *
  */
  protected function callEvent($eventStruct, $value) {
    list($callback, $params) = $eventStruct;
    return call_user_func($callback, $value, $params);
  }

  /**
  *   ////////////////////////// DOM FUNCTIONS //////////////////////////
  */

  /**
  * dom query function, also automaticaly translates name space
  * @param STRING $query - xpath query
  * @param DOM $o - dom subtree, may be omited
  *
  */
  public function domQuery($query, $o = NULL) {
    $query = $this->translateNodeNameSpace($query);

    $list = isset($o)
      ?$this->xpath->query($query, $o)
      :$this->xpath->query($query);
    return $list;
  }

  /**
  *
  * dom tree debug function
  * @param DOM $node - any dom node, a subtree or complete document
  * @param NUMBER $indent - internal indentation counter, used in the recursion
  * @return STRING - a readable dump of the dom tree
  *
  */
  public function debugTree($node, $indent = 0) {
    $nodeTypes = array(
      XML_ELEMENT_NODE        => 'XML_ELEMENT_NODE',
      XML_ATTRIBUTE_NODE      => 'XML_ATTRIBUTE_NODE',
      XML_TEXT_NODE           => 'XML_TEXT_NODE',
      XML_CDATA_SECTION_NODE  => 'XML_CDATA_SECTION_NODE',
      XML_ENTITY_REF_NODE     => 'XML_ENTITY_REF_NODE',
      XML_ENTITY_NODE         => 'XML_ENTITY_NODE',
      XML_PI_NODE             => 'XML_PI_NODE',
      XML_COMMENT_NODE        => 'XML_COMMENT_NODE',
      XML_DOCUMENT_NODE       => 'XML_DOCUMENT_NODE',
      XML_DOCUMENT_TYPE_NODE  => 'XML_DOCUMENT_TYPE_NODE',
      XML_DOCUMENT_FRAG_NODE  => 'XML_DOCUMENT_FRAG_NODE',
      XML_NOTATION_NODE       => 'XML_NOTATION_NODE',
      XML_HTML_DOCUMENT_NODE  => 'XML_HTML_DOCUMENT_NODE',
      XML_DTD_NODE            => 'XML_DTD_NODE',
      XML_ELEMENT_DECL_NODE   => 'XML_ELEMENT_DECL_NODE',
      XML_ATTRIBUTE_DECL_NODE => 'XML_ATTRIBUTE_DECL_NODE',
      XML_ENTITY_DECL_NODE    => 'XML_ENTITY_DECL_NODE',
      XML_NAMESPACE_DECL_NODE => 'XML_NAMESPACE_DECL_NODE',
    );

    $resultArray = array();
    $spaces = str_repeat(" ", $indent);
    if (is_array($node)) {
      //$resultArray[] = "Array(";
      foreach ($node as $k => $subElement) {
        $resultArray[] = $k . " => ".$this->debugTree($subElement, 2);
      }
      //$resultArray[] = ")";
      $result = implode("\n", $resultArray);
      $result = preg_replace('/(.+)/m', str_repeat(" ", $indent + 2).'\\1', $result);
      return $spaces . "Array(\n" . $result . "\n" . $spaces . ")";

    } elseif (is_object($node)) {

      $class = get_class($node);
      switch ($class) {
      case "DOMNodeList":
        for ($n = 0, $m = $node->length; $n < $m; $n++) {
          $resultArray[] = $this->debugTree($node->item($n), 0);
        }
        break;
      case "DOMCdataSection":
      case "DOMText":
        $value = trim($node->nodeValue);
        if (! empty($value)) {
          $resultArray[] = "Text: '".addslashes($value)."'";
        }
        break;
      case "DOMNode":
      case "DOMElement":
      case "DOMDocument":
        $attributes = array();
        if ($node->hasAttributes() && ! is_null($node->attributes)) {
          foreach ($node->attributes as $element) {
            $attributes[] = $element->name . '="' . addslashes($element->value) .'"';
          }
        }
        $type = $nodeTypes[$node->nodeType];
        $resultArray[] = $type . ': ' . $node->nodeName . '('.implode(', ', $attributes).') {';
        if (isset($node->childNodes) && $node->childNodes->length > 0) {
          $resultArray[] = $this->debugTree($node->childNodes, 2);
          $resultArray[] = '}';
        } else {
          $resultArray[sizeof($resultArray) - 1] .= '}';
        }

        break;
      default:
        $resultArray[] = print_r($node, TRUE);
      }
    } else {
      $resultArray[] = print_r($node, TRUE);
    }

    $result = implode("\n", $resultArray);
    if ($indent > 0) {
      $result = preg_replace('/(.+)/m', $spaces . '\\1', $result);
    }

    return $result;
  }

  /**
  * reimplementation of DOMDocument::saveXML to do pretty indentation
  * @param DOM $node - any dom node, a subtree or complete document
  * @return STRING - a readable dump of the dom tree
  * @see debugTree()
  */
  public function savePrettyXML($node) {

    $resultArray = array();
    $class = get_class($node);
    switch ($class) {
    case "DOMNodeList":
      for ($n = 0, $m = $node->length; $n < $m; $n++) {
        $childText = $this->savePrettyXML($node->item($n));
        if (! empty($childText)) {
          $resultArray[] = preg_replace("/^/m", "  ", $childText);
        }
      }
      break;
    case "DOMCdataSection":
    case "DOMText":
      $value = trim($node->nodeValue);
      if (! empty($value)) {
        $resultArray[] = $value;
      }
      break;
    case "DOMNode":
    case "DOMElement":
    case "DOMDocument":
      $nodeName = $node->nodeName;
      $attrText = "";
      $attributes = array();
      if ($node->hasAttributes() && ! is_null($node->attributes)) {
        foreach ($node->attributes as $element) {
          $attributes[] = $element->name . '="' . $element->value .'"';
        }
        $attrText = " " . implode(" ", $attributes);
      }
      if (isset($node->childNodes) && $node->childNodes->length > 0) {
        $resultArray[] = sprintf("<%s%s>", $nodeName, $attrText);
        $resultArray[] = $this->savePrettyXML($node->childNodes);
        $resultArray[] = sprintf("</%s>", $nodeName);
      } else {
        $resultArray[] = sprintf("<%s%s/>", $nodeName, $attrText);
      }

      break;
    default:
      $resultArray[] = $this->doc->saveXML($node);
    }

    return implode("\n", $resultArray);
  }

  /**
  * makes DOM tree from xml/html text
  * @param STRING $text - xml or html text, may be omited
  * @return DOM|FALSE document tree or failure
  *
  */
  public function getDom($text = NULL) {
    if (! empty($this->doc)) {
      return $this->doc;
    }

    if (is_null($text)) {
      $response = $this->client->currentResponse();
      $text = $response["body"];
    }

    $this->doc = new DOMDocument();

    switch ($this->papayaViewmode) {
    case "xml.preview":
      $status = $this->doc->loadXML($text);
    break;
    case "html":
      $status = @$this->doc->loadHTML($text);
    break;
    default:
      return $this->failure("wrong dom type");
    }
    if (! $status) {
      return $this->failure("error in document text");
    }

    $this->doc->normalizeDocument();
    $this->xpath = new DOMXPath($this->doc);
    $this->nameSpaceURI = $this->doc->documentElement->lookupNamespaceURI(NULL);
    if ($this->nameSpaceURI) {
      $this->nameSpacePrefix = $this->defaultNamespacePrefix;
      if (! $this->xpath->registerNamespace(
        $this->nameSpacePrefix,
        $this->nameSpaceURI)
      ) {
        return $this->failure("Error in XML document");
      }
    } else {
      $this->nameSpacePrefix = '';
    }

    return $this->doc;

  }

  /**
  * adds the given string as prefix for the XML DOM namespaces
  *
  * @param STRING $query path to be translated
  * @return STRING  translated query
  */
  protected function translateNodeNameSpace($query) {
    if (empty($this->nameSpacePrefix)) {
      return $query;
    }
    return preg_replace("{(\/?)(\w+)}", '$1'.$this->nameSpacePrefix.':$2', $query);
  }

}

?>