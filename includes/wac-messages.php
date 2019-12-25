<?php

/*
 Messages for pages
 sa_* for all start auction page
*/
		
$wacStrings = array(
	'sa_weblink' 	=> __('Add the full URL link of the listing of your property. In case of a Funda or Pararius URL, click on Import to automatically extract  information from the listing.','woo-auction'),
	'sa_title' 		=> __('Street name and house number','woo-auction'),
	'sa_city' 		=> __('The city in which the object is located','woo-auction'),
	'sa_obavfrom' 	=> __('The date from which the tenant can move in the object','woo-auction'),
	'sa_rental' 	=> __('The duration of the rental contract. If your option is not available, please indicate in additional information','woo-auction'),
	'sa_aucdatefor' => __('The date and time on which the auction starts and people can bid (the auction will be visible after it has been approved by TheNextBid)','woo-auction'),
	'sa_aucdateto' 	=> __("The date and time on which the auction will finish. We advise 9 o&#39;clock in the evening",'woo-auction'),
	'sa_startprice' => __('We advise 10% below the listing price to encourage bidding activity','woo-auction'),
	'sa_minprice' 	=> __('This means that you or the landlord want(s) a minimum price for the property that is higher than the start price. Otherwise the property will not be rented out. This information will be shown to bidders in the auction. This way bidders are informed that just any bid above the start price will not automatically be accepted.','woo-auction'),
	'sa_listprice' 	=> __('If there is no listing of the property (yet), enter a reasonable (monthly) rental price.','woo-auction'),
	'sa_tenantreq' 	=> __('Indicate all relevant requirements for the tenant (e.g. income)','woo-auction'),
	'sa_addinfo' 	=> __('Indicate all relevant additional information such as rental period, policies','woo-auction'),
	'sa_deposit'	=> __('Deposit in Euro','woo-auction'),
	'sa_comment'	=> __('If you have any specific request for the auction let us know in this comment section.','woo-auction'),

	'pd_autobid'	=> __('The amount of an auto bid is the maximum price you want to pay for the rental home. The advantage of an auto bid is that the maximum price has not been directly bid, but it automatically outbids the current highest bid and subsequently outbids any new bids with minimal increments until the highest bid passes the maximum amount of the submitted auto bid.','woo-auction'),
	'pd_comment_lbl'=> __('Add a comment to your bid','woo-auction'),
	'pd_comment_desc'=> __('Write down your conditions or preferences for your bid. E.g. deposit, transfer date','woo-auction'),
	'pd_minpriceinfo'=> __('The landlord wants a price that is higher than the start price of this auction','woo-auction'),
	
	'product_saved'	=> __('Auction succesfully submitted. We will check and approve the auction as soon as possible.','woo-auction'),
	'login_reg_title'=> __('Login required!','woo-auction'),
	'login_reg_msg'	=> sprintf(__("Please <a href='%s'>Login</a> or <a href='%s'>Register</a> if you want to participate in the auction",'woo-auction'),site_url().'/login',site_url().'/register'),

	//JS error strings
	'auctionform'	=> array(
		'validation'	=> array(
				'objavail'		=> __('Please select Object Available From','woo-auction'),
				'daterange'		=> __('Please select Auction Date Range','woo-auction'),
				'startprice'	=> __('Please select Start Price','woo-auction'),
				'listprice'		=> __('Please select Listing Price','woo-auction'),
				'rentalprice'	=> __('Select Rental Period','woo-auction'),
				'city'			=> __('City required','woo-auction'),
				'deposit'		=> __('Deposit price required','woo-auction'),
				'auctionrule'	=> __('Please accept auction rules','woo-auction'),
		)
	),

	//js file strings
	'jsstring'		=> array(
		'aucsearch'		=> __('Search for Auctions','woo-auction'),
		'aucsearching'	=> __('Searching...','woo-auction'),
		'choose'		=> __('Choose ...','woo-auction'),
		'yes'			=> __('Yes','woo-auction')
	),

	//datatable translations
	'datatable'		=> [
		'emptyTable'	=> __('No data available in table','woo-auction'),
		'info'			=> __('Showing _START_ to _END_ of _TOTAL_ entries','woo-auction'),
		'infoEmpty'		=> __('Showing 0 to 0 of 0 entries','woo-auction'),
		'infoFiltered'	=> __('(filtered from _MAX_ total entries)','woo-auction'),
		'infoPostFix'	=> __('','woo-auction'),
		'infoThousands'	=> __(',','woo-auction'),
		'lengthMenu'	=> __('Show _MENU_ entries','woo-auction'),
		'loadingRecords'=> __('Loading...','woo-auction'),
		'processing'	=> __('Processing...','woo-auction'),
		'search'		=> __('Search:','woo-auction'),
		'zeroRecords'	=> __('No matching records found','woo-auction'),
		'paginate'		=> [
				"first"		=> __('First','woo-auction'),
				"last"		=> __('Last','woo-auction'),
				"next"		=> __('Next','woo-auction'),
				"previous" 	=> __('Previous','woo-auction')
		],
		'aria'			=> [
			'sortAscending'	=> __(' activate to sort column ascending','woo-auction'),
			'sortDescending'=> __(' activate to sort column descending','woo-auction')
		]
	]

);