(function($){
	
	//wac scroll to section
	$.fn.wacScroll = function(options){
		var $this = this;
		var settings = $.extend({
            offset: 100,
			speed: 'slow'
        }, options );
		
		$('html, body').animate({
			scrollTop: $this.offset().top - settings.offset
		}, settings.speed);
		
		return this;
	}
	
	//display notices, message or error
	$.fn.wacNotices = function(options){
		
		var $this = this;
		var settings = $.extend({
            msgs: ['No msg!'],
			type: 'error',
			scroll: true,
			hideafter: false
        }, options );
		
		// console.log(settings.msgs);
		
		switch(settings.type){
			case 'error':
				var msgclass = 'woocommerce-error';
				break;
			case 'success':
				var msgclass = 'woocommerce-message';
				break;
			default:
				var msgclass = 'woocommerce-info';
				break;
		}
		
		//remove existing errors
		$this.find('.woocommerce-error, .woocommerce-message').remove();
		
		//add new errors
		$this.prepend('<ul class="'+msgclass+'" tabindex="-1"></ul>');
		
		if($.isArray(settings.msgs)){
			$.each(settings.msgs,function(i,msg){
				$this.find('.'+msgclass).append('<li class="error-msg">'+msg+'</li>');
			});
		}
		else{
			$this.find('.'+msgclass).append('<li class="error-msg">'+settings.msgs+'</li>');
		}
		
		//scroll to element
		if(settings.scroll) 
			$this.wacScroll();
		
		//hideafter secs
		if(settings.hideafter) 
			setTimeout(function(){
				$('.woocommerce-error,.woocommerce-message').hide('slow');
			}, settings.hideafter);
		
		return this;
	}
	
	//capitalize first letter
	$.fn.capitalize = function () {
		var caps = $(this).val();
		// console.log(caps);
		caps = $.trim(caps);
		caps = caps.charAt(0).toUpperCase() + caps.slice(1);
		$(this).val(caps);
		return this;
	}
	
	//only for numerics values
	$.fn.ForceNumericOnly = function() {
		return this.each(function()
		{
			$(this).keydown(function(e)
			{
				var key = e.charCode || e.keyCode || 0;
				// allow backspace, tab, delete, enter, arrows, numbers and keypad numbers ONLY
				// home, end, period, and numpad decimal
				return (
					key == 8 || 
					key == 9 ||
					key == 13 ||
					key == 46 ||
					key == 110 ||
					key == 190 ||
					(key >= 35 && key <= 40) ||
					(key >= 48 && key <= 57) ||
					(key >= 96 && key <= 105));
			});
		});
	}
	
	//unique array
	function unique_array(arr){
		return $.grep(arr, function(v, k){
			return $.inArray(v ,arr) === k;
		});
	}
	
	//round price by 10
	function RoundPrice(price){
		return Math.ceil(price / 10) * 10;
	}
	
	//add spacing or enters in textarea
	function nl2br(str, is_xhtml){ 
		var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br/>' : '<br>';      
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');  
	}
	
	//check if user logged in
	function is_logged_in(){	
		return (wac_vars.logged_in) ? true : false;
	}
	
	//logged in modal display
	function not_loggedin_modal(){
		if(!is_logged_in()){
			$.fancybox(
				'<h2>'+wac_vars.not_login_title+'</h2><p>'+wac_vars.not_login_msg+'</p>',
				{
					'autoDimensions'	: false,
					'width'         	: 350
				}
			);
			return true;
		}
		return false;
	}
	
	//capitalize title
	$('#auctitle').on('change', function(){
		$(this).capitalize();
	});
	
	//force numeric values only in fields
	$("#auction-deposit, #_yith_auction_start_price, #_yith_auction_listing_price").ForceNumericOnly();
	
	//check on change start price
	//set start price minimum 80% of listing price.
	//set max start price 90% of listing price if above the listing price.
	$("#_yith_auction_start_price").on('change',function(){
		$startprice 	= $(this).val();
		$listprice 		= $("#_yith_auction_listing_price").val();
		if($listprice > 0){
			/*$percent80   = $listprice * 0.8;
			$percent90   = $listprice * 0.9;
			$listprice80 = Math.ceil($percent80 / 10) * 10;
			$listprice90 = Math.ceil($percent90 / 10) * 10;
			if($startprice < $listprice80){
				$(this).val($listprice80);
			}
			else if($startprice > $listprice90){
				$(this).val($listprice90);
			}*/
			if($startprice > $listprice){
				$(this).val($listprice);
			}
		}
	});
	
	//set start price according to listing price
	//set start price to 80% if lower than that.
	//set max start price 90% of listing price if above the listing price.
	$("#_yith_auction_listing_price").on('change',function(){
		$startInput = $('#_yith_auction_start_price');
		$listprice 	= $(this).val();
		$percent80 	= $listprice * 0.8;
		$percent90 	= $listprice * 0.9;
		$startPrice80 = Math.ceil($percent80 / 10) * 10;
		$startPrice90 = Math.ceil($percent90 / 10) * 10;
		// console.log(listprice,startPrice);
		if($startInput.val() > 0 && $startInput.val() < $percent80){
			$startInput.val($startPrice80);
		}
		else{
			$startInput.val($startPrice90);
		}
	});
	
	//add select2 on product cats
	if($.fn.select2){
		$('#product-cats').select2({
			placeholder: wac_vars.jsstring.choose,
			tags: true,
			maximumSelectionLength: 1
		});
	}
	
	//add datepicker
	if($.fn.datepicker){
		$('#available-from').datepicker({
			dateFormat: 'dd-mm-yy'
		});
	}

	$('.minprice-switch > label').click(function(){
		var $inputSwitch = $('.minprice-switch > input');
		$inputSwitch.attr('checked',!$inputSwitch.attr('checked'));
	});
	
	//start/end time range
	if($.fn.timepicker){
		var endDateTextBox 	 = $('#_yith_auction_to');
		var startDateTextBox = $('#_yith_auction_for');

		if(startDateTextBox.length > 0){
			
			$.timepicker.datetimeRange(
				startDateTextBox,
				endDateTextBox,
				{
					minInterval: (1000*60), // 1min
					dateFormat: 'dd-mm-yy',
					timeFormat: 'HH:00',
					minDate:	0,
					/* start: {
						onSelect: function (date) {
							var startDate 	= startDateTextBox.datetimepicker('getDate');
							var startTime  	= new Date(startDate).getHours();
							var addDays		= (startTime > 21)? 4 : 3;
							// console.log(startTime,addDays);
							var endDate = new Date(startDate.getTime() + (addDays*86400000));
							endDateTextBox.datetimepicker('option', 'minDate', endDate )
							endDateTextBox.datetimepicker('option', 'maxTime', '21:00' )
							endDateTextBox.datetimepicker('setDate', endDate )
						}
					}, // start picker options
					end: {minDate : 0} // end picker options */
				}
			);
		}
	}
	
	//remove first menu active on front page
	$('.home .navbar-default li:first').removeClass('active');
	
	//display search bar
	$('.display-search-bar').click(function(){
		// $('#navbar-ex-collapse').removeClass('in');
		$('.header-search-bar').toggle(); 
	});
	
	//fetch site data (Pararius, Funda)
	$('.weblink-fetch').click(function(e){
		e.preventDefault();
		
		//auction form element
		$auctionForm = $('#wac_auction_form');
		$auctionForm.find('.woocommerce-error, .woocommerce-message').remove();
		$auctionForm.block({message: null,overlayCSS: {background: '#fff',opacity: 0.6}});
		
		if($('#weblink').val() == ''){
			$auctionForm.wacNotices({msgs: ['Fill correct URL and then fetch!']});
			$auctionForm.unblock();
			return false;
		}
		
		var data = {
			action: 'woo_auction_weblink_title',
			geturl: $('#weblink').val()
		}
		$.post(woocommerce_params.ajax_url, data, function(res) {
			// console.log(res);
			$auctionForm.unblock();
			
			if(res.success){
				var auction = res.data;
				$('#auctitle').val(auction.title).trigger('change');
				$('#_yith_auction_start_price').val(auction.sprice);
				$('#_yith_auction_listing_price').val(auction.price);
				$('#featured_img').val(auction.image);
				
				//display featured image
				$('#featured_img_button').hide();
				$('#featured_img_display').attr('src',auction.image).show();
				
				$product_cats = $('#product-cats');
				if($product_cats.find("option:contains('"+auction.city+"')").length > 0){
					var cityVal = $product_cats.find("option:contains('"+auction.city+"')").val();
					$product_cats.val(cityVal).trigger('change');
				}
				else{
					$product_cats.append('<option>'+auction.city+'</option>').select2('destroy');
					$product_cats.val(auction.city).select2();
				}
				
				if(auction.deposit){
					$('#auction-deposit').val(auction.deposit);
				}
				
				if(auction.availfrom){
					$('#available-from').val(auction.availfrom);
				}
				
				$auctionForm.append('<input type="hidden" name="fromsite" value="'+res.domain+'">');
			}
			else{
				$auctionForm.wacNotices({msgs: res.msg});
			}
		},'json');
		return false;
	});

	//upload image directly with ajax
	$('.upload-direct').click(function(e){
		e.preventDefault();
		
		var siteImages = [
			'https://media.pararius.nl/AmsterdamJekerstraat-95a7_1.jpg'
		];
		
		var data = {
			action: 'woo_auction_save_images',
			images: siteImages
		}
		$.post(woocommerce_params.ajax_url, data, function(res) {
			console.log(res);
			if(res.success){
				
			}
			else{
				$('#wac_auction_form').wacNotices({msgs: res.msg});
			}
		},'json');
		
		return false;
	});
	
	//validate start auction form
	function validate_auction_form(){
		var errMsgs = [];
		var hasError = false;
		var auctvalidate = wac_vars.auctvalidate;

		if($('#available-from').val() == ''){
			errMsgs.push(auctvalidate.objavail);
			hasError = true;
		}
		if($('#_yith_auction_for').val() == ''){
			errMsgs.push(auctvalidate.daterange);
			hasError = true;
		}
		if($('#_yith_auction_start_price').val() == ''){
			errMsgs.push(auctvalidate.startprice);
			hasError = true;
		}
		if($('#_yith_auction_listing_price').val() == ''){
			errMsgs.push(auctvalidate.listprice);
			hasError = true;
		}
		if($('#rental-period').val() == ''){
			errMsgs.push(auctvalidate.rentalprice);
			hasError = true;
		}
		if($('#product-cats').val() == '' || $('#product-cats').val() == null){
			errMsgs.push(auctvalidate.city);
			hasError = true;
		}
		if($('#auction-deposit').val() == ''){
			errMsgs.push(auctvalidate.deposit);
			hasError = true;
		}
		if(!$('.agreement').is(':checked')){
			errMsgs.push(auctvalidate.auctionrule);
			hasError = true;
		}
		
		if(hasError){
			$('#wac_auction_form').wacNotices({msgs: errMsgs});
			return false;
		}
		return true;
	}
	
	//show preview popup
	$('#check-auction').click(function(e){
		e.preventDefault();
		
		$is_valid = validate_auction_form();
		if($is_valid) {
			previewpagevalues();
			$('#preview-auction').modal('show');
		}
		
		return false;
	})
	
	//prefilled preview page data
	var prevtimer_widget;
	function previewpagevalues(){
		var strVal = "";
		var webLink  = $('#weblink').val();
		var aucTitle = $("#auctitle").val();
		var aucStartPrice = $("#_yith_auction_start_price").val();
		var aucDeposit = $("#auction-deposit").val();
		var aucListPrice = $("#_yith_auction_listing_price").val();
		var aucAddInfo = nl2br($("#additiondesc").val());
		var aucTenantInfo = nl2br($("#tenant-requirement").val());
		var imgSrc = $("#featured_img_display").attr('src');
		var availfrom = $('#available-from').datepicker('getDate');
		var aucAvailable = $('#_yith_auction_for').datepicker('getDate');
		var newDt = $.datepicker.formatDate("dd/mm/yy", availfrom);
		var rentalPeriod = $('#rental-period option:selected').val();
		var rentalPeriodTxt = $('#rental-period option:selected').text();
		var auctListedBy = $('#listed_by').val();

		/* Timer Script */						
			var date_now = Date.now()/1000;
			var date_start = parseInt(aucAvailable/1000);
			var result_widget = date_start - date_now;
			
			// console.log(date_now,date_start,result_widget);
			
			clearInterval(prevtimer_widget);
			
			$('.count-time,.static-time').addClass('hidden');
			
			if(result_widget > 0){
				$('#preview-auction .count-time').removeClass('hidden');
				prevtimer_widget = setInterval(function() {
					prevTimer(result_widget);
					result_widget--
				}, 1000);
			}
			else{
				$('#preview-auction .static-time').removeClass('hidden');
			}
		/* END */
		
		$('.select2-selection__choice').each(function(){
			strVal += $.trim($(this).attr('title')) + ",";
		})
		var lastChar = strVal.slice(-1);
		if (lastChar == ',') {
			strVal = strVal.slice(0, -1);
		}
		$('.keyinfo-list-cities').text(strVal);
		$('#prevTitle').text(aucTitle);
		$('.preview-Price-amount').text(aucStartPrice);		
		$('.preview-Price-deposit').text(aucDeposit);		
		$('.preview-Price-listing').text(aucListPrice);		
		$('.keyinfo-list-available').text(newDt);

		//check if minimum price added or not
		if($('.minprice-switch > input').is(':checked')){
			$('.kil-minprice').show();
		}
		else{
			$('.kil-minprice').hide();
		}

		//check if current language is NL
		if(typeof wpml_browser_redirect_params != 'undefined' && wpml_browser_redirect_params.pageLanguage == 'nl'){
			$('.keyinfo-list-rental').text(rentalPeriodTxt);
		}
		else{
			$('.keyinfo-list-rental').text(rentalPeriod);		
		}
		$('.keyinfo-list-addinfo').html(aucAddInfo);		
		$('.keyinfo-list-tenantingo').html(aucTenantInfo);		
		$('.keyinfo-list-user').text(auctListedBy);		
		$('#prevImg img').attr('src', imgSrc);
		
		//display only when has link
		if($.trim(webLink).length > 0){
			$('.externl-lnk').removeClass('hidden').attr('href',webLink);
		}
	}
	
	//preview timer in preview popup
	function prevTimer(result) {

        if (result <= 0) {
            // Timer done
            clearInterval(prevtimer_widget);
        } else {
            var seconds = Math.floor(result);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);
			
            hours %= 24;
            minutes %= 60;
            seconds %= 60;

			$('#preview-auction #days').text(days);
			$('#preview-auction #hours').text(hours);
			$('#preview-auction #minutes').text(minutes);
			$('#preview-auction #seconds').text(seconds);
        }
    }
	
	//save auction data
	$('#woo-auction-submit').click(function(e) {
		e.preventDefault();
		
		//close popup on submit click
		$('#preview-auction').modal('hide');
		
		// return console.log($('#wac_auction_form').serialize());
		  
		// Validations
		// $is_valid = product_manager_form_validate();
		$is_valid = validate_auction_form();
		// $is_valid = true;
		$auctionForm = $('#wac_auction_form');
		  
		if($is_valid) {
			
			//close preview
			$('#preview-auction').modal('hide');
			
			$auctionForm.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			var description = $('#additiondesc').val();
			var data = {
				action : 'woo_auction_add_new',
				product_manager_form : $auctionForm.serialize(),
				description : description,
				status : 'draft'
			}	
			$.post(woocommerce_params.ajax_url, data, function(response) {
				// console.log(response);
				if(response) {
					$response_json = response;
					if($response_json.status) {
						
						//type success
						$auctionForm.wacNotices({msgs: $response_json.message,type:'success'});
						
						//remove image after added
						$('#featured_img_button').show();
						$('#featured_img_remove_button').hide();
						$('#featured_img_display').attr('src','').hide();
						
						$auctionForm[0].reset();
						$("#product-cats").val('').trigger('change');
						// window.location = $response_json.redirect;
					} else {
						$auctionForm.wacNotices({msgs: $response_json.message});
					}
					if($response_json.id) $('#pro_id').val($response_json.id);
					$auctionForm.unblock();
				}
				// $('#wac_auction_form').unblock();
			},'json');	
		}
	});
	
	var timer_widgets = [];
	//time auction on product listing page.
	$(document.body).on('wac_timer',function () {
		$('.time-auction').each(function(index){
			var $this 	= $(this);
			var product = parseInt($(this).data('yith-product'));
			var date_now = Date.now()/1000;
			var date_finnish = parseInt($(this).data('finish-time'));
			var result_widget = date_finnish - date_now;
			// console.log(date_finnish);

			timer_widgets[index] = setInterval(function() {
				if(result_widget <= 0){
					$this.parent().html('<label for="yith_time_left">Closed</label>');
				}
				remainingTimer(result_widget,product,index);
				result_widget--
			}, 1000);
			
		});
	});
	$(document.body).trigger('wac_timer');
	
	
	//remaining time
	function remainingTimer(result,product,index) {
        if (result <= 0) {
            // Timer done
            clearInterval(index);
        } else {

            var seconds = Math.floor(result);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);

            hours %= 24;
            minutes %= 60;
            seconds %= 60;

			// console.log(product,days,hours,minutes,seconds);
			
            $( '.yith-date-auct-'+product+' > .yith-days' ).text(days +'d');
            $( '.yith-date-auct-'+product+' > .yith-hours' ).text(hours +'h');
            $( '.yith-date-auct-'+product+' > .yith-mins' ).text(minutes +'m');
            $( '.yith-date-auct-'+product+' > .yith-secs' ).text(seconds +'s');

        }
    }
	
	/**Product Page **/
	$("#_actual_bid").ForceNumericOnly();
	var currentBidPrice = $("#yith-wcact-form-bid").parent().data('current');
	if(currentBidPrice > 0){
		$newRoundPrice = (currentBidPrice % 10 == 0)? currentBidPrice + 10 : currentBidPrice;
		$("#_actual_bid").val($newRoundPrice);
	}
	
	//validate bid price in auction detail page
	function validatebid_price(){
		var current 	= $('#time').data('current');
		var actual_bid 	= $('#_actual_bid').val();
		var appendIn 	= $('div.product-type-auction').parent();
		var roundval	= RoundPrice(actual_bid);
		
		//check if bid is not rounded by 10
		if((actual_bid != roundval) && (actual_bid != listingprice)){
			$(appendIn).wacNotices({msgs: 'Please enter a Bid that is rounded to a multiplier of 10 (e.g. '+roundval+' instead of '+actual_bid+')'});
			return false;
		}
		
		//check if not a valid number
		if(actual_bid == ''){
			$(appendIn).wacNotices({msgs: 'Bid price is not correct!'});
			return false;
		}
		//check max bid placed per user
		else if(bidplaced !== undefined && (bidplaced >= allowedbids)){
			$(appendIn).wacNotices({msgs: 'You can only bid on 3 Live Auctions at the same time.'});
			return false;
		}
		//check bid price not more than 50% of listing price
		else if(listingprice !== undefined){
			//listing price 50% check
			$bidpricemax = listingprice*1.5;
			// console.log(actual_bid,$bidpricemax);
			if(actual_bid && actual_bid > $bidpricemax){
				$('#_actual_bid').val(current);
				$(appendIn).wacNotices({msgs: 'Bid more than 50% of listing price is not allowed!'});
				return false;
			}
			else{
				// console.log(actual_bid);
				return true;
			}
		}
		
		return true;
	}
	
	//remove click and add new one
	$(".bid").addClass('wacbid').removeClass('bid');
	
    $(".wacbid").click(function(e){
        e.preventDefault();
		
		var current = $('#time').data('current');
        var actual_bid = $('#_actual_bid').val();
		var crstart_val = actual_bid;
		
        if($(this).hasClass("button_bid_add")){
            if(!actual_bid){
                actual_bid = current;
            }
			$round_bid  = RoundPrice(actual_bid);
            actual_bid  = ($round_bid > actual_bid)? $round_bid : parseInt(actual_bid) + 10;
			
			//if round of listing price is same the current entered value then listing price will be used.
			actual_bid = (actual_bid == RoundPrice(listingprice) && crstart_val != listingprice)? listingprice : actual_bid;
				
            $('#_actual_bid').val(actual_bid);
        } else {
            if(actual_bid){
				$round_bid  = RoundPrice(parseInt(actual_bid));
				actual_bid  = $round_bid - 10;
                
				//if round of listing price is same the current entered value then listing price will be used.
				actual_bid = ($round_bid == RoundPrice(listingprice) && crstart_val != listingprice)? listingprice : actual_bid;
			
                if (actual_bid >= startprice){
                    $('#_actual_bid').val(actual_bid);
                }else{
                    $('#_actual_bid').val(startprice);
                }
            }
        }
    });
	
	//auto bid on click
	$('.wac-autobid').on('click',function(e){
		e.preventDefault();
	
		//display not logged in modal
		if(not_loggedin_modal()){
			return false;
		}
		
		//validate actual bid price
		if(!validatebid_price()){
			return false;
		}
		
		var form = $( this ).closest( '.cart' );
		var bidamount = form.find( '#_actual_bid').val();
		if(bidamount != ''){
			var data = {
				bid		: form.find( '#_actual_bid').val(),
				bidtype	: 'autobid',
				action	: 'woo_auction_autobid',
				product : form.find('#time').data('product'),
				comment : form.find('#comments').val(),
			}
			
			$('#yith-wcact-form-bid').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
			$.post(woocommerce_params.ajax_url, data, function(res) {
				 $('#yith-wcact-form-bid').unblock();
				 // console.log(res);
				 window.location = res.url;
			},'json');
		}
		else{
			alert('Enter amount!');
		}
		return false;
	});
	
	//add bid when click on bid button
	$(window).load(function(){
		$( document ).off( 'click', '.auction_bid' ).on( 'click', '.auction_bid', function( e ) {
		
			//display not logged in modal
			if(not_loggedin_modal()){
				return false;
			}
		
			//validate actual bid price
			if(!validatebid_price()){
				return false;
			}
			
			$('#yith-wcact-form-bid').block({message:null, overlayCSS:{background:"#fff",opacity:.6}});
			var form = $( this ).closest( '.cart' );
			var post_data = {
				  'bid': form.find( '#_actual_bid').val(),
				  'product' : form.find('#time').data('product'),
				  security: object.add_bid,
				  action: 'yith_wcact_add_bid',
				  comment : form.find('#comments').val()
			  };

			$.ajax({
			   type    : "POST",
			   data    : post_data,
			   url     : wac_vars.ajaxurl,
			   success : function ( response ) {
					//console.log(response.url);
				   $('#yith-wcact-form-bid').unblock();
					 window.location = response.url;

					 //window.location.reload(true);
				   // On Success
			   },
			   complete: function () {
			   }
			});
		});
	});
	
	/**Product Page end **/
	
	//save vendor profile
	$('#save-vendor-profile').click(function(e){
		e.preventDefault();
		
		// return console.log($('#wac-shop-settings-form').serialize());
		
		var $settingForm = $('#wac-shop-settings-form');
		var data = {
			action: 'woo_auction_save_vendor_profile',
			posted: $settingForm.serialize()
		}
		
		//remove error of success message on click
		$('.woocommerce-error,.woocommerce-message').remove();
		
		//display loading
		$settingForm.block({message: null,overlayCSS: {background: '#fff',opacity: 0.6}});
		
		$.post(woocommerce_params.ajax_url, data, function(res) {
			// console.log(res);
			$settingForm.unblock();
			
			if(res.success){
				$('.wac-vendorprofile .wcmp_top_logo_div h3').text($settingForm.find('#vcompany').val());
				$settingForm.find('.pswdfield').val('');
				$settingForm.wacNotices({msgs: res.msg, type: 'success'});
			}
			else{
				$settingForm.wacNotices({msgs: res.msg, hideafter: 5000});
			}
			
		},'json');
		
		return false;
	});
	
	//add popup on agreelink
	$(".agreelink, .howtobid").fancybox({
		width: 600,
		height: 450,
		autoSize: false
	});
	
	//add class on product listing homepage
	var no_ofprod = $('#work ul.products > li').size();
	if(no_ofprod <=2){
		if(no_ofprod == 1){
			$('#work ul.products > li').addClass('onlyone');
		}
		$('#work ul.products').addClass('middle');
	}
	
	//auction search
	$(".auction-search").select2({
		ajax: {
			url: woocommerce_params.ajax_url,
			dataType: 'json',
			type: 'POST',
			//delay: 250,
			data: function (params) {
				return {
					search: params.term, // search term
					action: 'woo_auction_search'
				};
			},
			processResults: function (data, params) {
				// scrolling can be used
				params.page = params.page || 1;

				return {
					results: data.items,
					pagination: {
						more: (params.page * 10) < data.total_count
					}
				};
			},
			cache: true
		},
		placeholder: wac_vars.jsstring.aucsearch,
		escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
		minimumInputLength: 1,
		templateResult: formatRepo,
		templateSelection: formatRepoSelection,
		language: { errorLoading:function(){ return wac_vars.jsstring.aucsearching }, inputTooShort: function () { return ''; }}
	});

	function formatRepo (auct) {
		if (auct.loading) {
			return auct.text;
		}

		var markup = "<div class='select2-result-auction clearfix'>" +
			"<div class='select2-result-auction__avatar'><img src='" + auct.image + "' /></div>" +
			"<div class='select2-result-auction__title'>" + auct.title + "</div>";

		markup += "</div>";

		return markup;
	}

	function formatRepoSelection(auct) {
		if(auct.link){
			window.location = auct.link;
		}
		return auct.title || auct.text;
	}
	
	//allow only numbers and letters
	$(document).on('keydown','.select2-search__field',function (e) {
		// console.log('dfsfd');
		if (e.shiftKey || e.ctrlKey || e.altKey) {
			e.preventDefault();
		}
		else{
			var key = e.keyCode;
			if (!((key == 8) || (key == 32) || (key == 46) || (key >= 35 && key <= 40) || (key >= 65 && key <= 90) || (key >= 48 && key <= 57) || (key >= 96 && key <= 105))) {
				e.preventDefault();
			}
		}
	});
	
	//remove button on profile page
	$('.remove_button').on('click',function(){
		setTimeout(function(){
			$('#vendor_image_display').attr('src',wac_vars.default_avatar);
		},10)
	});

	//check if dashboard page.
	function is_dashboard_page(){
		if($('#vendor-dashboard-auctions').size() > 0){
			return true;
		}
		return false;
	}
	
	//refresh or ajax hit after some interval of time
	if ( wac_vars.live_auction_product_page  > 0 ) {
		setInterval(updatetimeleft,wac_vars.live_auction_product_page);

		//check if dashboard page and update statuses
		if(is_dashboard_page()){
			setInterval(setAuctionStatus,wac_vars.live_auction_product_page);
		}
	}
	
	//update timeleft everywhere.
	function updatetimeleft(){
		var liveAuctions = [];
		$('.wac_date_auction').each(function(){
			var productId = $(this).data('yith-product');
			if(productId){
				liveAuctions.push(productId);
			}
		});
		
		if(liveAuctions.length > 0){
			liveAuctions = unique_array(liveAuctions);
			
			$.ajax({
				type    : "POST",
				data    : {action: 'woo_updatetimeleft', products: liveAuctions},
				url     : wac_vars.ajaxurl,
				success : function ( response ) {
					
					// clear all countdown intervals
					$.each(timer_widgets, function(index, val){
						clearInterval(val);
					});
					
					$timeleftHtml = $.parseJSON(response);
					$.each($timeleftHtml, function(aucid, auctdata){
						var yithauct_div = $('.yith-date-auct-'+aucid).parent();
						yithauct_div.html(auctdata.timeleft);

						//skip for vendor dashboard pages.
						if(is_dashboard_page()){
							if(auctdata.closed){
								$('.wac-status-'+aucid).find('a').text('Closed');
							}
							return;
						}
						yithauct_div.parent().find('.woocommerce-Price-amount.amount').replaceWith(auctdata.price);
					});
					$(document.body).trigger('wac_timer');
				}
			});
		}
	}

	//update timeleft everywhere.
	function setAuctionStatus(){
		var updateauctions = [];
		$('.wac-status').each(function(){
			var status 	= $(this).data('status');
			var productId = $(this).data('auction');
			//skip closed records
			if(productId){
				updateauctions.push(productId);
			}
		});
		
		if(updateauctions.length > 0){
			updateauctions = unique_array(updateauctions);
			
			$.ajax({
				type    : "POST",
				data    : {action: 'woo_updatestatus', products: updateauctions},
				url     : wac_vars.ajaxurl,
				success : function ( response ) {
					$statusHtml = $.parseJSON(response);
					$.each($statusHtml, function(aucid, auctstatus){
						$(".wac-status[data-auction='"+ aucid +"']").html(auctstatus);
					});
				}
			});
		}
	}
	
	//direct update time with hit this function
	window.updatetimeleft = function(){
		updatetimeleft();
	}
	window.setAuctionStatus = function(){
		setAuctionStatus();
	}
	
})(jQuery);