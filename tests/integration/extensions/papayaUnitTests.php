<?php
/**
* Papaya unittests extensions
*
* PHP version 5
*
* @copyright 2007-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
*
* @package    weisseliste
* @subpackage unittest
* @category   unittest
* @version    SVN: $Id: papayaUnitTests.php 38361 2013-04-04 12:09:41Z hapke $
*
*/

/**
* Papaya unittests extensions for error handling, mysql test transactions and data fixtures
*
* @package    weisseliste
* @subpackage unittest
* @category   unittest
*
*/

class papayaUnitTests extends PHPUnit_Framework_TestCase {

  /**
  * variables for error handler
  */
  public $errorCode = NULL;
  public $errorMessage = NULL;
  public $errorFile = NULL;
  public $errorLine = NULL;

  /**
   * default error handler
   * usage:
   *
   * set_error_handler(array($this, 'errorHandler'));
   *  $object->methodToTest();
   *  restore_error_handler();
   *  if ($this->errorCode) {
   *    ... logick to handle error occurence of events properly ...
   *  }
   */
  public function errorHandler($errorCode, $errorMessage, $file, $line) {
    $this->errorCode = $errorCode;
    $this->errorMessage = $errorMessage;
    $this->errorFile = $file;
    $this->errorLine = $line;
  }

  /**
  *
  * database test extension: transaction begin method
  *
  * @param object $dbObject an papaya base_db object
  * @param array  $tableList list of tables that may be changed
  */
  public function dbTestBeginTransaction($dbObject, $tableList) {
    if (empty($tableList)) {
      $this->fail('tableList is empty');
    }
    $this->assertInternalType('object', $dbObject);
    if (! isset($this->dbTransactions)) {
      $this->dbTransactions = array();
      $this->dbTestHash = array();
    }
    $this->testDb = &$dbObject;
    $hash = time().rand(1111111111, 9999999999);
    $this->dbTestHash[] = $hash;
    $transaction = array();
    foreach ($tableList as $source) {
      if (isset($transaction[$source])) {
        $this->fail('Table names in $tableList must be unique');
      }
      $destination = $source."_temp_".$hash;
      $fields = $this->_dbTestCopyTable($source, $destination);
      if ($fields) {
        $transaction[$source] = array($destination, $fields);
      }
    }
    if (empty($transaction)){
      return FALSE;
    } else {
      $this->dbTransactions[] = $transaction;
      return TRUE;
    }
  }

  /**
  * database test extension: method to return changed values
  *
  * @param bool $saveDiff flag to save diff to file
  * @return array : array( tableName => textDiff ... )
  */
  public function dbTestGetChanges($saveDiff = FALSE) {
    if (!isset($this->dbTransactions) || empty($this->dbTransactions)) {
      $this->fail('dbTestGetChanges without begin before');
    }
    if (!isset($this->testDb)) {
      $this->fail('testDb papaya object not defined');
    }

    $result = array();
    $tables = $this->dbTransactions[sizeof($this->dbTransactions) - 1];
    $hash = $this->dbTestHash[sizeof($this->dbTestHash) - 1];
    // loop for all involved tables
    foreach ($tables as $sourceName => $tempStruct) {
      list($tempName, $fields) = $tempStruct;

      // comparation
      $dir = PHPUNIT_DB_DUMPS_DIR . get_class($this);
      if (! file_exists($dir)) {
        mkdir($dir);
      }

      $srcDumpFile = $dir."/$sourceName.dump";
      $dstDumpFile = $dir."/$tempName.dump";
      $diffFile = $dir."/$sourceName.$hash.diff";
      if (! $this->_dbTestSqlDump($sourceName, $fields, $srcDumpFile)) {
        return $result;
      }
      if (! $this->_dbTestSqlDump($tempName, $fields, $dstDumpFile )) {
        return $result;
      }

      $diff = exec('diff -u $dstDumpFile $srcDumpFile');

      unlink($srcDumpFile);
      unlink($dstDumpFile);

      if (! empty($diff)) {
        $diff = preg_replace('/^(\-\-\-.+|\+\+\+.+)$/m', '', $diff);
        $result[$sourceName] = $diff;
        if ($saveDiff) {
          $fl = fopen($diffFile, 'w');
          if (!$fl){
            trigger_error("cannot open file $diffFile", E_USER_WARNING);
            return $result;
          }
          fwrite($fl, $diff);
          fclose($fl);
        }
      } else {
        $result[$sourceName] = '';
      }
    }

    return $result;
  }

  /**
  * database test extension: rollback and cleanup method
  *
  * @todo change databaseQueryFmt calls to proper papaya db methods
  */
  public function dbTestRollback() {
    if (!isset($this->dbTransactions) || empty($this->dbTransactions)) {
      $this->fail('dbTestRollback without begin before');
    }
    $tables = array_pop($this->dbTransactions);
    $hash = array_pop($this->dbTestHash);
    foreach ($tables as $sourceName => $tempStructure) {
      list($tempName, $fields) = $tempStructure;
      $res = $this->testDb->databaseQueryFmt('DROP TABLE %s', array($sourceName));
      $this->assertNotNull($res);
      $res = $this->testDb->databaseQueryFmt(
        'ALTER TABLE %s RENAME %s',
        array(
          $tempName,
          $sourceName
        )
      );
      $this->assertNotNull($res);
    }
  }

  /**
  * basic xml or html text preparations for better readability
  */
  public function prettyXml($text) {

    // convert utf-8 spaces to legacy ascii space character
    $ret=preg_replace("/[\x80-\xFF]+\xA0/","&nbsp;",$text);

    // break line after each xml tag
    $ret=preg_replace("/\>[\s\r\n\t]*(.|$)/",">\n\\1",$ret);

    // break line before each xml tag
    $ret=preg_replace("/(.)[\s\r\n\t]*\</","\$1\n<",$ret);

    // convert german entities to utf-8
    $ret=preg_replace_callback("/(\&\w{2,6}\;)/",array($this, '_entityCallback'),$ret);
    return $ret;
  }

  /**
  * very important function for data fixtures
  * @param STRING $file - relative file name for data fixture
  * @param STRING $data - data to save , always a string - we do not handle serialisation here
  * @return STRING - returns $data if no fixture exists, or early saved data if one found
  *
  * function save contents of $data to $file if no file exists, or returns contents of file otherwise
  * filename is relative to PHPUNIT_FIXTURES_DIR . get_class($this) directory
  */
  public function touchFile($file, $data) {
    $dir = PHPUNIT_FIXTURES_DIR . get_class($this);
    if (! file_exists($dir)) {
      mkdir($dir);
      if (! file_exists($dir)) {
        throw new Exception("cannot create directory $dir");
      }
    } elseif (! is_dir($dir)) {
      throw new Exception("$dir should be a directory");
    }
    $file = $dir."/".$file;
    if (! file_exists($file)) {
      print "file $file does not exist, will be created\n";
      $fl=fopen($file ,'w');
      if ($fl) {
        fwrite($fl, $data);
        fclose($fl);
        print "ok\n";
      } else {
        $this->fail("failure\n");
      }
      return $data;
    } else {
      return file_get_contents($file);
    }
  }

  /**
  * ////////////////// PRIVATE METHODS //////////////////
  */

  /**
  * entity replace callback
  * @param ARRAY $input - list of backreferences from preg_replace_callback()
  */
  public function _entityCallback($input) {
    // german html entities
    $ent = array(
      "&auml;" => "\xE4",
      "&ouml;" => "\xF6",
      "&uuml;" => "\xFC",
      "&Auml;" => "\xC4",
      "&Ouml;" => "\xD6",
      "&Uuml;" => "\xDC",
      "&szlig;" => "\xDF",
      "&ldquo;" => "\xAB",
      "&rdquo;" => "\xBB"
    );
    $val = $input[1];
    return isset($ent[$val]) ? $ent[$val] : "#".$val."#";
  }

  /**
  * method to simple dump a mysql table to csv file
  *
  * @return bool success flag
  */
  private function _dbTestSqlDump($table, $fields, $file) {
    $sql = 'SELECT %s FROM %s';
    $res = $this->testDb->databaseQueryFmt($sql, array(
      implode(",", $fields),
      $table
      ));
    if (! $res) {
      trigger_error("database error", E_USER_WARNING);
      return FALSE;
    }
    $fl = fopen($file, 'w');
    if (! $fl){
      trigger_error("cannot write to file $file", E_USER_WARNING);
      return FALSE;
    }
    ob_start();
    while ($row = $res->fetchRow(DB_FETCHMODE_DEFAULT)) {
      print implode("\t", $row)."\n";
    }
    $srcText = ob_get_clean();
    fwrite($fl, $srcText);
    fclose($fl);
    return TRUE;
  }

  /**
  *
  * methot to copy a sql table with content
  *
  * @param string $source source table name
  * @param string $destination destination table name
  * @return array  list of table fields
  */
  private function _dbTestCopyTable($source, $destination) {
    if (! isset($this->testDb)) {
      $this->fail('testDb papaya object not defined');
    }

    // get structure of source table
    $sql = "SHOW CREATE TABLE %s";
    $res = $this->testDb->databaseQueryFmt($sql, array($source));
    $this->assertNotNull($res, "database error");

    list($newTableName,$description) = $res->fetchRow(DB_FETCHMODE_DEFAULT);

    // rename source to destination
    $sql = "ALTER TABLE %s RENAME %s";
    $res = $this->testDb->databaseQueryFmt($sql, array($source, $destination));
    $this->assertNotNull($res, "database error");

    // create new version of source table
    $res = $this->testDb->databaseQuery($description);
    $this->assertNotNull($res, "database error");

    // get field names from source structure
    $fields = array();
    $description = preg_replace('/^[^\(]+\(|\)[^\)]+$/s', '', $description);
    //print $description ;exit;
    foreach (explode("\n", $description) as $line) {
      if (preg_match('/^[\s\`]+([\w\_]+)/', $line, $r)) {
        $name = strtolower($r[1]);
        if (in_array($name, array('key', 'primary', 'unique'))) {
          break;
        }
        $fields[] = $name;
      }
    }

    // copy data from destination to the source
    $sql = 'INSERT INTO %s (%s) SELECT %2$s FROM %s';
    $res = $this->testDb->databaseQueryFmt($sql, array(
      $source,
      implode( ",", $fields),
      $destination
      )
    );
    $this->assertNotNull($res, "database error");

    // return field list
    return $fields;
  }

  /**
  * strip random values from papaya xml or html tags
  */
  public function stripRandomPapayaValues($text) {
    // strip dates
    $text = preg_replace(
      '{(\w+\s?=\s?\")\d{4}\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d(\")}i',
      '$1__date__$2',
      $text
    );
    $text = preg_replace(
      '{(\w+\s?=\s?\")\w{3,4}\,\s\d\d\s\w{3,4}\s\d{4}\s[\d\:]{8}\s[\+\-]\d{4}(\")}i',
      '$1__date_rfc822__$2',
      $text
    );
    $text = preg_replace(
      '{(\<metatag\stype\=\"date\"\>)[^\<]+(\<\/metatag\>)}i',
      '$1__date__$2',
      $text
    );
    // strip md5 hashes
    $text = preg_replace(
      '{(\w+\s?=\s?\")[0-9a-f]{32}(\")}i',
      '$1__md5hash__$2',
      $text
    );
    return $text;
  }

}

?>