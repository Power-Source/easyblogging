/*
 * 	Easy Tooltip 1.0
 */
 
(function($) {

	$.fn.easyTooltip = function(options) {

		var defaults = {
			xOffset: 10,
			yOffset: 25,
			tooltipId: "easyTooltip",
			clickRemove: false,
			content: "",
			useElement: ""
		};

		var options = $.extend(defaults, options);

		this.each(function() {
			var $me = $(this);
			var title = $me.attr("title");

			if (!title) {
				return true;
			}

			$me.on(
				'mouseenter',
				function(e) {
					var left = e.pageX + options.xOffset;

					$me.attr("title", "");

					if ($("#" + options.tooltipId).length) {
						$("#" + options.tooltipId).remove();
					}

					$("body").append(
						"<div id='" + options.tooltipId + "'>" + title + "</div>"
					);

					$("#" + options.tooltipId).css("position", "absolute");

					var height = $("#" + options.tooltipId).height();
					var top = e.pageY - options.yOffset;

					if (top - height <= $(window).scrollTop() + 28) {
						top = e.pageY + options.yOffset;
						$("#" + options.tooltipId).addClass("reverse");
					} else {
						top = top - height;
					}

					$("#" + options.tooltipId).css({
						top: top,
						left: left
					});
				}
			).on(
				'mouseleave',
				function() {
					$("#" + options.tooltipId).remove();
					$me.attr("title", title);
				}
			);
		});

	};

})(jQuery);