<?php
/**
* domain management basic class
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
* @version $Id: base_domains.php 37870 2012-12-18 08:25:04Z weinert $
*/

/**
* No special handling
*/
define('PAPAYA_DOMAIN_MODE_DEFAULT', PapayaContentDomain::MODE_DEFAULT);
/**
* redirect to another domain - keep request uri
*/
define('PAPAYA_DOMAIN_MODE_DOMAIN', PapayaContentDomain::MODE_REDIRECT_DOMAIN);
/**
* redirct to a specific page on antoher domain
*/
define('PAPAYA_DOMAIN_MODE_PAGE', PapayaContentDomain::MODE_REDIRECT_PAGE);
/**
* redirect to a start page in a specific language
*/
define('PAPAYA_DOMAIN_MODE_LANG', PapayaContentDomain::MODE_REDIRECT_LANGUAGE);
/**
* restrict access to a part of the page tree
*/
define('PAPAYA_DOMAIN_MODE_TREE', PapayaContentDomain::MODE_VIRTUAL_DOMAIN);

/**
* Basic class database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* domain management basic class
*
* @package Papaya
* @subpackage Core
*/
class base_domains extends base_db {
  /**
  * Papaya database table urls
  * @var string $tableAliases
  */
  var $tableDomains = '';

  /**
  * loaded domain data
  * @var array | NULL $domainData
  */
  var $domainData = NULL;

  /**
  * domain options
  * @var array
  */
  var $domainOptions = array(
    'PAPAYA_CDN_THEMES' => 'isHTTPX',
    'PAPAYA_CDN_THEMES_SECURE' => 'isHTTPX',

    'PAPAYA_PAGEID_DEFAULT' => 'isNum',
    'PAPAYA_PAGEID_USERDATA' => 'isNum',
    'PAPAYA_PAGEID_STATUS_301' => 'isNum',
    'PAPAYA_PAGEID_STATUS_302' => 'isNum',
    'PAPAYA_PAGEID_ERROR_403' => 'isNum',
    'PAPAYA_PAGEID_ERROR_404' => 'isNum',
    'PAPAYA_PAGEID_ERROR_500' => 'isNum',

    'PAPAYA_URL_FIXATION' => 'isNum',
    'PAPAYA_URL_LEVEL_SEPARATOR' => '(^.?$)',
    'PAPAYA_URL_ALIAS_SEPARATOR' => '(^.?$)',

    'PAPAYA_CACHE_DATA' => 'isNum',
    'PAPAYA_CACHE_DATA_TIME' => 'isNum',
    'PAPAYA_CACHE_TIME_OUTPUT' => 'isNum',
    'PAPAYA_CACHE_TIME_BOXES' => 'isNum',
    'PAPAYA_CACHE_BOXES' => 'isNum',
    'PAPAYA_CACHE_TIME_PAGES' => 'isNum',
    'PAPAYA_CACHE_PAGES' => 'isNum',
    'PAPAYA_CACHE_TIME_BROWSER' => 'isNum',
    'PAPAYA_CACHE_TIME_FILES' => 'isNum',
    'PAPAYA_COMPRESS_OUTPUT' => 'isNum',
    'PAPAYA_COMPRESS_CACHE_OUTPUT' => 'isNum',
    'PAPAYA_COMPRESS_CACHE_THEMES' => 'isNum',

    'PAPAYA_CONTENT_LANGUAGE' => 'isNum',
    'PAPAYA_SESSION_START' => 'isNum',
    'PAPAYA_SESSION_DOMAIN' => 'isHTTPHost',
    'PAPAYA_SESSION_PATH' => 'isPath',
    'PAPAYA_SESSION_CACHE' => 'isAlpha',
    'PAPAYA_SESSION_ID_FALLBACK' => 'isAlpha',

    'PAPAYA_LAYOUT_TEMPLATES' => 'isAlphaNum',
    'PAPAYA_LAYOUT_THEME' => 'isAlphaNum',
    'PAPAYA_LAYOUT_THEME_SET' => 'isAlphaNum',

    'PAPAYA_PAGE_STATISTIC' => 'isNum',

    'PAPAYA_MAX_UPLOAD_SIZE' => 'isNum',
    'PAPAYA_THUMBS_FILETYPE' => 'isNum',
    'PAPAYA_THUMBS_JPEGQUALITY' => 'isNum',
    'PAPAYA_THUMBS_BACKGROUND' => 'isHTMLColor',
    'PAPAYA_THUMBS_TRANSPARENT' => 'isNum'
  );

  /**
  * initialize object data
  *
  * @access public
  */
  function __construct() {
    $this->tableDomains = PAPAYA_DB_TABLEPREFIX.'_domains';
  }

  /**
  * Load
  *
  * @param mixed $hosts
  * @access protected
  * @return mixed array row or boolean FALSE
  */
  function load($hosts, $protocol) {
    $filter = str_replace(
      '%', '%%', $this->databaseGetSQLCondition('domain_hostname', $hosts)
    );
    $sql = "SELECT domain_id, domain_hostname, domain_hostlength,
                   domain_protocol, domain_mode,
                   domain_data, domain_options
              FROM %s
             WHERE $filter
               AND domain_protocol IN (%d, 0)
             ORDER BY domain_hostlength DESC";
    $params = array($this->tableDomains, $protocol);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      $domains = array();
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $domains[$row['domain_hostname']] = $row;
      }
      if (count($domains) == 1) {
        return reset($domains);
      } elseif (count($domains) > 0) {
        for ($i = count($hosts) - 1; $i >= 0; $i--) {
          if (isset($domains[$hosts[$i]])) {
            return $domains[$hosts[$i]];
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * get configuration for the current domain
  *
  * @access protected
  * @return mixed domain data array or FALSE
  */
  function checkDomain($hostName = NULL, $protocol = NULL) {
    $domainData = NULL;
    if (!isset($hostName)) {
      $hostName = $this->getHostName();
    }
    if (isset($protocol) && $protocol > 0 && $protocol <= 2) {
      $protocol = (int)$protocol;
    } elseif (PapayaUtilServerProtocol::isSecure()) {
      $protocol = 2;
    } else {
      $protocol = 1;
    }
    if (!empty($hostName) && is_int($protocol)) {
      //check for the exact hostname
      if (!($domainData = $this->load($hostName, $protocol))) {
        //not found - split the hostname
        $hostParts = explode('.', $hostName);
        //does it have more then two parts?
        if (is_array($hostParts) && count($hostParts) > 1) {
          $hostNames[] = '*';
          $hostParts = array_reverse($hostParts);
          //last to parts of the hostname to the buffer
          $buffer = $hostParts[0];
          $tldLength = strlen($hostParts[0]);
          for ($i = 1; $i < count($hostParts); $i++) {
            //prefix hostname parts in buffer with a "*." and replace tld with *
            if ($i > 1) {
              $hostNames[] = '*.'.substr($buffer, 0, -1 * $tldLength).'*';
              $hostNames[] = $hostParts[$i].'.'.substr($buffer, 0, -1 * $tldLength).'*';
            }
            //prefix hostname parts in buffer with a "*."
            $hostNames[] = '*.'.$buffer;
            //add hostname part to the buffer
            $buffer = $hostParts[$i].'.'.$buffer;
          }
          //try to load domaindata for a list of wildcard domains like *.domain.tld
          $domainData = $this->load($hostNames, $protocol);
        }
      }
    }
    return $domainData;
  }

  /**
  * handle current domain
  *
  * @access public
  */
  function handleDomain() {
    // get domain data
    if ($domainData = $this->checkDomain()) {
      $this->domainData = $domainData;
      switch ($domainData['domain_mode']) {
      case PAPAYA_DOMAIN_MODE_DOMAIN :
        // redirect to the same request uri on a different domain
        $hostName = $this->getHostName();
        $protocol = $this->getHTTPProtocol();
        //get request uri and check it for linebreaks (http header injection)
        if (isset($_SERVER['REQUEST_URI']) &&
            FALSE === strpos($_SERVER['REQUEST_URI'], "\n")) {
          $paramString = $_SERVER['REQUEST_URI'];
        } else {
          $paramString = '';
        }
        $domain = $domainData['domain_data'];
        if (substr($domain, -1) == '/') {
          $domain = substr($domain, 0, -1);
        }
        // target domain no protocol defined
        if (FALSE === strpos($protocol, '://')) {
          if ($hostName != $domain) {
            //use current protocol
            $url = $protocol.'://'.$domain.$paramString;
            if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
              header('X-Papaya-Status: domain redirect');
            }
            header('Location: '.$url);
            exit;
          }
        } elseif ($protocol.'://'.$hostName != $domain) {
          //use target domain protocol
          $url = $domain.$paramString;
          if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
            header('X-Papaya-Status: domain redirect');
          }
          header('Location: '.$url);
          exit;
        }
        break;
      case PAPAYA_DOMAIN_MODE_PAGE :
        //redirect to another page on a different domain
        if (isset($domainData['domain_data'])) {
          $hostName = $this->getHostName();
          $protocol = $this->getHTTPProtocol();
          if (empty($domainData['domainProtocol'])) {
            $targetUrl = $protocol.'://'.strtolower($domainData['domain_data']);
          } else {
            $targetUrl = strtolower($domainData['domain_data']);
          }
          $checkDomain = 0 !== strpos($targetUrl, $protocol.'://'.$hostName);
          if ($checkDomain &&
              checkit::isHTTPX($targetUrl, TRUE)) {
            if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
              header('X-Papaya-Status: domain page redirect');
            }
            header('Location: '.$targetUrl);
            exit;
          }
        }
        break;
      case PAPAYA_DOMAIN_MODE_LANG :
        //redirect to the startpage of the domain specific default language
        if (isset($_SERVER['REQUEST_URI'])) {
          if ($_SERVER['REQUEST_URI'] == '' ||
              $_SERVER['REQUEST_URI'] == '/' ||
              preg_match('~/index\.\w+$~i', $_SERVER['REQUEST_URI'], $match)) {
            if (preg_match('~(\w+)(?:\.(\w+))(\.\w+)$~', $domainData['domain_data'], $matches)) {
              $ext = (!empty($matches[3])) ? urlencode($matches[3]) : '.html';
              $lng = urlencode($matches[2]);
              $hostName = $this->getHostName();
              $protocol = $this->getHTTPProtocol();
              $url = $protocol.'://'.$hostName.'/index.'.$lng.$ext;
              if (!(defined('PAPAYA_DISABLE_XHEADERS') && PAPAYA_DISABLE_XHEADERS)) {
                header('X-Papaya-Status: domain language redirect');
              }
              header('Location: '.$url);
              exit;
            }
          }
        }
        break;
      case PAPAYA_DOMAIN_MODE_TREE:
        //restrict output to a part of the page tree
        if (isset($domainData['domain_data']) && $domainData['domain_data'] > 0) {
          if (!defined('PAPAYA_PAGEID_DOMAIN_ROOT')) {
            define('PAPAYA_PAGEID_DOMAIN_ROOT', (int)$domainData['domain_data']);
          }
        }
        //load domain options
        if (isset($domainData['domain_options']) && trim($domainData['domain_options']) != '') {
          $this->papaya()->options->assign(
            PapayaUtilStringXml::unserializeArray($domainData['domain_options'])
          );
        }
        break;
      case PAPAYA_DOMAIN_MODE_DEFAULT :
      default :
        //just do nothing :-)
        return;
      }
    }
  }

  /**
  * get current host name / domain
  *
  * @access private
  * @return string
  */
  function getHostName() {
    if (isset($_SERVER['HTTP_HOST'])) {
      return strtolower($_SERVER['HTTP_HOST']);
    } elseif (isset($_SERVER['SERVER_NAME'])) {
      return strtolower($_SERVER['SERVER_NAME']);
    } else {
      return '';
    }
  }

  /**
  * get the request http protocol (http or https)
  *
  * @access public
  * @return string
  */
  function getHTTPProtocol() {
    return PapayaUtilServerProtocol::get();
  }

  /**
   * Return the id of the current domain if here is one that machted the host.
   *
   * @return integer
   */
  public function getCurrentId() {
    return empty($this->domainData['domain_id']) ? 0 : $this->domainData['domain_id'];
  }
}

?>
