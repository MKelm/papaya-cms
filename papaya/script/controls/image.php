<?php
/**
*
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
* @subpackage Scripts-Controls
* @version $Id: image.php 37550 2012-10-15 14:49:07Z weinert $
*/

/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title><?php echo _gt('Image'); ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet"
          type="text/css" href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
    <script type="text/javascript">
      var mediaFileData = {
        id : '',
        width : 0,
        height : 0,
        orgWidth : 0,
        orgHeight : 0,
        resizeMode : 'max'
      };
      var bProtextProportions = true;
      var papayaContext = null;
    </script>
    <script type="text/javascript" src="../xmlrpc.js"></script>
    <script type="text/javascript" src="image.js"></script>
  </head>
  <body id="popup">
    <div class="title">
      <div class="titleArtworkOverlay">
        <h1 id="popupTitle"><?php echo _gt('Select image');?></h1>
      </div>
    </div>


    <form action="#">
      <div id="divImage">
         <div id="divPreview" style="width: 280px;  float: left; padding: 5px;">
          <iframe src="about:blank" id="iframePreview" name="iframePreview"
            class="iframePreview" frameborder="0" scrolling="no"></iframe>
         </div>

         <fieldset style="width: 260px; float: right; margin-right: 5px;">
     <legend ><?php echo _gt('Size'); ?></legend>
         <table border="0" style="width: 260px;" class="dialogXXSmall" summary="">
           <tr style="vertical-align: center;">
             <td>
       <label ><?php echo _gt('Width'); ?></label>
               <input type="text" class="dialogInput dialogScale" id="editSizeWidth"
                 onchange="OnChangeWidth();" />
             </td>
             <td>
               <a href="#"><img src="../../pics/controls/size_linked_on.gif"
                 alt="Protect Proportions" style="width:19px; height:8px; border: none;"
                 id="imageProtectProportions" onclick="switchProtectProportions();"/></a>
             </td>
             <td>
       <label ><?php echo _gt('Height'); ?></label>
               <input type="text" class="dialogInput dialogScale" id="editSizeHeight"
                 onchange="OnChangeHeight();" />
             </td>
           </tr>
         </table>
         <table border="0" style="width: 260px;" summary="">
           <tr>
             <td class="symbolSize">
               <a href="#"><img src="../../pics/controls/size_abs.gif" id="imageSizeAbs"
                 class="symbolSize"  alt="Absolute" title="Absolute"
                 onclick="selectResize('abs');" /></a>
             </td>
             <td class="symbolSize">
               <a href="#"><img src="../../pics/controls/size_max.gif" id="imageSizeMax"
                 class="symbolSize"  alt="Maximum" title="Maximum"
                 onclick="selectResize('max');" /></a>
             </td>
             <td class="symbolSize">
               <a href="#"><img src="../../pics/controls/size_min.gif" id="imageSizeMin"
                 class="symbolSize"  alt="Minimum" title="Minimum"
                 onclick="selectResize('min');" /></a>
             </td>
             <td class="symbolSize">
               <a href="#"><img src="../../pics/controls/size_mincrop.gif" id="imageSizeMinCrop"
                 class="symbolSize"  alt="Minimum Cropped" title="Minimum Cropped"
                 onclick="selectResize('mincrop');" /></a>
             </td>
             <td style="padding-left: 10px;">
               <a href="#"><img src="../../pics/controls/size_getorg.gif"
                  style="border: none; width: 16px; height: 32px; padding: 0 2px"
                  alt="Get Original Size" title="Get Original Size"
                  onclick="getOriginalSize();" /></a>
             </td>
           </tr>
         </table>
       </fieldset>
      </div>
      <div class="popupButtonsArea">
        <input type="button" onclick="selectImage();"  value="<?php echo _gt('Select image')?>"
          class="dialogButton buttonLeft"/>
        <input type="button" value="<?php echo _gt('Ok');?>" class="dialogButton"
          papayaLang="DlgBtnOk" data-action="resolve"/>
        <input type="button" value="<?php echo _gt('Cancel')?>" class="dialogButton"
          papayaLang="DlgBtnCancel" data-action="reject"/>
      </div>
    </form>

    <div class="footer">
      <div class="footerArtworkOverlay"> </div>
    </div>
    <script type="text/javascript" src="../../script/jquery/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="../../script/jquery.papayaUtilities.js"></script>
    <script type="text/javascript">
      var papayaContext = null;

      jQuery(document).ready(
        function() {
          jQuery(window).on(
            'keyup',
            function (event) {
              if (event.keyCode == 27) {
                jQuery('input[data-action=reject]').click();
              }
            }
          );
          jQuery('input[data-action=resolve]').click(
            function () {
              if (window.papayaContext) {
                window.papayaContext.defer.resolve(mediaFileData);
              }
              if (!window.frameElement) {
                window.close();
              }
            }
          );
          jQuery('input[data-action=reject]').click(
            function () {
              if (window.papayaContext) {
                window.papayaContext.defer.reject();
              }
              if (!window.frameElement) {
                window.close();
              }
            }
          );
          jQuery.waitUntil(
            function () {
              return (window.papayaContext != null);
            },
            2000
          ).done(
            function () {
              initializeImageData(window.papayaContext.data);
            }
          );
        }
      );
    </script>
  </body>
</html>
