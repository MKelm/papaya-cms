<?php
/**
* Papaya HTTP Client File Resource - handle file upload resource using a resource id
*
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
* @package Papaya-Library
* @subpackage HTTP-Client
*/

/**
* Papaya HTTP Client File Resource - handle file upload resource using a resource id
*
* @package Papaya-Library
* @subpackage HTTP-Client
*/
class PapayaHttpClientFileResource extends PapayaHttpClientFile {

  protected $_size = NULL;

  /**
  * constructor
  *
  * @param string $name
  * @param string $fileName
  * @param resource_id $resource
  * @param string $mimeType optional, default value ''
  * @access public
  */
  public function __construct($name, $fileName, $resource, $mimeType = '') {
    if (!empty($name) &&
        !empty($fileName) &&
        is_resource($resource)) {
      $this->_name = $name;
      $this->_fileName = $fileName;
      $this->_resource = $resource;
      if (!empty($mimeType)) {
        $this->_mimeType = $mimeType;
      }
    } else {
      throw new InvalidArgumentException('Invalid arguments for element: '.$name);
    }
  }

  /**
  * read file resource size and/or return it
  *
  * @access public
  * @return integer
  */
  public function getSize() {
    if (!isset($this->_size)) {
      $this->_size = 0;
      $stat = fstat($this->_resource);
      if (isset($stat['size'])) {
        $this->_size = (int)$stat['size'];
      }
    }
    return $this->_size;
  }

  /**
  * send file data
  *
  * @param PapayaHttpClientSocket $socket
  * @param boolean $chunked optional, default value FALSE
  * @param integer $bufferSize optional, default value 0
  * @access public
  * @return void
  */
  public function send(PapayaHttpClientSocket $socket, $chunked = FALSE, $bufferSize = 0) {
    if (is_resource($this->_resource)) {
      if ($socket->isActive()) {
        if ($bufferSize <= 0) {
          $bufferSize = $this->_bufferSize;
        }
        if ($chunked) {
          while (!feof($this->_resource)) {
            $data = fread($this->_resource, $bufferSize);
            if ($data !== '') {
              $socket->writeChunk($data);
            }
          }
          $socket->writeChunk($this->_lineBreak);
        } else {
          $size = $this->getSize();
          $sent = 0;
          while (!feof($this->_resource) && $size >= ($sent + $bufferSize)) {
            $data = fread($this->_resource, $bufferSize);
            if ($data !== '') {
              $socket->write($data);
              $sent += strlen($data);
            }
          }
          if ($size > $sent) {
            $bytesToSend = $size - $sent;
            $data = fread($this->_resource, $bytesToSend);
            $socket->write($data);
          }
          $socket->write($this->_lineBreak);
        }
      }
    } else {
      throw new UnexpectedValueException('Invalid resource in element: '.$this->_name);
    }
  }
}