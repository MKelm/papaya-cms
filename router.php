<?php
/**
* router script that allows to use the php builtin webserver for papaya CMS
* 
* cd /your/directory
* php -S localhost:80 router.php
*/

$uri = empty($_SERVER['REQUEST_URI']) ? '' : $_SERVER['REQUEST_URI'];
// remove query string and/or fragment
$uri = preg_replace('([?#].*$)', '', $uri);


$rules = array(
  // remove session identifier
  '(/?sid[a-z]*([a-zA-Z0-9,-]{20,40})(/.*))' => array(
    'replacement' => '$2'  
  ),
  '(^/?papaya/module\_([a-z0-9\_]+)\.[a-z]{3,4})' => array(
    'replacement' => '/papaya/module.php',
    'last' => TRUE
  ),
  '(^/?
    ([a-fA-F0-9]/)*[a-zA-Z0-9_-]+\.
    (media|thumb|download|popup|image)
    (\.(preview))?
    ((\.([a-zA-Z0-9_]+))?
    (\.[a-zA-Z0-9_]+))
    $)x' => array(
      'replacement' => '/index.php',
      'last' => TRUE
  ),
  '(^/?
    [a-zA-Z0-9_-]+
    ((\.[0-9]+)?\.[0-9]+)
    ((\.[a-z]{2,5})?\.[a-z]+)
    ((\.[0-9]+)?.preview)
    ?$)x' => array(
    'replacement' => '/index.php',
    'last' => TRUE
  ),          
  '(^/?index((\.[a-z]{2,5})?\.[a-z]+)((\.[0-9]+)?.preview)?$)' => array(
    'replacement' => '/index.php',
    'last' => TRUE
  )
);


if (file_exists(__DIR__.$uri)) {
  return FALSE;
}

foreach ($rules as $pattern => $options) {
  if (preg_match($pattern, $uri)) {
    $uri = preg_replace($pattern, $options['replacement'], $uri);
    if (isset($options['last']) && $options['last']) {
      break;
    }
  }
}
$file = __DIR__.$uri;

define('PAPAYA_DOCUMENT_ROOT', __DIR__.'/');
if (file_exists($file)) {
  if (is_file($file)) {
    chdir(dirname($file));
    include($file);
    return TRUE;
  } elseif (is_dir($file)) {
    chdir($file);
    include('index.php');
    return TRUE;
  }
}

return FALSE;