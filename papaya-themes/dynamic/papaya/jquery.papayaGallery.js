/**
* papaya Gallery
*
* A gallery that's able to use a media-rss feed form items list, as well a an list of html tags.
*/
(function($) {
  $.fn.papayaGallery = function(options, data) {
    var copy = jQuery.extend(true, {}, $.papayaGallery);
    copy.initialize(this, options, data);
    return this;
  };

  $.papayaGallery = {

    container : null,
    template : null,
    items : null,

    xmlns : {
      'rss' : '',
      'atom' : 'http://www.w3.org/2005/Atom',
      'media-rss' : 'http://search.yahoo.com/mrss/'
    },

    settings : {
      feed : false,
      replace : true,
      items : [],
      template : 'div.galleryThumbnail',
      fadeDuration : 3000,
      lightboxOptions : {},
      navigation : 'both',
      captionLinkNext : 'Next',
      captionLinkPrevious : 'Previous'
    },

    /**
    * Initialize gallery, merge settings, get thumbnail template, trigger feed fetching
    */
    initialize : function(container, options, data) {
      this.container = container;
      this.settings = $.extend(this.settings, options);
      this.template = this.getThumbnailTemplate(this.settings.template);
      if (!this.settings.feed) {
        this.settings.feed = $.papayaGallery.getFeedUrlFromDocument();
      }
      if (typeof this.settings.feed == 'string') {
        this.fetchFeed(this.settings.feed);
      } else if (typeof options != 'undefined') {
        container.find(this.settings.template).papayaLightbox(
          this.settings.lightboxOptions, null, data
        );
      } else {
        container.find(this.settings.template).papayaLightbox(
          this.settings.lightboxOptions, null, galleryMapping
        );
      }
    },

    /**
    * Create a button to nvaigate between thumbnail groups/feed.
    *
    * @param DomNode parent
    * @param string type
    * @param Object item
    */
    createButton : function(parent, type, item) {
      var button = $('<a/>')
       .appendTo(parent)
       .attr('href', '#')
       .addClass(type == 'next' ? 'galleryLinkNext' : 'galleryLinkPrevious')
       .html(type == 'next' ? this.settings.captionLinkNext : this.settings.captionLinkPrevious);
      if (type == 'next') {
        button.click(function(event) {
          event.preventDefault();
          item.fetchNext();
        });
      } else {
        button.click(function(event) {
          event.preventDefault();
          item.fetchPrevious();
        });
      }
    },

    /**
    * Extract feed url from html head
    *
    * @return string
    */
    getFeedUrlFromDocument: function() {
      var feedLink = $('link[rel=alternate][type="application/rss+xml"]');
      if (feedLink.length > 0) {
        return feedLink.attr('href');
      }
      return false;
    },

    /**
    * Try to get the thumbnail template, create a default strcuture if it can not be found
    *
    * @param string selector
    */
    getThumbnailTemplate: function(selector) {
      var template = $(selector).eq(0).clone();
      if (template.length == 0) {
        template = $(
          '<div class="galleryThumbnail"><a class="galleryThumbnalFrame"><img /></a></div>'
        );
      }
      template.css('display', 'none');
      template
        .find('img')
        .removeAttr('width')
        .removeAttr('height')
        .removeAttr('style');
      return template;
    },

    /**
    * Fetch the feed using XHR
    *
    * @param string url
    * @param function fnSuccess
    * @param function fnFailure
    */
    fetchFeed: function(url, fnSuccess, fnFailure) {
      $.ajax({
        url: url,
        dataType: 'xml',
        context: this,
        success: function(xml) {
          this.readFeed(xml, fnSuccess, fnFailure);
        },
        error: function(xhr, textStatus, errorThrown) {
          this.handleFeedError(fnFailure);
        }
      });
    },

    /**
    * Read the fetched feed and generate items
    *
    * @param object xml
    * @param function fn
    */
    readFeed: function(xml, fnSuccess, fnFailure) {
      var gallery = this;
      $(xml).xmlns(
        gallery.xmlns,
        function() {
          gallery.items = [];
          this.find('rss|item').each(
            function() {
              gallery.readFeedItem($(this));
            }
          );
          var next = this.find('atom|link[rel=next]');
          var prev = this.find('atom|link[rel=previous]');
          if (next.length > 0) {
            gallery.getItem(-1).nextFeed = next.attr('href');
          }
          if (prev.length > 0) {
            gallery.getItem(0).previousFeed = prev.attr('href');
          }
        }
      );
      if (this.items.length > 0) {
        if (this.settings.replace) {
          this.replaceContainerWithGallery();
        } else {
          this.wireUpAlreadyPresentGallery();
        }
        if (fnSuccess) {
          this.container.find('div:hidden').show();
          fnSuccess(this.items);
        }
      } else {
        this.handleFeedError();
      }
    },

    /**
    * Wire up a-elements in a container to trigger a lightbox
    * based on their href.
    */
    wireUpAlreadyPresentGallery: function() {
      var gallery = this;
      $(this.container).find('a').each(function(index, Element) {
        var href = Element.href;
        for (var i = 0; i < gallery.items.length; i++) {
          if (gallery.items[i].getHref() === href) {
            $(Element).papayaLightbox(
              gallery.settings.lightboxOptions, gallery.items[i]);
            return;
          }
        }
      });
    },

    /**
    * Replace container with gallery
    */
    replaceContainerWithGallery: function() {
      $(this.container).empty();
      var group = $('<div class="galleryImages clearfix" />').appendTo(this.container);
      for (var i = 0; i < this.items.length; i++) {
        this.appendThumbnail(group, this.items[i]);
      }
      if (this.items.length > 0) {
        var navigation = $('<div class="galleryNavigation"/>');
        var firstItem = this.getItem(0);
        if (firstItem.hasPrevious()) {
          this.createButton(navigation, 'previous', firstItem);
        }
        var lastItem = this.getItem(-1);
        if (lastItem.hasNext()) {
          this.createButton(navigation, 'next', lastItem);
        }
        switch (this.settings.navigation) {
        case 'top' :
          navigation.prependTo(this.container);
          break;
        case 'both' :
          navigation.clone(true).prependTo(this.container);
          navigation.appendTo(this.container);
          break;
        case 'bottom' :
        default :
          navigation.appendTo(this.container);
          break;
        }
      }
    },

    /**
    * Handle feed laoding error
    *
    * @param function fn
    */
    handleFeedError: function(fn) {
      if (fn) {
        fn();
      }
    },

    /**
    * Read one feed item and connect it to its previous/next items
    *
    * @param DomNode feedItem
    */
    readFeedItem: function(feedItem) {
      var current = new $.papayaGalleryFeedItem(feedItem, this);
      var newIndex = this.items.length;
      var prevIndex = newIndex - 1;
      if (this.items[prevIndex]) {
        this.items[prevIndex].nextItem = current;
        current.previousItem = this.items[prevIndex];
      }
      this.items[newIndex] = current;
      return current;
    },

    /**
    * Get item from internal list by index
    *
    * @param integer index
    */
    getItem : function(index) {
      if (index < 0) {
        index = this.items.length + index;
      };
      return this.items[index];
    },

    /**
    * Append html elements for thumbnail display into container.
    *
    * @param object parent
    * @param object item
    */
    appendThumbnail: function(parent, item) {
      var element = this.template.clone();
      var fadeDuration = this.settings.fadeDuration;
      element
        .find('a')
        .attr('href', item.getImage())
        .papayaLightbox(this.settings.lightboxOptions, item);
      element.find('img').unbind('load').bind(
        'load',
        function() {
          $(element).fadeIn(fadeDuration);
        }
      );
      element.find('img').attr('src', item.getThumbnail());
      $(parent).append(element);
    }
  };

  /**
  * An lightbox item, generated from feed item. Items are connected to their preceding or following
  * siblings the first and the last know the previous/next feed
  *
  * @param DomNode feedItem
  * @param object gallery
  */
  $.papayaGalleryFeedItem = function(feedItem, gallery) {

    this.imageUrl = feedItem.find('media-rss|content').attr('url');
    this.thumbnailUrl = feedItem.find('media-rss|thumbnail').attr('url');
    this.caption = feedItem.find('media-rss|title').text();
    this.href = feedItem.find('rss|link').text();
    this.nextItem = null;
    this.previousItem = null;
    this.nextFeed = null;
    this.previousFeed = null;

    /**
    * fetch item or trigger feed fetching, depending on given parameters
    *
    * @param object item item to fetch
    * @param string feed feed to fetch, only used if item is empty
    * @param integer index item index in fetchted feed, only used if feed is fetched
    * @param function fnSuccess
    * @param function fnFailure
    */
    var fetchItem = function(item, feed, index, fnSuccess, fnFailure) {
      if (fnSuccess) {
        if (item) {
          fnSuccess(item);
        } else if (feed) {
          gallery.fetchFeed(
            feed,
            function(items) {
              fnSuccess((index < 0) ? items[items.length + index] : items[index]);
            },
            fnFailure
          );
        } else if (fnFailure) {
          fnFailure();
        }
      } else if (feed) {
        gallery.fetchFeed(feed);
      }
    };

    /**
    * Image url string
    * @return string
    */
    this.getImage = function() {
      return this.imageUrl;
    };
    /**
    * Thumbnail url string (not used by lightbox but the gallery itself)
    * @return string
    */
    this.getThumbnail = function() {
      return this.thumbnailUrl;
    };
    /**
    * Caption string
    * @return string
    */
    this.getCaption = function() {
      return this.caption;
    };

    /**
    * alternate link
    * @return string
    */
    this.getHref = function() {
      return this.href;
    };

    /**
    * Check if a next item is available
    * @return boolean
    */
    this.hasNext = function() {
      return (this.nextItem || this.nextFeed) ? true : false;
    };
    /**
    * Fetch next item
    *
    * @param function fnSuccess
    * @param function fnFailure
    */
    this.fetchNext = function(fnSuccess, fnFailure) {
      fetchItem(this.nextItem, this.nextFeed, 0, fnSuccess, fnFailure);
    };
    /**
    * Check if a previous item is available
    * @return boolean
    */
    this.hasPrevious = function() {
      return (this.previousItem || this.previousFeed) ? true : false;
    };
    /**
    * Fetch previous item
    *
    * @param function fnSuccess
    * @param function fnFailure
    */
    this.fetchPrevious = function(fnSuccess, fnFailure) {
      fetchItem(this.previousItem, this.previousFeed, -1, fnSuccess, fnFailure);
    };
  };
})(jQuery);
