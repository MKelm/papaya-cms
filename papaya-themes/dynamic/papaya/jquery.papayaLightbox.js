/**
* papaya Lightbox
*
* Works an item object or a simple url.
*
* Based on: Slimbox v2.04, (c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
*
* @licence MIT-style license.
*/
(function($) {

  var lightbox = {
    overlay : null,
    center : null,
    image : null,
    sizer : null,
    prevLink : null,
    nextLink : null,
    bottomContainer : null,
    bottom : null,
    caption : null
  };

  var
   $window = $(window),
   $document = $(document),
   settings = {},
   isIE6 = !window.XMLHttpRequest,
   compatibleOverlay,
   hiddenElements = [],
   preLoader,
   middle,
   currentItem;

  $.fn.papayaLightbox = function(options, content, urlMapper) {
    if (!(content)) {
      var prevItem = null;
      var items = [];
      for (var i = 0; i < this.length; i++) {
        var $el = this.eq(i);
        var $link = $el.find('a');
        var current = {};
        var currentIndex = items.length;
        var prevIndex = currentIndex - 1;
        if ($link.has('a[href]')) {
          var href = $link.attr('href');
          var src = href;
          var descr = $link.attr('title');
          if (urlMapper && urlMapper.getImageUrl) {
            if (urlMapper.getImageDescription) {
              descr = urlMapper.getImageDescription(href);
            }
            src = urlMapper.getImageUrl(src);
          }
          current = new $.papayaLightboxItem(
            src, $link.attr('title')
          );
          current.caption = descr;
          current.href = href;
          $link.attr('href', src);
          if (items[prevIndex]) {
            current.previousItem = items[prevIndex];
            items[prevIndex].nextItem = current;
          }
          items[currentIndex] = current;
          $link.unbind("click").click(
            function(index) {
              return function(event) {
                event.preventDefault();
                show(items[index], options);
                return false;
              };
            }(items.length - 1)
          );
          $el.fadeIn('slow');
        }
      }
    } else {
      var item = null;
      if (typeof item == 'string') {
        item = new $.papayaLightboxItem(content, '');
      } else {
        item = content;
      }
      return this.unbind("click").click(
        function(event) {
          event.preventDefault();
          show(item, options);
          return false;
        }
      );
    }
  };

  function show(item, options) {
    settings = $.extend({
      overlayOpacity: 0.8,      // 1 is opaque, 0 is completely transparent (change the color in the CSS file)
      overlayFadeDuration: 400,   // Duration of the overlay fade-in and fade-out animations (in milliseconds)
      resizeDuration: 400,      // Duration of each of the box resize animations (in milliseconds)
      resizeEasing: "swing",      // "swing" is jQuery's default easing
      initialWidth: 250,      // Initial width of the box (in pixels)
      initialHeight: 250,     // Initial height of the box (in pixels)
      imageFadeDuration: 400,     // Duration of the image fade-in animation (in milliseconds)
      captionAnimationDuration: 400,    // Duration of the caption animation (in milliseconds)
      closeKeys: [27, 88, 67],    // Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
      previousKeys: [37, 80],     // Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
      nextKeys: [39, 78],      // Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
      showLinkAlternate : false,
      captionLinkAlternate : 'Link'
    }, options);

    create();
    middle = $window.scrollTop() + ($window.height() / 2);
    var centerWidth = settings.initialWidth;
    var centerHeight = settings.initialHeight;
    $(lightbox.center).css({
      top: Math.max(0, middle - (centerHeight / 2)),
      width: centerWidth,
      height: centerHeight,
      marginLeft: -centerWidth/2
    }).show();
    compatibleOverlay = isIE6 || (
        lightbox.overlay.currentStyle && (lightbox.overlay.currentStyle.position != "fixed")
    );
    if (compatibleOverlay) {
      lightbox.overlay.style.position = "absolute";
    }
    $(lightbox.overlay).css("opacity", settings.overlayOpacity).fadeIn(settings.overlayFadeDuration);
    center();
    setUp();
    return loadImage(item);
  }

  function loadImage(item) {
    stop();
    lightbox.center.className = "lightboxLoading";
    preLoader = new Image();
    preLoader.onload = function() {
      showImage(item);
    };
    preLoader.src = item.getImage();
  }

  function showImage(item) {
    currentItem = item;
    lightbox.center.className = "";
    $(lightbox.image).css(
      {backgroundImage: "url(" + item.getImage() + ")", visibility: "hidden", display: ""}
    );
    $(lightbox.sizer).width(preLoader.width);
    $([lightbox.sizer, lightbox.prevLink, lightbox.nextLink]).height(preLoader.height);

    $(lightbox.caption).html(item.getCaption());
    if (settings.showLinkAlternate && item.getHref) {
      var href = item.getHref();
      if (typeof href == 'string' && href != '') {
         var $link = $('<a class="galleryLinkAlternate"/>').appendTo(lightbox.caption);
         $link.attr('href', href);
         $link.html(settings.captionLinkAlternate);
      }
    }

    var centerWidth = lightbox.image.offsetWidth;
    var centerHeight = lightbox.image.offsetHeight;

    var top = Math.max(0, middle - (centerHeight / 2));
    if (lightbox.center.offsetHeight != centerHeight) {
      $(lightbox.center).animate(
        {height: centerHeight, top: top}, settings.resizeDuration, settings.resizeEasing
      );
    }
    if (lightbox.center.offsetWidth != centerWidth) {
      $(lightbox.center).animate(
        {width: centerWidth, marginLeft: -centerWidth/2}, settings.resizeDuration, settings.resizeEasing
      );
    }
    $(lightbox.center).queue(function() {
      $(lightbox.bottomContainer).css(
        {width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""}
      );
      $(lightbox.image).css(
        {display: "none", visibility: "", opacity: ""}
      ).fadeIn(settings.imageFadeDuration, showCaption);
    });
  }

  function showCaption() {
    if (currentItem && currentItem.hasPrevious()) $(lightbox.prevLink).show();
    if (currentItem && currentItem.hasNext()) $(lightbox.nextLink).show();
    $(lightbox.bottom).css("marginTop", -lightbox.bottom.offsetHeight).animate(
      {marginTop: 0}, settings.captionAnimationDuration
    );
    lightbox.bottomContainer.style.visibility = "";
  }

  function create() {
    if (!lightbox.overlay) {
      $("body").append(
        $([
          lightbox.overlay = $('<div id="lightboxOverlay" />')[0],
          lightbox.center = $('<div id="lightboxCenter" />')[0],
          lightbox.bottomContainer = $('<div id="lightboxBottomContainer" />')[0]
        ]).css("display", "none")
      );

      lightbox.image = $('<div id="lightboxImage" />').appendTo(lightbox.center).append(
        lightbox.sizer = $('<div style="position: relative;" />').append([
          lightbox.prevLink = $('<a id="lightboxPrevLink" href="javascript:;" />').click(previous)[0],
          lightbox.nextLink = $('<a id="lightboxNextLink" href="javascript:;" />').click(next)[0]
        ])[0]
      )[0];

      lightbox.bottom = $('<div id="lightboxBottom" />').appendTo(lightbox.bottomContainer).append([
        $('<a id="lightboxCloseLink" href="#" />').add(lightbox.overlay).click(close)[0],
        lightbox.caption = $('<div id="lightboxCaption" />')[0],
        lightbox.number = $('<div id="lightboxNumber" />')[0],
        $('<div style="clear: both;" />')[0]
      ])[0];
      $('#lightboxCloseLink').click(function (event) {
        event.preventDefault();
      });
    }
  }

  function center() {
    var left = $window.scrollLeft(), width = $window.width();
    $([lightbox.center, lightbox.bottomContainer]).css("left", left + (width / 2));
    if (compatibleOverlay) {
      $(overlay).css(
        {left: left, top: $window.scrollTop(), width: width, height: $window.height()}
      );
    }
  }

  function close() {
    currentItem = null;
    stop();
    $(lightbox.center).hide();
    $(lightbox.overlay).stop().fadeOut(settings.overlayFadeDuration, tearDown);
  }

  function stop() {
    if (preLoader) {
      preLoader.onload = null;
      preLoader.src = "";
    }
    $([lightbox.center, lightbox.image, lightbox.bottom]).stop(true);
    $([lightbox.prevLink, lightbox.nextLink, lightbox.image, lightbox.bottomContainer]).hide();
  }

  function setUp() {
    $("object").add(isIE6 ? "select" : "embed").each(function(index, element) {
      hiddenElements[index] = [element, element.style.visibility];
      element.style.visibility = "hidden";
    });
    $window.bind("scroll resize", center);
    $document.bind("keydown", keyDown);
  }

  function tearDown() {
    $.each(hiddenElements, function(index, element) {
      element[0].style.visibility = element[1];
    });
    hiddenElements = [];
    $window.unbind("scroll resize", center);
    $document.unbind("keydown", keyDown);
  }

  function keyDown(event) {
    var code = event.keyCode, fn = $.inArray;
    // Prevent default keyboard action (like navigating inside the page)
    return (fn(code, settings.closeKeys) >= 0) ? close()
      : (fn(code, settings.nextKeys) >= 0) ? next()
      : (fn(code, settings.previousKeys) >= 0) ? previous()
      : false;
  }

  function previous() {
    if (currentItem && currentItem.fetchPrevious) {
      currentItem.fetchPrevious(
        function(item) {
          loadImage(item);
        },
        close
      );
    }
  }

  function next() {
    if (currentItem && currentItem.fetchNext) {
      currentItem.fetchNext(
        function(item) {
          loadImage(item);
        },
        close
      );
    }
  }

  /**
  * Simple Lightbox item
  */
  $.papayaLightboxItem = function(image, caption) {

    this.imageUrl = image;
    this.caption = caption;
    this.href = caption;
    this.nextItem = null;
    this.previousItem = null;

    this.getImage = function() {
      return this.imageUrl;
    };
    this.getCaption = function() {
      return this.caption;
    };
    this.getHref = function() {
      return this.href;
    };

    this.hasNext = function() {
      return (this.nextItem) ? true : false;
    };
    this.fetchNext = function(fn) {
      if (fn && this.nextItem) {
        fn(this.nextItem);
      }
    };

    this.hasPrevious = function() {
      return (this.previousItem) ? true : false;
    };
    this.fetchPrevious = function(fn) {
      if (fn && this.previousItem) {
        fn(this.previousItem);
      }
    };
  };
})(jQuery);