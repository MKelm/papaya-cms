<?php
/**
* Papaya controller class for error pages
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
* @package Papaya-Library
* @subpackage Controller
* @version $Id: Error.php 38142 2013-02-19 14:46:04Z weinert $
*/

/**
* Papaya controller class for error pages
*
* @package Papaya-Library
* @subpackage Controller
*/
class PapayaControllerError extends PapayaObject implements PapayaController {

  /**
  * HTTP resposne status
  * @var integer
  */
  protected $_status = 500;
  /**
  * Error message
  * @var string
  */
  protected $_errorMessage = 'Service unavailable.';
  /**
  * Error identifier
  * @var string
  */
  protected $_errorIdentifier = '';
  /**
  * Error template
  * @var string
  */
  protected $_template =
    '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
     <html>
       <head>
         <title>{%status%} - {%message%}</title>
         <style type="text/css">
         <!--
         body {
           background-color: #FFF;
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         th, td {
           font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
         }
         pre {
           color: gray;
           font-weight: bold;
           font-size: 1.4em;
         }
         a {
           color: #95A41A;
           white-space: nowrap;
           font-size: 0.8em;
         }
         //-->
         </style>
       </head>
       <body>
         <div align="center">
         <br />
         <br />
         <br />
         <table cellpadding="4" border="0" width="400">
           <tr>
             <th colspan="2">Error in page request!</th>
           </tr>
           <tr valign="top">
             <td align="center"><pre>{%artwork%}</pre></td>
           </tr>
           <tr>
             <th><h3>{%message%}</h3></th>
           </tr>
           <tr>
             <th>{%status%}.{%identifier%}</th>
           </tr>
           <tr valign="top">
             <th>
               <hr style="border: none; border-bottom: 1px solid black;">
               <h4><a href="http://{%host%}/">http://{%host%}/</a></h4>
             </th>
           </tr>
         </table>
       </div>
       </body>
     </html>';

  /**
  * Set HTTP response status
  * @param integer $status
  * @return void
  */
  public function setStatus($status) {
    $this->_status = (int)$status;
  }

  /**
  * Set error message and identifier
  * @param string $identifier
  * @param string $message
  * @return void
  */
  public function setError($identifier, $message) {
    $this->_errorMessage = $message;
    $this->_errorIdentifier = $identifier;
  }

  /**
  * Execute controller
  * @param object $dispatcher
  * @return boolean|PapayaController
  */
  public function execute($dispatcher) {
    $application = $this->papaya();
    $response = $application->getObject('Response');
    $response->setStatus($this->_status);
    $response->setContentType('text/html');
    $response->content(
      new PapayaResponseContentString($this->_getOutput())
    );
    $response->send();
    $result = FALSE;
    return $result;
  }

  /**
  * Generate error output
  * @return string
  */
  protected function _getOutput() {
    $application = $this->papaya();
    $replace = array(
      '{%status%}' => PapayaUtilStringXml::escape($this->_status),
      '{%artwork%}' => PapayaUtilStringAsciiArtwork::get($this->_status),
      '{%identifier%}' => PapayaUtilStringXml::escape($this->_errorIdentifier),
      '{%message%}' => PapayaUtilStringXml::escape($this->_errorMessage),
      '{%host%}' => PapayaUtilStringXml::escape(
        empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST']
      ),
    );
    return str_replace(
      array_keys($replace),
      array_values($replace),
      $this->_template
    );
  }
}