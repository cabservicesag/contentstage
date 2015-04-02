/*!
        Slimbox v2.04 - The ultimate lightweight Lightbox clone for jQuery
        (c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
        MIT-style license.
        
        Adapted to just display a given jQuery object.
*/

(function($) {

        // Global variables, accessible to Slimbox only
        var win = $(window), options, compatibleOverlay, middle, centerWidth, centerHeight,
                ie6 = !window.XMLHttpRequest, hiddenElements = [], documentElement = document.documentElement, cloned,

        // DOM elements
        overlay, center, content, closeButton;

        /*
                Initialization
        */

        $(function() {
                // Append the Slimbox HTML code at the bottom of the document
                $("body").append(
                        $([
                                overlay = $('<div id="ilbOverlay" />')[0],
                                center = $('<div id="ilbCenter" class="clearfix" />')[0]
                        ]).css("display", "none")
                );
				closeButton = $('<div id="ilbClose" />').appendTo(center);
				content = $('<div id="ilbContent" />').appendTo(center);
                $(overlay).click(close);
                $(closeButton).click(close);
        });

        /*
                API
        */

        // Open Slimbox with the specified parameters
        $.iLightbox = function(_options) {
                options = $.extend({
                        overlayOpacity: 0.8,                    // 1 is opaque, 0 is completely transparent (change the color in the CSS file)
                        overlayFadeDuration: 400,               // Duration of the overlay fade-in and fade-out animations (in milliseconds)
                        resizeDuration: 400,                    // Duration of each of the box resize animations (in milliseconds)
                        resizeEasing: "swing",                  // "swing" is jQuery's default easing
                        initialWidth: 250,                      // Initial width of the box (in pixels)
                        initialHeight: 250,                     // Initial height of the box (in pixels)
                        imageFadeDuration: 400,                 // Duration of the image fade-in animation (in milliseconds) text for image groups
                        closeKeys: [27, 88, 67]                // Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
                }, _options);

                resize();
                
                compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
                if (compatibleOverlay) overlay.style.position = "absolute";
                $(center).css({display: "none", visibility: "", opacity: ""}).fadeIn(options.imageFadeDuration);
                $(overlay).css("opacity", options.overlayOpacity).fadeIn(options.overlayFadeDuration);
                position();
                setup(1);
        };

        /*
                options:        Optional options object, see jQuery.slimbox()
                linkMapper:     Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
                                the image URL and the image caption (may contain HTML)
                linksFilter:    Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
                                the image collection that will be shown on click, false if not. "this" refers to the element that was clicked.
                                This function must always return true when the DOM element argument is "this".
        */
        $.fn.iLightbox = function(_options) {
        	$(content).html('');
        	cloned = this.clone().appendTo(content);
        	
        	$.iLightbox.call(cloned, _options);
        	return cloned;
        };
        $.fn.iLightboxClose = function() {
        	close();
        	return this;
        };


        /*
                Internal functions
        */

        function position() {
                var l = win.scrollLeft(), w = win.width();
                $(center).css("left", l + (w / 2));
                if (compatibleOverlay) $(overlay).css({left: l, top: win.scrollTop(), width: w, height: win.height()});
        }

        function setup(open) {
                if (open) {
                        $("object").add(ie6 ? "select" : "embed").each(function(index, el) {
                                el.style.visibility = "hidden";
                        });
                } else {
                        $.each(hiddenElements, function(index, el) {
                                el[0].style.visibility = el[1];
                        });
                        hiddenElements = [];
                }
                var fn = open ? "bind" : "unbind";
                win[fn]("scroll resize", position);
                $(document)[fn]("keydown", keyDown);
        }

        function keyDown(event) {
                var code = event.which, fn = $.inArray;
                // Prevent default keyboard action (like navigating inside the page)
                return (fn(code, options.closeKeys) >= 0) ? close() : null;
        }

        function close() {
                $(center).hide();
				$(overlay).stop().fadeOut(options.overlayFadeDuration, setup);
				$(document).trigger('iLightboxClose');

                return false;
        }
        
        function resize() {
        	middle = win.scrollTop() + (win.height() / 2);
			centerWidth = $(center).width();
			centerHeight = $(center).height();
			$(center).css({top: Math.max(0, middle - (centerHeight / 2)), marginLeft: -centerWidth/2}).show();
        }
        
        $.fn.iLightboxPosition = function() {
        	resize();
        	position();
        	return this;
        };


})(jQuery);
