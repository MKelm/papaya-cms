<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same name in your xsl file. This will override the imported template from
  this file.
-->

<xsl:param name="PAGING_SHOW_SYMBOLS" select="true()" />
<xsl:param name="PAGING_SHOW_TEXT" select="true()" />
<xsl:param name="PAGING_SHOW_LINKS_ONLY" select="false()" />

<xsl:param name="PAGING_LINK_MAXIMUM" select="3" />

<xsl:param name="PAGING_SYMBOL_FIRST">&#xAB;</xsl:param>
<xsl:param name="PAGING_SYMBOL_PREVIOUS">&#x2C2;</xsl:param>
<xsl:param name="PAGING_SYMBOL_NEXT">&#x2C3;</xsl:param>
<xsl:param name="PAGING_SYMBOL_LAST">&#xBB;</xsl:param>

<!--
  a generic template for numbered paging links
    start - start number
    end - ending number
    selection - currently selected number
    href - base link string
    stepSize - offset size (multiplied with page number - 1 and appended to href)
    count - maximum count of page links (needs to be larger 2 to have an effect, depends on selection)
    showSymbols - show symbols for first/prev/... links
    showText - show text for first/prev/... links
    showLinksOnly - show only items with a link
-->
<xsl:template name="paging-links-numbered">
  <xsl:param name="start" select="0"/>
  <xsl:param name="end" select="0"/>
  <xsl:param name="selection" select="5"/>
  <xsl:param name="href">#</xsl:param>
  <xsl:param name="stepSize" select="1"/>
  <xsl:param name="count" select="$PAGING_LINK_MAXIMUM"/>
  <xsl:param name="showSymbols" select="$PAGING_SHOW_SYMBOLS"/>
  <xsl:param name="showText" select="$PAGING_SHOW_TEXT"/>
  <xsl:param name="showLinksOnly" select="$PAGING_SHOW_LINKS_ONLY"/>
  <ul>
    <xsl:call-template name="paging-link-item">
      <xsl:with-param name="showLinksOnly" select="$showLinksOnly"/>
      <xsl:with-param name="title">
        <xsl:if test="$showSymbols">
          <xsl:value-of select="$PAGING_SYMBOL_FIRST" />
        </xsl:if>
        <xsl:if test="$showSymbols and $showText">
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="$showText">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">PAGE_FIRST</xsl:with-param>
          </xsl:call-template>
        </xsl:if>
      </xsl:with-param>
      <xsl:with-param name="href">
        <xsl:if test="$selection &gt; $start">
          <xsl:value-of select="concat($href, '0')" />
        </xsl:if>
      </xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="paging-link-item">
      <xsl:with-param name="showLinksOnly" select="$showLinksOnly"/>
      <xsl:with-param name="title">
        <xsl:if test="$showSymbols">
          <xsl:value-of select="$PAGING_SYMBOL_PREVIOUS" />
        </xsl:if>
        <xsl:if test="$showSymbols and $showText">
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="$showText">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">PAGE_PREVIOUS</xsl:with-param>
          </xsl:call-template>
        </xsl:if>
      </xsl:with-param>
      <xsl:with-param name="href">
        <xsl:if test="$selection &gt; 1">
          <xsl:value-of select="concat($href, ($selection - 2) * $stepSize)" />
        </xsl:if>
      </xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="paging-links-numbers">
      <xsl:with-param name="start" select="$start" />
      <xsl:with-param name="end" select="$end" />
      <xsl:with-param name="selection" select="$selection" />
      <xsl:with-param name="count" select="$count" />
      <xsl:with-param name="href" select="$href" />
      <xsl:with-param name="stepSize" select="$stepSize"/>
    </xsl:call-template>
    <xsl:call-template name="paging-link-item">
      <xsl:with-param name="showLinksOnly" select="$showLinksOnly"/>
      <xsl:with-param name="title">
        <xsl:if test="$showSymbols">
          <xsl:value-of select="$PAGING_SYMBOL_NEXT" />
        </xsl:if>
        <xsl:if test="$showSymbols and $showText">
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="$showText">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">PAGE_NEXT</xsl:with-param>
          </xsl:call-template>
        </xsl:if>
      </xsl:with-param>
      <xsl:with-param name="href">
        <xsl:if test="$selection &lt; $end">
          <xsl:value-of select="concat($href, $selection * $stepSize)" />
        </xsl:if>
      </xsl:with-param>
    </xsl:call-template>
    <xsl:call-template name="paging-link-item">
      <xsl:with-param name="showLinksOnly" select="$showLinksOnly"/>
      <xsl:with-param name="title">
        <xsl:if test="$showSymbols">
          <xsl:value-of select="$PAGING_SYMBOL_LAST" />
        </xsl:if>
        <xsl:if test="$showSymbols and $showText">
          <xsl:text> </xsl:text>
        </xsl:if>
        <xsl:if test="$showText">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">PAGE_LAST</xsl:with-param>
          </xsl:call-template>
        </xsl:if>
      </xsl:with-param>
      <xsl:with-param name="href">
        <xsl:if test="$selection &lt; $end">
          <xsl:value-of select="concat($href, ($end - 1) * $stepSize)" />
        </xsl:if>
      </xsl:with-param>
    </xsl:call-template>
  </ul>
</xsl:template>

<xsl:template name="paging-links-numbers">
  <xsl:param name="start" select="0"/>
  <xsl:param name="end" select="0"/>
  <xsl:param name="selection" select="0"/>
  <xsl:param name="stepSize" select="1"/>
  <xsl:param name="href">#</xsl:param>
  <xsl:param name="current" select="$start"/>
  <xsl:param name="count" select="0"/>
  <xsl:param name="isRecursion" select="false()"/>
  <xsl:choose>
    <xsl:when test="not($isRecursion) and $selection &gt; 1 and $count &gt; 2 and $count &lt;= ($end - $start)">
      <xsl:variable name="min" select="$selection - ceiling($count div 2) + 1"/>
      <xsl:variable name="max" select="$selection + floor($count div 2)"/>
      <xsl:call-template name="paging-links-numbers">
        <xsl:with-param name="isRecursion" select="true()"/>
        <xsl:with-param name="selection" select="$selection" />
        <xsl:with-param name="count" select="$count"/>
        <xsl:with-param name="href" select="$href" />
        <xsl:with-param name="start">
          <xsl:choose>
            <xsl:when test="$min &gt;= $start and $max &lt;= $end">
              <xsl:value-of select="$min"/>
            </xsl:when>
            <xsl:when test="$max &gt; $end and ($end - $count &gt;= $start)">
              <xsl:value-of select="$end - $count + 1"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$start"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
        <xsl:with-param name="end">
          <xsl:choose>
            <xsl:when test="$min &gt; $start and $max &lt; $end">
              <xsl:value-of select="$max"/>
            </xsl:when>
            <xsl:when test="$min &lt;= $start and ($start + $count &gt;= $end)">
              <xsl:value-of select="$start + $count -1"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$end"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:choose>
        <xsl:when test="$current &lt; $end">
          <xsl:call-template name="paging-link-item">
            <xsl:with-param name="title" select="$current"/>
            <xsl:with-param name="href" select="concat($href, (($current - 1) * $stepSize))" />
            <xsl:with-param name="class">
              <xsl:choose>
                <xsl:when test="$current = $start and $current = $selection">first selected</xsl:when>
                <xsl:when test="$current = $selection">selected</xsl:when>
                <xsl:when test="$current = $start">first</xsl:when>
              </xsl:choose>
            </xsl:with-param>
          </xsl:call-template>
          <xsl:call-template name="paging-links-numbers">
            <xsl:with-param name="current" select="$current + 1" />
            <xsl:with-param name="start" select="$start" />
            <xsl:with-param name="end" select="$end" />
            <xsl:with-param name="count" select="$count" />
            <xsl:with-param name="selection" select="$selection" />
            <xsl:with-param name="href" select="$href" />
            <xsl:with-param name="isRecursion" select="true()"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="$current = $end">
          <xsl:call-template name="paging-link-item">
            <xsl:with-param name="title" select="$current"/>
            <xsl:with-param name="href" select="concat($href, (($current - 1) * $stepSize))" />
            <xsl:with-param name="class">
              <xsl:choose>
                <xsl:when test="$current = $start and  $current = $selection">first last selected</xsl:when>
                <xsl:when test="$current = $selection">last selected</xsl:when>
                <xsl:when test="$current = $start">first last</xsl:when>
                <xsl:otherwise>last</xsl:otherwise>
              </xsl:choose>
            </xsl:with-param>
          </xsl:call-template>
        </xsl:when>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="paging-link-item">
  <xsl:param name="title">P</xsl:param>
  <xsl:param name="href"/>
  <xsl:param name="class"></xsl:param>
  <xsl:param name="showLinksOnly" select="false()"/>
  <xsl:if test="not($showLinksOnly) or ($href and $href != '')">
    <li>
      <xsl:if test="$class and $class != ''">
        <xsl:attribute name="class">
          <xsl:value-of select="$class" />
        </xsl:attribute>
      </xsl:if>
      <xsl:call-template name="paging-link">
        <xsl:with-param name="title" select="$title"/>
        <xsl:with-param name="href" select="$href" />
      </xsl:call-template>
    </li>
  </xsl:if>
</xsl:template>

<xsl:template name="paging-link">
  <xsl:param name="title">P</xsl:param>
  <xsl:param name="href"/>
  <xsl:choose>
    <xsl:when test="$href and $href != ''">
      <a href="{$href}"><xsl:value-of select="$title"/></a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$title"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>