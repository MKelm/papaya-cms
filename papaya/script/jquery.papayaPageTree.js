(function($) {

  var PapayaPageTreeItem = {

    tree : null,

    itemId : 'id0',
    children : [],
    node : null,
    status : 'empty',

    pageId : 0,
    pageTitle : '',

    /**
     * Store objects and attach events
     *
     * @param parent
     * @param node
     */
    setUp : function(parent, node) {
      this.tree = parent;
      this.node = node;
      this.node.find('.nodeIcon').click(
        $.proxy(this.onNodeIconClick, this)
      );
      this.node.find('.itemIcon, .itemTitle').click(
        $.proxy(this.onItemClick, this)
      );
    },

    /**
     * If the item node icon is clicked toggle the open/closed status.
     *
     * @param event
     */
    onNodeIconClick : function(event) {
      event.preventDefault();
      this.toggle();
      this.tree.formatRows(this.node);
    },

    /**
     * If the item is clicked, select it.
     *
     * @param event
     */
    onItemClick : function(event) {
      event.preventDefault();
      this.tree.select(this);
    },

    /**
     * Open the current node. This has only an effect if the node has children. If maxDepth doe not
     * equals zero open is called on the children, too.
     */
    open : function(maxDepth) {
      var max = this.children.length;
      if (max > 0) {
        for (var i = 0; i < max; i++) {
          this.children[i].show();
          if (maxDepth != 0) {
            this.children[i].open(maxDepth > 0 ? maxDepth - 1 : maxDepth);
          }
        }
        this.setStatus('open');
      }
    },

    /**
     * Close the current node. This has only an effect if the node has children.
     * Close is always called for the children, too.
     */
    close : function() {
      var max = this.children.length;
      if (max > 0) {
        for (var i = 0; i < max; i++) {
          this.children[i].close();
          this.children[i].hide();
        }
        this.setStatus('closed');
      }
    },

    /**
     * toogle the node status - open/closes the node.
     */
    toggle : function() {
      switch (this.status) {
      case 'open' :
        this.close();
        break;
      case 'closed' :
        this.open(0);
        break;
      }
    },

    /**
     * Show the attached node
     */
    show : function() {
      if (this.node) {
        this.node.show();
      }
    },

    /**
     * Hide the attached node
     */
    hide : function() {
      if (this.node) {
        this.node.hide();
      }
    },

    /**
     * Set the insternal status member varialbe and change the node icon if an element is
     * attached.
     *
     * @param status
     * @returns {String}
     */
    setStatus : function(status) {
      switch (status) {
      case 'open' :
        this.status = 'open';
        break;
      case 'closed' :
        this.status = 'closed';
        break;
      case 'empty' :
        this.status = 'empty';
      }
      if (this.node) {
        this.node.find('.nodeIcon img').attr('src', this.tree.statusImages[this.status]);
      }
      return this.status;
    }
  }

  var papayaPageTree = {

    options : {
      url : '../../xmltree.php'
    },

    node : null,

    statusImages :{
      'closed' : '../../pics/icons/16x16/status/node-closed.png',
      'open' : '../../pics/icons/16x16/status/node-open.png',
      'empty' : '../../pics/icons/16x16/status/node-empty.png'
    },

    items : {},
    itemCount : 0,

    itemTemplate :
      '<tr style="visibity: hidden; display: none;">' +
        '<td>' +
          '<a class="nodeIcon" href="#">' +
            '<img class="glyph" alt="+" src="../../pics/icons/16x16/status/node-empty.png">' +
          '</a>' +
          '<a class="itemIcon" href="#">' +
            '<img class="glyph" src="../../pics/icons/16x16/items/page.png" alt=""/>' +
          '</a>' +
          '<a class="itemTitle" href="#"/>' +
        '</td>' +
      '</tr>',

    /**
     * Set up the page tree on the given dom element
     *
     * @param element
     * @param options
     */
    setUp : function (element, options) {
      this.node = $(element);
      this.options = $.extend(this.options, options);
      this.fetch();
    },

    /**
     * add an item from the xml response. An additional item with the id 0 is created implicit
     * for a list of the root items.
     *
     * @param item
     */
    add : function (item) {
      var parentId = 'id' + item.attr('prev');
      var parent = this.items[parentId];
      if (!parent) {
        parentId = 'id0';
        parent = this.items[parentId];
        if (!parent) {
          this.items['id0'] = $.extend(true, {}, PapayaPageTreeItem);
        }
        parent = this.items['id0'];
      } else if (parentId != 'id0' && parent.children.length == 0) {
        parent.setStatus('closed');
      }
      var itemId = 'id' + item.attr('id');
      var node = $(this.itemTemplate).appendTo(this.node);
      node.find('.itemTitle').text(item.attr('title'));
      if (item.attr('prev') > 0  && item.attr('indent') > 0) {
        node.find('td').css('padding-left', (item.attr('indent') * 24) +'px');
      }
      var item = $.extend(
        true,
        {},
        PapayaPageTreeItem,
        {
          itemId : itemId,
          parentId : parentId,
          pageId : item.attr('id'),
          pageTitle : item.attr('title')
        }
      );
      item.setUp(this, node);
      this.items[itemId] = item;
      parent.children[parent.children.length] = item;
    },

    /**
     * Trigger the callback to select the given item
     *
     * @param item
     */
    select : function(item) {
      if (this.options.onSelect) {
        this.options.onSelect(item.pageId, item.pageTitle);
      }
    },

    /**
     * Add odd/even classes to the visible tables rows. If currentRow ist provided only table rows
     * following that row are changed.
     *
     * @param currentRow
     */
    formatRows : function(currentRow) {
      var nodes = currentRow ? currentRow.nextAll(':visible') : this.node.find('tr:visible');
      nodes.each(
        function (index) {
          var modulo = index % 2;
          $(this)
            .removeClass(modulo ? 'even'  : 'odd')
            .addClass(modulo ? 'odd'  : 'even');
        }
      );
    },

    /**
     * Fetch page tree xml from webserver
     */
    fetch : function() {
      if (this.options.url != '') {
        $.get(this.options.url)
          .success($.proxy(this.ajaxSuccess, this));
      }
    },

    /**
     * Read items from xml response and add them to the tree.
     *
     * @param data
     */
    read : function(data) {
      var tree = this;
      data.find('item').each(
        function () {
          tree.add($(this));
        }
      )
      if (this.items['id0']) {
        this.items['id0'].open(1);
      }
      this.formatRows();
    },

    /**
     * Ajax request successful callback. If the response contains a string
     * try to convert it into a dom.
     *
     * @param data
     */
    ajaxSuccess : function(data) {
      if (typeof data == 'string') {
        data = new DOMParser().parseFromString(data, 'text/xml');
      }
      this.read($(data));
    }
  };

  $.fn.papayaPageTree = function(options) {
    this.each(
      function() {
        var instance = jQuery.extend(true, {}, papayaPageTree);
        instance.setUp(this, options);
      }
    );
    return this;
  };
})(jQuery);
