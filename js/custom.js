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

	$(function () {

		// Fix the left navigation.
		$('#primary_left').css({
			position: 'fixed',
			top: 0
		});

		initMenu();

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

