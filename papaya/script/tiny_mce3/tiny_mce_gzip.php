<?php
/**
* This file compresses the TinyMCE JavaScript using GZip and
* enables the browser to do two requests instead of one for each .js file.
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Administration-TinyMCE
* @version $Id: tiny_mce_gzip.php 36613 2012-01-06 12:45:56Z weinert $
*/

if (isset($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != '') {
  $adminPath = str_replace('\\', '/', dirname(dirname(dirname($_SERVER['PATH_TRANSLATED']))));
} else {
  $adminPath = str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))));
}
if (substr($adminPath, -1) != '/') {
  $adminPath .= '/';
}
/**
* Papaya Document Root
* @ignore
*/
define('PAPAYA_DOCUMENT_ROOT', dirname($adminPath).'/');

require_once($adminPath.'inc.conf.php');
require_once($adminPath.'inc.func.php');

$application = setUpApplication();
$PAPAYA_OPTIONS = $application->options;
$PAPAYA_OPTIONS->loadAndDefine();

// Get input
$plugins = explode(',', getParam('plugins'));
$languages = explode(',', getParam('languages'));
$themes = explode(',', getParam('themes'));
$diskCache = getParam('diskcache') == 'true';
$isJS = getParam('js', 'true') == 'true';
$compress = getParam('compress', 'true') == 'true';
$core = getParam('core', 'true') == 'true';
$suffix = getParam('suffix', '_src') == '_src' ? '_src' : '';
$expiresOffset = 3600 * 24 * 10; // Cache for 10 days in browser cache

// Custom extra javascripts to pack
$path = dirname(__FILE__);
$custom = array(
  dirname($path).'/xmlrpc.js',
  $path.'/plugins/papaya/js/jsonclass.js',
  $path.'/plugins/papaya/js/papayaparser.js',
  $path.'/plugins/papaya/js/papayatag.js',
  $path.'/plugins/papaya/js/papayautils.js'
);

// Headers
header("Content-type: text/javascript");
header("Vary: Accept-Encoding");  // Handle proxies
controlScriptFileCaching(__FILE__, FALSE, FALSE);


// Is called directly then auto init with default settings
if (!$isJS) {
  echo getFileContents("tiny_mce_gzip.js");
  echo "tinyMCE_GZ.init({});\n";
  exit();
}

$gzipEncoding = checkGzipSupport();
// Setup cache info
if ($diskCache) {
  if (defined('PAPAYA_VERSION_STRING') && PAPAYA_VERSION_STRING != '') {
    $revision = PAPAYA_VERSION_STRING;
  } else {
    $revision = 'DEV';
  }
  $cacheKey = getParam("plugins", "").getParam("languages", "").getParam("themes", "").$suffix;
  foreach ($custom as $file) {
    $cacheKey .= $file;
  }
  $cacheKey = 'tiny_mce3_'.md5($cacheKey);
  $cacheKey .= ($gzipEncoding && $compress) ? '.gz' :'.js';

  include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Cache.php');
  $cache = PapayaCache::getService($PAPAYA_OPTIONS);
  $cacheData = $cache->read('admin', 'rev'.$revision, $cacheKey, $expiresOffset);
  // Use cached file disk cache
  if (!empty($cacheData)) {
    if ($gzipEncoding && $compress) {
      header("Content-Encoding: ".$gzipEncoding);
      header("Content-Length: ".strlen($cacheData));
      header("X-Papaya-GZIP: yes (cached)");
    } else {
      header("X-Papaya-GZIP: no (cached)");
    }
    echo $cacheData;
    exit();
  }
}

$content = '';
// Add core
if ($core) {
  $content .= getFileContents($path.'/tiny_mce'.$suffix.'.js');
  // Patch loading functions
  $content .= "tinyMCE_GZ.start(); ";
}

//Add custom files
foreach ($custom as $file) {
  $content .= getFileContents($file);
}
// Add core languages
$content .= getLanguageFileContents($path, $languages);
// Add themes
$content .= getFilesContents($path.'/themes', $themes, 'editor_template'.$suffix.'.js', $languages);
// Add plugins
$content .= getFilesContents($path.'/plugins', $plugins, 'editor_plugin'.$suffix.'.js', $languages);

//Restore loading functions
if ($core == "true") {
  $content .= "\ntinyMCE_GZ.end();";
}

//Generate GZIP'd content
if ($gzipEncoding && $compress) {
  header("Content-Encoding: ".$gzipEncoding);
  header("X-Papaya-GZIP: yes");
  $content = gzencode($content, 9, FORCE_GZIP);
  header('Content-Length: '.strlen($content));
} else {
  header("X-Papaya-GZIP: no");
}
//output content
echo $content;
flush();

// Write cache file
if ($diskCache && !empty($cacheKey)) {
  include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Cache.php');
  $cache = PapayaCache::getService($PAPAYA_OPTIONS);
  return $cache->write('admin', 'rev'.$revision, $cacheKey, $content);
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

function getParam($name, $default = '') {
  if (!isset($_GET[$name])) {
    return $default;
  }
  return preg_replace('([^\\d\\w_,-]+)', '', $_GET[$name]); // Remove anything but 0-9,a-z,-_
}

function checkGzipSupport() {
  $encodings = array();
  // Check if it supports gzip
  if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
    $encodings = explode(
      ',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING']))
    );
  }
  if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings)) &&
       function_exists('gzencode') &&
       !ini_get('zlib.output_compression')) {
    return in_array('x-gzip', $encodings) ? 'x-gzip' : 'gzip';
  }
  return FALSE;
}

function getFilesContents($basePath, $paths, $fileName, $languages) {
  $result = '';
  foreach ($paths as $path) {
    $result .= getFileContents($basePath.'/'.$path.'/'.$fileName);
    $result .= getLanguageFileContents($basePath.'/'.$path, $languages);
  }
  return $result;
}

function getLanguageFileContents($basePath, $languages) {
  $result = '';
  foreach ($languages as $language) {
    $result .= getFileContents($basePath.'/langs/'.$language.'.js');
  }
  return $result;
}

function getFileContents($path) {
  $path = realpath($path);
  if (!empty($path) && is_file($path) && is_readable($path)) {
    return "\n".file_get_contents($path)."\n\n".getLoadingMarker($path);
  }
  return '';
}

function getLoadingMarker($fileName) {
  $protocol = PapayaUtilServerProtocol::get();
  $systemURL = $protocol.'://'.strtolower($_SERVER['HTTP_HOST']);
  $file = substr($fileName, strlen($_SERVER['DOCUMENT_ROOT']));
  $file = preg_replace('(^[/\\\'"\r\n]+)', '', $file);
  $file = '/'.str_replace('\\', '/', $file);
  return "tinymce.ScriptLoader.markDone('".$systemURL.$file."');\n\n";
}
?>