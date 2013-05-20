<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="../../../../_functions/format-bytes.xsl"/>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))"/>
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<!-- show download list or teaser mode -->
<xsl:param name="FILES_SHOW_LIST" select="false()"/>
<!-- show a detail link (or details if disabled)-->
<xsl:param name="FILES_SHOW_LINK_DETAIL" select="false()"/>
<!-- show a download link on overviews/lists -->
<xsl:param name="FILES_SHOW_LINK_DOWNLOAD" select="true()"/>
<!--
  byte formattigg mode:
    decimal divides by 1000 and uses decimal units kilo, mega, ...
    binary divides by 1024 and uses binary units kibi, mibi, ...
-->
<xsl:param name="FILES_BYTE_FORMAT">binary</xsl:param>
<!-- digit length for byte output (without decimal seperator and unit) -->
<xsl:param name="FILES_BYTE_DIGITS" select="3"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_files.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_download'">
      <xsl:call-template name="module-content-download">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_upload'">
      <xsl:call-template name="module-content-upload">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-default">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="multiple-columns-item">
  <xsl:param name="item"/>
  <xsl:param name="itemType">item</xsl:param>
  <xsl:choose>
    <xsl:when test="$itemType = 'file'">
      <h2>
        <xsl:choose>
          <xsl:when test="$item/@file_title and $item/@file_title != '' and $item/@file_title != $item/@file_name">
            <xsl:value-of select="$item/@file_title"/>
            <xsl:text> </xsl:text>
            <span class="subTitle">
              <xsl:value-of select="$item/@file_name"/>
              <xsl:if test="not($FILES_SHOW_LINK_DETAIL)">
                <xsl:text> (</xsl:text>
                <xsl:call-template name="format-bytes">
                  <xsl:with-param name="bytes" select="$item/@file_size"/>
                  <xsl:with-param name="mode" select="$FILES_BYTE_FORMAT"/>
                  <xsl:with-param name="digits" select="$FILES_BYTE_DIGITS"/>
                </xsl:call-template>
                <xsl:text>)</xsl:text>
              </xsl:if>
            </span>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$item/@file_name"/>
            <xsl:if test="not($FILES_SHOW_LINK_DETAIL)">
              <xsl:text> </xsl:text>
              <span class="subTitle">
                <xsl:call-template name="format-bytes">
                  <xsl:with-param name="bytes" select="$item/@file_size"/>
                  <xsl:with-param name="mode" select="$FILES_BYTE_FORMAT"/>
                  <xsl:with-param name="digits" select="$FILES_BYTE_DIGITS"/>
                </xsl:call-template>
              </span>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
      </h2>
      <xsl:choose>
        <xsl:when test="$FILES_SHOW_LINK_DETAIL">
          <a href="{$item/@href}" class="more">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">MORE</xsl:with-param>
            </xsl:call-template>
          </a>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="$item/node()" mode="richtext"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="$FILES_SHOW_LINK_DOWNLOAD">
        <a href="{$item/@download}" class="download">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">DOWNLOAD</xsl:with-param>
          </xsl:call-template>
        </a>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="module-content-category-item">
        <xsl:with-param name="item" select="$item" />
        <xsl:with-param name="itemType" select="$itemType" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-download">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
  </xsl:call-template>
  <xsl:choose>
    <xsl:when test="$pageContent/file">
      <xsl:variable name="item" select="$pageContent/file"/>
      <h2>
        <xsl:choose>
          <xsl:when test="$item/@file_title and $item/@file_title != '' and $item/@file_title != $item/@file_name">
            <xsl:value-of select="$item/@file_title"/>
            <xsl:text> </xsl:text>
            <span class="subTitle"><xsl:value-of select="$item/@file_name"/></span>
          </xsl:when>
          <xsl:otherwise><xsl:value-of select="$item/@file_name"/></xsl:otherwise>
        </xsl:choose>
      </h2>
      <xsl:if test="count($item/node()) &gt; 0">
        <xsl:apply-templates select="$item/node()" mode="richtext"/>
      </xsl:if>
      <xsl:if test="$FILES_SHOW_LINK_DOWNLOAD">
        <a href="{$item/@download}" class="download">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">DOWNLOAD</xsl:with-param>
          </xsl:call-template>
        </a>
      </xsl:if>
    </xsl:when>
    <xsl:when test="$pageContent/files/file and $FILES_SHOW_LIST">
      <table class="fileList">
        <thead>
          <tr>
            <th class="title"><xsl:value-of select="$pageContent/captions/file_name" /></th>
            <th class="date"><xsl:value-of select="$pageContent/captions/file_date" /></th>
            <th class="size"><xsl:value-of select="$pageContent/captions/file_size" /></th>
            <xsl:if test="$FILES_SHOW_LINK_DOWNLOAD">
              <th class="link"><xsl:value-of select="$pageContent/captions/download" /></th>
            </xsl:if>
          </tr>
        </thead>
        <tbody>
          <xsl:for-each select="$pageContent/files/file">
            <tr id="{generate-id()}">
              <xsl:attribute name="class">
                <xsl:choose>
                  <xsl:when test="not(position() mod 2)">even</xsl:when>
                  <xsl:otherwise>odd</xsl:otherwise>
                </xsl:choose>
              </xsl:attribute>
              <td class="title">
                <a href="{@href}">
                  <xsl:value-of select="@file_name" />
                </a>
              </td>
              <td class="date">
                <xsl:call-template name="format-date">
                  <xsl:with-param name="date" select="@file_date"/>
                </xsl:call-template>
              </td>
              <td class="size">
                <xsl:value-of select="@file_size"/>
              </td>
              <xsl:if test="$FILES_SHOW_LINK_DOWNLOAD">
                <td class="link">
                  <a href="{@download}">
                    <xsl:call-template name="language-text">
                      <xsl:with-param name="text">DOWNLOAD</xsl:with-param>
                    </xsl:call-template>
                  </a>
                </td>
              </xsl:if>
            </tr>
          </xsl:for-each>
        </tbody>
      </table>
    </xsl:when>
    <xsl:when test="$pageContent/files/file">
      <xsl:call-template name="multiple-columns">
        <xsl:with-param name="items" select="$pageContent/files/file"/>
        <xsl:with-param name="itemType">file</xsl:with-param>
      </xsl:call-template>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="module-content-upload">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent" />
    <xsl:with-param name="withText" select="not($pageContent/message/node())"/>
  </xsl:call-template>
  <xsl:if test="$pageContent/message/node()">
    <div class="message">
      <xsl:apply-templates select="$pageContent/message/node()" mode="richtext"/>
    </div>
  </xsl:if>
  <xsl:if test="$pageContent/uploaddialog">
    <xsl:call-template name="dialog">
      <xsl:with-param name="dialog" select="$pageContent/uploaddialog" />
    </xsl:call-template>
  </xsl:if>
  <xsl:if test="$pageContent/uploadedFiles/filedata">
    <table class="fileList">
      <thead>
        <tr>
          <th class="title">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FILE_NAME</xsl:with-param>
            </xsl:call-template>
          </th>
          <th class="date">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FILE_DATE</xsl:with-param>
            </xsl:call-template>
          </th>
          <th class="size">
            <xsl:call-template name="language-text">
              <xsl:with-param name="text">FILE_SIZE</xsl:with-param>
            </xsl:call-template>
          </th>
        </tr>
      </thead>
      <tbody>
        <xsl:for-each select="$pageContent/uploadedFiles/filedata">
          <tr id="{generate-id()}">
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="not(position() mod 2)">even</xsl:when>
                <xsl:otherwise>odd</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <td class="title">
              <a href="{@download}">
                <xsl:if test="title and title != ''">
                  <xsl:attribute name="title"><xsl:value-of select="title"/></xsl:attribute>
                </xsl:if>
                <xsl:value-of select="file_name" />
              </a>
            </td>
            <td class="date">
              <xsl:call-template name="format-date">
                <xsl:with-param name="date" select="file_date"/>
              </xsl:call-template>
            </td>
            <td class="size">
              <xsl:call-template name="format-bytes">
                <xsl:with-param name="bytes" select="file_size"/>
                <xsl:with-param name="mode" select="$FILES_BYTE_FORMAT"/>
                <xsl:with-param name="digits" select="$FILES_BYTE_DIGITS"/>
              </xsl:call-template>
            </td>
          </tr>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:if>
</xsl:template>

<xsl:template name="format-bytes-output">
  <xsl:param name="numberString"></xsl:param>
  <xsl:param name="numberUnit"></xsl:param>
  <xsl:value-of select="$numberString" />
  <xsl:text> </xsl:text>
  <span class="unit"><xsl:value-of select="$numberUnit"/></span>
</xsl:template>

</xsl:stylesheet>
