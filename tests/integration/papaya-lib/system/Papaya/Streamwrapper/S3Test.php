<?php
/**
* Integration Test for the Amazon S3 Stream Wrapper.
*
*  File functions that are not tested because they are
*    internally implemented by tested functions or the other way around:
*    file, fgetc, fgetcsv, fgets, fgetss, fputcsv, fputs, fscanf, fpassthru,
*    is_writable, parse_ini_file, parse_ini_string, move_uploaded_file,
*    readfile, rewind, stat
*  File functions that are not tested because they
*    don't touch stream wrappers at all:
*    basename, clearstatcache, dirname, fnmatch, is_uploaded_file, pathinfo,
*    pclose, popen, set_file_buffer, realpath, tempnam, tmpfile
*  File functions that are not tested because they are not used:
*    chgrp, chmod, chown, disk_free_space, disk_total_space, diskfreespace,
*    fileatime, filegroup, fileinode, filemtime, fileowner, fileperms, filetype,
*    flock, glob, is_executable, is_link, lchgrp, lchown, link, linkinfo,
*    lstat, , readlink, symlink, umask
*  File functions that can not be used with the stream wrapper:
*    chgrp, chmod, chown, imagepng
*  @todo: File functions that still need to be tested:
*    rename, fflush, ftruncate, feof, filectime, touch
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
* @version $Id$
*/

require_once(substr(__FILE__, 0, -68).'/tests-unittests/Framework/PapayaTestCase.php');

if (file_exists(substr(__FILE__, 0, -3).'conf.php')) {
  // setup configuration the this file
  include_once(substr(__FILE__, 0, -3).'conf.php');
  /* example:
    define('PAPAYAS3AUTH', 'KEYID123456789012345:1234567890123456789012345678901234567890');
    // the path includes the bucket and may not end with a /
    define('PAPAYAS3PATH', 'papaya-test');
  */
}

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Http/Client.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Streamwrapper/S3.php');
stream_wrapper_register('s3', 'PapayaStreamwrapperS3', STREAM_IS_URL);

class PapayaStreamwrapperS3IntegrationTest extends PapayaTestCase {

  private $_skipS3 = TRUE;

  function setUp() {
    if (defined('PAPAYAS3AUTH') && defined('PAPAYAS3PATH')) {
      $s3path = "s3://".PAPAYAS3AUTH."@".PAPAYAS3PATH.'/';
      $this->_skipS3 = FALSE;
    } else {
      $s3path = './';
    }
    $this->filename = $s3path.'testobject';
    $this->dirname = $s3path.'testdirectory';
    $this->filenameNonExisting = $this->filename.'-non-existing';
    $this->content = 'testcontent';
    file_put_contents($this->filename, $this->content);
  }

  function tearDown() {
    @unlink($this->filename);
    @rmdir($this->dirname);
  }

  function testExistsNot() {
    $this->assertFalse(file_exists($this->filenameNonExisting));
  }

  function testFilePutContents() {
    $this->assertSame(
      strlen($this->content),
      file_put_contents($this->filename, $this->content)
    );
  }

  function testFileGetContents() {
    $this->assertSame(
      $this->content,
      file_get_contents($this->filename)
    );
  }

  function testFileReadLength() {
    $resource = fopen($this->filename, 'r');
    $this->assertSame(substr($this->content, 0, 4), fread($resource, 4));
    fclose($resource);
  }

  function testFilesize() {
    $this->assertSame(strlen($this->content), filesize($this->filename));
  }

  function testExists() {
    $this->assertTrue(file_exists($this->filename));
  }

  function testIsFile() {
    $this->assertTrue(is_file($this->filename));
  }

  function testIsDirNot() {
    $this->assertFalse(is_dir($this->filename));
  }

  function testRmDirWithNotExisting() {
    $this->assertFalse(@rmdir($this->dirname));
  }

  function testIsDir() {
    $this->assertTrue(mkdir($this->dirname), 'mkdir');
    $this->assertTrue(is_dir($this->dirname), 'is_dir');
    $this->assertFalse(@mkdir($this->dirname), 'mkdir on existing');
    $this->assertTrue(rmdir($this->dirname), 'rmdir');
  }

  function testIsReadable() {
    $this->assertTrue(is_readable($this->filename));
  }

  function testIsWriteable() {
    $this->assertTrue(is_writeable($this->filename));
  }

  function testFileTell() {
    $resource = fopen($this->filename, 'r');
    $this->assertSame(0, ftell($resource));
    fclose($resource);
  }

  function testFileOpenExclPlus() {
    if ($this->_skipS3) {
      $this->markTestSkipped('Skipping S3 specific test.');
    }
    $this->assertFalse(@fopen($this->filename, 'x+'));
  }

  function testFileOpenAppendPlus() {
    if ($this->_skipS3) {
      $this->markTestSkipped('Skipping S3 specific test.');
    }
    $this->assertFalse(@fopen($this->filename, 'a+'));
  }

  function testFileOpenReadPlus() {
    if ($this->_skipS3) {
      $this->markTestSkipped('Skipping S3 specific test.');
    }
    $this->assertFalse(@fopen($this->filename, 'r+'));
  }

  function testFileOpenWritePlus() {
    if ($this->_skipS3) {
      $this->markTestSkipped('Skipping S3 specific test.');
    }
    $this->assertFalse(@fopen($this->filename, 'w+'));
  }

  function testFileSeek() {
    $resource = fopen($this->filename, 'r');
    $this->assertSame(0, fseek($resource, strlen($this->content)));
    $this->assertSame(strlen($this->content), ftell($resource));
    fclose($resource);
  }

  function testFileWrite() {
    $resource = fopen($this->filename, 'w');
    $this->assertSame(strlen($this->content), fwrite($resource, $this->content));
    $this->assertTrue(fclose($resource));
    $this->assertSame(
      $this->content,
      file_get_contents($this->filename)
    );
  }

  function testSeekWrite() {
    if ($this->_skipS3) {
      $this->markTestSkipped('Skipping S3 specific test.');
    }
    $resource = fopen($this->filename, 'w');
    $this->assertSame(strlen($this->content), fwrite($resource, $this->content));
    // the following should currently fail for the s3 stream wrapper, thus -1
    $this->assertSame(-1, @fseek($resource, 0));
    $this->assertSame(strlen($this->content), fwrite($resource, $this->content));
    $this->assertTrue(fclose($resource));
    $this->assertSame(
      $this->content.$this->content,
      file_get_contents($this->filename)
    );
  }

  function testStatWrite() {
    $resource = fopen($this->filename, 'w');
    $this->assertSame(strlen($this->content), fwrite($resource, $this->content));
    $result = fstat($resource);
    $this->assertInternalType('array', $result);
    $this->assertEquals($result['size'], strlen($this->content));
    $this->assertLessThan($result['atime'], 0);
    $this->assertLessThan($result['mtime'], 0);
    $this->assertLessThan($result['ctime'], 0);
    $this->assertTrue(fclose($resource));
  }

  function testFileUnlink() {
    $this->assertTrue(unlink($this->filename));
    $this->assertFalse(@unlink($this->filename));
  }

  function testCopy() {
    // copy is implemented internally in PHP by fread and fwrite on streams
    $this->assertTrue(copy($this->filename, $this->filenameNonExisting));
    @unlink($this->filenameNonExisting);
  }

  function testGD() {
    $image = imagecreatetruecolor(16, 16);
    $file = tempnam(sys_get_temp_dir(), 'papaya-');
    // imagepng does not support output to a stream wrapper
    imagepng($image, $file);
    // rename is not supported accross stream wrappers
    $this->assertTrue(copy($file, $this->filename));
    unlink($file);
    $size = getimagesize($this->filename);
    $this->assertSame(16, $size[0]);
    $this->assertSame(16, $size[1]);
    $this->assertSame('image/png', $size['mime']);
  }

}
?>
