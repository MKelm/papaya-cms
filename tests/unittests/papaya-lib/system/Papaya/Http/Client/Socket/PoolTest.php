<?php

require_once(substr(__FILE__, 0, -57).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Http/Client/Socket/Pool.php');

class PapayaHttpClientSocketPoolTest extends PapayaTestCase {

  function testSetGetConnection() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $this->assertSame($resource, $pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithEmptyPool() {
    $pool = new PapayaHttpClientSocketPool();
    $this->assertNull($pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithEmptiedPool() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $pool->getConnection("example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 80));
  }

  function testGetConnectionWithDifferentPort() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    $pool->putConnection($resource, "example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 8080));
  }

  function testGetConnectionWithEof() {
    $pool = new PapayaHttpClientSocketPool();
    $resource = fopen('data://text/plain,test', 'r');
    fread($resource, 4);
    $pool->putConnection($resource, "example.com", 80);
    $this->assertNull($pool->getConnection("example.com", 80));
    $this->assertFalse(is_resource($resource));
  }

}

?>
