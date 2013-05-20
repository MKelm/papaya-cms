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


<xsl:template name="dialog">
  <xsl:param name="dialog" />
  <xsl:param name="title" select="''" />
  <xsl:param name="id" select="''" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:param name="submitButton" select="''" />
  <xsl:param name="captions" select="false()" />
  <xsl:if test="$dialog">
    <xsl:variable name="idVal">
      <xsl:choose>
        <xsl:when test="$id and $id != ''"><xsl:value-of select="$id"/></xsl:when>
        <xsl:when test="$dialog/@id and $dialog/@id != ''"><xsl:value-of select="$dialog/@id"/></xsl:when>
        <xsl:otherwise><xsl:value-of select="generate-id($dialog)" /></xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <form id="{$idVal}" action="{$dialog/@action}">
      <xsl:copy-of select="$dialog/@*[name() = 'onclick']" />
      <xsl:attribute name="method">
        <xsl:choose>
          <xsl:when test="$dialog/@method"><xsl:value-of select="$dialog/@method"/></xsl:when>
          <xsl:otherwise>post</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="count($dialog//input[@type = 'file']) &gt; 0">
          <xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
        </xsl:when>
        <xsl:when test="$dialog/@encoding">
          <xsl:attribute name="enctype"><xsl:value-of select="$dialog/@encoding" /></xsl:attribute>
        </xsl:when>
      </xsl:choose>
      <xsl:copy-of select="$dialog/input[@type='hidden']"/>
      <xsl:call-template name="dialog-content">
        <xsl:with-param name="dialog" select="$dialog" />
        <xsl:with-param name="title" select="$title" />
        <xsl:with-param name="id" select="$idVal" />
        <xsl:with-param name="showMandatory" select="$showMandatory" />
        <xsl:with-param name="submitButton" select="$submitButton" />
        <xsl:with-param name="captions" select="$captions" />
      </xsl:call-template>
    </form>
  </xsl:if>
</xsl:template>

<xsl:template name="dialog-content">
  <xsl:param name="dialog" />
  <xsl:param name="title" />
  <xsl:param name="id" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:param name="submitButton"/>
  <xsl:param name="captions"/>
  <xsl:if test="$title and $title != ''">
    <h2><xsl:value-of select="$title" /></h2>
  </xsl:if>
  <fieldset>
    <xsl:choose>
      <xsl:when test="$dialog/lines">
        <xsl:for-each select="$dialog/lines//line">
          <xsl:call-template name="dialog-field">
            <xsl:with-param name="dialog" select="$dialog" />
            <xsl:with-param name="field" select="." />
            <xsl:with-param name="showMandatory" select="$showMandatory" />
          </xsl:call-template>
        </xsl:for-each>
      </xsl:when>
      <xsl:when test="$dialog/element/*">
        <xsl:for-each select="$dialog/element">
          <xsl:call-template name="dialog-field">
            <xsl:with-param name="dialog" select="$dialog" />
            <xsl:with-param name="field" select="." />
            <xsl:with-param name="showMandatory" select="$showMandatory" />
          </xsl:call-template>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <xsl:for-each select="$dialog/*">
          <xsl:call-template name="dialog-direct-element">
            <xsl:with-param name="dialog" select="$dialog" />
            <xsl:with-param name="element" select="." />
            <xsl:with-param name="showMandatory" select="$showMandatory" />
            <xsl:with-param name="captions" select="$captions" />
          </xsl:call-template>
        </xsl:for-each>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:call-template name="dialog-buttons">
      <xsl:with-param name="dialog" select="$dialog" />
      <xsl:with-param name="id" select="$id" />
      <xsl:with-param name="submitButton" select="$submitButton" />
    </xsl:call-template>
  </fieldset>
</xsl:template>

<xsl:template name="dialog-direct-element">
  <xsl:param name="dialog" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:param name="element" select="." />
  <xsl:param name="captions"/>
  <xsl:variable name="elementId">
    <xsl:choose>
      <xsl:when test="$element/@id"><xsl:value-of select="$element/@id" /></xsl:when>
      <xsl:when test="$element/@fid"><xsl:value-of select="$element/@fid" /></xsl:when>
      <xsl:otherwise><xsl:value-of select="generate-id($element)" /></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$captions and $captions/*[name() = $element/@name] and $captions/*[name() = $element/@name]/text()">
      <label for="{$elementId}"><xsl:value-of select="$captions/*[name() = $element/@name]/text()" /></label>
    </xsl:when>
    <xsl:when test="@title and @title != ''">
      <label for="{$elementId}"><xsl:value-of select="@title" /></label>
    </xsl:when>
  </xsl:choose>
  <xsl:variable name="elementNamePrefix">
    <xsl:if test="$dialog/@name and $dialog/@name != ''">
      <xsl:value-of select="$dialog/@name"/>
    </xsl:if>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$element/@type = 'hidden'">
      <xsl:call-template name="dialog-element-hidden">
        <xsl:with-param name="element" select="$element" />
        <xsl:with-param name="elementId" select="$elementId" />
        <xsl:with-param name="elementNamePrefix" select="$elementNamePrefix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$element/@type = 'password'">
      <xsl:call-template name="dialog-element-password">
        <xsl:with-param name="element" select="$element" />
        <xsl:with-param name="elementId" select="$elementId" />
        <xsl:with-param name="elementNamePrefix" select="$elementNamePrefix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$element/@type = 'text' or @type = 'input'">
      <xsl:call-template name="dialog-element-input">
        <xsl:with-param name="element" select="$element" />
        <xsl:with-param name="elementId" select="$elementId" />
        <xsl:with-param name="elementNamePrefix" select="$elementNamePrefix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="self::select">
      <xsl:call-template name="dialog-element-select">
        <xsl:with-param name="element" select="$element" />
        <xsl:with-param name="elementId" select="$elementId" />
        <xsl:with-param name="elementNamePrefix" select="$elementNamePrefix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="self::textarea">
      <xsl:call-template name="dialog-element-textarea">
        <xsl:with-param name="element" select="$element" />
        <xsl:with-param name="elementId" select="$elementId" />
        <xsl:with-param name="elementNamePrefix" select="$elementNamePrefix" />
      </xsl:call-template>
    </xsl:when>
  </xsl:choose>
</xsl:template>

<xsl:template name="dialog-buttons">
  <xsl:param name="dialog" />
  <xsl:param name="id" />
  <xsl:param name="submitButton" select="''" />
  <div class="buttons">
    <xsl:for-each select="$dialog/dlgbutton|$dialog/button">
      <xsl:call-template name="dialog-submit-button">
        <xsl:with-param name="buttonType">
          <xsl:choose>
            <xsl:when test="@type != ''">
              <xsl:value-of select="@type" />
            </xsl:when>
            <xsl:otherwise>
              <xsl:text></xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
        <xsl:with-param name="buttonName">
          <xsl:choose>
            <xsl:when test="@name != ''">
              <xsl:value-of select="@name" />
            </xsl:when>
            <xsl:otherwise>
              <xsl:text></xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
        <xsl:with-param name="buttonValue">
          <xsl:choose>
            <xsl:when test="@value and @value != ''">
              <xsl:value-of select="@value" />
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="node()" />
            </xsl:otherwise>
          </xsl:choose>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:for-each>
    <xsl:if test="$submitButton and $submitButton != ''">
      <xsl:call-template name="dialog-submit-button">
         <xsl:with-param name="buttonValue">
           <xsl:value-of select="$submitButton"/>
         </xsl:with-param>
      </xsl:call-template>
    </xsl:if>
    <xsl:text> </xsl:text>
  </div>
</xsl:template>

<xsl:template name="dialog-submit-button">
  <xsl:param name="buttonName" select="''"/>
  <xsl:param name="buttonValue" select="''"/>
  <xsl:param name="buttonType" select="'submit'"/>

  <button>
    <xsl:attribute name="type">
      <xsl:value-of select="$buttonType" />
    </xsl:attribute>
    <xsl:if test="$buttonName and $buttonName != ''">
      <xsl:attribute name="name">
        <xsl:value-of select="$buttonName" />
      </xsl:attribute>
    </xsl:if>
    <xsl:value-of select="$buttonValue"/>
  </button>
</xsl:template>

<xsl:template name="dialog-marker-mandatory">
  <span class="markerMandatory">
    <xsl:attribute name="title">
      <xsl:call-template name="language-text">
        <xsl:with-param name="text">FIELD_MANDATORY</xsl:with-param>
      </xsl:call-template>
    </xsl:attribute>
    <xsl:text>*</xsl:text>
  </span>
</xsl:template>

<xsl:template name="dialog-field-id">
  <xsl:param name="field" />
  <xsl:if test="$field">
    <xsl:choose>
      <xsl:when test="$field/input/@id"><xsl:value-of select="$field/input/@id" /></xsl:when>
      <xsl:when test="$field/input/@fid"><xsl:value-of select="$field/input/@fid" /></xsl:when>
      <xsl:when test="$field/select/@id"><xsl:value-of select="$field/select/@id" /></xsl:when>
      <xsl:when test="$field/select/@fid"><xsl:value-of select="$field/select/@fid" /></xsl:when>
      <xsl:when test="$field/textarea/@id"><xsl:value-of select="$field/textarea/@id" /></xsl:when>
      <xsl:when test="$field/textarea/@fid"><xsl:value-of select="$field/textarea/@fid" /></xsl:when>
      <xsl:when test="$field/@fid"><xsl:value-of select="$field/@fid" /></xsl:when>
      <xsl:when test="$field/@id"><xsl:value-of select="$field/@id" /></xsl:when>
      <xsl:otherwise><xsl:value-of select="generate-id($field)" /></xsl:otherwise>
    </xsl:choose>
  </xsl:if>
</xsl:template>

<xsl:template name="dialog-field">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <div class="field">
    <xsl:choose>
      <xsl:when test="$field/input[@type = 'file']">
        <xsl:call-template name="dialog-field-file">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/input[@type = 'text']">
        <xsl:call-template name="dialog-field-input">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/input[@type = 'password']">
        <xsl:call-template name="dialog-field-password">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/input[@type = 'checkbox']">
        <xsl:call-template name="dialog-field-checkbox">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/input[@type = 'radio']">
        <xsl:call-template name="dialog-field-radio">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/textarea">
        <xsl:call-template name="dialog-field-textarea">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$field/select">
        <xsl:call-template name="dialog-field-select">
          <xsl:with-param name="dialog" select="$dialog" />
          <xsl:with-param name="field" select="$field" />
          <xsl:with-param name="showMandatory" select="$showMandatory" />
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="dialog-field-input">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-input">
    <xsl:with-param name="element" select="$field/input" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="dialog-field-password">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-password">
    <xsl:with-param name="element" select="$field/input" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="dialog-field-file">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-file">
    <xsl:with-param name="element" select="$field/input" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
  <xsl:if test="$field/img/@src != ''">
    <img>
      <xsl:attribute name="class">communityUserAvatar</xsl:attribute>
      <xsl:attribute name="alt"><xsl:value-of select="$field/@caption" /></xsl:attribute>
      <xsl:copy-of select="$field/img/@*[name() != 'class']" />
    </img>
  </xsl:if>
</xsl:template>

<xsl:template name="dialog-field-textarea">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-textarea">
    <xsl:with-param name="element" select="$field/textarea" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="dialog-field-select">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-select">
    <xsl:with-param name="element" select="$field/select" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="dialog-field-checkbox">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:variable name="fieldId">
    <xsl:call-template name="dialog-field-id">
       <xsl:with-param name="field" select="$field" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label for="{$fieldId}">
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:call-template name="dialog-element-checkbox">
    <xsl:with-param name="element" select="$field/input" />
    <xsl:with-param name="elementId" select="$fieldId" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="dialog-field-radio">
  <xsl:param name="dialog" />
  <xsl:param name="field" />
  <xsl:param name="showMandatory" select="true()" />
  <xsl:choose>
    <xsl:when test="$field/@caption and $field/@caption != ''">
      <label>
        <xsl:value-of select="$field/@caption" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
    <xsl:when test="$field/label and $field/label/text() != ''">
      <label>
        <xsl:value-of select="$field/label" />
        <xsl:if test="$showMandatory and $field/input/@mandatory='true'">
          <xsl:call-template name="dialog-marker-mandatory" />
        </xsl:if>
      </label>
    </xsl:when>
  </xsl:choose>
  <xsl:for-each select="$field/input">
    <xsl:call-template name="dialog-element-radio">
      <xsl:with-param name="element" select="." />
    </xsl:call-template>
  </xsl:for-each>
</xsl:template>


<!-- dialog element attributes -->
<xsl:template name="dialog-element-attribute-name">
  <xsl:param name="name"></xsl:param>
  <xsl:param name="prefix"></xsl:param>
  <xsl:choose>
    <xsl:when test="$prefix and $prefix != ''">
      <xsl:value-of select="$prefix"/>
      <xsl:text>[</xsl:text>
      <xsl:value-of select="$name"/>
      <xsl:text>]</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$name"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- dialog elements -->
<xsl:template name="dialog-element-checkbox">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <xsl:param name="labelText"></xsl:param>
  <input type="checkbox">
    <xsl:copy-of select="$element/@*[name() = 'name' or name() = 'value' or name() = 'checked']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:attribute name="class">
      <xsl:text>checkbox</xsl:text>
      <xsl:if test="$element/@class and $element/@class != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="$element/@class" />
      </xsl:if>
    </xsl:attribute>
  </input>
  <label for="{$elementId}" class="checkbox">
    <xsl:value-of select="$labelText" />
  </label>
</xsl:template>

<xsl:template name="dialog-element-file">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <input type="file">
    <xsl:copy-of select="$element/@*[name() = 'name' or name() = 'value' or name() = 'maxlength' or name() = 'size']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:attribute name="class">
      <xsl:text>file</xsl:text>
      <xsl:if test="$element/@class and $element/@class != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="$element/@class" />
      </xsl:if>
    </xsl:attribute>
  </input>
</xsl:template>

<xsl:template name="dialog-element-hidden">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <xsl:param name="elementNamePrefix"></xsl:param>
  <xsl:variable name="elementName">
    <xsl:call-template name="dialog-element-attribute-name">
      <xsl:with-param name="name" select="$element/@name"/>
      <xsl:with-param name="prefix" select="$elementNamePrefix"/>
    </xsl:call-template>
  </xsl:variable>
  <input type="hidden" name="{$elementName}">
    <xsl:copy-of select="$element/@*[name() = 'value']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
  </input>
</xsl:template>

<xsl:template name="dialog-element-input">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <xsl:param name="elementNamePrefix"></xsl:param>
  <xsl:variable name="elementName">
    <xsl:call-template name="dialog-element-attribute-name">
      <xsl:with-param name="name" select="$element/@name"/>
      <xsl:with-param name="prefix" select="$elementNamePrefix"/>
    </xsl:call-template>
  </xsl:variable>
  <input type="text" name="{$elementName}">
    <xsl:copy-of select="$element/@*[name() = 'value' or name() = 'maxlength' or name() = 'size' or name() = 'disabled']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:attribute name="class">
      <xsl:text>text</xsl:text>
      <xsl:if test="$element/@class and $element/@class != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="$element/@class" />
      </xsl:if>
    </xsl:attribute>
  </input>
</xsl:template>

<xsl:template name="dialog-element-password">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <xsl:param name="elementNamePrefix"></xsl:param>
  <xsl:variable name="elementName">
    <xsl:call-template name="dialog-element-attribute-name">
      <xsl:with-param name="name" select="$element/@name"/>
      <xsl:with-param name="prefix" select="$elementNamePrefix"/>
    </xsl:call-template>
  </xsl:variable>
  <input type="password" name="{$elementName}">
    <xsl:copy-of select="$element/@*[name() = 'maxlength' or name() = 'size']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:attribute name="class">
      <xsl:text>password</xsl:text>
      <xsl:if test="$element/@class and $element/@class != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="$element/@class" />
      </xsl:if>
    </xsl:attribute>
  </input>
</xsl:template>

<xsl:template name="dialog-element-radio">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <xsl:param name="labelText" select="$element/text()" />
  <xsl:param name="elementName" />
  <xsl:param name="elementValue" />
  <input type="radio">
    <xsl:copy-of select="$element/@*[name() = 'name' or name() = 'value' or name() = 'checked']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:attribute name="class">
      <xsl:text>radio</xsl:text>
      <xsl:if test="$element/@class and $element/@class != ''">
        <xsl:text> </xsl:text>
        <xsl:value-of select="$element/@class" />
      </xsl:if>
    </xsl:attribute>
    <xsl:if test="$elementName != ''">
      <xsl:attribute name="name">
        <xsl:value-of select="$elementName" />
      </xsl:attribute>
    </xsl:if>
    <xsl:if test="$elementValue != ''">
      <xsl:attribute name="value">
        <xsl:value-of select="$elementValue" />
      </xsl:attribute>
    </xsl:if>
  </input>
  <label for="{$elementId}" class="radio">
    <xsl:value-of select="$labelText" />
  </label>
</xsl:template>

<xsl:template name="dialog-element-select">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <select>
    <xsl:copy-of select="$element/@*[name() = 'name' or name() = 'size' or name() = 'class']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:for-each select="$element/option">
      <option>
        <xsl:copy-of select="@*" />
        <xsl:apply-templates select="./node()" mode="richtext"/>
      </option>
    </xsl:for-each>
  </select>
</xsl:template>

<xsl:template name="dialog-element-textarea">
  <xsl:param name="element" />
  <xsl:param name="elementId" select="generate-id($element)"/>
  <textarea>
    <xsl:copy-of select="$element/@*[name() = 'name' or name() = 'class' or name() = 'rows' or name() = 'cols']" />
    <xsl:attribute name="id"><xsl:value-of select="$elementId"/></xsl:attribute>
    <xsl:choose>
      <xsl:when test="$element/node()">
        <xsl:apply-templates select="$element/node()" mode="richtext"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text> </xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </textarea>
</xsl:template>

</xsl:stylesheet>
