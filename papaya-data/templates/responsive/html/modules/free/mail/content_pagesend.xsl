<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="LANGUAGE_MODULE_CURRENT" select="document(concat($PAGE_LANGUAGE, '.xml'))" />
<xsl:param name="LANGUAGE_MODULE_FALLBACK" select="document('en-US.xml')"/>

<xsl:template name="page-styles">
  <xsl:call-template name="link-style">
    <xsl:with-param name="file">page_feedback.css</xsl:with-param>
  </xsl:call-template>
</xsl:template>

<xsl:template name="content-area">
  <xsl:param name="pageContent" select="content/topic"/>
  <xsl:choose>
    <xsl:when test="$pageContent/@module = 'content_pagesend'">
      <xsl:call-template name="module-content-pagesend">
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

<xsl:template name="module-content-pagesend">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:if test="$pageContent/mail/message">
    <p>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="$pageContent/mail/message/@type = 'error'">error</xsl:when>
          <xsl:when test="$pageContent/mail/message/@type = 'warning'">warning</xsl:when>
          <xsl:otherwise>text</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:apply-templates select="$pageContent/mail/message/node()" mode="richtext"/>
    </p>
  </xsl:if>
  <xsl:if test="$pageContent/mail/form">
    <form class="mail">
      <xsl:copy-of select="$pageContent/mail/form/@*" />
      <xsl:copy-of select="$pageContent/mail/form/input[@type='hidden']"/>
      <fieldset>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[mail_to]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_to]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[mail_from]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_from]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/textarea[@name='mail[mail_comments]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_comments]']" />
        </xsl:call-template>
        <xsl:if test="$pageContent/mail/form/input[@name='mail[captchaanswer]']">
          <img src="{$pageContent/mail/form/img[@type='captcha']/@src}" class="captcha" />
          <xsl:call-template name="module-content-feedback-field">
            <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[captchaanswer]']" />
            <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[captchaanswer]']" />
          </xsl:call-template>
        </xsl:if>
      </fieldset>
      <fieldset class="button">
        <button type="submit">
          <xsl:value-of select="$pageContent/mail/form/submitbutton/@caption" />
        </button>
      </fieldset>
    </form>
    <xsl:if test="$pageContent/mail/privacy/node()">
      <div class="privacyText">
        <xsl:apply-templates select="$pageContent/mail/privacy/node()" mode="richtext"/>
      </div>
    </xsl:if>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>