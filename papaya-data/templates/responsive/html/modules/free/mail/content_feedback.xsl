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
    <xsl:when test="$pageContent/@module = 'content_feedback'">
      <xsl:call-template name="module-content-feedback-simple">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_feedback_store'">
      <xsl:call-template name="module-content-feedback-simple">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_feedback_form'">
      <xsl:call-template name="module-content-feedback-form">
        <xsl:with-param name="pageContent" select="$pageContent"/>
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$pageContent/@module = 'content_pagecomment'">
      <xsl:call-template name="module-content-page-comment">
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

<xsl:template name="module-content-feedback-simple">
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
          <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[mail_name]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_name]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[mail_from]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_from]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/input[@name='mail[mail_subject]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_subject]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$pageContent/mail/form/textarea[@name='mail[mail_message]']" />
          <xsl:with-param name="label" select="$pageContent/mail/form/label[@for='mail[mail_message]']" />
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
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">SEND</xsl:with-param>
          </xsl:call-template>
        </button>
      </fieldset>
    </form>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-feedback-form">
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
      <xsl:for-each select="$pageContent/mail/form/fieldset">
        <fieldset>
          <xsl:variable name="fieldset" select="." />
          <xsl:if test="legend/title">
            <legend><xsl:value-of select="legend/title" /></legend>
          </xsl:if>
          <xsl:for-each select="label">
            <xsl:variable name="elementName" select="@for" />
            <xsl:variable name="elements" select="$fieldset/*[@name = $elementName or @name = concat($elementName, '[]')]" />
            <xsl:choose>
              <xsl:when test="count($elements) &gt; 1">
                <xsl:call-template name="module-content-feedback-field-group">
                  <xsl:with-param name="elements" select="$elements" />
                  <xsl:with-param name="label" select="." />
                </xsl:call-template>
              </xsl:when>
              <xsl:when test="count($elements) = 1">
                <xsl:call-template name="module-content-feedback-field">
                  <xsl:with-param name="element" select="$elements[1]" />
                  <xsl:with-param name="label" select="." />
                </xsl:call-template>
              </xsl:when>
            </xsl:choose>
          </xsl:for-each>
        </fieldset>
      </xsl:for-each>
      <fieldset class="button">
        <button type="submit">
          <xsl:choose>
            <xsl:when test="$pageContent/mail/form/input[@type='submit']/@value != ''">
              <xsl:value-of select="$pageContent/mail/form/input[@type='submit']/@value"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="language-text">
                <xsl:with-param name="text">SEND</xsl:with-param>
              </xsl:call-template>
            </xsl:otherwise>
          </xsl:choose>
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

<xsl:template name="module-content-feedback-field">
  <xsl:param name="element" />
  <xsl:param name="label" />
  <xsl:param name="mandatory" select="boolean($element/@required)" />
  <xsl:param name="error" select="boolean($element/@error)" />
  <xsl:variable name="elementId">
    <xsl:choose>
      <xsl:when test="$element/@id and $element/@id != ''"><xsl:value-of select="$element/@id"/></xsl:when>
      <xsl:when test="$element/@fid and $element/@fid != ''"><xsl:value-of select="$element/@fid"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="generate-id($element)"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <div>
    <xsl:attribute name="class">
      <xsl:text>field</xsl:text>
      <xsl:if test="$error">
        <xsl:text> error</xsl:text>
        
      </xsl:if>
    </xsl:attribute>
    <label for="{$elementId}">
      <xsl:value-of select="$label/*|$label/text()" />
      <xsl:if test="$mandatory">
        <xsl:call-template name="dialog-marker-mandatory" />
      </xsl:if>
      <xsl:text> </xsl:text>
    </label>
    <xsl:choose>
      <xsl:when test="name($element) = 'input' and $element/@type = 'checkbox'">
        <xsl:call-template name="dialog-element-checkbox">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
          <xsl:with-param name="labelText">
            <xsl:choose>
              <xsl:when test="count($element/text()) &gt; 0"><xsl:value-of select="$element/text()" /></xsl:when>
              <xsl:when test="count($element/following-sibling::text()) &gt; 1"><xsl:value-of select="$element/following-sibling::text()" /></xsl:when>
            </xsl:choose>
          </xsl:with-param>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'input' and $element/@type = 'file'">
        <xsl:call-template name="dialog-element-file">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'input' and $element/@type = 'password'">
        <xsl:call-template name="dialog-element-password">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'input' and $element/@type = 'radio'">
        <xsl:call-template name="dialog-element-radio">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
          <xsl:with-param name="labelText">
            <xsl:choose>
              <xsl:when test="count($element/text()) &gt; 0"><xsl:value-of select="$element/text()" /></xsl:when>
              <xsl:when test="count($element/following-sibling::text()) &gt; 1"><xsl:value-of select="$element/following-sibling::text()" /></xsl:when>
            </xsl:choose>
          </xsl:with-param>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'input' and $element/@type = 'text'">
        <xsl:call-template name="dialog-element-input">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'select'">
        <xsl:call-template name="dialog-element-select">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="name($element) = 'textarea'">
        <xsl:call-template name="dialog-element-textarea">
          <xsl:with-param name="element" select="$element" />
          <xsl:with-param name="elementId" select="$elementId" />
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </div>
</xsl:template>

<xsl:template name="module-content-feedback-field-group">
  <xsl:param name="elements" />
  <xsl:param name="label" />
  <xsl:param name="mandatory" select="false()" />
  <div class="field">
    <span class="label">
      <xsl:value-of select="$label/*|$label/text()" />
      <xsl:if test="$mandatory">
        <xsl:call-template name="dialog-marker-mandatory" />
      </xsl:if>
      <xsl:text> </xsl:text>
    </span>
    <xsl:for-each select="$elements">
      <xsl:variable name="elementId" select="generate-id(.)" />
      <xsl:choose>
        <xsl:when test="name() = 'input' and @type = 'checkbox'">
          <span class="checkbox">
            <xsl:call-template name="dialog-element-checkbox">
              <xsl:with-param name="element" select="." />
              <xsl:with-param name="elementId" select="$elementId" />
              <xsl:with-param name="labelText">
                <xsl:choose>
                  <xsl:when test="count(./text()) &gt; 0"><xsl:value-of select="./text()" /></xsl:when>
                  <xsl:when test="count(./following-sibling::text()) &gt; 1"><xsl:value-of select="./following-sibling::text()" /></xsl:when>
                </xsl:choose>
              </xsl:with-param>
            </xsl:call-template>
          </span>
        </xsl:when>
      </xsl:choose>
    </xsl:for-each>
  </div>
</xsl:template>

<xsl:template name="module-content-page-comment">
  <xsl:param name="pageContent"/>
  <xsl:call-template name="module-content-topic">
    <xsl:with-param name="pageContent" select="$pageContent"/>
  </xsl:call-template>
  <xsl:call-template name="module-content-page-comment-referer-page">
    <xsl:with-param name="referer" select="$pageContent/mail/subtopic" />
  </xsl:call-template>
  <xsl:call-template name="module-content-page-comment-message">
    <xsl:with-param name="message" select="$pageContent/mail/message" />
  </xsl:call-template>
  <xsl:call-template name="module-content-page-comment-dialog">
    <xsl:with-param name="dialog" select="$pageContent/mail/form" />
  </xsl:call-template>
  <xsl:if test="$pageContent/mail/privacy/node()">
    <div class="privacyText">
      <xsl:apply-templates select="$pageContent/mail/privacy/node()" mode="richtext"/>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-page-comment-referer-page">
  <xsl:param name="referer"/>
  <xsl:if test="$referer">
    <div class="subTopic">
      <h2><a href="{$referer/@href}"><xsl:value-of select="$referer/title"/></a></h2>
      <xsl:if test="$referer/text/node()">
        <div class="subTopicData">
          <xsl:apply-templates select="$referer/text/node()" mode="richtext"/>
        </div>
      </xsl:if>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-page-comment-message">
  <xsl:param name="message"/>
  <xsl:if test="$message">
    <div>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="@type = 'error'">error</xsl:when>
          <xsl:when test="@type = 'warning'">warning</xsl:when>
          <xsl:otherwise>information</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:apply-templates select="$message/node()" mode="richtext"/>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="module-content-page-comment-dialog">
  <xsl:param name="dialog" />
  <xsl:if test="$dialog">
    <form class="mail">
      <xsl:copy-of select="$dialog/@*" />
      <xsl:copy-of select="$dialog/input[@type='hidden']"/>
      <fieldset>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$dialog/input[@name='mail[mail_from]']" />
          <xsl:with-param name="label" select="$dialog/label[@for='mail[mail_from]']" />
        </xsl:call-template>
        <xsl:call-template name="module-content-feedback-field">
          <xsl:with-param name="element" select="$dialog/textarea[@name='mail[mail_comments]']" />
          <xsl:with-param name="label" select="$dialog/label[@for='mail[mail_comments]']" />
        </xsl:call-template>
      </fieldset>
      <fieldset class="button">
        <button type="submit">
          <xsl:call-template name="language-text">
            <xsl:with-param name="text">SEND</xsl:with-param>
          </xsl:call-template>
        </button>
      </fieldset>
    </form>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
