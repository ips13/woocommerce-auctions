<?php

/**
 * Parse two websites data (Pararius, Funda)
 */
class Woo_Auction_Parsesite{
	
	//fetch pararius site data
	function parariusSiteData($url){
		$htmlget  = $this->get_fcontent($url);
		$htmlData = $htmlget[0];

		$dom = new DOMDocument();
		@$dom->loadHTML($htmlData);
		$finder = new DomXPath($dom);
		
		//get heading
		$heading = $this->clean($finder->query("//h1/span[@itemprop='name']")->item(0)->nodeValue);
		
		//get city
		$city = $this->clean($finder->query("//li[@class='city']")->item(0)->nodeValue);
		
		//get Price, Image
		$price = $priceCurrency = '';
		foreach($dom->getElementsByTagName('meta') as $meta) {
			if($meta->getAttribute('property')=='og:image'){ 
				$metaImage = $meta->getAttribute('content');
			}
			elseif($meta->getAttribute('itemprop')=='price'){ 
				$price = $meta->getAttribute('content');
			}
			elseif($meta->getAttribute('itemprop')=='priceCurrency'){ 
				$priceCurrency = $meta->getAttribute('content');
			}
		}
		
		//get availability
		$available = $finder->query("//div[@id='details']/dl/dd[8]")->item(0)->nodeValue;
		$available = $this->parseDate($available);
		
		//get object availability from
		$availfrom = $finder->query("//div[@id='details']/dl/dd[6]")->item(0)->nodeValue;
		$availfrom = $this->parseDate($availfrom);
		
		$decodedContent = array(
			'url' 		=> @$url,
			'title'		=> @$heading,
			'city'		=> @$city,
			'image'		=> @$metaImage,
			'price'		=> @$price,
			'sprice'	=> $this->startPrice($price),
			'deposit'	=> false,
			'available'	=> @$available,
			'availfrom'	=> @$availfrom
		);
		
		return $decodedContent;
	}

	//fetch funda website data
	function fundaSiteData($url){
		$htmlget  = $this->get_fcontent($url);
		$htmlData = $htmlget[0];

		$dom = new DOMDocument();
		@$dom->loadHTML($htmlData);
		$finder = new DomXPath($dom);
		
		//get heading
		$heading = $this->clean($finder->query("//h1[@class='object-header__address']/text()")->item(0)->nodeValue);
		
		//get image
		foreach($dom->getElementsByTagName('meta') as $meta) {
			if($meta->getAttribute('itemprop')=='image'){ 
				$metaImage = $meta->getAttribute('content');
			}
		}
		
		//get city
		$city = $this->clean($finder->query("//li[@class='breadcrumb-listitem'][2]")->item(0)->nodeValue);
		
		//get Price, Deposit, Status
		$nodeList = $finder->query("//dl[@class='object-kenmerken-list']");
		foreach($nodeList as $node){
			$auctionPrice 	= $finder->query('dd[1]', $node)->item(0)->nodeValue;
			$auctionDeposit = $finder->query('dd[2]/dl/dd[1]', $node)->item(0)->nodeValue;
			$auctionStatus 	= $finder->query('dd[6]', $node)->item(0)->nodeValue;
			
			/* pre($auctionPrice);
			pre($auctionDeposit);
			pre($auctionStatus); */
			
			$price 		= $this->parsePrice($this->parseNum($auctionPrice));
			$deposit 	= $this->parsePrice($this->parseNum($auctionDeposit));
			$available 	= $this->parseAvailablity($auctionStatus);
			break;
		}
		
		$decodedContent = array(
			'url' 		=> @$url,
			'title'		=> @$heading,
			'city'		=> @$city,
			'image'		=> @$metaImage,
			'price'		=> @$price,
			'sprice'	=> $this->startPrice($price),
			'deposit'	=> @$deposit,
			'available'	=> @$available,
			'availfrom'	=> false
		);
		return $decodedContent;
	}

	//clean TEXT
	function clean($var){
		return trim(preg_replace('/\s+/', ' ', $var));
	}
	
	//parse DATE from string
	function parseDate($str){
		if($this->is_date($str)){
			return date('d-m-Y',strtotime($str));
		}
		return false;
	}
	
	//check if valid date
	function is_date($str){
		return (date('d-m-Y', strtotime($str)) == $str)? true : false;
	}
	
	//parse NUN from string
	function parseNum($str){
		preg_match('!\d+\.*\d*!', $str ,$match);
		return current($match);
	}
	
	//convert price of Dutch and USA
	function parsePrice($price){
		$parsedPrice = floatval(str_replace(',', '.', str_replace('.', '', $price)));
		return $parsedPrice;
	}
	
	//get start price 90% of listing price.
	function startPrice($p){
		$price = $p * '0.9';
		return ceil($price / 10) * 10;
	}
	
	//parse availability
	function parseAvailablity($str){
		$str = $this->clean($str);
		if(!empty($str)){
			if($str == 'Beschikbaar'){
				return 'Available';
			}
			else{
				return $this->parseDate($str);
			}
		}
		return '';
	}
	
	//get site content with curl
	function get_fcontent( $url,  $javascript_loop = 0, $timeout = 5 ) {
		$url = str_replace( "&amp;", "&", urldecode(trim($url)) );

		$cookie = tempnam ("/tmp", "CURLCOOKIE");
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		$content = curl_exec( $ch );
		$response = curl_getinfo( $ch );
		curl_close ( $ch );
		
		return array( $content, $response );
	}
}