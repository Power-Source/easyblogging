(function ($) {
	'use strict';

	function initMenu() {
		$('#menu ul ul').hide();

		$('#menu ul li').on('click', function () {
			const $item = $(this);
			const $parent = $item.parent();

			$parent.find('> li > ul').stop(true, true).slideUp('fast');
			$parent.find('> li').removeClass('current');

			$item.find('> ul').stop(true, true).slideToggle('fast');
			$item.toggleClass('current');
		});
	}

	function initMobileMenu() {
		const $body = $('body');
		const $sidebar = $('#primary_left');
		const $toggle = $('.wdeb_menu_toggle');
		const $close = $('.wdeb_menu_close');
		const $backdrop = $('.wdeb_menu_backdrop');
		const mobileQuery = window.matchMedia('(max-width: 782px)');
		let returnFocus = null;

		$('#wp-admin-bar-menu-toggle').remove();

		function setMenuState(isOpen) {
			$body.toggleClass('wdeb_mobile_menu_open', isOpen);
			$sidebar.toggleClass('wdeb_menu_is_open', isOpen);
			$toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
			$backdrop.prop('hidden', !isOpen);

			if (isOpen) {
				returnFocus = document.activeElement;
				$close.get(0).focus({ preventScroll: true });
			} else if (returnFocus && returnFocus.isConnected && typeof returnFocus.focus === 'function') {
				returnFocus.focus({ preventScroll: true });
				returnFocus = null;
			}
		}

		$toggle.on('click', function () {
			setMenuState(!$body.hasClass('wdeb_mobile_menu_open'));
		});

		$close.add($backdrop).on('click', function () {
			setMenuState(false);
		});

		$sidebar.on('keydown', function (event) {
			if (event.key === 'Escape') {
				event.preventDefault();
				setMenuState(false);
				return;
			}

			if (event.key !== 'Tab' || !mobileQuery.matches) {
				return;
			}

			const $focusable = $sidebar
				.find('a:visible, button:visible, input:visible, select:visible, textarea:visible, [tabindex]:not([tabindex="-1"]):visible')
				.filter(':enabled');

			if (!$focusable.length) {
				return;
			}

			const first = $focusable.get(0);
			const last = $focusable.get($focusable.length - 1);

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});

		$('#menu a').on('click', function () {
			if (mobileQuery.matches) {
				setMenuState(false);
			}
		});

		$(document).on('keydown', function (event) {
			if (event.key === 'Escape' && $body.hasClass('wdeb_mobile_menu_open')) {
				setMenuState(false);
			}
		});

		function handleBreakpointChange(event) {
			if (!event.matches) {
				setMenuState(false);
			}
		}

		if (mobileQuery.addEventListener) {
			mobileQuery.addEventListener('change', handleBreakpointChange);
		} else {
			mobileQuery.addListener(handleBreakpointChange);
		}
	}

	$(function () {
		initMenu();
		initMobileMenu();

		// Notifications.
		$('.notification').css('cursor', 'pointer');

		$('.notification span').on('click', function () {
			$(this).closest('.notification').fadeOut(800);
		});

		// Select all checkboxes.
		$('.checkall').on('click', function () {
			$(this)
				.closest('table')
				.find("input[type='checkbox']")
				.prop('checked', this.checked);
		});

		// iPhone-style checkboxes.
		if ($.fn.iphoneStyle) {
			$('.iphone').iphoneStyle();
		}

		// Tooltips.
		if ($.fn.easyTooltip) {
			$('.tooltip').easyTooltip({
				xOffset: -60,
				yOffset: 50
			});
		}

		// Menu animation.
		$('#menu li:not(.current), #menu ul ul li a')
			.on('mouseenter', function () {
				$(this).find('span').stop(true, true).animate({
					paddingLeft: '12px'
				}, 100);
			})
			.on('mouseleave', function () {
				$(this).find('span').stop(true, true).animate({
					paddingLeft: '10px'
				}, 100);
			});

		// Fade effect.
		$('.fade_hover')
		.on('mouseenter', function () {
			$(this).stop(true, true).animate({
				opacity: 0.6
			}, 200);
		})
		.on('mouseleave', function () {
			$(this).stop(true, true).animate({
				opacity: 1
			}, 200);
		});

		// Portlets.
		$('.portlet')
			.addClass('ui-widget ui-widget-content ui-helper-clearfix ui-corner-all')
			.find('.portlet-header')
			.addClass('ui-widget-header ui-corner-all')
			.prepend('<span class="ui-icon ui-icon-circle-arrow-s"></span>');

		$('.portlet-header .ui-icon').on('click', function () {
			$(this).toggleClass('ui-icon-minusthick');
			$(this)
				.closest('.portlet')
				.find('.portlet-content')
				.toggle();
		});

		// Statistics.
		if ($.fn.visualize) {
			$('table.stats').each(function () {
				const $table = $(this);
				const classes = $table.attr('class') || '';
				const statsType = classes.replace(/^.*?\bstats\s+/, '') || 'area';

				$table.hide().visualize({
					type: statsType,
					width: '800px',
					height: '240px',
					colors: [
						'#6fb9e8',
						'#ec8526',
						'#9dc453',
						'#ddd74c'
					]
				});
			});
		}

	});

})(jQuery);
