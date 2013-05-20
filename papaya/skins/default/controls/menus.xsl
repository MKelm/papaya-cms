<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="menu-bar">
  <xsl:param name="menu" />
  <div class="menuBar">
    <xsl:choose>
      <xsl:when test="$menu/group">
        <xsl:for-each select="$menu/group">
          <div class="group">
            <h2 class="nonGraphicBrowser"><xsl:value-of select="@title" /></h2>
            <xsl:call-template name="menu-elements">
              <xsl:with-param name="elements" select="button"/>
              <xsl:with-param name="icon-size">22</xsl:with-param>
            </xsl:call-template>
            <div class="name">
              <span class="nonGraphicBrowser">/</span>
              <xsl:value-of select="@title" />
            </div>
          </div>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="menu-elements">
          <xsl:with-param name="elements" select="*[name() != 'group']"/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:call-template name="float-fix"/>
  </div>
</xsl:template>

<xsl:template name="menu-elements">
  <xsl:param name="elements"/>
  <xsl:param name="icon-size">16</xsl:param>
  <xsl:if test="$elements">
    <div class="buttons">
      <ul>
        <xsl:for-each select="$elements">
          <xsl:call-template name="menu-element">
            <xsl:with-param name="element" select="."/>
            <xsl:with-param name="icon-size" select="$icon-size"/>
          </xsl:call-template>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="menu-element">
  <xsl:param name="element"/>
  <xsl:param name="icon-size">16</xsl:param>
  <xsl:choose>
    <xsl:when test="name($element) = 'seperator' or name($element) = 'separator'">
      <xsl:call-template name="menu-separator"/>
    </xsl:when>
    <xsl:when test="name($element) = 'button'">
      <xsl:call-template name="menu-button">
        <xsl:with-param name="button" select="$element"/>
        <xsl:with-param name="icon-size" select="$icon-size"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="name($element) = 'combo'">
      <xsl:call-template name="menu-combobox">
        <xsl:with-param name="combobox" select="$element"/>
        <xsl:with-param name="icon-size" select="$icon-size"/>
      </xsl:call-template>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="menu-button">
  <xsl:param name="button" />
  <xsl:param name="icon-size">16</xsl:param>
  <xsl:if test="$button">
    <xsl:variable name="hint">
      <xsl:value-of select="$button/@hint" />
      <xsl:if test="$button/@accesskey"> (<xsl:value-of select="$button/@accesskey" />) </xsl:if>
    </xsl:variable>
    <xsl:variable name="glyph">
      <xsl:choose>
        <xsl:when test="$button/@glyphscript">
          <xsl:value-of select="$button/@glyphscript" />
          <xsl:text>&amp;size=</xsl:text>
          <xsl:value-of select="$icon-size"/>
          <xsl:text>&amp;behavior=.png</xsl:text>
        </xsl:when>
        <xsl:when test="contains($button/@glyph, '?')">
          <xsl:value-of select="$button/@glyph" />
          <xsl:text>&amp;size=</xsl:text>
          <xsl:value-of select="$icon-size"/>
          <xsl:text>&amp;behavior=.png</xsl:text>
        </xsl:when>
        <xsl:when test="$button/@glyph">
          <xsl:call-template name="icon-url">
            <xsl:with-param name="icon-src" select="$button/@glyph"/>
            <xsl:with-param name="icon-size" select="$icon-size"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="target">
      <xsl:choose>
        <xsl:when test="$button/@target"><xsl:value-of select="$button/@target" /></xsl:when>
        <xsl:otherwise>_self</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <li>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="$button/@down">button selected</xsl:when>
          <xsl:otherwise>button</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:if test="$glyph != ''">
        <xsl:choose>
          <xsl:when test="$button/@href">
            <a href="{$button/@href}" class="icon" target="{$target}" title="{$hint}" tabindex="0"><img src="{$glyph}"  alt=""/></a>
          </xsl:when>
          <xsl:otherwise>
            <span class="icon"><img src="{$glyph}"  alt=""/></span>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
      <xsl:if test="$button/@title and $button/@title != ''">
        <xsl:variable name="onclick">
          <xsl:choose>
            <xsl:when test="substring-after($button/@href, 'javascript:') != ''">return <xsl:value-of select="substring-after($button/@href, 'javascript:')"/></xsl:when>
            <xsl:otherwise></xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:choose>
          <xsl:when test="$button/@href">
            <a href="{$button/@href}" class="caption" target="{$target}" title="{$hint}">
              <xsl:choose>
                <xsl:when test="$onclick != ''">
                  <xsl:attribute name="href">#</xsl:attribute>
                  <xsl:attribute name="onclick"><xsl:value-of select="$onclick" /></xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:attribute name="href"><xsl:value-of select="$button/@href" /></xsl:attribute>
                </xsl:otherwise>
              </xsl:choose>
              <xsl:if test="$button/@accesskey">
                <xsl:attribute name="accesskey"><xsl:value-of select="$button/@accesskey" /></xsl:attribute>
              </xsl:if>
              <xsl:value-of select="$button/@title" />
            </a>
          </xsl:when>
          <xsl:otherwise>
            <span class="caption"><xsl:value-of select="$button/@title" /></span>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </li>
  </xsl:if>
</xsl:template>

<xsl:template name="menu-separator">
  <li class="separator">&#160;</li>
</xsl:template>

<xsl:template name="menu-combobox">
  <xsl:param name="combobox" />
  <li class="combobox">
    <form action="{$combobox/@action}" method="get">
      <xsl:for-each select="$combobox/parameter">
        <input type="hidden" name="{@name}" value="{@value}"/>
      </xsl:for-each>
      <xsl:if test="$combobox/@title and $combobox/@title != ''">
        <span class="caption"><xsl:value-of select="$combobox/@title" /></span>
      </xsl:if>
      <select name="{$combobox/@name}" onchange="this.form.submit();">
        <xsl:for-each select="$combobox/option">
          <option value="{@value}">
            <xsl:if test="@selected">
              <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="text()"/>
          </option>
        </xsl:for-each>
      </select>
    </form>
  </li>
</xsl:template>

<xsl:template name="tool-bar">
  <xsl:param name="menu"/>
  <div class="toolBar">
    <xsl:call-template name="menu-bar">
      <xsl:with-param name="menu" select="menu"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template match="toolbar">
  <xsl:call-template name="tool-bar">
    <xsl:with-param name="menu" select="toolbar"/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>