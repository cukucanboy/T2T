<?php
header('Access-Control-Allow-Origin: *');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . 'modules/Api/libraries/REST_Controller.php';

class Spa extends REST_Controller {

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
        $this->load->library('Spa/Spa_lib');
        $this->settings = $this->Settings_model->get_settings_data();
        $lang = $this->get('lang');
				$this->Spa_lib->set_lang($lang);
    }

    function list_get() {
        $offset = $this->get('offset');
        $list = $this->Spa_lib->show_spa($offset);
        $totalPages = ceil($list['paginationinfo']['totalrows'] / $list['paginationinfo']['perpage']);
        if (!empty ($list['all_spa'])) {
         $this->response(array('response' => $list['all_spa'], 'error' => array('status' => FALSE,'msg' => ''), 'totalPages' => $totalPages), 200);
        }
        else {
           $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Spa could not be found')), 200);
        }
    }

    function locations_get(){
        $locations = $this->Spa_lib->getLocationsList();
        $locArray = array();
        foreach($locations as $loc){
           $locArray[] = array('id' => $loc->id, 'name' => $loc->name);
        }

         if (!empty ($locArray)) {
            $this->response(array('locations' => $locArray, 'maxGuests' =>  pt_max_adults()), 200); // 200 being the HTTP response code
        }
        else {
             $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Locations could not be found')), 200);
        }
    }

    function spatypes_get(){
        $spatypes = $this->Spa_lib->spaTypes();


         if (!empty ($spatypes)) {
              $this->response(array('response' => $spatypes, 'error' => array('status' => FALSE,'msg' => '')), 200);
        }
        else {
            $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Types could not be found')), 200);
        }
    }

     function suggestions_get(){
        $query = $this->input->get('query');
        $suggestions = $this->Spa_lib->suggestionResults($query);


         if (!empty ($suggestions['items'])) {
              $this->response(array('response' => $suggestions['items'], 'error' => array('status' => FALSE,'msg' => '')), 200);
        }
        else {
            $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Results could not be found')), 200);
        }
    }

    function details_get() {
        $id = $this->get('id');

        $appDate = $this->get('date');
        if(empty($appDate)){
        $date = "";
        }else{
        $date = date($this->settings[0]->date_f, strtotime($appDate));
        }

        if (empty($id)) {
             $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Tour ID Missing')), 200);
        }
        $details['spa'] = $this->Spa_lib->spa_details($id, $date);
        $details['spa']->desc = strip_tags($details['spa']->desc);

        if (pt_is_module_enabled('reviews')) {
                        $details['reviews'] = $this->Spa_lib->spa_reviews_for_api($details['spa']->id);
                        $details['avgReviews'] = $this->Spa_lib->spaReviewsAvg($details['spa']->id);
        }

        if (!empty ($details)) {
         $this->response(array('response' => $details, 'error' => array('status' => FALSE,'msg' => '')), 200);
        }else {
         $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Tour Details could not be found')), 200);
        }

    }

    function search_get() {



                $offset = $this->input->get('offset');
                $cityid = $this->get('location');

                /*$appCheckout = $this->get('checkout');
                $checkout = date($this->settings[0]->date_f, strtotime($appCheckout));*/
                $details = $this->Spa_lib->search_spa($cityid , $offset);
                $totalPages = ceil($details['paginationinfo']['totalrows'] / $details['paginationinfo']['perpage']);

                if (!empty ($details['all_spa'])) {
                  $this->response(array('response' => $details['all_spa'], 'error' => array('status' => FALSE,'msg' => ''), 'totalPages' => $totalPages), 200);
                }
                else {
             $this->response(array('response' => '', 'error' => array('status' => TRUE,'msg' => 'Results not found')), 200);
                }
        }

     function invoice_post() {
                $this->load->model('Admin/Bookings_model');
                $userid = $this->post('userId');
                if(!empty($userid)){
                $data = $this->Bookings_model->do_booking($userid);
                }else{
                $data = $this->Bookings_model->doGuestBooking();
                }
                $message = array('response' => $data);
                $this->response($message, 200); // 200 being the HTTP response code
        }

    function show_get($param, $vars = null) {
        $arr = $this->input->get();
        $arrstr = "";
        foreach($arr as $key => $val){
            $arrstr .= $key."=".$val."&";
        }

         $url = base_url()."api/spa/".$param."?".$arrstr;
        //              $url = base_url() . "api/hotels/hoteldetails?id=40";
        //  $url = base_url()."api/hotels/book?id=40&checkin=20/01/2015&checkout=22/01/2015";
        //  $url = base_url()."api/hotels/user";
        $ch = curl_init();
        $timeout = 3;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        @ $json = json_decode($rawdata);
        echo "<pre>";
        print_r($json);
    }
}
