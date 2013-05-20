<?php
/**
* Session object
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: sys_session.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Basic class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');

/**
* Session managment
*
* @package Papaya
* @subpackage Core
*/
class rewrite_session extends base_object {
  /**
  * Session ID
  * @var string $sessionId
  */
  var $sessionId = '';
  /**
  * Session name starting with "sid"
  * @var string $sessionName
  */
  var $sessionName = 'sid';

  /**
  * the session pattern match group
  * @var string
  */
  var $sessionPattern = '([a-zA-Z\d,-]{20,40})';

  /**
  * Session-ID mode
  * @var string $sessionMode
  */
  var $sessionMode = '';

  /**
  * session started
  * @var boolean
  */
  protected $_active = FALSE;

  /**
  * session singleton handler
  *
  * @staticvar object rewrite_session $session
  * @access public
  * @return object rewrite_session
  */
  function &getInstance() {
    static $session;
    if (!(isset($session) && is_object($session))) {
      $session = new rewrite_session();
    }
    return $session;
  }

  /**
  * Reset data
  *
  * @access public
  */
  function resetData() {
    session_unset();
    $_SESSION = array();
  }

  /**
  * Regenerate SessionID
  *
  * @param string $redirectionURL optional url to redirect
  * @access public
  */
  function regenerateSID($redirectionURL = NULL, $queryString = NULL) {
    if ($this->isActive()) {
      $oldSession = $_SESSION;
      if (function_exists('session_regenerate_id')) {
        session_regenerate_id();
        $this->sessionId = session_id();
      }
      if ($this->checkSIDString($this->sessionId)) {
        $_SESSION = $oldSession;
        $this->reloadPage(TRUE, $redirectionURL, $queryString, 'Session id, regenerated.');
      }
    }
  }

  /**
  * Check session id
  *
  * @access public
  * @return boolean
  */
  function checkSID($allowStart = FALSE) {
    $sid = FALSE;
    if ((isset($_SERVER['X-SESSION-COOKIE']) && $_SERVER['X-SESSION-COOKIE'] == 'false') ||
        (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'Shockwave Flash')) {
      $allowCookie = FALSE;
    } else {
      $allowCookie = TRUE;
    }
    if ($allowCookie && $sid = $this->checkSIDCookie()) {
      //SID in cookie
      if ($this->checkSIDPath() ||
          ((!$this->disableTransSID()) && $this->checkSIDGETParam())) {
        //erase SID from path/parameter
        if ($this->allowRedirects) {
          $this->reloadPage(FALSE, NULL, NULL, 'Cookie found, remove fallback');
        }
      } else {
        $this->sessionId = $sid;
      }
    } elseif ($this->fallbackMode == 'rewrite') {
      if ($sid = $this->checkSIDPath()) {
        //pathmode
        if ((!$this->disableTransSID()) && $this->checkSIDGETParam()) {
          //erase SID from path/parameter
          if ($this->allowRedirects) {
            $this->reloadPage(TRUE, 'Rewrite fallback, remove get parameter');
          }
        } else {
          $this->sessionId = $sid;
        }
      } else {
        if ($this->allowRedirects && $allowStart) {
          $this->start();
          $this->reloadPage(TRUE, NULL, NULL, 'No Cookie, activate rewrite fallback');
        }
      }
    } elseif ($this->fallbackMode == 'get') {
      //GET-modus (Trans-SID) - Danger! incompatible with caching
      if (!($sid = $this->checkSIDParam())) {
        if ($allowStart && $this->allowRedirects) {
          $this->start();
          $this->reloadPage(TRUE, NULL, NULL, 'No cookie, activate get parameter fallback');
        }
      } else {
        $this->sessionId = $sid;
      }
    } else {
      $this->disableTransSID();
      return FALSE;
    }
    return TRUE;
  }

  /**
  * Get SID out of cookie
  *
  * @access public
  * @return string|FALSE session id or boolean
  */
  function checkSIDCookie() {
    if (isset($_COOKIE[$this->sessionName])) {
      $sessionId = $_COOKIE[$this->sessionName];
      $pattern = '((?:^|;\s*)'.preg_quote($this->sessionName).'=)';
      if (!empty($_SERVER['HTTP_COOKIE']) &&
          substr_count($_SERVER['HTTP_COOKIE'], $this->sessionName.'=') > 1 &&
          preg_match_all($pattern, $_SERVER['HTTP_COOKIE'], $cookieMatches, PREG_SET_ORDER)) {
        //cookies count > 1 - goto fallback
        if (count($cookieMatches) > 1) {
          return FALSE;
        }
      }
      if ($this->checkSIDString($sessionId)) {
        return $sessionId;
      }
    }
    return FALSE;
  }

  /**
  * Check SID out of string
  *
  * @param string $sessionId
  * @access public
  * @return boolean
  */
  function checkSIDString($sessionId) {
    $pattern = '~^'.$this->sessionPattern.'$~';
    if (preg_match($pattern, $sessionId)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Get SID out of Parmeter
  *
  * @access public
  * @return string|FALSE Session id or FALSE
  */
  function checkSIDParam() {
    if ($sessionId = $this->checkSIDGETParam()) {
      return $sessionId;
    } elseif ($sessionId = $this->checkSIDPOSTParam()) {
      return $sessionId;
    }
    return FALSE;
  }

  /**
  * Check SID out of get parameter
  *
  * @access public
  * @return string|FALSE session id or boolean
  */
  function checkSIDGETParam() {
    if (isset($_GET[$this->sessionName])) {
      $sessionId = $_GET[$this->sessionName];
      if ($this->checkSIDString($sessionId)) {
        return $sessionId;
      }
    }
    return FALSE;
  }

  /**
  * Check SID out of post parameter
  *
  * @access public
  * @return string|FALSE session id or boolean
  */
  function checkSIDPOSTParam() {
    if (isset($_POST[$this->sessionName])) {
      $sessionId = $_POST[$this->sessionName];
      if ($this->checkSIDString($sessionId)) {
        return $sessionId;
      }
    }
    return FALSE;
  }

  /**
  * Get SID out of path
  *
  * @access public
  * @return string|FALSE
  */
  function checkSIDPath() {
    $pattern = '{^/'.preg_quote($this->sessionName).$this->sessionPattern.'}i';
    if (preg_match($pattern, $_SERVER['REQUEST_URI'], $regs)) {
      return $regs[1];
    }
    return FALSE;
  }

  /**
  * Disable trans SID
  *
  * @access public
  * @return string $transSid
  */
  function disableTransSID() {
    @ini_set('session.use_trans_sid', '0');
    if ($transSid = @ini_get('session.use_trans_sid')) {
      $transSid = (!@ini_set('url_rewriter.tags', ''));
    }
    return $transSid;
  }

  /**
  * Reload page
  *
  * @param string $sid session id
  * @param string $redirectionURL optional url to redirect
  * @access public
  */
  function reloadPage($sid, $redirectionURL = NULL, $queryString = NULL, $reason = NULL) {
    $queryParams = array();
    if ($redirectionURL) {
      $destination = $redirectionURL;
    } else {
      $protocol = PapayaUtilServerProtocol::get();
      $host = $protocol.'://'.$_SERVER['HTTP_HOST'];
      $file = $_SERVER['REQUEST_URI'];
      if (0 === strpos($file, '/'.$this->sessionName)) {
        $posSidEnd = strpos($file, '/', 1);
        if (FALSE !== $posSidEnd) {
          $file = substr($file, $posSidEnd);
        }
      }
      $posQueryString = strpos($file, '?');
      if (FALSE !== $posQueryString) {
        $file = substr($file, 0, $posQueryString);
      }

      if ($sid) {
        if (isset($this->sessionId) && $this->checkSIDString($this->sessionId)) {
          if ($this->fallbackMode == 'rewrite') {
            $queryParams[$this->sessionName] = NULL;
            $destination = $host.'/'.$this->sessionName.$this->sessionId.$file;
          } else {
            $queryParams[$this->sessionName] = $this->sessionId;
            $destination = $host.$file;
          }
        } else {
          return FALSE;
        }
        $this->close();
      } else {
        $queryParams[$this->sessionName] = NULL;
        $destination = $host.$file;
      }
      if (isset($GLOBALS['PAPAYA_PAGE']) && is_object($GLOBALS['PAPAYA_PAGE'])) {
        $GLOBALS['PAPAYA_PAGE']->logRequest();
      }
      unset($_GET['preview']);
      if (isset($_SERVER['HTTP_USER_AGENT']) &&
          FALSE !== strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') &&
          FALSE !== strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        $queryParams['random'] = substr(md5(uniqid(rand() * 10000)), 0, 6);
      }
    }
    $currentQueryString = $_SERVER['QUERY_STRING'];
    if (!empty($queryString)) {
      $destination .= $queryString;
      $currentQueryString = substr($this->recodeQueryString($currentQueryString, $queryParams), 1);
      if (!empty($currentQueryString)) {
        $destination .= '&'.$currentQueryString;
      }
    } elseif (isset($_GET) && is_array($_GET) && count($_GET) > 0) {
      $destination .= $this->recodeQueryString($currentQueryString, $queryParams);
    }
    if (headers_sent()) {
      $message = 'Can not redirect to: "'.$destination.'".';
      if (!empty($reason)) {
        $message .= ' ('.$reason.')';
      }
      echo $message;
    } else {
      if (php_sapi_name() == 'cgi') {
        header('Status: 302 Found');
      } elseif (php_sapi_name() == 'cgi-fcgi' || php_sapi_name() == 'fast-cgi') {
        header('HTTP/1.1 302 Found');
        header('Status: 302 Found');
      } else {
        header('HTTP/1.1 302 Found');
      }
      if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
        header('X-Papaya-Status: initializing session');
        if (defined('PAPAYA_DBG_DEVMODE') && PAPAYA_DBG_DEVMODE && !empty($reason)) {
          header('X-Papaya-Redirect-Note: '.$reason);
        }
      }
      @session_cache_limiter('nocache');
      header('Location: '.$destination);
      header('Expires: '.gmdate('D, d M Y H:i:s', (time() - 31536000)).' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
      header('Pragma: no-cache');
    }
    exit();
  }

  /**
  * Start session
  *
  * @param string $name optional, default value ""
  * @param string $fallbackMode optional, default value 'rewrite'
  * @param boolean $redirect allow http redirects for session initialization
  * @param string $cache
  *
  * @access public
  */
  function initialize($name = "", $fallbackMode = '', $redirect = FALSE, $cache = 'private') {
    if (!$this->isActive()) {
      if (empty($name) && defined('PAPAYA_SESSION_NAME')) {
        $name = PAPAYA_SESSION_NAME;
      }
      if (empty($fallbackMode)) {
        $fallbackMode = defined('PAPAYA_SESSION_ID_FALLBACK') ?
        PAPAYA_SESSION_ID_FALLBACK : 'rewrite';
      }
      $this->fallbackMode = $fallbackMode;
      $this->allowRedirects = $redirect;
      $this->allowCache = $cache;
      if ($this->sessionAllowed()) {
        $this->sessionName = 'sid'.$name;
        $this->checkSID(TRUE);
        $this->start();
      }
    }
  }

  function start() {
    if (!$this->isActive()) {
      $cookieParams = session_get_cookie_params();
      if (defined('PAPAYA_SESSION_DOMAIN') && trim(PAPAYA_SESSION_DOMAIN) != '') {
        $cookieParams['domain'] = PAPAYA_SESSION_DOMAIN;
      }
      if (defined('PAPAYA_SESSION_PATH') && trim(PAPAYA_SESSION_PATH) != '') {
        $cookieParams['path'] = PAPAYA_SESSION_PATH;
      }
      if (defined('PAPAYA_SESSION_SECURE') && PAPAYA_SESSION_SECURE) {
        $cookieParams['secure'] = (bool)PAPAYA_SESSION_SECURE;
      } elseif (defined('PAPAYA_ADMIN_SESSION') && PAPAYA_ADMIN_SESSION &&
      defined('PAPAYA_UI_SECURE')) {
        $cookieParams['secure'] = (bool)PAPAYA_UI_SECURE;
      }
      if (defined('PAPAYA_SESSION_HTTP_ONLY')) {
        $cookieParams['httponly'] = (bool)PAPAYA_SESSION_HTTP_ONLY;
      }
      if (version_compare(PHP_VERSION, '5.2.0', '>=') &&
      isset($cookieParams['httponly'])) {
        session_set_cookie_params(
        $cookieParams['lifetime'],
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure'],
        $cookieParams['httponly']
        );
      } else {
        session_set_cookie_params(
        $cookieParams['lifetime'],
        $cookieParams['path'],
        $cookieParams['domain'],
        $cookieParams['secure']
        );
      }
      session_name($this->sessionName);
      if (isset($this->sessionId) && $this->checkSIDString($this->sessionId)) {
        session_id($this->sessionId);
      }
      header('P3P: CP="NOI NID ADMa OUR IND UNI COM NAV"');
      if (defined('PAPAYA_ADMIN_PAGE_STATIC') && PAPAYA_ADMIN_PAGE_STATIC) {
        session_cache_limiter('private_no_expire');
      } elseif (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE ||
      defined('PAPAYA_ADMIN_SESSION') && PAPAYA_ADMIN_SESSION) {
        session_cache_limiter('nocache');
      } else {
        session_cache_limiter($this->allowCache);
      }
      session_start();
      $this->sessionId = session_id();
      $this->_active = TRUE;
    }
  }

  /**
  * Session permitted? - no sessions for robots
  *
  * @access public
  * @return boolean
  */
  function sessionAllowed() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_robots.php');
    if (!$this->sessionProtocolAllowed() || base_robots::checkRobot()) {
      $pattern = '{^/'.preg_quote($this->sessionName).$this->sessionPattern.'}i';
      if ($this->allowRedirects &&
          isset($_SERVER['REQUEST_URI']) &&
          preg_match($pattern, $_SERVER['REQUEST_URI'], $regs)) {
        // session not permitted but sid in url - remove it
        $this->reloadPage(FALSE, NULL, NULL, 'No session, remove sid from url');
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
  * check if session for this protocol allowed
  *
  * @access public
  * @return boolean
  */
  function sessionProtocolAllowed() {
    if ((defined('PAPAYA_SESSION_SECURE') && PAPAYA_SESSION_SECURE)) {
      if (PapayaUtilServerProtocol::isSecure()) {
        return TRUE;
      } else {
        return FALSE;
      }
    } elseif (defined('PAPAYA_ADMIN_PAGE') && PAPAYA_ADMIN_PAGE &&
              defined('PAPAYA_UI_SECURE') && PAPAYA_UI_SECURE) {
      if (PapayaUtilServerProtocol::isSecure()) {
        return TRUE;
      } else {
        return FALSE;
      }
    } else {
      return TRUE;
    }
  }

  /**
  * Close a session - for current request
  *
  */
  function close() {
    if ($this->isActive()) {
      session_write_close();
      $this->_active = FALSE;
    }
  }

  /**
  * Destroy session
  *
  * @param string $url calling URL
  * @access public
  */
  function destroy() {
    if (isset($_SESSION) && is_array($_SESSION) && count($_SESSION) > 0) {
      foreach ($_SESSION as $key => $val) {
        unset($_SESSION[$key]);
      }
    }
    // destroy
    @session_unset();
    @session_destroy();
    // Where do you want to go now?
    if (php_sapi_name() == 'cgi') {
      header('Status: 302 Found');
    } elseif (php_sapi_name() == 'cgi-fcgi' || php_sapi_name() == 'fast-cgi') {
      header('HTTP/1.1 302 Found');
      header('Status: 302 Found');
    } else {
      header('HTTP/1.1 302 Found');
    }
    if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
      header('X-Papaya-Status: session destroy');
    }
    header('Location: '.$this->getAbsoluteURL($this->getBaselink(), FALSE));
  }

  /**
  * Store variable in session
  *
  * @param string $identifier variable name
  * @param string $value value variable
  * @access public
  */
  public function setValue($identifier, $value) {
    if ($this->isActive()) {
      $_SESSION[$this->_getSessionParameterName($identifier)] = $value;
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Get variable either in session
  *
  * @param string|array|object $identifier
  * @access public
  */
  public function getValue($identifier, $defaultValue = NULL, $filter = NULL) {
    $result = NULL;
    if (isset($_SESSION) &&
        is_array($_SESSION)) {
      $name = $this->_getSessionParameterName($identifier);
      if (isset($_SESSION[$name])) {
        $result = $_SESSION[$name];
      }
    }
    if (isset($filter)) {
      $result = $filter->filter($value);
    }
    if (is_null($result)) {
      return $defaultValue;
    } elseif (isset($defaultValue)) {
      settype($result, gettype($defaultValue));
    }
    return $result;
  }

  /**
  * Remove a session variable
  *
  * @param string|array|object $identifier
  */
  public function unsetValue($identifier) {
    if ($this->isActive()) {
      $name = $this->_getSessionParameterName($identifier);
      if (isset($_SESSION[$name])) {
        unset($_SESSION[$name]);
      }
    }
  }

  /**
  * Compile a session parameter name from the identifier
  * @param string|array|object $identifier
  */
  private function _getSessionParameterName($identifier) {
    if (is_array($identifier)) {
      $result = '';
      foreach ($identifier as $part) {
        if (is_object($part)) {
          $result .= '_'.get_class($part);
        } elseif (is_array($part)) {
          $result .= '_'.md5(serialize($part));
        } else {
          $result .= '_'.((string)$part);
        }
      }
      return substr($result, 1);
    } elseif (is_object($identifier)) {
      return get_class($identifier);
    } elseif (is_string($identifier)) {
      return (string)$identifier;
    }
  }

  /**
  * Return TRUE if the session is started, FALSE if not.
  *
  * @return boolean
  */
  public function isActive() {
    return (bool)$this->_active;
  }

  /**
  * Check if the session identifier is in the uri and need to be stripped from it before redirect to
  * external pages.
  *
  * @return boolean
  */
  public function isIdentifierInUri() {
    $id = $this->papaya()->request->getParameter(
      'session', '', NULL, PapayaRequest::SOURCE_PATH
    );
    if (empty($id)) {
      $id = $this->papaya()->request->getParameter(
        $this->sessionName, '', NULL, PapayaRequest::SOURCE_QUERY
      );
    }
    return !empty($id);
  }
}
?>
