<?php
header('Access-Control-Allow-Origin: *');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . 'modules/Api/libraries/REST_Controller.php';

class NearbyMaps extends REST_Controller {
	private $settings;
		function __construct() {
// Construct our parent class
				parent :: __construct();

			 if(!$this->isValidApiKey){
               	$this->response($this->invalidResponse, 400);
            }
// Configure limits on our controller methods. Ensure
// you have created the 'limits' table and enabled 'limits'
// within application/config/rest.php
				$this->methods['list_get']['limit'] = 500; //500 requests per hour per user/key
				$this->methods['user_post']['limit'] = 100; //100 requests per hour per user/key
				$this->methods['user_delete']['limit'] = 50; //50 requests per hour per user/key
				$this->load->library('Hotels/Hotels_lib');
				$this->load->model('Api/Apihotels_model');
				$this->settings = $this->Settings_model->get_settings_data();
				$lang = $this->get('lang');
				$this->Hotels_lib->set_lang($lang);
		}

		function featured_get() {
				$list = $this->Hotels_lib->getFeaturedHotels();
				if (!empty ($list)) {
						$this->response(array('response' => $list, 'error' => array('status' => FALSE,'msg' => '')), 200);

				}
				else {
						$this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Hotels could not be found')), 200);
				}
		}

		function locations_get(){
        $locations = $this->Hotels_lib->getLocationsList();
        $locArray = array();
        foreach($locations as $loc){
            $locArray[] = array('id' => $loc->id, 'name' => $loc->name);
        }

         if (!empty ($locArray)) {
            $this->response(array('locations' => $locArray), 200); // 200 being the HTTP response code
        }
        else {
            $this->response(array('response' => array('error' => 'Locations could not be found')), 200);
        }
   	   }

		function list_get() {
			//	$perpage = $this->get('perpage');
				$offset = $this->get('offset');
				// if (empty ($perpage)) {
				// 		$perpage = 10;
				// }
				if (empty ($offset)) {
						$offset = 1;
				}
				$list = $this->Hotels_lib->show_hotels($offset);
				$Objresponse = $list['all_hotels'];
				$totalPages = ceil($list['paginationinfo']['totalrows'] / $list['paginationinfo']['perpage']);
				if (!empty ($Objresponse)){
					$this->response(array('response' => $Objresponse, 'error' => array('status' => FALSE,'msg' => ''), 'totalPages' => $totalPages), 200);

				}else {

					$this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Hotels Not found')), 200);
				}
		}

		function search_get() {

				if (!$this->get('checkin')) {
						$this->response(array('response' => array('error' => 'Check In date is required')), 200);
				}
				if (!$this->get('checkout')) {
						$this->response(array('response' => array('error' => 'Check Out date is required')), 200);
				}
				$offset = $this->input->get('offset');
				$appCheckin= $this->get('checkin');
				$checkin = date($this->settings[0]->date_f, strtotime($appCheckin));

				$appCheckout = $this->get('checkout');
				$checkout = date($this->settings[0]->date_f, strtotime($appCheckout));
				$cityid = $this->get('searching');

				$details = $this->Hotels_lib->search_hotels_by_text($cityid, $offset, $checkin,$checkout);

				$Objresponse = $details['all'];
				$totalPages = ceil($details['paginationinfo']['totalrows'] / $details['paginationinfo']['perpage']);
				if (!empty ($Objresponse)){
					$this->response(array('response' => $Objresponse, 'error' => array('status' => FALSE,'msg' => ''), 'totalPages' => $totalPages), 200);

				}else {
			   $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Results Not found')), 200);

				}
		}

		function suggestions_get(){
		$query = $this->input->get('query');
		$suggestions = $this->Hotels_lib->suggestionResults($query);

		if(empty($query)){
				$this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Query missing')), 200);
		}

		if (!empty ($suggestions['forApi']['items'])) {
		$this->response(array('response' => $suggestions['forApi']['items'], 'error' => array('status' => FALSE,'msg' => '')), 200);
		}
		else {
		$this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Results could not be found')), 200);
		}
		}

        function countries_get() {
          $this->load->model('Admin/Countries_model');
          $list = $this->Countries_model->Api_all_countries();
	    	if (!empty ($list)) {
						$this->response(array('response' => $list, 200)); // 200 being the HTTP response code
				}
				else {
						$this->response(array('response' => array('error' => 'countries could not be found')), 200);
				}
		}




}
?>
