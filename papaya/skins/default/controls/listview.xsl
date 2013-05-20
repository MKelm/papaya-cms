<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="listview">
  <xsl:param name="listview"/>
  <xsl:if test="$listview/@maximize or $listview/items/listitem or $listview/cols/col or $listview/toolbar">
    <xsl:call-template name="panel">
      <xsl:with-param name="title" select="$listview/@title" />
      <xsl:with-param name="hint" select="$listview/@hint" />
      <xsl:with-param name="maximize" select="$listview/@maximize"/>
      <xsl:with-param name="minimize" select="$listview/@minimize"/>
      <xsl:with-param name="icon" select="$listview/@icon"/>
      <xsl:with-param name="width">
        <xsl:choose>
          <xsl:when test="$listview/@width and $listview/@width = '100%'"><xsl:value-of select="$listview/@width"/></xsl:when>
          <xsl:when test="$listview/@width"><xsl:value-of select="$listview/@width"/>px</xsl:when>
          <xsl:otherwise>100%</xsl:otherwise>
        </xsl:choose>
      </xsl:with-param>
      <xsl:with-param name="data">
        <xsl:call-template name="listview-buttons">
          <xsl:with-param name="buttons" select="$listview/buttons" />
        </xsl:call-template>
        <xsl:call-template name="listview-toolbars">
          <xsl:with-param name="toolbarLeft" select="$listview/toolbar[@position = 'top left']"/>
          <xsl:with-param name="toolbarRight" select="$listview/toolbar[@position = 'top right']"/>
        </xsl:call-template>
        <xsl:if test="$listview/items/listitem">
          <xsl:choose>
            <xsl:when test="$listview/@mode = 'tile'">
              <xsl:call-template name="listview-items-tiled">
                <xsl:with-param name="title" select="$listview/@title"/>
                <xsl:with-param name="columns" select="$listview/cols/col"/>
                <xsl:with-param name="items" select="$listview/items/listitem"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="$listview/@mode = 'thumbs'">
              <xsl:call-template name="listview-items-thumbnails">
                <xsl:with-param name="title" select="$listview/@title"/>
                <xsl:with-param name="columns" select="$listview/cols/col"/>
                <xsl:with-param name="items" select="$listview/items/listitem"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="listview-items">
                <xsl:with-param name="title" select="$listview/@title"/>
                <xsl:with-param name="columns" select="$listview/cols/col"/>
                <xsl:with-param name="items" select="$listview/items/listitem"/>
              </xsl:call-template>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
        <xsl:call-template name="listview-toolbars">
          <xsl:with-param name="toolbarLeft" select="$listview/toolbar[@position = 'bottom left']"/>
          <xsl:with-param name="toolbarRight" select="$listview/toolbar[@position = 'bottom right']"/>
          <xsl:with-param name="positionClass">bottomToolbar</xsl:with-param>
        </xsl:call-template>
        <xsl:call-template name="listview-menu">
          <xsl:with-param name="menu" select="$listview/menu"/>
        </xsl:call-template>
        <xsl:call-template name="listview-status">
          <xsl:with-param name="status" select="$listview/status"/>
        </xsl:call-template>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="listview-items">
  <xsl:param name="title"></xsl:param>
  <xsl:param name="columns"/>
  <xsl:param name="items"/>
  <table class="listview">
    <xsl:if test="$columns">
      <xsl:call-template name="listview-items-columns">
        <xsl:with-param name="columns" select="$columns"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:for-each select="$items">
      <tr>
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="@selected">selected</xsl:when>
            <xsl:when test="not(position() mod 2)">even</xsl:when>
            <xsl:otherwise>odd</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@id">
          <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
        </xsl:if>
        <td>
          <xsl:if test="@span and (@span &gt; 1)">
            <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
          </xsl:if>
          <xsl:if test="@indent and @indent &gt; 0">
            <xsl:attribute name="style">padding-left: <xsl:value-of select="@indent * 24"/>px</xsl:attribute>
          </xsl:if>
          <xsl:if test="@hint and @hint != ''">
            <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
            <xsl:if test="$PAPAYA_USE_OVERLIB">
              <xsl:variable name="hint-js">
                <xsl:call-template name="escape-quotes-js">
                  <xsl:with-param name="string" select="@hint" />
                </xsl:call-template>
              </xsl:variable>
              <xsl:attribute name="onmouseout">nd();</xsl:attribute>
              <xsl:attribute name="onmouseover">overlib("<xsl:value-of select="$hint-js"/>"); this.title = "";</xsl:attribute>
            </xsl:if>
          </xsl:if>
          <xsl:choose>
            <xsl:when test="count(node) &gt; 0">
              <xsl:choose>
                <xsl:when test="node/@status = 'closed'">
                  <a href="{node/@href}" class="nodeIcon">
                    <img src="pics/icons/16x16/status/node-closed.png" class="glyph" alt="+"/>
                  </a>
                </xsl:when>
                <xsl:when test="node/@status = 'open'">
                  <a href="{node/@href}" class="nodeIcon">
                    <img src="pics/icons/16x16/status/node-open.png" class="glyph" alt="-"/>
                  </a>
                </xsl:when>
                <xsl:when test="node/@status = 'empty'">
                  <span class="nodeIcon">
                    <img src="pics/icons/16x16/status/node-empty.png" class="glyph" alt=" "/>
                  </span>
                </xsl:when>
              </xsl:choose>
            </xsl:when>
            <xsl:when test="@nhref and @node = 'close'">
              <a href="{@nhref}" class="nodeIcon">
                <img src="pics/icons/16x16/status/node-closed.png" class="glyph" alt="+"/>
              </a>
            </xsl:when>
            <xsl:when test="@nhref and @node = 'open'">
              <a href="{@nhref}" class="nodeIcon">
                <img src="pics/icons/16x16/status/node-open.png" class="glyph" alt="-"/>
              </a>
            </xsl:when>
            <xsl:when test="@node = 'empty'">
              <span class="nodeIcon">
                <img src="pics/icons/16x16/status/node-empty.png" class="glyph" alt=" "/>
              </span>
            </xsl:when>
          </xsl:choose>

          <xsl:if test="@image">
            <xsl:variable name="glyphsrc">
              <xsl:call-template name="icon-url">
                <xsl:with-param name="icon-src" select="@image"/>
              </xsl:call-template>
            </xsl:variable>
            <xsl:choose>
              <xsl:when test="@href">
                <a href="{@href}" class="itemIcon" tabindex="0"><img src="{$glyphsrc}" class="glyph" alt="" title="" /></a>
              </xsl:when>
              <xsl:otherwise>
                <span class="itemIcon"><img src="{$glyphsrc}" class="glyph" alt="" title="" /></span>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:if>

          <xsl:call-template name="listview-item-part-title">
            <xsl:with-param name="item" select="."/>
          </xsl:call-template>

          <xsl:if test="@subtitle">
            <span class="itemSubTitle"><xsl:value-of select="@subtitle"/></span>
          </xsl:if>
        </td>
        <xsl:for-each select="subitem">
          <td>
            <xsl:if test="@span and (@span &gt; 1)">
              <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@align or @overflow or @wrap">
              <xsl:attribute name="style">
                <xsl:if test="@align">text-align: <xsl:value-of select="@align"/>; </xsl:if>
                <xsl:if test="@overflow = 'hidden'">overflow: hidden; </xsl:if>
                <xsl:if test="@wrap = 'nowrap'">white-space: nowrap; </xsl:if>
              </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates />
          </td>
        </xsl:for-each>
      </tr>
    </xsl:for-each>
  </table>
</xsl:template>

<xsl:template name="listview-items-tiled">
  <xsl:param name="columns"/>
  <xsl:param name="items"/>
  <xsl:if test="$columns">
    <table class="listview">
      <xsl:call-template name="listview-items-columns">
        <xsl:with-param name="columns" select="$columns"/>
      </xsl:call-template>
    </table>
  </xsl:if>
  <div class="listviewBackground">
    <xsl:for-each select="$items">
      <div class="listitemTile">
        <div>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="@selected">tile selected</xsl:when>
              <xsl:otherwise>tile</xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:if test="@hint">
	          <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
	        </xsl:if>
	        <div class="icon">
	          <xsl:variable name="glyphsrc">
	            <xsl:choose>
	              <xsl:when test="starts-with(@image, './') and contains(@image, '?')">
	                <xsl:value-of select="@image"/>
	                <xsl:text>&amp;size=48</xsl:text>
	              </xsl:when>
                <xsl:when test="starts-with(@image, 'http://')"><xsl:value-of select="@image"/></xsl:when>
                <xsl:when test="starts-with(@image, './')"><xsl:value-of select="@image"/></xsl:when>
	              <xsl:when test="starts-with(@image, '../')"><xsl:value-of select="@image"/></xsl:when>
	              <xsl:when test="not(@image) or (@image = '') or (@image = false())">pics/tpoint.gif</xsl:when>
	              <xsl:otherwise>pics/icons/48x48/<xsl:value-of select="@image"/></xsl:otherwise>
	            </xsl:choose>
	          </xsl:variable>
	          <xsl:choose>
	            <xsl:when test="@href">
	              <a href="{@href}"><img src="{$glyphsrc}" class="glyph" alt="" title="{@hint}"/></a>
	            </xsl:when>
	            <xsl:otherwise>
	              <img src="{$glyphsrc}" class="glyph" alt="" title="{@hint}" align="middle"/>
	            </xsl:otherwise>
	          </xsl:choose>
	        </div>
	        <div class="subitems">
	          <xsl:text> </xsl:text>
	          <xsl:for-each select="subitem">
	            <span class="subitem"><xsl:apply-templates /></span><br/>
	          </xsl:for-each>
	        </div>
	        <div class="data">
            <xsl:call-template name="listview-item-part-title">
              <xsl:with-param name="item" select="."/>
            </xsl:call-template>
	          <xsl:if test="@subtitle">
	            <xsl:text> </xsl:text>
	            <span class="description"><xsl:value-of select="@subtitle"/></span>
	          </xsl:if>
	        </div>
          <xsl:call-template name="float-fix"/>
        </div>
      </div>
    </xsl:for-each>
    <xsl:call-template name="float-fix"/>
  </div>
</xsl:template>

<xsl:template name="listview-items-thumbnails">
  <xsl:param name="columns"/>
  <xsl:param name="items"/>
  <xsl:if test="$columns">
    <table class="listview">
      <xsl:call-template name="listview-items-columns">
        <xsl:with-param name="columns" select="$columns"/>
      </xsl:call-template>
    </table>
  </xsl:if>
  <div class="listviewBackground">
    <xsl:for-each select="$items">
      <div class="listitemThumbnail">
        <xsl:if test="@hint">
          <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
        </xsl:if>
        <div>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="@selected">thumbnail selected</xsl:when>
              <xsl:otherwise>thumbnail</xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <div class="image">
	          <xsl:variable name="glyphsrc">
	            <xsl:choose>
	              <xsl:when test="starts-with(@image, './') and contains(@image, '?')">
	                <xsl:value-of select="@image"/>
	                <xsl:text>&amp;size=48</xsl:text>
	              </xsl:when>
                <xsl:when test="starts-with(@image, 'http://')"><xsl:value-of select="@image"/></xsl:when>
	              <xsl:when test="starts-with(@image, './')"><xsl:value-of select="@image"/></xsl:when>
	              <xsl:when test="starts-with(@image, '../')"><xsl:value-of select="@image"/></xsl:when>
	              <xsl:when test="not(@image) or (@image = '') or (@image = false())">pics/tpoint.gif</xsl:when>
	              <xsl:otherwise>pics/icons/48x48/<xsl:value-of select="@image"/></xsl:otherwise>
	            </xsl:choose>
	          </xsl:variable>
	          <xsl:choose>
	            <xsl:when test="@href">
	              <a href="{@href}"><img src="{$glyphsrc}" class="glyph" alt="" title="{@hint}"/></a>
	            </xsl:when>
	            <xsl:otherwise>
	              <img src="{$glyphsrc}" class="glyph" alt="" title="{@hint}" align="middle"/>
	            </xsl:otherwise>
	          </xsl:choose>
          </div>
         <div class="data">
            <xsl:call-template name="listview-item-part-title">
              <xsl:with-param name="item" select="."/>
            </xsl:call-template>
          </div>
          <xsl:call-template name="float-fix"/>
        </div>
      </div>
    </xsl:for-each>
    <xsl:call-template name="float-fix"/>
  </div>
</xsl:template>

<xsl:template name="listview-buttons">
  <xsl:param name="buttons"/>
  <xsl:if test="count($buttons//button) &gt; 0">
    <xsl:choose>
      <xsl:when test="count($buttons/left/button) &gt; 0 or count($buttons/right/button) &gt; 0">
        <div class="menuBar">
          <ul style="float: left;">
            <xsl:for-each select="$buttons/left/button">
              <xsl:call-template name="menu-element">
                <xsl:with-param name="element" select="." />
              </xsl:call-template>
            </xsl:for-each>
          </ul>
          <ul style="float: right;">
            <xsl:for-each select="$buttons/right/button">
              <xsl:call-template name="menu-element">
                <xsl:with-param name="element" select="." />
              </xsl:call-template>
            </xsl:for-each>
          </ul>
          <xsl:call-template name="float-fix"/>
        </div>
      </xsl:when>
      <xsl:otherwise>
        <div class="menuBar">
          <ul>
            <xsl:for-each select="$buttons//button">
              <xsl:call-template name="menu-element">
                <xsl:with-param name="element" select="." />
              </xsl:call-template>
            </xsl:for-each>
          </ul>
          <xsl:call-template name="float-fix"/>
        </div>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

<xsl:template name="listview-items-columns">
  <xsl:param name="columns" />
  <tr class="columns">
    <xsl:for-each select="$columns">
      <th>
        <xsl:choose>
          <xsl:when test="position() = 1">
            <xsl:attribute name="class">first</xsl:attribute>
          </xsl:when>
          <xsl:when test="position() = last()">
            <xsl:attribute name="class">last</xsl:attribute>
          </xsl:when>
        </xsl:choose>
        <xsl:if test="@align">
          <xsl:attribute name="style">text-align: <xsl:value-of select="@align"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@span and @span &gt; 1">
          <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@hint and @hint != ''">
          <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
          <xsl:if test="$PAPAYA_USE_OVERLIB">
            <xsl:variable name="hint-js">
              <xsl:call-template name="escape-quotes-js">
                <xsl:with-param name="string" select="@hint" />
              </xsl:call-template>
            </xsl:variable>
            <xsl:attribute name="onmouseout">nd();</xsl:attribute>
            <xsl:attribute name="onmouseover">overlib("<xsl:value-of select="$hint-js"/>"); this.title = "";</xsl:attribute>
          </xsl:if>
        </xsl:if>
        <xsl:choose>
          <xsl:when test="@href">
            <a href="{@href}" style="display: block;">
              <xsl:choose>
                <xsl:when test="@sort = 'asc'"> <img src="{$PAPAYA_PATH_SKIN}pics/asc.png" class="columnBullet" alt="ASC" /></xsl:when>
                <xsl:when test="@sort = 'desc'"> <img src="{$PAPAYA_PATH_SKIN}pics/desc.png" class="columnBullet" alt="DESC" /></xsl:when>
                <xsl:when test="@sort = 'none'"> <img src="{$PAPAYA_PATH_SKIN}pics/nosort.png" class="columnBullet" alt="SORT" /></xsl:when>
              </xsl:choose>
              <xsl:value-of select="."/>
            </a>
          </xsl:when>
          <xsl:otherwise><xsl:apply-templates /></xsl:otherwise>
        </xsl:choose>
      </th>
    </xsl:for-each>
  </tr>
</xsl:template>

<xsl:template name="listview-item-part-title">
  <xsl:param name="item"/>
  <xsl:choose>
    <xsl:when test="$item/caption">
      <span class="itemTitle"><xsl:apply-templates select="$item/caption"/></span>
    </xsl:when>
    <xsl:when test="$item/@emphased and $item/@href and $item/@title != ''">
      <a href="{$item/@href}" class="itemTitle"><b><xsl:value-of select="$item/@title"/></b></a>
    </xsl:when>
    <xsl:when test="$item/@emphased and $item/@title != ''">
      <span class="itemTitle"><b><xsl:value-of select="$item/@title"/></b></span>
    </xsl:when>
    <xsl:when test="$item/@href and $item/@title != ''">
      <a href="{@href}" class="itemTitle"><xsl:value-of select="$item/@title"/></a>
    </xsl:when>
    <xsl:when test="$item/@title != ''">
      <span class="itemTitle"><xsl:value-of select="$item/@title"/></span>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="listview-toolbars">
  <xsl:param name="toolbarLeft"/>
  <xsl:param name="toolbarRight"/>
  <xsl:param name="positionClass">topToolbar</xsl:param>
  <xsl:if test="$toolbarLeft or $toolbarRight">
    <div class="menuBar {$positionClass}">
      <xsl:if test="$toolbarLeft">
        <xsl:call-template name="listview-toolbar">
          <xsl:with-param name="toolbar" select="$toolbarLeft"/>
          <xsl:with-param name="float">left</xsl:with-param>
        </xsl:call-template>
      </xsl:if>
      <xsl:if test="$toolbarRight">
        <xsl:call-template name="listview-toolbar">
          <xsl:with-param name="toolbar" select="$toolbarRight"/>
          <xsl:with-param name="float">right</xsl:with-param>
        </xsl:call-template>
      </xsl:if>
      <xsl:call-template name="float-fix"/>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="listview-toolbar">
  <xsl:param name="toolbar"/>
  <xsl:param name="float">left</xsl:param>
  <ul style="float: {$float};">
    <xsl:for-each select="$toolbar/button|$toolbar/combo|$toolbar/separator">
      <xsl:call-template name="menu-element">
        <xsl:with-param name="element" select="." />
      </xsl:call-template>
    </xsl:for-each>
  </ul>
</xsl:template>

<xsl:template name="listview-menu">
  <xsl:param name="menu" />
  <xsl:if test="$menu">
    <div class="listviewMenu">
      <div class="menuBar">
        <xsl:call-template name="menu-elements">
          <xsl:with-param name="elements" select="$menu/button"/>
        </xsl:call-template>
        <xsl:call-template name="float-fix"/>
      </div>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="listview-status">
  <xsl:param name="status"/>
  <xsl:if test="$status">
    <div class="statusBar">
      <div class="statusBarArtWork">
        <xsl:apply-templates select="$status/*|$status/text()" />
      </div>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template match="listview">
  <xsl:call-template name="listview">
    <xsl:with-param name="listview" select="."/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>