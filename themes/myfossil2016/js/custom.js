/*
These functions are required to make HideShowPassword work in buddypress settings
THESE NEED TO BE REWORKED. THEY WERE WORKING BEFORE AN UPDATE, BUT NOW HIDESHOWPASSWORD ISN'T BEING LOADED EXCEPT ON LOGIN SCREEN
*/
(function (factory) {

  if (typeof define === 'function' && define.amd) {
    // AMD. Register as an anonymous module.
    define(['jquery'], factory);
  } else if (typeof exports === 'object') {
    // Node/CommonJS
    factory(require('jquery'));
  } else {
    // Browser globals
    factory(jQuery);
  }

}(function ($, undef) {

  var dataKey = 'plugin_hideShowPassword',
    shorthandArgs = ['show', 'innerToggle'],
    SPACE = 32,
    ENTER = 13;

  var canSetInputAttribute = (function(){
    var body = document.body,
      input = document.createElement('input'),
      result = true;
    if (! body) {
      body = document.createElement('body');
    }
    input = body.appendChild(input);
    try {
      input.setAttribute('type', 'text');
    } catch (e) {
      result = false;
    }
    body.removeChild(input);
    return result;
  }());

  var defaults = {
    // Visibility of the password text. Can be true, false, 'toggle'
    // or 'infer'. If 'toggle', it will be the opposite of whatever
    // it currently is. If 'infer', it will be based on the input
    // type (false if 'password', otherwise true).
    show: 'infer',

    // Set to true to create an inner toggle for this input. Can
    // also be sent to an event name to delay visibility of toggle
    // until that event is triggered on the input element.
    innerToggle: false,

    // If false, the plugin will be disabled entirely. Set to
    // the outcome of a test to insure input attributes can be
    // set after input has been inserted into the DOM.
    enable: canSetInputAttribute,

    // Class to add to input element when the plugin is enabled.
    className: 'hideShowPassword-field',

    // Event to trigger when the plugin is initialized and enabled.
    initEvent: 'hideShowPasswordInit',

    // Event to trigger whenever the visibility changes.
    changeEvent: 'passwordVisibilityChange',

    // Properties to add to the input element.
    props: {
      autocapitalize: 'off',
      autocomplete: 'off',
      autocorrect: 'off',
      spellcheck: 'false'
    },

    // Options specific to the inner toggle.
    toggle: {
      // The element to create.
      element: '<button type="button">',
      // Class name of element.
      className: 'hideShowPassword-toggle',
      // Whether or not to support touch-specific enhancements.
      // Defaults to the value of Modernizr.touch if available,
      // otherwise false.
      touchSupport: (typeof Modernizr === 'undefined') ? false : Modernizr.touch,
      // Non-touch event to bind to.
      attachToEvent: 'click.hideShowPassword',
      // Event to bind to when touchSupport is true.
      attachToTouchEvent: 'touchstart.hideShowPassword mousedown.hideShowPassword',
      // Key event to bind to if attachToKeyCodes is an array
      // of at least one keycode.
      attachToKeyEvent: 'keyup',
      // Key codes to bind the toggle event to for accessibility.
      // If false, this feature is disabled entirely.
      // If true, the array of key codes will be determined based
      // on the value of the element option.
      attachToKeyCodes: true,
      // Styles to add to the toggle element. Does not include
      // positioning styles.
      styles: { position: 'absolute' },
      // Styles to add only when touchSupport is true.
      touchStyles: { pointerEvents: 'none' },
      // Where to position the inner toggle relative to the
      // input element. Can be 'right', 'left' or 'infer'. If
      // 'infer', it will be based on the text-direction of the
      // input element.
      position: 'infer',
      // Where to position the inner toggle on the y-axis
      // relative to the input element. Can be 'top', 'bottom'
      // or 'middle'.
      verticalAlign: 'middle',
      // Amount by which to "offset" the toggle from the edge
      // of the input element.
      offset: 0,
      // Attributes to add to the toggle element.
      attr: {
        role: 'button',
        'aria-label': 'Show Password',
        tabIndex: 0
      }
    },

    // Options specific to the wrapper element, created
    // when the innerToggle is initialized to help with
    // positioning of that element.
    wrapper: {
      // The element to create.
      element: '<div>',
      // Class name of element.
      className: 'hideShowPassword-wrapper',
      // If true, the width of the wrapper will be set
      // unless it is already the same width as the inner
      // element. If false, the width will never be set. Any
      // other value will be used as the width.
      enforceWidth: true,
      // Styles to add to the wrapper element. Does not
      // include inherited styles or width if enforceWidth
      // is not false.
      styles: { position: 'relative' },
      // Styles to "inherit" from the input element, allowing
      // the wrapper to avoid disrupting page styles.
      inheritStyles: [
        'display',
        'verticalAlign',
        'marginTop',
        'marginRight',
        'marginBottom',
        'marginLeft'
      ],
      // Styles for the input element when wrapped.
      innerElementStyles: {
        marginTop: 0,
        marginRight: 0,
        marginBottom: 0,
        marginLeft: 0
      }
    },

    // Options specific to the 'shown' or 'hidden'
    // states of the input element.
    states: {
      shown: {
        className: 'hideShowPassword-shown',
        changeEvent: 'passwordShown',
        props: { type: 'text' },
        toggle: {
          className: 'hideShowPassword-toggle-hide',
          content: 'Hide',
          attr: { 'aria-pressed': 'true' }
        }
      },
      hidden: {
        className: 'hideShowPassword-hidden',
        changeEvent: 'passwordHidden',
        props: { type: 'password' },
        toggle: {
          className: 'hideShowPassword-toggle-show',
          content: 'Show',
          attr: { 'aria-pressed': 'false' }
        }
      }
    }

  };

  function HideShowPassword (element, options) {
    this.element = $(element);
    this.wrapperElement = $();
    this.toggleElement = $();
    this.init(options);
  }

  HideShowPassword.prototype = {

    init: function (options) {
      if (this.update(options, defaults)) {
        this.element.addClass(this.options.className);
        if (this.options.innerToggle) {
          this.wrapElement(this.options.wrapper);
          this.initToggle(this.options.toggle);
          if (typeof this.options.innerToggle === 'string') {
            this.toggleElement.hide();
            this.element.one(this.options.innerToggle, $.proxy(function(){
              this.toggleElement.show();
            }, this));
          }
        }
        this.element.trigger(this.options.initEvent, [ this ]);
      }
    },

    update: function (options, base) {
      this.options = this.prepareOptions(options, base);
      if (this.updateElement()) {
        this.element
          .trigger(this.options.changeEvent, [ this ])
          .trigger(this.state().changeEvent, [ this ]);
      }
      return this.options.enable;
    },

    toggle: function (showVal) {
      showVal = showVal || 'toggle';
      return this.update({ show: showVal });
    },

    prepareOptions: function (options, base) {
      var keyCodes = [],
        testElement;
      base = base || this.options;
      options = $.extend(true, {}, base, options);
      if (options.enable) {
        if (options.show === 'toggle') {
          options.show = this.isType('hidden', options.states);
        } else if (options.show === 'infer') {
          options.show = this.isType('shown', options.states);
        }
        if (options.toggle.position === 'infer') {
          options.toggle.position = (this.element.css('text-direction') === 'rtl') ? 'left' : 'right';
        }
        if (! $.isArray(options.toggle.attachToKeyCodes)) {
          if (options.toggle.attachToKeyCodes === true) {
            testElement = $(options.toggle.element);
            switch(testElement.prop('tagName').toLowerCase()) {
              case 'button':
              case 'input':
                break;
              case 'a':
                if (testElement.filter('[href]').length) {
                  keyCodes.push(SPACE);
                  break;
                }
              default:
                keyCodes.push(SPACE, ENTER);
                break;
            }
          }
          options.toggle.attachToKeyCodes = keyCodes;
        }
      }
      return options;
    },

    updateElement: function () {
      if (! this.options.enable || this.isType()) return false;
      this.element
        .prop($.extend({}, this.options.props, this.state().props))
        .addClass(this.state().className)
        .removeClass(this.otherState().className);
      this.updateToggle();
      return true;
    },

    isType: function (comparison, states) {
      states = states || this.options.states;
      comparison = comparison || this.state(undef, undef, states).props.type;
      if (states[comparison]) {
        comparison = states[comparison].props.type;
      }
      return this.element.prop('type') === comparison;
    },

    state: function (key, invert, states) {
      states = states || this.options.states;
      if (key === undef) {
        key = this.options.show;
      }
      if (typeof key === 'boolean') {
        key = key ? 'shown' : 'hidden';
      }
      if (invert) {
        key = (key === 'shown') ? 'hidden' : 'shown';
      }
      return states[key];
    },

    otherState: function (key) {
      return this.state(key, true);
    },

    wrapElement: function (options) {
      var enforceWidth = options.enforceWidth,
        targetWidth;
      if (! this.wrapperElement.length) {
        targetWidth = this.element.outerWidth();
        $.each(options.inheritStyles, $.proxy(function (index, prop) {
          options.styles[prop] = this.element.css(prop);
        }, this));
        this.element.css(options.innerElementStyles).wrap(
          $(options.element).addClass(options.className).css(options.styles)
        );
        this.wrapperElement = this.element.parent();
        if (enforceWidth === true) {
          enforceWidth = (this.wrapperElement.outerWidth() === targetWidth) ? false : targetWidth;
        }
        if (enforceWidth !== false) {
          this.wrapperElement.css('width', enforceWidth);
        }
      }
      return this.wrapperElement;
    },

    initToggle: function (options) {
      if (! this.toggleElement.length) {
        // Create element
        this.toggleElement = $(options.element)
          .attr(options.attr)
          .addClass(options.className)
          .css(options.styles)
          .appendTo(this.wrapperElement);
        // Update content/attributes
        this.updateToggle();
        // Position
        this.positionToggle(options.position, options.verticalAlign, options.offset);
        // Events
        if (options.touchSupport) {
          this.toggleElement.css(options.touchStyles);
          this.element.on(options.attachToTouchEvent, $.proxy(this.toggleTouchEvent, this));
        } else {
          this.toggleElement.on(options.attachToEvent, $.proxy(this.toggleEvent, this));
        }
        if (options.attachToKeyCodes.length) {
          this.toggleElement.on(options.attachToKeyEvent, $.proxy(this.toggleKeyEvent, this));
        }
      }
      return this.toggleElement;
    },

    positionToggle: function (position, verticalAlign, offset) {
      var styles = {};
      styles[position] = offset;
      switch (verticalAlign) {
        case 'top':
        case 'bottom':
          styles[verticalAlign] = offset;
          break;
        case 'middle':
          styles.top = '50%';
          styles.marginTop = this.toggleElement.outerHeight() / -2;
          break;
      }
      return this.toggleElement.css(styles);
    },

    updateToggle: function (state, otherState) {
      var paddingProp,
        targetPadding;
      if (this.toggleElement.length) {
        paddingProp = 'padding-' + this.options.toggle.position;
        state = state || this.state().toggle;
        otherState = otherState || this.otherState().toggle;
        this.toggleElement
          .attr(state.attr)
          .addClass(state.className)
          .removeClass(otherState.className)
          .html(state.content);
        targetPadding = this.toggleElement.outerWidth() + (this.options.toggle.offset * 2);
        if (this.element.css(paddingProp) !== targetPadding) {
          this.element.css(paddingProp, targetPadding);
        }
      }
      return this.toggleElement;
    },

    toggleEvent: function (event) {
      event.preventDefault();
      this.toggle();
    },

    toggleKeyEvent: function (event) {
      $.each(this.options.toggle.attachToKeyCodes, $.proxy(function(index, keyCode) {
        if (event.which === keyCode) {
          this.toggleEvent(event);
          return false;
        }
      }, this));
    },

    toggleTouchEvent: function (event) {
      var toggleX = this.toggleElement.offset().left,
        eventX,
        lesser,
        greater;
      if (toggleX) {
        eventX = event.pageX || event.originalEvent.pageX;
        if (this.options.toggle.position === 'left') {
          toggleX+= this.toggleElement.outerWidth();
          lesser = eventX;
          greater = toggleX;
        } else {
          lesser = toggleX;
          greater = eventX;
        }
        if (greater >= lesser) {
          this.toggleEvent(event);
        }
      }
    }

  };

  $.fn.hideShowPassword = function () {
    var options = {};
    $.each(arguments, function (index, value) {
      var newOptions = {};
      if (typeof value === 'object') {
        newOptions = value;
      } else if (shorthandArgs[index]) {
        newOptions[shorthandArgs[index]] = value;
      } else {
        return false;
      }
      $.extend(true, options, newOptions);
    });
    return this.each(function(){
      var $this = $(this),
        data = $this.data(dataKey);
      if (data) {
        data.update(options);
      } else {
        $this.data(dataKey, new HideShowPassword(this, options));
      }
    });
  };

  $.each({ 'show':true, 'hide':false, 'toggle':'toggle' }, function (verb, showVal) {
    $.fn[verb + 'Password'] = function (innerToggle, options) {
      return this.hideShowPassword(showVal, innerToggle, options);
    };
  });

}));

(function ($) {
	"use strict";
	$(function () {
		var hideShowPasswordVars = {"innerToggle":"1","checkboxLabel":"Show Password"};
		var el = $('#pass1');
		var innerToggle = (1 == hideShowPasswordVars.innerToggle) ? true : false;
		var enableTouchSupport = false;
		if ( ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch ) {
			enableTouchSupport = true;
		}
		el.hideShowPassword( false, innerToggle, {
			toggle: {
				touchSupport: enableTouchSupport
			}
		});
		if ( false == innerToggle ) {
			var checkbox = $('<label class="hideShowPassword-checkbox"><input type="checkbox" /> '+hideShowPasswordVars.checkboxLabel+'</label>').insertAfter(el.parent('label'));
			checkbox.on( 'change.hideShowPassword', 'input[type="checkbox"]', function() {
				el.togglePassword().focus();
			})
		}
	});
}(jQuery));

(function ($) {
	"use strict";
	$(function () {
		var hideShowPasswordVars = {"innerToggle":"1","checkboxLabel":"Show Password"};
		var el = $('#pass2');
		var innerToggle = (1 == hideShowPasswordVars.innerToggle) ? true : false;
		var enableTouchSupport = false;
		if ( ('ontouchstart' in window) || window.DocumentTouch && document instanceof DocumentTouch ) {
			enableTouchSupport = true;
		}
		el.hideShowPassword( false, innerToggle, {
			toggle: {
				touchSupport: enableTouchSupport
			}
		});
		if ( false == innerToggle ) {
			var checkbox = $('<label class="hideShowPassword-checkbox"><input type="checkbox" /> '+hideShowPasswordVars.checkboxLabel+'</label>').insertAfter(el.parent('label'));
			checkbox.on( 'change.hideShowPassword', 'input[type="checkbox"]', function() {
				el.togglePassword().focus();
			})
		}
	});
}(jQuery));

/**
 * CODE TO STRIP @ OUT OF "TO" LINE IN MESSAGING WHEN YOU CLICK SUBMIT
*/
(function($){
  function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
  }

  $(document).ready(function(){
    $('#send_message_form input#send').on('click', function(event) {
      var sendTo = $('#send_message_form input#send-to-input');
      sendTo.val(replaceAll(sendTo.val(), '@', ''));
    });
  });
})(jQuery);

/**
 * Get Registration form showing the proper sections
 */
(function($){

  $(function(){
    var minor = function() {
      if ($('input[type=radio][name=field_343]:checked').val() == 'Yes') {
        $('.adult-consent-minor').css("display", "block");
        $('.adult').css("display", "block");
        $('.minor').css("display", "none");
        $('.consent-minor').css("display", "none");
      }
      else if ($('input[type=radio][name=field_343]:checked').val() == 'No') {
        $('.minor').css("display", "block");
        $('.adult').css("display", "none");

        consent();
      }
    }

    var consent = function() {
      if ($('input[type=radio][name=field_346]').is(':checked') && $('input[type=radio][name=field_346]:checked').val().includes('Yes')) {
        $('.consent-minor').css("display", "block");
        $('.adult-consent-minor').css("display", "block");
      }
      else {
        $('.consent-minor').css("display", "none");
        $('.adult-consent-minor').css("display", "none");
      }
    }

    $('input[type=radio][name=field_343]').change(function() {
      minor();
    });

    $('input[type=radio][name=field_346]').change(function() {
      consent();
    });

    minor();
  });
})(jQuery);

/**
 * CODE TO MAKE DROPDOWN MENUS WORK PROPERLY SINCE BOOTSTRAP REMOVED DROPDOWN SUPPORT
 */
(function($){
	$(document).ready(function(){
		$('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
			event.preventDefault(); 
			event.stopPropagation(); 
			$(this).parent().siblings().removeClass('open');
			$(this).parent().toggleClass('open');
		});
	});
})(jQuery);

jQuery(document).ready(function($) {
	
	/* necessary to get @ mentions working in the tinyMCE on the forums */
	window.onload = function() { 
	      my_timing = setInterval(function(){myTimer();},1000);
	      function myTimer() {
	        if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.activeEditor !== null && typeof window.tinyMCE.activeEditor !== 'undefined') {  
		        $( window.tinyMCE.activeEditor.contentDocument.activeElement )
					.atwho( 'setIframe', $( '.wp-editor-wrap iframe' )[0] )
					.bp_mentions( bp.mentions.users );
				 window.clearInterval(my_timing);
		    }
	      }
	      myTimer();
	};
	
	/* necessary to get the custom post-form working */
  	//Deregister buddypress built in actions
  	$('#whats-new').off("focus");
  	$('#whats-new-form').off("focusout");
  	$('#whats-new-options').show();
  	$('#aw-whats-new-submit').off('click');

  	/* New posts */
	$('#aw-whats-new-submit').on( 'click', function() {
		var editor = tinymce.get('whats-new');
		editor.save();

		var last_date_recorded = 0,
			button = $(this),
			form   = button.closest('form#whats-new-form'),
			inputs = {}, post_data;

		// Get all inputs and organize them into an object {name: value}
		$.each( form.serializeArray(), function( key, input ) {
			// Only include public extra data
			if ( '_' !== input.name.substr( 0, 1 ) && 'whats-new' !== input.name.substr( 0, 9 ) ) {
				if ( ! inputs[ input.name ] ) {
					inputs[ input.name ] = input.value;
				} else {
					// Checkboxes/dropdown list can have multiple selected value
					if ( ! $.isArray( inputs[ input.name ] ) ) {
						inputs[ input.name ] = new Array( inputs[ input.name ], input.value );
					} else {
						inputs[ input.name ].push( input.value );
					}
				}
			}
		} );

		form.find( '*' ).each( function() {
			if ( $.nodeName( this, 'textarea' ) || $.nodeName( this, 'input' ) ) {
				$(this).prop( 'disabled', true );
			}
		} );

		/* Remove any errors */
		$('div.error').remove();
		button.addClass('loading');
		button.prop('disabled', true);
		form.addClass('submitted');

		/* Default POST values */
		object = '';
		item_id = $('#whats-new-post-in').val();
		content = $('#whats-new').val();
		firstrow = $( '#buddypress ul.activity-list li' ).first();
		activity_row = firstrow;
		timestamp = null;

		// Checks if at least one activity exists
		if ( firstrow.length ) {

			if ( activity_row.hasClass( 'load-newest' ) ) {
				activity_row = firstrow.next();
			}

			timestamp = activity_row.prop( 'class' ).match( /date-recorded-([0-9]+)/ );
		}

		if ( timestamp ) {
			last_date_recorded = timestamp[1];
		}

		/* Set object for non-profile posts */
		if ( item_id > 0 ) {
			object = $('#whats-new-post-object').val();
		}

		post_data = $.extend( {
			action: 'post_update',
			'cookie': bp_get_cookies(),
			'_wpnonce_post_update': $('#_wpnonce_post_update').val(),
			'content': content,
			'object': object,
			'item_id': item_id,
			'since': last_date_recorded,
			'_bp_as_nonce': $('#_bp_as_nonce').val() || ''
		}, inputs );

		$.post( ajaxurl, post_data, function( response ) {
			form.find( '*' ).each( function() {
				if ( $.nodeName( this, 'textarea' ) || $.nodeName( this, 'input' ) ) {
					$(this).prop( 'disabled', false );
				}
			});

			/* Check for errors and append if found. */
			if ( response[0] + response[1] === '-1' ) {
				form.prepend( response.substr( 2, response.length ) );
				$( '#' + form.attr('id') + ' div.error').hide().fadeIn( 200 );
			} else {
				if ( 0 === $('ul.activity-list').length ) {
					$('div.error').slideUp(100).remove();
					$('#message').slideUp(100).remove();
					$('div.activity').append( '<ul id="activity-stream" class="activity-list item-list">' );
				}

				if ( firstrow.hasClass( 'load-newest' ) ) {
					firstrow.remove();
				}

				$('#activity-stream').prepend(response);

				if ( ! last_date_recorded ) {
					$('#activity-stream li:first').addClass('new-update just-posted');
				}

				if ( 0 !== $('#latest-update').length ) {
					var l   = $('#activity-stream li.new-update .activity-content .activity-inner p').html(),
						v     = $('#activity-stream li.new-update .activity-content .activity-header p a.view').attr('href'),
						ltext = $('#activity-stream li.new-update .activity-content .activity-inner p').text(),
						u     = '';

					if ( ltext !== '' ) {
						u = l + ' ';
					}

					u += '<a href="' + v + '" rel="nofollow">' + BP_DTheme.view + '</a>';

					$('#latest-update').slideUp(300,function(){
						$('#latest-update').html( u );
						$('#latest-update').slideDown(300);
					});
				}

				$('li.new-update').hide().slideDown( 300 );
				$('li.new-update').removeClass( 'new-update' );
				$('#whats-new').val('');
				form.get(0).reset();

				// reset vars to get newest activities
				newest_activities = '';
				activity_last_recorded  = 0;
			}

			//$('#whats-new-options').slideUp();
			$('#whats-new-form textarea').animate({
				height:'2.2em'
			});
			$('#aw-whats-new-submit').removeClass('loading');
			$( '#whats-new-content' ).removeClass( 'active' );
		});

		return false;
	});

  $('#show-homepage-whats-new').click(function () {
    $('#homepage-whats-new-mask').css('display','none');
    $('#homepage-whats-new').css('display','block');
    $('#wp-whats-new-editor-container').css('height','247px');
    tinyMCE.activeEditor.focus();
  });
})

/**
 * remove cookie that breaks Buddypress groups
 */
jq(document).ready(function() {
  /* Reset the page */
  jq.cookie('bp-activity-oldestpage', 1, {
      path: '/'
  });
});