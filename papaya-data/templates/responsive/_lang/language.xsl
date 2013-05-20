<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:import href="./datetime.xsl" />

<xsl:param name="PAPAYA_DEBUG_LANGUAGE_PHRASES" select="false()"/>
<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="PAGE_LANGUAGE_GROUP" select="substring-before($PAGE_LANGUAGE, '-')" />

<!--
  the templates uses 3 documents for the current language
  1) loaded in page_main.xsl (contains individual texts)
  2) loaded in module specific template file (allows a module template to provide additional texts)
  3) loaded here in language.xsl (contains all the default texts)
-->
<xsl:param name="LANGUAGE_TEXTS_CURRENT"/>
<xsl:param name="LANGUAGE_MODULE_CURRENT"/>
<xsl:param name="LANGUAGE_DEFAULTS_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))"/>
<!--
  for texts missing a localisation en-US is loaded
-->
<xsl:param name="LANGUAGE_TEXTS_FALLBACK"/>
<xsl:param name="LANGUAGE_MODULE_FALLBACK"/>
<xsl:param name="LANGUAGE_DEFAULTS_FALLBACK" select="document('en-US.xml')"/>

<xsl:param name="LANGUAGE_FORMATS" select="document('formats.xml')/formats" />

<xsl:template name="language-accessiblity-separator">
  <span class="accessibilityElement accessibilitySeparator">
    <xsl:choose>
      <xsl:when test="$PAGE_LANGUAGE_GROUP = 'de'">
        <xsl:text>. </xsl:text>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> | </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </span>
</xsl:template>

<xsl:template name="format-currency">
  <xsl:param name="float"/>
  <xsl:param name="decimalSeparator"></xsl:param>
  <xsl:param name="pattern">#,##0.00</xsl:param>
  <xsl:call-template name="format-number">
    <xsl:with-param name="float" select="$float"/>
    <xsl:with-param name="pattern" select="$pattern"/>
    <xsl:with-param name="isCurrency" select="true()" />
    <xsl:with-param name="decimalSeparator" select="$decimalSeparator" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="format-number">
  <xsl:param name="float"/>
  <xsl:param name="pattern">#,##0.00</xsl:param>
  <xsl:param name="decimalSeparator"></xsl:param>
  <xsl:param name="thousandsSeparator">&#160;</xsl:param>
  <xsl:param name="isCurrency" select="false()" />
  <xsl:param name="isRecursion" select="false()" />
  <xsl:choose>
    <xsl:when test="$decimalSeparator != ''">
      <xsl:variable name="numberString">
        <xsl:value-of select="format-number($float, $pattern)"/>
      </xsl:variable>
      <xsl:variable name="charDecimal" select="substring($decimalSeparator, 1, 1)" />
      <xsl:variable name="charThousands">
        <xsl:choose>
          <xsl:when test="$thousandsSeparator = ''">&#160;</xsl:when>
          <xsl:when test="starts-with($thousandsSeparator, ' ')">&#160;</xsl:when>
          <xsl:otherwise><xsl:value-of select="substring($thousandsSeparator, 1, 1)"/></xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="$charDecimal != '.' or $charThousands != ','">
          <xsl:value-of select="translate($numberString, '.,', concat($charDecimal, $charThousands))"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$numberString"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:when test="$isRecursion">
      <xsl:value-of select="format-number($float, $pattern)"/>
    </xsl:when>
    <xsl:when test="$isCurrency and $LANGUAGE_FORMATS/currency/format[@language = $PAGE_LANGUAGE]">
      <xsl:variable name="format" select="$LANGUAGE_FORMATS/currency/format[@language = $PAGE_LANGUAGE]" />
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$float"/>
        <xsl:with-param name="pattern" select="$pattern"/>
        <xsl:with-param name="decimalSeparator" select="$format/@decimal" />
        <xsl:with-param name="thousandsSeparator" select="$format/@thousands" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$isCurrency and $LANGUAGE_FORMATS/currency/format[@language = $PAGE_LANGUAGE_GROUP]">
      <xsl:variable name="format" select="$LANGUAGE_FORMATS/currency/format[@language = $PAGE_LANGUAGE_GROUP]" />
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$float"/>
        <xsl:with-param name="pattern" select="$pattern"/>
        <xsl:with-param name="decimalSeparator" select="$format/@decimal" />
        <xsl:with-param name="thousandsSeparator" select="$format/@thousands" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/number/format[@language = $PAGE_LANGUAGE]">
      <xsl:variable name="format" select="$LANGUAGE_FORMATS/number/format[@language = $PAGE_LANGUAGE]" />
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$float"/>
        <xsl:with-param name="pattern" select="$pattern"/>
        <xsl:with-param name="decimalSeparator" select="$format/@decimal" />
        <xsl:with-param name="thousandsSeparator" select="$format/@thousands" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/number/format[@language = $PAGE_LANGUAGE_GROUP]">
      <xsl:variable name="format" select="$LANGUAGE_FORMATS/number/format[@language = $PAGE_LANGUAGE_GROUP]" />
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$float"/>
        <xsl:with-param name="pattern" select="$pattern"/>
        <xsl:with-param name="decimalSeparator" select="$format/@decimal" />
        <xsl:with-param name="thousandsSeparator" select="$format/@thousands" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="format-number">
        <xsl:with-param name="float" select="$float"/>
        <xsl:with-param name="pattern" select="$pattern"/>
        <xsl:with-param name="decimalSeparator">.</xsl:with-param>
        <xsl:with-param name="thousandsSeparator"> </xsl:with-param>
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="language-text">
  <xsl:param name="text"/>
  <xsl:param name="userText"/>
  <xsl:param name="allowTags" select="false()"/>
  
  <xsl:variable name="phrase">
    <xsl:choose>
      <xsl:when test="$userText and $userText != ''">
        <xsl:value-of select="$userText" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="$LANGUAGE_TEXTS_CURRENT and $LANGUAGE_TEXTS_CURRENT/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_TEXTS_CURRENT/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_MODULE_CURRENT and $LANGUAGE_MODULE_CURRENT/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_MODULE_CURRENT/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_DEFAULTS_CURRENT and $LANGUAGE_DEFAULTS_CURRENT/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_DEFAULTS_CURRENT/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_TEXTS_FALLBACK and $LANGUAGE_TEXTS_FALLBACK/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_TEXTS_FALLBACK/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_MODULE_FALLBACK and $LANGUAGE_MODULE_FALLBACK/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_MODULE_FALLBACK/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_DEFAULTS_FALLBACK and $LANGUAGE_DEFAULTS_FALLBACK/texts/text[@ident = $text]">
            <xsl:copy-of select="$LANGUAGE_DEFAULTS_FALLBACK/texts/text[@ident = $text]" />
          </xsl:when>
          <xsl:otherwise><xsl:copy-of select="$text"/></xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="result">
    <xsl:choose>
      <xsl:when test="$allowTags"><xsl:copy-of select="$phrase"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="$phrase"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$PAPAYA_DEBUG_LANGUAGE_PHRASES">
      #<xsl:value-of select="$text"/> = <xsl:value-of select="$result"/>#
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$result"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
