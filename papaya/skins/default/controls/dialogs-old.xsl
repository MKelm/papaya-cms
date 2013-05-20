<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="old-dialog">
  <xsl:param name="dialog" />
  <xsl:choose>
    <xsl:when test="$dialog/@action">
      <form action="{$dialog/@action}" method="post">
        <xsl:attribute name="action">
          <xsl:choose>
            <xsl:when test="$dialog/@id and not(contains($dialog/@action, '#') or starts-with($dialog/@action, 'javascript:'))">
              <xsl:value-of select="$dialog/@action"/>
              <xsl:text>#</xsl:text>
              <xsl:value-of select="$dialog/@id"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="$dialog/@action"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:if test="$dialog/@enctype">
          <xsl:attribute name="enctype"><xsl:value-of select="@enctype"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="$dialog/@id">
          <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="$dialog/@onsubmit">
          <xsl:attribute name="onsubmit"><xsl:value-of select="@onsubmit"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="$dialog/@target">
          <xsl:attribute name="target"><xsl:value-of select="@target"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="$dialog/@protected or $dialog/lines/@class">
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="$dialog/@protected and $dialog/lines/@class">
                <xsl:text>dialogProtectChanges </xsl:text>
                <xsl:value-of select="$dialog/lines/@class"/>
              </xsl:when>
              <xsl:when test="$dialog/@protected">
                <xsl:text>dialogProtectChanges</xsl:text>
              </xsl:when>
              <xsl:when test="$dialog/lines/@class">
                <xsl:value-of select="$dialog/lines/@class"/>
              </xsl:when>
            </xsl:choose>
          </xsl:attribute>
        </xsl:if>
        <xsl:copy-of select="$dialog/input[@type='hidden']"/>
        <xsl:call-template name="old-dialog-scripts">
          <xsl:with-param name="scripts" select="$dialog/script"/>
        </xsl:call-template>
        <xsl:call-template name="old-dialog-parts">
          <xsl:with-param name="dialog" select="$dialog"/>
        </xsl:call-template>
      </form>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="old-dialog-parts">
        <xsl:with-param name="dialog" select="$dialog"/>
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="old-dialog-scripts">
  <xsl:param name="scripts"/>
  <xsl:if test="$scripts and count($scripts) &gt; 0">
    <xsl:for-each select="$scripts">
      <script type="{@type}">
        <xsl:comment>
          <xsl:copy-of select="text()"/>
        //</xsl:comment>
      </script>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="old-dialog-parts">
  <xsl:param name="dialog" />
  <div class="panel">
    <h2 class="panelHeader">
      <xsl:if test="$dialog/@hint">
        <span class="panelInfoButton">
          <xsl:call-template name="panel-info-button">
            <xsl:with-param name="text" select="$dialog/@hint" />
          </xsl:call-template>
        </span>
      </xsl:if>
      <xsl:if test="$dialog/@maximize">
        <span class="panelInfoButton">
          <xsl:call-template name="panel-info-button">
            <xsl:with-param name="mode">maximize</xsl:with-param>
            <xsl:with-param name="href" select="$dialog/@maximize" />
          </xsl:call-template>
        </span>
      </xsl:if>
      <xsl:if test="$dialog/@minimize">
        <span class="panelInfoButton">
          <xsl:call-template name="panel-info-button">
            <xsl:with-param name="mode">minimize</xsl:with-param>
            <xsl:with-param name="href" select="$dialog/@minimize" />
          </xsl:call-template>
        </span>
      </xsl:if>
      <xsl:if test="$dialog/@icon">
        <span class="panelIcon">
          <xsl:call-template name="panel-icon">
            <xsl:with-param name="icon" select="$dialog/@icon" />
          </xsl:call-template>
        </span>
      </xsl:if>
      <xsl:value-of select="$dialog/@title" />
    </h2>
    <div class="panelBody">
      <xsl:call-template name="old-dialog-buttons">
        <xsl:with-param name="buttons" select="$dialog/dlgbutton"/>
        <xsl:with-param name="before-dialog" select="true()"/>
      </xsl:call-template>
      <xsl:choose>
        <xsl:when test="$dialog/listview/@mode = 'thumbs'">
          <xsl:call-template name="listview-buttons">
            <xsl:with-param name="buttons" select="$dialog/listview/buttons" />
          </xsl:call-template>
          <xsl:call-template name="listview-items-thumbnails">
            <xsl:with-param name="columns" select="$dialog/listview/cols/col"/>
            <xsl:with-param name="items" select="$dialog/listview/items/listitem"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="$dialog/listview/@mode = 'tile'">
          <xsl:call-template name="listview-buttons">
            <xsl:with-param name="buttons" select="$dialog/listview/buttons" />
          </xsl:call-template>
          <xsl:call-template name="listview-items-tiled">
            <xsl:with-param name="columns" select="$dialog/listview/cols/col"/>
            <xsl:with-param name="items" select="$dialog/listview/items/listitem"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="$dialog/listview">
          <xsl:call-template name="listview-buttons">
            <xsl:with-param name="buttons" select="$dialog/listview/buttons" />
          </xsl:call-template>
          <xsl:call-template name="listview-items">
            <xsl:with-param name="columns" select="$dialog/listview/cols/col"/>
            <xsl:with-param name="items" select="$dialog/listview/items/listitem"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="old-dialog-lines">
            <xsl:with-param name="lines" select="$dialog/lines"/>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:call-template name="old-dialog-buttons">
        <xsl:with-param name="buttons" select="$dialog/dlgbutton"/>
      </xsl:call-template>
    </div>
  </div>
</xsl:template>

<xsl:template name="old-dialog-lines">
  <xsl:param name="lines" />
  <xsl:if test="$lines">
    <table>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="$lines/@class">dialog <xsl:value-of select="$lines/@class"/></xsl:when>
          <xsl:otherwise>dialog</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:choose>
        <xsl:when test="$lines/linegroup">
          <xsl:for-each select="$lines/linegroup">
            <xsl:if test="@caption">
              <tr><th colspan="3" class="subtitle"><xsl:value-of select="@caption"/></th></tr>
            </xsl:if>
            <xsl:call-template name="old-dialog-linegroup">
              <xsl:with-param name="lines" select="line"/>
            </xsl:call-template>
          </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="old-dialog-linegroup">
            <xsl:with-param name="lines" select="$lines/line"/>
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </table>
  </xsl:if>
</xsl:template>

<xsl:template name="old-dialog-linegroup">
  <xsl:param name="lines" />
  <xsl:if test="$lines">
    <xsl:for-each select="$lines">
      <tr>
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="not(position() mod 2)">even</xsl:when>
            <xsl:otherwise>odd</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@caption and @caption != ''">
          <td class="caption"><xsl:value-of select="@caption"/></td>
          <td class="infos">
            <xsl:if test="@hint">
              <xsl:call-template name="old-dialog-info-button">
                <xsl:with-param name="text" select="@hint" />
              </xsl:call-template>
            </xsl:if>
            <xsl:if test="@error">
              <xsl:call-template name="old-dialog-info-button">
                <xsl:with-param name="mode">error</xsl:with-param>
              </xsl:call-template>
            </xsl:if>
            <xsl:text> </xsl:text>
          </td>
        </xsl:if>
        <td>
          <xsl:attribute name="class">
            <xsl:choose>
              <xsl:when test="@error">element error</xsl:when>
              <xsl:otherwise>element</xsl:otherwise>
            </xsl:choose>
          </xsl:attribute>
          <xsl:if test="not(@caption and @caption != '')">
            <xsl:attribute name="colspan">3</xsl:attribute>
          </xsl:if>
          <xsl:if test="@align">
            <xsl:attribute name="style">text-align: <xsl:value-of select="@align"/></xsl:attribute>
          </xsl:if>
          <xsl:choose>
            <xsl:when test="layout|grid">
              <xsl:call-template name="old-dialog-element-grid">
                <xsl:with-param name="grid" select="layout|grid"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="input[@type = 'text']">
              <xsl:call-template name="old-dialog-input-text">
                <xsl:with-param name="input" select="input[@type = 'text']"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="input[@type = 'password']">
              <xsl:call-template name="old-dialog-input-password">
                <xsl:with-param name="input" select="input[@type = 'password']"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="input[@type = 'file']">
              <xsl:call-template name="old-dialog-input-file">
                <xsl:with-param name="input" select="input[@type = 'file']"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="select">
              <xsl:call-template name="old-dialog-input-select">
                <xsl:with-param name="select" select="select"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="textarea">
              <xsl:call-template name="old-dialog-textarea">
                <xsl:with-param name="textarea" select="textarea"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="input[@type = 'radio']">
              <xsl:call-template name="old-dialog-input-radiogroup">
                <xsl:with-param name="radioboxes" select="input[@type = 'radio']"/>
                <xsl:with-param name="captions" select="./text()"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test=".//input[@type = 'checkbox']">
              <xsl:call-template name="old-dialog-input-checkgroup">
                <xsl:with-param name="checkboxes" select=".//input[@type = 'checkbox']"/>
                <xsl:with-param name="captions" select="./text()"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:when test="object">
              <xsl:copy-of select="object"/>
              <xsl:if test="script">
                <xsl:copy-of select="script"/>
              </xsl:if>
            </xsl:when>
            <xsl:when test="info">
              <xsl:value-of select="info"/>
            </xsl:when>
            <xsl:when test="div">
              <xsl:call-template name="old-dialog-element-info">
                <xsl:with-param name="element" select="div"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates />
            </xsl:otherwise>
          </xsl:choose>
        </td>
      </tr>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="old-dialog-input-text">
  <xsl:param name="input" />
  <input type="text" name="{$input/@name}">
    <xsl:choose>
      <xsl:when test="$input/@disabled">
        <xsl:attribute name="class">
          <xsl:if test="$input/@class">
            <xsl:value-of select="$input/@class"/>
            <xsl:text> </xsl:text>
          </xsl:if>
          <xsl:text>dialogDisabled</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="disabled">disabled</xsl:attribute>
      </xsl:when>
      <xsl:when test="$input/@readonly">
        <xsl:attribute name="class">
          <xsl:if test="$input/@class">
            <xsl:value-of select="$input/@class"/>
            <xsl:text> </xsl:text>
          </xsl:if>
          <xsl:text>dialogReadonly</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="class"><xsl:value-of select="$input/@class"/></xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="$input/@maxlength">
      <xsl:attribute name="maxlength"><xsl:value-of select="$input/@maxlength"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@size">
      <xsl:attribute name="size"><xsl:value-of select="$input/@size"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@onkeyup">
      <xsl:attribute name="onkeyup"><xsl:value-of select="$input/@onkeyup"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@value">
      <xsl:attribute name="value"><xsl:value-of select="$input/@value"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@id">
      <xsl:attribute name="id"><xsl:value-of select="$input/@id"/></xsl:attribute>
    </xsl:if>
  </input>
</xsl:template>

<xsl:template name="old-dialog-input-password">
  <xsl:param name="input" />
  <input type="password" name="{$input/@name}">
    <xsl:if test="$input/@class">
      <xsl:attribute name="class"><xsl:value-of select="$input/@class"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@maxlength">
      <xsl:attribute name="maxlength"><xsl:value-of select="$input/@maxlength"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@size">
      <xsl:attribute name="size"><xsl:value-of select="$input/@size"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@value">
      <xsl:attribute name="value"><xsl:value-of select="$input/@value"/></xsl:attribute>
    </xsl:if>
  </input>
</xsl:template>

<xsl:template name="old-dialog-input-file">
  <xsl:param name="input" />
  <input type="file" name="{$input/@name}">
    <xsl:if test="$input/@class">
      <xsl:attribute name="class"><xsl:value-of select="$input/@class"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@size">
      <xsl:attribute name="size"><xsl:value-of select="$input/@size"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$input/@id">
      <xsl:attribute name="id"><xsl:value-of select="$input/@id"/></xsl:attribute>
    </xsl:if>
  </input>
</xsl:template>

<xsl:template name="old-dialog-input-select">
  <xsl:param name="select" />
  <select name="{$select/@name}">
    <xsl:if test="$select/@id">
      <xsl:attribute name="id"><xsl:value-of select="$select/@id"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="$select/@multiple">
      <xsl:attribute name="multiple">multiple</xsl:attribute>
    </xsl:if>
    <xsl:if test="$select/@size and $select/@size &gt; 1">
      <xsl:attribute name="size"><xsl:value-of select="$select/@size"/></xsl:attribute>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="$select/@disabled">
        <xsl:attribute name="class">
          <xsl:if test="$select/@class">
            <xsl:value-of select="$select/@class"/>
            <xsl:text> </xsl:text>
          </xsl:if>
          <xsl:text>dialogDisabled</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="disabled">disabled</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="class"><xsl:value-of select="$select/@class"/></xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:if test="$select/@onchange">
      <xsl:attribute name="onchange"><xsl:value-of select="$select/@onchange"/></xsl:attribute>
    </xsl:if>
    <xsl:for-each select="$select/option">
      <option value="{@value}">
        <xsl:attribute name="class">
          <xsl:choose>
            <xsl:when test="not(position() mod 2)">even</xsl:when>
            <xsl:otherwise>odd</xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:if test="@hint">
          <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@selected">
          <xsl:attribute name="selected">selected</xsl:attribute>
        </xsl:if>
        <xsl:value-of select="text()"/>
      </option>
    </xsl:for-each>
    <xsl:for-each select="$select/optgroup">
      <optgroup label="{@label}">
        <xsl:for-each select="option">
          <option value="{@value}">
            <xsl:attribute name="class">
              <xsl:choose>
                <xsl:when test="not(position() mod 2)">even</xsl:when>
                <xsl:otherwise>odd</xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
            <xsl:if test="@hint">
              <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
            </xsl:if>
            <xsl:if test="@selected">
              <xsl:attribute name="selected">selected</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="text()"/>
          </option>
        </xsl:for-each>
      </optgroup>
    </xsl:for-each>
  </select>
</xsl:template>

<xsl:template name="old-dialog-textarea">
  <xsl:param name="textarea" />
  <textarea name="{$textarea/@name}">
    <xsl:choose>
      <xsl:when test="$textarea/@disabled">
        <xsl:attribute name="class">
          <xsl:if test="$textarea/@class">
            <xsl:value-of select="$textarea/@class"/>
            <xsl:text> </xsl:text>
          </xsl:if>
          <xsl:text>dialogDisabled</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="disabled">disabled</xsl:attribute>
      </xsl:when>
      <xsl:when test="$textarea/@readonly">
        <xsl:attribute name="class">
          <xsl:if test="$textarea/@class">
            <xsl:value-of select="$textarea/@class"/>
            <xsl:text> </xsl:text>
          </xsl:if>
          <xsl:text>dialogReadonly</xsl:text>
        </xsl:attribute>
        <xsl:attribute name="readonly">readonly</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="$textarea/@class">
          <xsl:attribute name="class"><xsl:value-of select="$textarea/@class"/></xsl:attribute>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:attribute name="cols">
      <xsl:choose>
        <xsl:when test="$textarea/@cols"><xsl:value-of select="$textarea/@cols"/></xsl:when>
        <xsl:otherwise>70</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:if test="$textarea/@rows">
      <xsl:attribute name="rows"><xsl:value-of select="$textarea/@rows"/></xsl:attribute>
    </xsl:if>
    <xsl:attribute name="id">
      <xsl:choose>
        <xsl:when test="$textarea/@id"><xsl:value-of select="$textarea/@id"/></xsl:when>
        <xsl:otherwise><xsl:value-of select="generate-id()"/></xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:value-of select="$textarea/node()" />
  </textarea>
</xsl:template>

<xsl:template name="old-dialog-input-radiogroup">
  <xsl:param name="radioboxes" />
  <xsl:param name="captions" />
  <xsl:for-each select="$radioboxes">
    <span class="dialogRadio">
      <xsl:variable name="index" select="position()" />
      <xsl:variable name="auto-id" select="generate-id()"/>
      <input type="radio" name="{@name}" value="{@value}" id="{$auto-id}">
        <xsl:if test="@class">
          <xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@checked">
          <xsl:attribute name="checked"><xsl:value-of select="@checked"/></xsl:attribute>
        </xsl:if>
      </input>
      <label for="{$auto-id}">
        <xsl:choose>
          <xsl:when test="text()"><xsl:value-of select="text()"/></xsl:when>
          <xsl:when test="$captions[position() = $index]"><xsl:value-of select="$captions[position() = $index]"/></xsl:when>
          <xsl:otherwise><i>No Caption!</i></xsl:otherwise>
        </xsl:choose>
      </label>
    </span>
  </xsl:for-each>
  <xsl:call-template name="float-fix"/>
</xsl:template>


<xsl:template name="old-dialog-input-checkgroup">
  <xsl:param name="checkboxes" />
  <xsl:param name="captions" />
  <xsl:for-each select="$checkboxes">
    <span class="dialogCheckBox">
      <xsl:variable name="index" select="position()" />
      <xsl:variable name="auto-id" select="generate-id()"/>
      <input type="checkbox" name="{@name}" value="{@value}" id="{$auto-id}">
        <xsl:if test="@class">
          <xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="@checked">
          <xsl:attribute name="checked"><xsl:value-of select="@checked"/></xsl:attribute>
        </xsl:if>
      </input>
      <xsl:choose>
        <xsl:when test="text()">
          <label for="{$auto-id}"><xsl:value-of select="text()"/></label>
        </xsl:when>
        <xsl:when test="$captions[position() = $index]">
          <label for="{$auto-id}"><xsl:value-of select="$captions[position() = $index]"/></label>
        </xsl:when>
        <xsl:otherwise></xsl:otherwise>
      </xsl:choose>
    </span><xsl:text> </xsl:text>
  </xsl:for-each>
  <xsl:call-template name="float-fix"/>
</xsl:template>

<xsl:template name="old-dialog-element-info">
  <xsl:param name="element" />
  <xsl:value-of select="$element" />
</xsl:template>

<xsl:template name="old-dialog-element-grid">
  <xsl:param name="grid" />
  <table class="dialogGrid">
    <xsl:for-each select="$grid/row">
      <tr>
        <xsl:for-each select="cell">
          <td>
            <xsl:if test="@width">
              <xsl:attribute name="style">width: <xsl:value-of select="@width"/></xsl:attribute>
            </xsl:if>
            <xsl:apply-templates />
          </td>
        </xsl:for-each>
      </tr>
    </xsl:for-each>
  </table>
</xsl:template>

<xsl:template name="old-dialog-buttons">
  <xsl:param name="buttons"/>
  <xsl:param name="before-dialog" select="false()"/>
  <xsl:if test="$buttons and (not($before-dialog) or $buttons/@sandwich)">
    <div>
      <xsl:attribute name="class">
        <xsl:choose>
          <xsl:when test="$before-dialog">dialogButtonsTop</xsl:when>
          <xsl:otherwise>dialogButtonsBottom</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <div class="dialogButtonsArtWork">
        <xsl:for-each select="$buttons[not(@align) or (@align != 'left')]">
          <xsl:call-template name="old-dialog-button">
            <xsl:with-param name="button" select="."/>
          </xsl:call-template>
        </xsl:for-each>
        <xsl:for-each select="$buttons[@align = 'left']">
          <xsl:call-template name="old-dialog-button">
            <xsl:with-param name="button" select="."/>
          </xsl:call-template>
        </xsl:for-each>
        <xsl:call-template name="float-fix" />
      </div>
    </div>
  </xsl:if>
</xsl:template>

<xsl:template name="old-dialog-button">
  <xsl:param name="button"/>
  <button>
    <xsl:attribute name="class">
      <xsl:choose>
        <xsl:when test="@image and @caption and @mode and @mode = 'both'">dialogButton</xsl:when>
        <xsl:when test="@image">dialogImageButton</xsl:when>
        <xsl:otherwise>dialogButton</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:choose>
      <xsl:when test="@align = 'left'">
        <xsl:attribute name="style">float: left;</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="style">float: right;</xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:attribute name="type">
      <xsl:choose>
        <xsl:when test="@type and @type != ''"><xsl:value-of select="@type"/></xsl:when>
        <xsl:otherwise>submit</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>
    <xsl:if test="@name and @name != ''">
      <xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@id and @id != ''">
      <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@hint and @hint != ''">
      <xsl:attribute name="title"><xsl:value-of select="@hint"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@onclick and @onclick != ''">
      <xsl:attribute name="onclick"><xsl:value-of select="@onclick"/></xsl:attribute>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="@image and @caption and @mode and @mode = 'both'">
        <xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
        <img class="glyph16" alt="{@caption}">
          <xsl:attribute name="src">
            <xsl:call-template name="icon-url">
              <xsl:with-param name="icon-src" select="@image"/>
            </xsl:call-template>
          </xsl:attribute>
        </img>
        <xsl:value-of select="@caption" />
      </xsl:when>
      <xsl:when test="@image">
        <xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
        <img class="glyph16" alt="{@caption}">
          <xsl:attribute name="src">
            <xsl:call-template name="icon-url">
              <xsl:with-param name="icon-src" select="@image"/>
            </xsl:call-template>
          </xsl:attribute>
        </img>
      </xsl:when>
      <xsl:when test="@caption">
        <xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
        <xsl:value-of select="@caption" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="@value" />
      </xsl:otherwise>
    </xsl:choose>
  </button>
</xsl:template>

<xsl:template name="old-dialog-info-button">
  <xsl:param name="text" />
  <xsl:param name="mode" />
  <xsl:call-template name="panel-info-button">
    <xsl:with-param name="text" select="$text" />
    <xsl:with-param name="mode" select="$mode" />
  </xsl:call-template>
</xsl:template>

<xsl:template match="dialog">
  <xsl:call-template name="old-dialog">
    <xsl:with-param name="dialog" select="."/>
  </xsl:call-template>
</xsl:template>

</xsl:stylesheet>