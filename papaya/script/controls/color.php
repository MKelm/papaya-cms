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
* @version $Id: color.php 37658 2012-11-09 14:36:22Z weinert $
*/

/**
* incusion of base or additional libraries
*/
require_once(dirname(__FILE__).'/inc.controls.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title><?php echo _gt('Color'); ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css"
          href="../../skins/<?php echo PAPAYA_UI_SKIN; ?>/css.popups.php">
    <style type="text/css">
      table {
        border-collapse: collapse;
        margin-bottom: 3px;
      }
      table td {
        border: 1px solid black;
        width: 19px;
        height: 12px;
        font-size: 1px;
      }
      table th {
        border: 1px solid black;
        background-color: #000;
        padding: 20px;
      }
      table th input {
        border: 1px solid black;
        background-color: #FFF;
        padding: 2px;
        font-size: 12px;
      }
    </style>
  </head>
  <body id="popup">
    <div class="title">
      <div class="titleArtworkOverlay">
	  <h1 id="popupTitle"><?php echo _gt('Select color');?></h1>
      </div>
    </div>
    <form action="#" class="colorSelector">
      <table>
        <tr>
          <th colspan="18">
             <input type="text"/>
          </th>
        </tr>
        <tr>
          <td style="background-color: #FFFFFF">&nbsp;</td>
          <td style="background-color: #FFCCFF">&nbsp;</td>
          <td style="background-color: #FF99FF">&nbsp;</td>
          <td style="background-color: #FF66FF">&nbsp;</td>
          <td style="background-color: #FF33FF">&nbsp;</td>
          <td style="background-color: #FF00FF">&nbsp;</td>
          <td style="background-color: #FFFFCC">&nbsp;</td>
          <td style="background-color: #FFCCCC">&nbsp;</td>
          <td style="background-color: #FF99CC">&nbsp;</td>
          <td style="background-color: #FF66CC">&nbsp;</td>
          <td style="background-color: #FF33CC">&nbsp;</td>
          <td style="background-color: #FF00CC">&nbsp;</td>
          <td style="background-color: #FFFF99">&nbsp;</td>
          <td style="background-color: #FFCC99">&nbsp;</td>
          <td style="background-color: #FF9999">&nbsp;</td>
          <td style="background-color: #FF6699">&nbsp;</td>
          <td style="background-color: #FF3399">&nbsp;</td>
          <td style="background-color: #FF0099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #CCFFFF">&nbsp;</td>
          <td style="background-color: #CCCCFF">&nbsp;</td>
          <td style="background-color: #CC99FF">&nbsp;</td>
          <td style="background-color: #CC66FF">&nbsp;</td>
          <td style="background-color: #CC33FF">&nbsp;</td>
          <td style="background-color: #CC00FF">&nbsp;</td>
          <td style="background-color: #CCFFCC">&nbsp;</td>
          <td style="background-color: #CCCCCC">&nbsp;</td>
          <td style="background-color: #CC99CC">&nbsp;</td>
          <td style="background-color: #CC66CC">&nbsp;</td>
          <td style="background-color: #CC33CC">&nbsp;</td>
          <td style="background-color: #CC00CC">&nbsp;</td>
          <td style="background-color: #CCFF99">&nbsp;</td>
          <td style="background-color: #CCCC99">&nbsp;</td>
          <td style="background-color: #CC9999">&nbsp;</td>
          <td style="background-color: #CC6699">&nbsp;</td>
          <td style="background-color: #CC3399">&nbsp;</td>
          <td style="background-color: #CC0099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #99FFFF">&nbsp;</td>
          <td style="background-color: #99CCFF">&nbsp;</td>
          <td style="background-color: #9999FF">&nbsp;</td>
          <td style="background-color: #9966FF">&nbsp;</td>
          <td style="background-color: #9933FF">&nbsp;</td>
          <td style="background-color: #9900FF">&nbsp;</td>
          <td style="background-color: #99FFCC">&nbsp;</td>
          <td style="background-color: #99CCCC">&nbsp;</td>
          <td style="background-color: #9999CC">&nbsp;</td>
          <td style="background-color: #9966CC">&nbsp;</td>
          <td style="background-color: #9933CC">&nbsp;</td>
          <td style="background-color: #9900CC">&nbsp;</td>
          <td style="background-color: #99FF99">&nbsp;</td>
          <td style="background-color: #99CC99">&nbsp;</td>
          <td style="background-color: #999999">&nbsp;</td>
          <td style="background-color: #996699">&nbsp;</td>
          <td style="background-color: #993399">&nbsp;</td>
          <td style="background-color: #990099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #66FFFF">&nbsp;</td>
          <td style="background-color: #66CCFF">&nbsp;</td>
          <td style="background-color: #6699FF">&nbsp;</td>
          <td style="background-color: #6666FF">&nbsp;</td>
          <td style="background-color: #6633FF">&nbsp;</td>
          <td style="background-color: #6600FF">&nbsp;</td>
          <td style="background-color: #66FFCC">&nbsp;</td>
          <td style="background-color: #66CCCC">&nbsp;</td>
          <td style="background-color: #6699CC">&nbsp;</td>
          <td style="background-color: #6666CC">&nbsp;</td>
          <td style="background-color: #6633CC">&nbsp;</td>
          <td style="background-color: #6600CC">&nbsp;</td>
          <td style="background-color: #66FF99">&nbsp;</td>
          <td style="background-color: #66CC99">&nbsp;</td>
          <td style="background-color: #669999">&nbsp;</td>
          <td style="background-color: #666699">&nbsp;</td>
          <td style="background-color: #663399">&nbsp;</td>
          <td style="background-color: #660099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #33FFFF">&nbsp;</td>
          <td style="background-color: #33CCFF">&nbsp;</td>
          <td style="background-color: #3399FF">&nbsp;</td>
          <td style="background-color: #3366FF">&nbsp;</td>
          <td style="background-color: #3333FF">&nbsp;</td>
          <td style="background-color: #3300FF">&nbsp;</td>
          <td style="background-color: #33FFCC">&nbsp;</td>
          <td style="background-color: #33CCCC">&nbsp;</td>
          <td style="background-color: #3399CC">&nbsp;</td>
          <td style="background-color: #3366CC">&nbsp;</td>
          <td style="background-color: #3333CC">&nbsp;</td>
          <td style="background-color: #3300CC">&nbsp;</td>
          <td style="background-color: #33FF99">&nbsp;</td>
          <td style="background-color: #33CC99">&nbsp;</td>
          <td style="background-color: #339999">&nbsp;</td>
          <td style="background-color: #336699">&nbsp;</td>
          <td style="background-color: #333399">&nbsp;</td>
          <td style="background-color: #330099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #00FFFF">&nbsp;</td>
          <td style="background-color: #00CCFF">&nbsp;</td>
          <td style="background-color: #0099FF">&nbsp;</td>
          <td style="background-color: #0066FF">&nbsp;</td>
          <td style="background-color: #0033FF">&nbsp;</td>
          <td style="background-color: #0000FF">&nbsp;</td>
          <td style="background-color: #00FFCC">&nbsp;</td>
          <td style="background-color: #00CCCC">&nbsp;</td>
          <td style="background-color: #0099CC">&nbsp;</td>
          <td style="background-color: #0066CC">&nbsp;</td>
          <td style="background-color: #0033CC">&nbsp;</td>
          <td style="background-color: #0000CC">&nbsp;</td>
          <td style="background-color: #00FF99">&nbsp;</td>
          <td style="background-color: #00CC99">&nbsp;</td>
          <td style="background-color: #009999">&nbsp;</td>
          <td style="background-color: #006699">&nbsp;</td>
          <td style="background-color: #003399">&nbsp;</td>
          <td style="background-color: #000099">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #FFFF66">&nbsp;</td>
          <td style="background-color: #FFCC66">&nbsp;</td>
          <td style="background-color: #FF9966">&nbsp;</td>
          <td style="background-color: #FF6666">&nbsp;</td>
          <td style="background-color: #FF3366">&nbsp;</td>
          <td style="background-color: #FF0066">&nbsp;</td>
          <td style="background-color: #FFFF33">&nbsp;</td>
          <td style="background-color: #FFCC33">&nbsp;</td>
          <td style="background-color: #FF9933">&nbsp;</td>
          <td style="background-color: #FF6633">&nbsp;</td>
          <td style="background-color: #FF3333">&nbsp;</td>
          <td style="background-color: #FF0033">&nbsp;</td>
          <td style="background-color: #FFFF00">&nbsp;</td>
          <td style="background-color: #FFCC00">&nbsp;</td>
          <td style="background-color: #FF9900">&nbsp;</td>
          <td style="background-color: #FF6600">&nbsp;</td>
          <td style="background-color: #FF3300">&nbsp;</td>
          <td style="background-color: #FF0000">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #CCFF66">&nbsp;</td>
          <td style="background-color: #CCCC66">&nbsp;</td>
          <td style="background-color: #CC9966">&nbsp;</td>
          <td style="background-color: #CC6666">&nbsp;</td>
          <td style="background-color: #CC3366">&nbsp;</td>
          <td style="background-color: #CC0066">&nbsp;</td>
          <td style="background-color: #CCFF33">&nbsp;</td>
          <td style="background-color: #CCCC33">&nbsp;</td>
          <td style="background-color: #CC9933">&nbsp;</td>
          <td style="background-color: #CC6633">&nbsp;</td>
          <td style="background-color: #CC3333">&nbsp;</td>
          <td style="background-color: #CC0033">&nbsp;</td>
          <td style="background-color: #CCFF00">&nbsp;</td>
          <td style="background-color: #CCCC00">&nbsp;</td>
          <td style="background-color: #CC9900">&nbsp;</td>
          <td style="background-color: #CC6600">&nbsp;</td>
          <td style="background-color: #CC3300">&nbsp;</td>
          <td style="background-color: #CC0000">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #99FF66">&nbsp;</td>
          <td style="background-color: #99CC66">&nbsp;</td>
          <td style="background-color: #999966">&nbsp;</td>
          <td style="background-color: #996666">&nbsp;</td>
          <td style="background-color: #993366">&nbsp;</td>
          <td style="background-color: #990066">&nbsp;</td>
          <td style="background-color: #99FF33">&nbsp;</td>
          <td style="background-color: #99CC33">&nbsp;</td>
          <td style="background-color: #999933">&nbsp;</td>
          <td style="background-color: #996633">&nbsp;</td>
          <td style="background-color: #993333">&nbsp;</td>
          <td style="background-color: #990033">&nbsp;</td>
          <td style="background-color: #99FF00">&nbsp;</td>
          <td style="background-color: #99CC00">&nbsp;</td>
          <td style="background-color: #999900">&nbsp;</td>
          <td style="background-color: #996600">&nbsp;</td>
          <td style="background-color: #993300">&nbsp;</td>
          <td style="background-color: #990000">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #66FF66">&nbsp;</td>
          <td style="background-color: #66CC66">&nbsp;</td>
          <td style="background-color: #669966">&nbsp;</td>
          <td style="background-color: #666666">&nbsp;</td>
          <td style="background-color: #663366">&nbsp;</td>
          <td style="background-color: #660066">&nbsp;</td>
          <td style="background-color: #66FF33">&nbsp;</td>
          <td style="background-color: #66CC33">&nbsp;</td>
          <td style="background-color: #669933">&nbsp;</td>
          <td style="background-color: #666633">&nbsp;</td>
          <td style="background-color: #663333">&nbsp;</td>
          <td style="background-color: #660033">&nbsp;</td>
          <td style="background-color: #66FF00">&nbsp;</td>
          <td style="background-color: #66CC00">&nbsp;</td>
          <td style="background-color: #669900">&nbsp;</td>
          <td style="background-color: #666600">&nbsp;</td>
          <td style="background-color: #663300">&nbsp;</td>
          <td style="background-color: #660000">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #33FF66">&nbsp;</td>
          <td style="background-color: #33CC66">&nbsp;</td>
          <td style="background-color: #339966">&nbsp;</td>
          <td style="background-color: #336666">&nbsp;</td>
          <td style="background-color: #333366">&nbsp;</td>
          <td style="background-color: #330066">&nbsp;</td>
          <td style="background-color: #33FF33">&nbsp;</td>
          <td style="background-color: #33CC33">&nbsp;</td>
          <td style="background-color: #339933">&nbsp;</td>
          <td style="background-color: #336633">&nbsp;</td>
          <td style="background-color: #333333">&nbsp;</td>
          <td style="background-color: #330033">&nbsp;</td>
          <td style="background-color: #33FF00">&nbsp;</td>
          <td style="background-color: #33CC00">&nbsp;</td>
          <td style="background-color: #339900">&nbsp;</td>
          <td style="background-color: #336600">&nbsp;</td>
          <td style="background-color: #333300">&nbsp;</td>
          <td style="background-color: #330000">&nbsp;</td>
        </tr>
        <tr>
          <td style="background-color: #00FF66">&nbsp;</td>
          <td style="background-color: #00CC66">&nbsp;</td>
          <td style="background-color: #009966">&nbsp;</td>
          <td style="background-color: #006666">&nbsp;</td>
          <td style="background-color: #003366">&nbsp;</td>
          <td style="background-color: #000066">&nbsp;</td>
          <td style="background-color: #00FF33">&nbsp;</td>
          <td style="background-color: #00CC33">&nbsp;</td>
          <td style="background-color: #009933">&nbsp;</td>
          <td style="background-color: #006633">&nbsp;</td>
          <td style="background-color: #003333">&nbsp;</td>
          <td style="background-color: #000033">&nbsp;</td>
          <td style="background-color: #00FF00">&nbsp;</td>
          <td style="background-color: #00CC00">&nbsp;</td>
          <td style="background-color: #009900">&nbsp;</td>
          <td style="background-color: #006600">&nbsp;</td>
          <td style="background-color: #003300">&nbsp;</td>
          <td style="background-color: #000000">&nbsp;</td>
        </tr>
      </table>
      <div class="popupButtonsArea" style="text-align: right; white-space: nowrap; border: none;">
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

      (function($) {
         dialog = {

           node : null,

           selected : '',

           settings : {
             selectors : {
               preview : 'th',
               input : 'th input',
               colors : 'td',
               confirm : '[data-action=resolve]',
               cancel : '[data-action=reject]'
             }
           },

           setUp : function(node, settings) {
             this.node = node;
             this.settings = $.extend(true, this.settings, settings);
             var that = this;
             this.node.find(this.settings.selectors.colors).each(
               function () {
                 var color = $(this).css('background-color');
                 $(this).click(
                   function () {
                     that.select(color);
                   }
                 );
               }
             );
             jQuery(window).on(
               'keyup',
               function (event) {
                 if (event.keyCode == 27) {
                   $(that.settings.selectors.cancel).click();
                 }
               }
             );
             this.node.find(this.settings.selectors.confirm).click(
               $.proxy(this.confirm, this)
             );
             this.node.find(this.settings.selectors.cancel).click(
               $.proxy(this.cancel, this)
             );
           },

           select : function(color) {
             if (color != null) {
               var r = null;
               if (r = color.match(/rgb\(?([0-9]+),\s*([0-9]+),\s*([0-9]+)\)/)) {
                 color = '#' +
                   this.padLeft(parseInt(r[1]).toString(16), 2, '0') +
                   this.padLeft(parseInt(r[2]).toString(16), 2, '0') +
                   this.padLeft(parseInt(r[3]).toString(16), 2, '0');
               } else if (r = color.match(/^#?([0-9A-Fa-f]{6})$/)) {
                 color = '#' + r[1];
               } else if (r = color.match(/^#?([0-9A-Fa-f])([0-9A-Fa-f])([0-9A-Fa-f])$/)) {
                 color = '#' + r[1] + r[1] + r[2] + r[2] + r[3] + r[3];
               } else {
                 color = '#000000';
               }
               color = color.toUpperCase();
               this.selected = color;
               this.node.find(this.settings.selectors.input).val(color);
               this.node.find(this.settings.selectors.preview).css('background-color', color);
             }
           },

           padLeft : function (string, length, character) {
             if (length + 1 >= string.length) {
               return Array(length + 1 - string.length).join(character) + string;
             } else {
               return string;
             }
           },

           confirm : function() {
             window.papayaContext.defer.resolve(this.selected);
             if (!window.frameElement) {
               window.close();
             }
           },

           cancel : function() {
             window.papayaContext.defer.reject();
             if (!window.frameElement) {
               window.close();
             }
           }
         };

         $.fn.papayaColorDialog = function(settings) {
           this.each(
             function () {
               var instance = $.extend(true, {}, dialog);
               instance.setUp($(this), settings);
               instance.select(window.papayaContext.data);
             }
           );
         };
      })(jQuery);

      jQuery(document).ready(
        function () {
          jQuery.waitUntil(
            function () {
              return (window.papayaContext != null);
            },
            3000
          ).done(
            function() {
              jQuery('.colorSelector').papayaColorDialog();
            }
          );
        }
      );
    </script>
  </body>
</html>
