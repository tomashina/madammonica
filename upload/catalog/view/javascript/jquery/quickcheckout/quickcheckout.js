function moduleLoad(element, spinner) {
	if (spinner) {
		element.find('.quickcheckout-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-5x"></i></div>');
	} else {
		moduleLoaded(element, spinner);
		
		var width = element.width();
		var height = element.height();
		var margin = height / 2 - 30;
		
		if (height > 30) {
			html = '<div class="overlay" style="position:absolute;bottom:0;left:0;z-index:99999;background:none;width:' + width + 'px;height:' + height + 'px;text-align:center;"><i class="fa fa-spinner fa-spin fa-5x" style="margin-top:' + margin + 'px;"></i></div>';
			
			element.append(html);
			
			element.css({
				'opacity': '0.5',
				'position': 'relative'
			});
		}
	}
}

function moduleLoaded(element, spinner) {
	if (!spinner) {
		element.find('.overlay').remove();
		
		element.removeAttr('style');
	}
}

function disableCheckout() {
	var checkout = $('#quickcheckout-disable');
	var payment = $('#payment');

	// The payment form is rendered inside #quickcheckout-disable. A full-size
	// overlay also covered interactive payment widgets (Revolut card, Revolut
	// Pay and payment request buttons), leaving them grey and unclickable.
	// slideDown() is still at height 0 when disableCheckout() runs, so jQuery's
	// :visible check is false even though the payment form has just been added.
	if (payment.length && payment.children().length) {
		checkout.find('.disable-overlay').remove();
		checkout.css({
			'opacity': '1',
			'position': 'relative'
		});

		$('#login-box, #payment-address, #shipping-address, #shipping-method, #payment-method, #cart1, #voucher').css({
			'opacity': '0.5',
			'pointer-events': 'none'
		});

		$('#button-payment-method').button('reset').prop('disabled', true).hide();
		$('#button-payment-method').next('.fa-spinner').remove();
		$('#terms .notification-info').hide();

		return;
	}

	checkout.css('opacity', '0.5');
	
	var width = checkout.width();
	var height = checkout.height();

	html = '<div class="disable-overlay" style="position:absolute;top:0;left:0;z-index:99999;background:none;width:' + width + 'px;height:' + height + 'px;text-align:center;"></div>';
	
	checkout.css('position', 'relative').append(html);
}
