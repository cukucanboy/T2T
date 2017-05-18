<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Advertising extends MX_Controller {
		private $validlang;
		function __construct() {
// $this->session->sess_destroy();
				parent :: __construct();
				$chk = modules :: run('Home/is_main_module_enabled', 'advertising');
				if (!$chk) {
					Module_404();
				}

				$this->load->library("Advertising_lib");
				$this->load->model("Advertising_model");
				$this->load->helper("Advertising_front");

				$this->data['usersession'] = $userid = $this->session->userdata('pt_logged_customer');
				$this->data['modulelib'] = $this->Advertising_lib;
				$this->data['appModule'] = "Advertising";

				$this->data['phone'] = $this->load->get_var('phone');
				$this->data['contactemail'] = $this->load->get_var('contactemail');

                $languageid = $this->uri->segment(2);
                $this->validlang = pt_isValid_language($languageid);

                if($this->validlang){
                  $this->data['lang_set'] =  $languageid;
                }else{
                  $this->data['lang_set'] = $this->session->userdata('set_lang');
                }

                $defaultlang = pt_get_default_language();
				if (empty ($this->data['lang_set'])){
						$this->data['lang_set'] = $defaultlang;
				}


                $this->Advertising_lib->set_lang($this->data['lang_set']);
                $this->data['locationsList'] = $this->Advertising_lib->getLocationsList();

                $this->data['destinationList'] = $this->Advertising_lib->getDestinationList();


                $this->data['selectedAdults'] = $this->Advertising_lib->adults;
                $this->data['selectedChild'] = $this->Advertising_lib->child;
                $this->data['selectedInfants'] = $this->Advertising_lib->infants;

		}

		public function index() {

				$settings = $this->Settings_model->get_front_settings('Advertising');
				$this->data['minprice'] = $settings[0]->front_search_min_price;
				$this->data['maxprice'] = $settings[0]->front_search_max_price;
				$this->data['loadMap'] = TRUE;

                if($this->validlang){
                	//$countryName = $this->uri->segment(3);
					//$cityName = $this->uri->segment(4);
                	$advertisinglug = $this->uri->segment(5);
                }else{
                	// $countryName = $this->uri->segment(2);
                   // $cityName = $this->uri->segment(3);
                    $advertisinglug = $this->uri->segment(4);
                }

				$check = $this->Advertising_model->advertising_exists($advertisinglug);

				if ($check && !empty ($advertisinglug)){
					$this->lang->load("front", $this->data['lang_set']);

						$this->Advertising_lib->set_advertisingid($advertisinglug);
						$this->data['module'] = $this->Advertising_lib->advertising_details();
						$this->data['moduleTypes'] = $this->Advertising_lib->advertisingTypes();




						 if (pt_is_module_enabled('reviews')) {
								$this->data['reviews'] = $this->Advertising_lib->advertisingReviews($this->data['module']->id);
								$this->data['avgReviews'] = $this->Advertising_lib->advertisingReviewsAvg($this->data['module']->id);
						}

						$this->data['checkin'] = $this->Advertising_lib->date;
						$this->data['adults'] = $this->Advertising_lib->adults;
						$this->data['child'] = (int) $this->Advertising_lib->child;

						$this->data['checkinMonth'] = strtoupper(date("F",convert_to_unix($this->Advertising_lib->date)));
						$this->data['checkinDay'] = date("d",convert_to_unix($this->Advertising_lib->date));
						//$this->data['checkoutMonth'] = strtoupper(date("F",convert_to_unix($this->Advertising_lib->checkout)));
						//$this->data['checkoutDay'] = date("d",convert_to_unix($this->Advertising_lib->checkout));

                        $this->data['langurl'] = base_url()."advertising/{langid}/".$this->data['module']->slug;
                $this->setMetaData($this->data['module']->title,$this->data['module']->metadesc,$this->data['module']->keywords);
                        $this->theme->view('details', $this->data, $this);
				}
				else {
						$this->listing();
				}
		}

		function listing($offset = null) {
			$this->lang->load("front", $this->data['lang_set']);
				$settings = $this->Settings_model->get_front_settings('advertising');
				$this->data['moduleTypes'] = $this->Advertising_lib->advertisingTypes();
				$alladvertising = $this->Advertising_lib->show_advertising($offset);
				$this->data['module'] = $alladvertising['all_advertising'];
				$this->data['info'] = $alladvertising['paginationinfo'];
				$this->data['checkin'] = $this->Advertising_lib->date;
				$this->data['adults'] = $this->Advertising_lib->adults;
                $this->data['child'] = (int) $this->Advertising_lib->child;

				$this->data['minprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_min_price);
				$this->data['maxprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_max_price);
				$this->data['currCode'] = $this->Advertising_lib->currencycode;
				$this->data['currSign'] = $this->Advertising_lib->currencysign;
                $this->data['langurl'] = base_url()."advertising/{langid}/";
                $this->setMetaData($settings[0]->header_title,$settings[0]->meta_description,$settings[0]->meta_keywords);
                $this->theme->view('listing', $this->data, $this);
		}

		function search($country = null, $city = null, $citycode = null,$offset = null) {
				$checkout = $this->input->get('checkout');
				$this->data['adults'] = (int) $this->input->get('adults');
				$this->data['child'] = (int) $this->input->get('child');
				$this->data['checkin'] = $this->input->get('date');
				//$country = $this->input->get('country');
				//$state = $this->input->get('state');
				$type = $this->input->get('type');
				$cityid = $this->input->get('searching');
				$modType = $this->input->get('modType');

				if(empty($country)){
					$surl = http_build_query($_GET);
	    $locationInfo = pt_LocationsInfo($cityid);
					$country = url_title($locationInfo->country, 'dash', true);
					$city = url_title($locationInfo->city, 'dash', true);
					$cityid = $locationInfo->id;
					if(!empty($cityid) && $modType == "location"){
						redirect('advertising/search/'.$country.'/'.$city.'/'.$cityid.'?'.$surl);
					}else if(!empty($cityid) && $modType == "advertising"){
						$this->Advertising_lib->set_id($cityid);
						$this->Advertising_lib->advertising_short_details();
						$title = $this->Advertising_lib->title;
						$slug = $this->Advertising_lib->slug;
						if(!empty($title)){
							redirect('advertising/'.$slug);
						}

					}


				}else{

					if($modType == "location"){
					$cityid = $citycode;
					}else{
					$cityid = "";
					}

					if(is_numeric($country)){
						$offset = $country;
					}

				}

				if (array_filter($_GET)) {

						$alladvertising = $this->Advertising_lib->search_advertising($cityid, $offset);

						$this->data['module'] = $alladvertising['all_advertising'];
			      $this->data['info'] = $alladvertising['paginationinfo'];
				}
				else {
						$this->data['module'] = array();
				}

				$this->lang->load("front", $this->data['lang_set']);
				$this->data['city'] = $cityid;


				$this->data['selectedLocation'] = $cityid;//$this->advertising_lib->selectedLocation;
				$this->data['checkin'] = $this->Advertising_lib->date;
				$this->data['adults'] = $this->Advertising_lib->adults;
        $this->data['child'] = (int) $this->Advertising_lib->child;
				$this->data['selectedAdvertisingType'] = $this->Advertising_lib->selectedAdvertisingType;
				$this->data['moduleTypes'] = $this->Advertising_lib->advertisingTypes();
				$settings = $this->Settings_model->get_front_settings('advertising');
				$this->data['searchText'] = $this->input->get('txtSearch');
				$this->data['minprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_min_price);
				$this->data['maxprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_max_price);
				$this->data['currCode'] = $this->Advertising_lib->currencycode;
				$this->data['currSign'] = $this->Advertising_lib->currencysign;
				$this->setMetaData('Search Results');
				$this->theme->view('listing', $this->data, $this);

		}

		function book($advertisinglug) {
			$this->load->model('Admin/Countries_model');

			$this->data['allcountries'] = $this->Countries_model->get_all_countries();

			$check = $this->Advertising_model->advertising_exists($advertisinglug);
				$this->load->library("Paymentgateways");
				$this->data['hideHeader'] = "1";
				//echo "<pre>";
				//print_r($this->Paymentgateways->getAllGateways());
				//echo "</pre>";
  				if ($check && !empty($advertisinglug)) {
  					$this->lang->load("front", $this->data['lang_set']);
  				  	$this->load->model('Admin/Payments_model');
                      $this->data['error'] = "";
                      $this->Advertising_lib->set_advertisingid($advertisinglug);
                      $advertisingID = $this->Advertising_lib->get_id();
                      $bookInfo = $this->Advertising_lib->getBookResultObject($advertisingID);
                      $this->data['module'] = $bookInfo['advertising'];
                      $this->data['extraChkUrl'] = $bookInfo['advertising']->extraChkUrl;
                      $this->data['totalGuests'] = $this->Advertising_lib->guestCount;


                     /* if($this->data['room']->price < 1 ||  $this->data['room']->stay < 1){
                        $this->data['error'] = "error";
                      }*/

                    //  $this->data['paymentTypes'] = $this->Payments_model->get_all_payments_front();
                      $this->load->model('Admin/Accounts_model');
                      $loggedin = $this->loggedin = $this->session->userdata('pt_logged_customer');
                      $this->data['profile'] = $this->Accounts_model->get_profile_details($loggedin);
                      $this->setMetaData($this->data['module']->title,$this->data['module']->metadesc,$this->data['module']->keywords);
					  $this->theme->view('booking', $this->data, $this);
				}else{
                   redirect("advertising");
				}
		}

		function txtsearch() {

		}

		function featuredAdvertising($locid = null){
			if(empty($locid)){
			$fadvertising = $this->Advertising_lib->getFeaturedAdvertising();
			}else{
			$fadvertising = $this->Advertising_lib->getLocationBasedFeaturedAdvertising($locid);
			}

			echo json_encode($fadvertising);
		}

		function advertisingmap($advertisingid) {
				if (!empty ($advertisingid)) {
						//$starting = pt_advertising_start_end_map($advertisingid, 'start');
						//$ending = pt_advertising_start_end_map($advertisingid, 'end');
						//$visiting = pt_advertising_visiting_map($advertisingid);
						$locationsData = $this->Advertising_lib->advertisingLocations($advertisingid);
						//print_r($locationsData->locations); exit;
						$locations = $locationsData->locations;

						$this->load->library('mapbuilder');
						$map = $this->mapbuilder;
						$map->setScrollwheel(FALSE);
						$map->_setBounds = TRUE;
						$map->setCenter($locations[0]->lat, $locations[0]->long);
						$map->setMapTypeId('ROADMAP');
						$map->setSize('100%', '100%');
						$map->setApiKey($this->data['app_settings'][0]->mapApi);
						$latDiff = $locations[count($locations) - 1]->lat - $locations[0]->lat;
						$longDiff = $locations[count($locations) - 1]->long - $locations[0]->long;
						$maxDiff = max(array($latDiff,$longDiff));
						//echo "Lat: ".$latDiff."----";
						//echo "Long: ".$longDiff."----";
						//echo $maxDiff; exit;

	if($maxDiff >= 0 && $maxDiff <= 0.0037)  //zoom 17
		$map->setZoom(14);
	else if($maxDiff > 0.0037 && $maxDiff <= 0.0070)  //zoom 16
		$map->setZoom(13);
	else if($maxDiff > 0.0070 && $maxDiff <= 0.0130)  //zoom 15
		$map->setZoom(12);
	else if($maxDiff > 0.0130 && $maxDiff <= 0.0290)  //zoom 14
		$map->setZoom(13);
	else if($maxDiff > 0.0290 && $maxDiff <= 0.0550)  //zoom 13
		$map->setZoom(10);
	else if($maxDiff > 0.0550 && $maxDiff <= 0.1200)  //zoom 12
		$map->setZoom(9);
	else if($maxDiff > 0.1200 && $maxDiff <= 0.4640)  //zoom 10
		$map->setZoom(8);
	else if($maxDiff > 0.4640 && $maxDiff <= 1.8580)  //zoom 8
		$map->setZoom(8);
	else if($maxDiff > 1.8580 && $maxDiff <= 3.5310)  //zoom 7
		$map->setZoom(7);
	else if($maxDiff > 3.5310 && $maxDiff <= 7.3367)  //zoom 6
		$map->setZoom(6);
	else if($maxDiff > 7.3367 && $maxDiff <= 14.222)  //zoom 5
		$map->setZoom(5);
	else if($maxDiff > 14.222 && $maxDiff <= 28.000)  //zoom 4
		$map->setZoom(4);
	else if($maxDiff > 28.000 && $maxDiff <= 58.000)  //zoom 3
		$map->setZoom(3);
	else
		$map->setZoom(1);



						/*$locations = array();
						if (!empty ($starting)) {
								$locations[] = $starting;
						}
						if (!empty ($visiting)) {
								foreach ($visiting as $v) {
										$locations[] = $v;
								}
						}
						if (!empty ($ending)) {
								$locations[] = $ending;
						}*/




						$path1 = array();
						$count = 0;
						foreach ($locations as $location) {
							$count++;
								//if ($i < sizeof($locations)) {
										$path1[] = array($location->lat, $location->long);
								//}
								$map->addMarker($location->lat, $location->long, array('title' => $location->name, 'icon' => "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=$count|2b7de2|FFFFFF",
								'html' => '<b>' . $location->name . '</b>', 'infoCloseOthers' => true));
						}
						$map->addPolyline($path1, '#0658bd', 3, 1);
						$map->show();
				}
		}


		function _remap($method, $params=array()){
		$funcs = get_class_methods($this);
		if(in_array($method, $funcs)){

		return call_user_func_array(array($this, $method), $params);

		}else{

			$result = checkUrlParams($method, $params,$this->validlang);
			if($result->showIndex){
			$this->index();
			}else{

			$this->lang->load("front", $this->data['lang_set']);
			$settings = $this->Settings_model->get_front_settings('advertising');
			$this->data['moduleTypes'] = $this->Advertising_lib->advertisingTypes();
			$alladvertising = $this->Advertising_lib->showAdvertisingByLocation($result, $result->offset);
			//$alladvertising = $this->Advertising_lib->showAdvertisingByDestination($result, $result->offset);


			$this->data['module'] = $alladvertising['all_advertising'];
			$this->data['info'] = $alladvertising['paginationinfo'];
			$this->data['date'] = $this->Advertising_lib->date;
			$this->data['minprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_min_price);
			$this->data['maxprice'] = $this->Advertising_lib->convertAmount($settings[0]->front_search_max_price);
			$this->data['currCode'] = $this->Advertising_lib->currencycode;
			$this->data['currSign'] = $this->Advertising_lib->currencysign;
			$this->data['langurl'] = base_url()."advertising/{langid}/";

			$this->setMetaData($settings[0]->header_title,$settings[0]->meta_description,$settings[0]->meta_keywords);
			$this->theme->view('listing', $this->data, $this);


			}



		}

		}



           }
