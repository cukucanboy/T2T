entertainment<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Entertainment extends MX_Controller {
		private $validlang;
		function __construct() {
// $this->session->sess_destroy();
				parent :: __construct();
				$chk = modules :: run('Home/is_main_module_enabled', 'entertainment');
				if (!$chk) {
					Module_404();
				}

				$this->load->library("Entertainment_lib");
				$this->load->model("Entertainment_model");
				$this->load->helper("Entertainment_front");

				$this->data['usersession'] = $userid = $this->session->userdata('pt_logged_customer');
				$this->data['modulelib'] = $this->Entertainment_lib;
				$this->data['appModule'] = "entertainment";

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



                $this->Entertainment_lib->set_lang($this->data['lang_set']);
                $this->data['locationsList'] = $this->Entertainment_lib->getLocationsList();
                $this->data['selectedAdults'] = $this->Entertainment_lib->adults;
                $this->data['selectedChild'] = $this->Entertainment_lib->child;
                $this->data['selectedInfants'] = $this->Entertainment_lib->infants;

		}

		public function index() {
				$settings = $this->Settings_model->get_front_settings('entertainment');
				$this->data['minprice'] = $settings[0]->front_search_min_price;
				$this->data['maxprice'] = $settings[0]->front_search_max_price;
				$this->data['loadMap'] = TRUE;

                if($this->validlang){
                	//$countryName = $this->uri->segment(3);
					//$cityName = $this->uri->segment(4);
                	$entertainmentlug = $this->uri->segment(5);
                }else{
                	// $countryName = $this->uri->segment(2);
                   // $cityName = $this->uri->segment(3);
                    $entertainmentlug = $this->uri->segment(4);
                }

				$check = $this->Entertainment_model->entertainment_exists($entertainmentlug);
				if ($check && !empty ($entertainmentlug)){
					$this->lang->load("front", $this->data['lang_set']);

						$this->Entertainment_lib->set_entertainmentid($entertainmentlug);
						$this->data['module'] = $this->Entertainment_lib->entertainment_details();
						$this->data['moduleTypes'] = $this->Entertainment_lib->entertainmentTypes();

						 if (pt_is_module_enabled('reviews')) {
								$this->data['reviews'] = $this->Entertainment_lib->entertainmentReviews($this->data['module']->id);
								$this->data['avgReviews'] = $this->Entertainment_lib->entertainmentReviewsAvg($this->data['module']->id);
						}

						$this->data['checkin'] = $this->Entertainment_lib->date;
						$this->data['adults'] = $this->Entertainment_lib->adults;
						$this->data['child'] = (int) $this->Entertainment_lib->child;

						$this->data['checkinMonth'] = strtoupper(date("F",convert_to_unix($this->Entertainment_lib->date)));
						$this->data['checkinDay'] = date("d",convert_to_unix($this->Entertainment_lib->date));
						//$this->data['checkoutMonth'] = strtoupper(date("F",convert_to_unix($this->Entertainment_lib->checkout)));
						//$this->data['checkoutDay'] = date("d",convert_to_unix($this->Entertainment_lib->checkout));

                        $this->data['langurl'] = base_url()."entertainment/{langid}/".$this->data['module']->slug;
                $this->setMetaData($this->data['module']->title,$this->data['module']->metadesc,$this->data['module']->keywords);
                        $this->theme->view('details', $this->data, $this);
				}
				else {
						$this->listing();
				}
		}

		function listing($offset = null) {
			$this->lang->load("front", $this->data['lang_set']);
				$settings = $this->Settings_model->get_front_settings('entertainment');
				$this->data['moduleTypes'] = $this->Entertainment_lib->entertainmentTypes();
				$allentertainment = $this->Entertainment_lib->show_entertainment($offset);
				$this->data['module'] = $allentertainment['all_entertainment'];
				$this->data['info'] = $allentertainment['paginationinfo'];
				$this->data['checkin'] = $this->Entertainment_lib->date;
				$this->data['adults'] = $this->Entertainment_lib->adults;
                $this->data['child'] = (int) $this->Entertainment_lib->child;

				$this->data['minprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_min_price);
				$this->data['maxprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_max_price);
				$this->data['currCode'] = $this->Entertainment_lib->currencycode;
				$this->data['currSign'] = $this->Entertainment_lib->currencysign;
                $this->data['langurl'] = base_url()."entertainment/{langid}/";
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
						redirect('entertainment/search/'.$country.'/'.$city.'/'.$cityid.'?'.$surl);
					}else if(!empty($cityid) && $modType == "entertainment"){
						$this->Entertainment_lib->set_id($cityid);
						$this->Entertainment_lib->entertainment_short_details();
						$title = $this->Entertainment_lib->title;
						$slug = $this->Entertainment_lib->slug;
						if(!empty($title)){
							redirect('entertainment/'.$slug);
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

						$allentertainment = $this->Entertainment_lib->search_entertainment($cityid, $offset);

						$this->data['module'] = $allentertainment['all_entertainment'];
			        	$this->data['info'] = $allentertainment['paginationinfo'];
				}
				else {
						$this->data['module'] = array();
				}

				$this->lang->load("front", $this->data['lang_set']);
				$this->data['city'] = $cityid;


				$this->data['selectedLocation'] = $cityid;//$this->Entertainment_lib->selectedLocation;
				$this->data['checkin'] = $this->Entertainment_lib->date;
				$this->data['adults'] = $this->Entertainment_lib->adults;
                $this->data['child'] = (int) $this->Entertainment_lib->child;
				$this->data['selectedEntertainmentType'] = $this->Entertainment_lib->selectedEntertainmentType;


				$this->data['moduleTypes'] = $this->Entertainment_lib->entertainmentTypes();
				$settings = $this->Settings_model->get_front_settings('entertainment');
				$this->data['searchText'] = $this->input->get('txtSearch');
				$this->data['minprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_min_price);
				$this->data['maxprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_max_price);
				$this->data['currCode'] = $this->Entertainment_lib->currencycode;
				$this->data['currSign'] = $this->Entertainment_lib->currencysign;
				$this->setMetaData('Search Results');
				$this->theme->view('listing', $this->data, $this);

		}

		function book($entertainmentlug) {
			$this->load->model('Admin/Countries_model');

			$this->data['allcountries'] = $this->Countries_model->get_all_countries();

			$check = $this->Entertainment_model->entertainment_exists($entertainmentlug);
				$this->load->library("Paymentgateways");
				$this->data['hideHeader'] = "1";
				//echo "<pre>";
				//print_r($this->Paymentgateways->getAllGateways());
				//echo "</pre>";
  				if ($check && !empty($entertainmentlug)) {
  					$this->lang->load("front", $this->data['lang_set']);
  				  	$this->load->model('Admin/Payments_model');
                      $this->data['error'] = "";
                      $this->Entertainment_lib->set_entertainmentid($entertainmentlug);
                      $entertainmentID = $this->Entertainment_lib->get_id();
                      $bookInfo = $this->Entertainment_lib->getBookResultObject($entertainmentID);
                      $this->data['module'] = $bookInfo['entertainment'];
                      $this->data['extraChkUrl'] = $bookInfo['entertainment']->extraChkUrl;
                      $this->data['totalGuests'] = $this->Entertainment_lib->guestCount;


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
                   redirect("entertainment");
				}
		}

		function txtsearch() {

		}

		function featuredEntertainment($locid = null){
			if(empty($locid)){
			$fentertainment = $this->Entertainment_lib->getFeaturedEntertainment();
			}else{
			$fentertainment = $this->Entertainment_lib->getLocationBasedFeaturedEntertainment($locid);
			}

			echo json_encode($fentertainment);
		}

		function entertainmentmap($entertainmentid) {
				if (!empty ($entertainmentid)) {
						//$starting = pt_entertainment_start_end_map($entertainmentid, 'start');
						//$ending = pt_entertainment_start_end_map($entertainmentid, 'end');
						//$visiting = pt_entertainment_visiting_map($entertainmentid);
						$locationsData = $this->Entertainment_lib->entertainmentLocations($entertainmentid);
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
			$settings = $this->Settings_model->get_front_settings('entertainment');
			$this->data['moduleTypes'] = $this->Entertainment_lib->entertainmentTypes();
			$allentertainment = $this->Entertainment_lib->showEntertainmentByLocation($result, $result->offset);
			$this->data['module'] = $allentertainment['all_entertainment'];
			$this->data['info'] = $allentertainment['paginationinfo'];
			$this->data['date'] = $this->Entertainment_lib->date;
			$this->data['minprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_min_price);
			$this->data['maxprice'] = $this->Entertainment_lib->convertAmount($settings[0]->front_search_max_price);
			$this->data['currCode'] = $this->Entertainment_lib->currencycode;
			$this->data['currSign'] = $this->Entertainment_lib->currencysign;
			$this->data['langurl'] = base_url()."entertainment/{langid}/";

			$this->setMetaData($settings[0]->header_title,$settings[0]->meta_description,$settings[0]->meta_keywords);
			$this->theme->view('listing', $this->data, $this);


			}



		}

		}



           }
