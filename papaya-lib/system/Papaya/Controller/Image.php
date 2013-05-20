<?php
/**
* Papaya controller class for dynamic images
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
* @version $Id: Image.php 38142 2013-02-19 14:46:04Z weinert $
*/

/**
* Papaya controller class for dynamic images
*
* @package Papaya-Library
* @subpackage Controller
*/
class PapayaControllerImage extends PapayaObject implements PapayaController {

  private $_imageGenerator = NULL;

  /**
  * Set image generator object
  * @param base_imagegenerator $imageGenerator
  * @return void
  */
  public function setImageGenerator($imageGenerator) {
    $this->_imageGenerator = $imageGenerator;
  }

  /**
  * Get image generator object (implicit create)
  * @return base_imagegenerator
  */
  public function getImageGenerator() {
    if (is_null($this->_imageGenerator)) {
      $this->_imageGenerator = new base_imagegenerator();
    }
    return $this->_imageGenerator;
  }

  /**
  * Execute controller
  * @param object $dispatcher
  * @return boolean|PapayaController
  */
  public function execute($dispatcher) {
    $application = $this->papaya();
    $request = $application->getObject('Request');
    $imgGenerator = $this->getImageGenerator();
    $imgGenerator->publicMode = $request->getParameter(
      'preview', TRUE, NULL, PapayaRequest::SOURCE_PATH
    );
    if ($imgGenerator->publicMode || $dispatcher->validateEditorAccess()) {
      $ident = $request->getParameter(
        'image_identifier', '', NULL, PapayaRequest::SOURCE_PATH
      );
      if (!empty($ident) &&
          $imgGenerator->loadByIdent($ident)) {
        if ($imgGenerator->generateImage()) {
          $dispatcher->logRequest();
          return TRUE;
        } else {
          return PapayaControllerFactory::createError(
            500, 'DYNAMIC_IMAGE_CREATE', $imgGenerator->lastError
          );
        }
      } else {
        return PapayaControllerFactory::createError(
          404, 'DYNAMIC_IMAGE_NOT_FOUND', 'Image identifier not found'
        );
      }
    } else {
      return PapayaControllerFactory::createError(
        403, 'DYNAMIC_IMAGE_ACCESS', 'Permission denied'
      );
    }
  }
}