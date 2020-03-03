(function($) {
    if ($('#ms-payment-expiry-countdown').length > 0 && $().countdown) {
        var finalDate = $('#ms-payment-expiry-countdown').data('date');
        $('#ms-payment-expiry-countdown').countdown(finalDate).on("update.countdown", function(event) {
            var totalHours = event.offset.totalDays * 24 + event.offset.hours;
            if ( totalHours != 0 ) {
  				$(this).html(event.strftime(
  					'<div>\
	  					<span class="time">'+totalHours+'</span>\
	  					<span class="label">Jam</span>\
  					</div>\
  					<div>\
	  					<span class="time">%M</span>\
	  					<span class="label">Menit</span>\
  					</div>\
  					<div>\
	  					<span class="time">%S</span>\
	  					<span class="label">Detik</span>\
  					</div>'
  				));
  			} else {
  				$(this).html(event.strftime(
  					'<div>\
	  					<span class="time">%M</span>\
	  					<span class="label">Menit</span>\
  					</div>\
  					<div>\
	  					<span class="time">%S</span>\
	  					<span class="label">Detik</span>\
  					</div>'
  				));
  			}
        });;
    }

    $('.ms-midtrans-core-payment-details ul.accordion > li').on('click',function(){
    	$(this).find('>ul,>ol').slideToggle('fast');
    })
})(jQuery);