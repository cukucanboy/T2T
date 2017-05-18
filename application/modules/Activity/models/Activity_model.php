<?php

class Activity_model extends CI_Model {
        public $langdef;
		function __construct() {
// Call the Model constructor
				parent :: __construct();
                $this->langdef = DEFLANG;
		}

// Get all enabled activity short info
		function shortInfo($id = null) {
				$result = array();
				$this->db->select('activity_id,activity_title,activity_slug');
				if (!empty ($id)) {
						$this->db->where('activity_owned_by', $id);
				}
				$this->db->where('activity_status', 'Yes');
				$this->db->order_by('activity_id', 'desc');
				$activity = $this->db->get('pt_activity')->result();
				foreach($activity as $activity){
					$result[] = (object)array('id' => $activity->activity_id, 'title' => $activity->activity_title, 'slug' => $activity->activity_slug);
				}

				return $result;
		}

// Get all activity id and names only
		function all_activity_names($id = null) {
				$this->db->select('activity_id,activity_title');
				if (!empty ($id)) {
						$this->db->where('activity_owned_by', $id);
				}
				$this->db->order_by('activity_id', 'desc');
				return $this->db->get('pt_activity')->result();
		}

		// Get all activity for extras
		function all_activity($id = null) {
				$this->db->select('activity_id as id,activity_title as title');
				if (!empty ($id)) {
						$this->db->where('activity_owned_by', $id);
				}
				$this->db->order_by('activity_id', 'desc');
				return $this->db->get('pt_activity')->result();
		}

		function convert_price($amount) {

		}

// get latest activity
		function latest_activity_front() {
				$settings = $this->Settings_model->get_front_settings('activity');
				$limit = $settings[0]->front_latest;
				$this->db->select('pt_activity.activity_status,pt_activity.activity_basic_price,pt_activity.activity_basic_discount,pt_activity.activity_id,pt_activity.activity_desc,pt_activity.activity_title,pt_activity.activity_slug,pt_activity.activity_type,pt_activity_types_settings.sett_name');
				$this->db->order_by('pt_activity.activity_id', 'desc');
				$this->db->where('pt_activity.activity_status', 'Yes');
				$this->db->join('pt_activity_types_settings', 'pt_activity.activity_type = pt_activity_types_settings.sett_id', 'left');
				$this->db->limit($limit);
				return $this->db->get('pt_activity')->result();
		}

// get all data of single activity by slug
		function get_activity_data($activityname) {
				$this->db->select('pt_activity.*');
				$this->db->where('pt_activity.activity_slug', $activityname);

				return $this->db->get('pt_activity')->result();
		}

// get all activity info
		function get_all_activity_back($id = null) {
				$this->db->select('pt_activity.activity_featured_forever,pt_activity.activity_id,pt_activity.activity_title,pt_activity.activity_slug,pt_activity.activity_owned_by,pt_activity.activity_order,pt_activity.activity_status,pt_activity.activity_is_featured,
    pt_activity.activity_featured_from,pt_activity.activity_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_activity_types_settings.sett_name');
// $this->db->where('pt_activity_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_activity.activity_owned_by', $id);
				}
				$this->db->order_by('pt_activity.activity_id', 'desc');
				$this->db->join('pt_accounts', 'pt_activity.activity_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_activity_images','pt_activity.activity_id = pt_activity_images.timg_activity_id','left');
				$query = $this->db->get('pt_activity');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all activity info with limit
		function get_all_activity_back_limit($id = null, $perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_activity.activity_featured_forever,pt_activity.activity_id,pt_activity.activity_title,pt_activity.activity_slug,pt_activity.activity_created_at,pt_activity.activity_owned_by,pt_activity.activity_order,pt_activity.activity_status,pt_activity.activity_is_featured,
    pt_activity.activity_featured_from,pt_activity.activity_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_activity_types_settings.sett_name');
// $this->db->where('pt_activity_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_activity.activity_owned_by', $id);
				}
				$this->db->order_by('pt_activity.activity_id', 'desc');
				$this->db->join('pt_accounts', 'pt_activity.activity_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_activity_images','pt_activity.activity_id = pt_activity_images.timg_activity_id','left');
				$query = $this->db->get('pt_activity', $perpage, $offset);
				$data['all'] = $query->result();
				return $data;
		}

// add activity data
		function add_activity($user = null) {
			if(empty($user)){
				$user = 1;
			}

                $depval = floatval($this->input->post('depositvalue'));
                $deptype = $this->input->post('deposittype');

                $taxval = floatval($this->input->post('taxvalue'));
                $taxtype = $this->input->post('taxtype');

                $commper = 0;
                $commfixed = 0;
                $taxper = 0;
                $taxfixed = 0;
                if($deptype == "fixed"){
                 $commfixed = $depval;
                 $commper = 0;
                }else{
                 $commfixed = 0;
                 $commper = $depval;
                }

                if($taxtype == "fixed"){
                 $taxfixed = $taxval;
                 $taxper = 0;
                }else{
                 $taxfixed = 0;
                 $taxper = $taxval;
                }

                $this->db->select("activity_id");
				$this->db->order_by("activity_id", "desc");
				$query = $this->db->get('pt_activity');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$activitylastid = 1;
				}
				else {
						$activitylastid = $lastid[0]->activity_id + 1;
				}

				$activitycount = $query->num_rows();
				$activityorder = $activitycount + 1;
				$this->db->select("activity_id");
				$this->db->where("activity_title", $this->input->post('activityname'));
				$queryc = $this->db->get('pt_activity')->num_rows();
				if ($queryc > 0) {
						$activitylug = create_url_slug($this->input->post('activityname')) . "-" . $activitylastid;
				}
				else {
						$activitylug = create_url_slug($this->input->post('activityname'));
				}
				$amenities = @ implode(",", $this->input->post('activityamenities'));
				$exclusions = @ implode(",", $this->input->post('activityexclusions'));
				$paymentopt = @ implode(",", $this->input->post('activitypayments'));
				$relatedactivity = @ implode(",", $this->input->post('relatedactivity'));

				// Start Related Products 1
				$relatedProdHotels = @ implode(",", $this->input->post('relatedProdHotels'));
				$relatedProdTestaurant = @ implode(",", $this->input->post('relatedProdRestaurant'));
				$relatedProdTours = @ implode(",", $this->input->post('relatedProdTours'));
				$relatedProdWedding = @ implode(",", $this->input->post('relatedProdWedding'));
				$relatedProdCars = @ implode(",", $this->input->post('relatedProdCars'));
				$relatedProdSpa = @ implode(",", $this->input->post('relatedProdspa'));
				// End Related Products



				$featured = $this->input->post('isfeatured');
				if(empty($featured)){
                 $featured = "no";
                }

                $ffrom = $this->input->post('ffrom');
				$fto = $this->input->post('fto');
				if(empty($ffrom) || empty($fto) && $featured == "yes" ){

                    $isforever = 'forever';

				}else{

				  	$isforever = '';
				}

				if($featured == "no"){
					$isforever = '';
				}

				$location =  $this->input->post('locations');
				$activityLocation = $location[0];

				$stars = $this->input->post('activitytars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('activity_title' => $this->input->post('activityname'),
					'activity_slug' => $activitylug, 'activity_desc' => $this->input->post('activitydesc'),
					'activity_stars' => intval($stars),
					'activity_is_featured' => $featured,
					'activity_featured_from' => convert_to_unix($ffrom),
					'activity_featured_to' => convert_to_unix($fto),
					'activity_owned_by' => $user,
					'activity_type' => $this->input->post('activitytype'),
					'activity_location' => $activityLocation,
					'activity_latitude' => $this->input->post('latitude'),
					'activity_longitude' => $this->input->post('longitude'),
					'activity_mapaddress' => $this->input->post('activitymapaddress'),
	                		//'activity_basic_price' => $this->input->post('basic'),
					//'activity_basic_discount' => $this->input->post('discount'),
					'activity_meta_title' => $this->input->post('activitymetatitle'),
					'activity_meta_keywords' => $this->input->post('activitykeywords'),
					'activity_meta_desc' => $this->input->post('activitymetadesc'), 'activity_amenities' => $amenities,
					'activity_exclusions' => $exclusions, 'activity_payment_opt' => $paymentopt,
					'activity_max_adults' => intval($this->input->post('maxadult')),
					'activity_max_child' => intval($this->input->post('maxchild')),
					'activity_max_infant' => intval($this->input->post('maxinfant')),
					'activity_adult_price' => floatval($this->input->post('adultprice')),
					'activity_child_price' => floatval($this->input->post('childprice')),
					'activity_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'activity_days' => intval($this->input->post('activitydays')),
					'activity_nights' => intval($this->input->post('activitynights')),
					'activity_privacy' => $this->input->post('activityprivacy'),
					'activity_status' => $this->input->post('activitytatus'),
					'activity_related' => $relatedactivity,
					/* product_related */
					'product_related_hotels' =>$relatedProdHotels,
					'product_related_restaurant' =>$relatedProdTestaurant,
					'product_related_wedding' =>$relatedProdWedding,
					'product_related_tours' => $relatedProdTours,
					'product_related_spa' => $relatedProdSpa,
					'product_related_cars' => $relatedProdCars,
					/* product_related */


          'activity_order' => $activityorder,
					'activity_comm_fixed' => $commfixed, 'activity_comm_percentage' => $commper,
					'activity_tax_fixed' => $taxfixed, 'activity_tax_percentage' => $taxper,
					'activity_email' => $this->input->post('activityemail'),
					'activity_phone' => $this->input->post('activityphone'),
					'activity_website' => $this->input->post('activitywebsite'),
					'activity_fulladdress' => $this->input->post('activityfulladdress'),
					'activity_featured_forever' => $isforever,
					'activity_created_at' => time());
				$this->db->insert('pt_activity', $data);
				$activityid = $this->db->insert_id();
				$this->updateActivityLocations($this->input->post('locations'), $activityid);
				return $activityid;
		}

// update activity data
		function update_activity($id) {

				$activitycomm = $this->input->post('deposit');
                $depval = floatval($this->input->post('depositvalue'));
                $deptype = $this->input->post('deposittype');

                $taxval = floatval($this->input->post('taxvalue'));
                $taxtype = $this->input->post('taxtype');

                $commper = 0;
                $commfixed = 0;
                $taxper = 0;
                $taxfixed = 0;
                if($deptype == "fixed"){
                 $commfixed = $depval;
                 $commper = 0;
                }else{
                 $commfixed = 0;
                 $commper = $depval;
                }

                if($taxtype == "fixed"){
                 $taxfixed = $taxval;
                 $taxper = 0;
                }else{
                 $taxfixed = 0;
                 $taxper = $taxval;
                }


				$this->db->select("activity_id");
				$this->db->where("activity_id !=", $id);
				$this->db->where("activity_title", $this->input->post('activityname'));
				$queryc = $this->db->get('pt_activity')->num_rows();
				if ($queryc > 0) {
						$activitylug = create_url_slug($this->input->post('activityname')) . "-" . $id;
				}
				else {
						$activitylug = create_url_slug($this->input->post('activityname'));
				}
				$amenities = @ implode(",", $this->input->post('activityamenities'));
				$exclusions = @ implode(",", $this->input->post('activityexclusions'));
				$paymentopt = @ implode(",", $this->input->post('activitypayments'));
				$relatedactivity = @ implode(",", $this->input->post('relatedactivity'));

				// Start Related Products
				$relatedProdHotels = @ implode(",", $this->input->post('relatedProdHotels'));
				$relatedProdTestaurant = @ implode(",", $this->input->post('relatedProdRestaurant'));
				$relatedProdTours = @ implode(",", $this->input->post('relatedProdTours'));
				$relatedProdWedding = @ implode(",", $this->input->post('relatedProdWedding'));
				$relatedProdCars = @ implode(",", $this->input->post('relatedProdCars'));
				$relatedProdSpa = @ implode(",", $this->input->post('relatedProdspa'));
				// End Related Products


				$featured = $this->input->post('isfeatured');

				if(empty($featured)){
                 $featured = "no";
                }

                $ffrom = $this->input->post('ffrom');
				$fto = $this->input->post('fto');
				if(empty($ffrom) || empty($fto) && $featured == "yes" ){

                    $isforever = 'forever';

				}else{

				  	$isforever = '';
				}

				if($featured == "no"){
					$isforever = '';
				}

				$location =  $this->input->post('locations');
				$activityLocation = $location[0];

				$stars = $this->input->post('activitytars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('activity_title' => $this->input->post('activityname'),
					'activity_slug' => $activitylug, 'activity_desc' => $this->input->post('activitydesc'),
					'activity_stars' => intval($stars),
					'activity_is_featured' => $featured,
					'activity_featured_from' => convert_to_unix($ffrom),
					'activity_featured_to' => convert_to_unix($fto),
					'activity_type' => $this->input->post('activitytype'),
					'activity_location' => $activityLocation,
					'activity_latitude' => $this->input->post('latitude'),
					'activity_longitude' => $this->input->post('longitude'),
					'activity_mapaddress' => $this->input->post('activitymapaddress'),
	                		//'activity_basic_price' => $this->input->post('basic'),
					//'activity_basic_discount' => $this->input->post('discount'),
					'activity_meta_title' => $this->input->post('activitymetatitle'),
					'activity_meta_keywords' => $this->input->post('activitykeywords'),
					'activity_meta_desc' => $this->input->post('activitymetadesc'), 'activity_amenities' => $amenities,
					'activity_exclusions' => $exclusions, 'activity_payment_opt' => $paymentopt,
					'activity_max_adults' => intval($this->input->post('maxadult')),
					'activity_max_child' => intval($this->input->post('maxchild')),
					'activity_max_infant' => intval($this->input->post('maxinfant')),
					'activity_adult_price' => floatval($this->input->post('adultprice')),
					'activity_child_price' => floatval($this->input->post('childprice')),
					'activity_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'activity_days' => intval($this->input->post('activitydays')),
					'activity_nights' => intval($this->input->post('activitynights')),
					'activity_privacy' => $this->input->post('activityprivacy'),
					'activity_status' => $this->input->post('activitytatus'),
					'activity_related' => $relatedactivity,

					/* product_related */
					'product_related_hotels' =>$relatedProdHotels,
					'product_related_restaurant' =>$relatedProdTestaurant,
					'product_related_wedding' =>$relatedProdWedding,
					'product_related_tours' => $relatedProdTours,
					'product_related_spa' => $relatedProdSpa,
					'product_related_cars' => $relatedProdCars,
					/* product_related */



					'activity_comm_fixed' => $commfixed, 'activity_comm_percentage' => $commper,
					'activity_tax_fixed' => $taxfixed, 'activity_tax_percentage' => $taxper,
					'activity_email' => $this->input->post('activityemail'),
					'activity_phone' => $this->input->post('activityphone'),
					'activity_website' => $this->input->post('activitywebsite'),
					'activity_fulladdress' => $this->input->post('activityfulladdress'),
					'activity_featured_forever' => $isforever);
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);

				$this->updateActivityLocations($this->input->post('locations'), $id);
	}

  function convert_json($nearbyrelatedtour)
  {
    $return_js = [];
    $data = explode(',',$nearbyrelatedtour);
    foreach ($data as $key) {
      //print_r($key);
      $getmd = substr($key,-2);
      $getid = substr($key, 0, -2);
      $chkmd = $this->chkmodulefw($getmd);
      $row['id'] = $getid;
      $row['module'] = $chkmd;
      array_push($return_js,$row);
    }
    return json_encode($return_js);

  }

  function chkmodulefw($module)
  {
    $list = array('advertising' => 'ad', 'car' => 'ca', 'spa' => 'sp', 'entertainment' => 'et', 'activity' => 'at' ,'tour' => 'to', 'restaurant' => 'rt' ,'wedding' => 'wd' , 'hotel' => 'ht');
    $key = array_search($module ,$list);
    return $key;
  }

  function chkmodulerr($module)
  {
    $list = array('ad' => 'advertising', 'ca' => 'car', 'sp' => 'spa', 'et' => 'entertainment', 'at' => 'activity' ,'to' => 'tour', 'rt' => 'restaurant' ,'wd' => 'wedding' , 'ht' => 'hotel');
    $key = array_search($module ,$list);
    return $key;
  }

  function nearbyhtml($data)
  {
    $items = json_decode($data);
    foreach ($items as $key => $value)
    {
      $md = $this->chkmodulerr($value->module);
      $list .= $value->id.'' .$md .',';
    }
    return $list;
  }

// Add activity settings data
		function add_settings_data() {
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
				$this->db->insert('pt_activity_types_settings', $data);
		}

// update activity settings data
		function update_settings_data() {
				$id = $this->input->post('id');
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
				$this->db->where('sett_id', $id);
				$this->db->update('pt_activity_types_settings', $data);
		}

// Disable activity settings
		function disable_settings($id) {
				$data = array('sett_status' => 'No');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_activity_types_settings', $data);
		}

// Enable activity settings
		function enable_settings($id) {
				$data = array('sett_status' => 'Yes');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_activity_types_settings', $data);
		}

// Delete activity settings
		function delete_settings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_activity_types_settings');
		}

// get all activity for related selection for backend
		function select_related_activity($id = null) {
				$this->db->select('activity_title,activity_id');
				if (!empty ($id)) {
						$this->db->where('activity_id !=', $id);
				}
				return $this->db->get('pt_activity')->result();
		}

    // get data all module to relate product
    function data_for_relate_near_by()
    {
      $sql = ("SELECT hotel_id as id,hotel_title as title, 'hotel' as module FROM pt_hotels
        UNION ALL
        SELECT car_id as id,car_title as title, 'car' as module FROM pt_cars
        UNION ALL
        SELECT spa_id as id,spa_title as title, 'spa' as module FROM pt_spa
        UNION ALL
        SELECT activity_id as id,activity_title as title, 'activity' as module FROM pt_activity
        UNION ALL
        SELECT tour_id as id,tour_title as title, 'tour' as module FROM pt_tours
        UNION ALL
        SELECT restaurant_id as id,restaurant_title as title, 'restaurant' as module FROM pt_restaurant
        UNION ALL
        SELECT wedding_id as id,wedding_title as title, 'wedding' as module FROM pt_wedding
        UNION ALL
        SELECT entertainment_id as id,entertainment_title as title, 'entertainment' as module FROM pt_entertainment;"
      );
      $query = $this->db->query($sql);
      return $query->result();
    }

// Get activity settings data
		function get_activity_settings_data($type) {
			if(!empty($type)){
             	$this->db->where('sett_type', $type);
		  }

				$this->db->order_by('sett_id', 'desc');
				return $this->db->get('pt_activity_types_settings')->result();
		}

// Get activity settings data for adding activity
		function get_tsettings_data($type) {
				$this->db->where('sett_type', $type);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_activity_types_settings')->result();
		}

// Get activity settings data for adding activity
		function get_tsettings_data_front($type, $items) {
				$this->db->where('sett_type', $type);
				$this->db->where_in('sett_id', $items);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_activity_types_settings')->result();
		}

// add Activity images by type
		function add_activity_image($type, $filename, $activityid) {
				$imgorder = 0;
				if ($type == "slider") {
						$this->db->where('timg_type', 'slider');
						$this->db->where('timg_activity_id', $activityid);
						$imgorder = $this->db->get('pt_activity_images')->num_rows();
						$imgorder = $imgorder + 1;
				}
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_activity_id', $activityid);
				$hasdefault = $this->db->get('pt_activity_images')->num_rows();
				if ($hasdefault < 1) {
						$type = 'default';
				}
				$approval = pt_admin_gallery_approve();
				$data = array('timg_activity_id' => $activityid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
				$this->db->insert('pt_activity_images', $data);
		}

// update activity map order
		function update_map_order($id, $order) {
				$data = array('map_order' => $order);
				$this->db->where('map_id', $id);
				$this->db->update('pt_activity_maps', $data);
		}


// update activity order
		function update_activity_order($id, $order) {
				$data = array('activity_order' => $order);
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);
		}

// update featured status
		function update_featured() {
				$isfeatured = $this->input->post('isfeatured');
                $id = $this->input->post('id');

                if($isfeatured == "no"){
					$isforever = '';
				}else{

				$isforever = "forever";

				}



			 $data = array('activity_is_featured' => $isfeatured, 'activity_featured_forever' => $isforever);
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);
		}
// Disable Activity

		public function disable_activity($id) {
				$data = array('activity_status' => 'No');
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);
		}
// Enable Activity

		public function enable_activity($id) {
				$data = array('activity_status' => 'Yes');
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);
		}

// Delete activity
		function delete_activity($activityid) {
				$activityimages = $this->activity_images($activityid);
				foreach ($activityimages['all_slider'] as $sliderimg) {
						$this->delete_image($sliderimg->timg_image,$sliderimg->timg_id,$activityid);
				}


				$this->db->where('review_itemid', $activityid);
				$this->db->where('review_module', 'activity');
				$this->db->delete('pt_reviews');
				$this->db->where('map_activity_id', $activityid);
				$this->db->delete('pt_activity_maps');

				$this->db->where('item_id', $activityid);
                $this->db->delete('pt_activity_translation');

                $this->db->where('activity_id',$activityid);
            	$this->db->delete('pt_activity_locations');

				$this->db->where('activity_id', $activityid);
				$this->db->delete('pt_activity');
		}

// Get Activity Images
		function activity_images($id) {
				$this->db->where('timg_activity_id', $id);
				$this->db->where('timg_type', 'default');
				$q = $this->db->get('pt_activity_images');
				$data['def_image'] = $q->result();
				$this->db->where('timg_type', 'slider');
				$this->db->order_by('timg_id', 'desc');
				$this->db->having('timg_activity_id', $id);
				$q = $this->db->get('pt_activity_images');
				$data['all_slider'] = $q->result();
				$data['slider_counts'] = $q->num_rows();
				return $data;
		}

//update activity thumbnail
		function update_thumb($oldthumb, $newthumb, $activityid) {
				$data = array('timg_type' => 'slider');
				$this->db->where('timg_id', $oldthumb);
				$this->db->where('timg_activity_id', $activityid);
				$this->db->update('pt_activity_images', $data);
				$data2 = array('timg_type' => 'default');
				$this->db->where('timg_id', $newthumb);
				$this->db->where('timg_activity_id', $activityid);
				$this->db->update('pt_activity_images', $data2);
		}

// Approve or reject Hotel Images
		function approve_reject_images() {
				$data = array('timg_approved' => $this->input->post('apprej'));
				$this->db->where('timg_id', $this->input->post('imgid'));
				$this->db->update('pt_activity_images', $data);
		}

// update image order
		function update_image_order($imgid, $order) {
				$data = array('timg_order' => $order);
				$this->db->where('timg_id', $imgid);
				$this->db->update('pt_activity_images', $data);
		}


// Delete activity Images
		function delete_image($imgname, $imgid, $activityid) {
				$this->db->where('timg_id', $imgid);
				$this->db->delete('pt_activity_images');
                $this->updateActivityThumb($activityid,$imgname,"delete");
                @ unlink(PT_ACTIVITY_SLIDER_THUMB_UPLOAD . $imgname);
				@ unlink(PT_ACTIVITY_SLIDER_UPLOAD . $imgname);
		}

//update activity thumbnail
		function updateActivityThumb($activityid,$imgname,$action) {
		  if($action == "delete"){
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image',$imgname);
            $this->db->where('activity_id',$activityid);
            $rs = $this->db->get('pt_activity')->num_rows();
            if($rs > 0){
              $data = array(
              'thumbnail_image' => PT_BLANK_IMG
              );
              $this->db->where('activity_id',$activityid);
              $this->db->update('pt_activity',$data);
            }
            }else{
              $data = array(
              'thumbnail_image' => $imgname
              );
              $this->db->where('activity_id',$activityid);
              $this->db->update('pt_activity',$data);
            }

		}




		function offers_data($id) {
				/*$this->db->where('offer_module', 'activity');
				$this->db->where('offer_item', $id);
				return $this->db->get('pt_special_offers')->result();*/
		}

		function add_to_map() {
				$maporder = 0;
				$activityid = $this->input->post('activityid');
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_activity_id', $activityid);
				$res = $this->db->get('pt_activity_maps')->num_rows();
				$addtype = $this->input->post('addtype');
				if ($addtype == "visit") {
						$maporder = $res + 1;
				}
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_activity_id' => $activityid, 'map_order' => $maporder);
				$this->db->insert('pt_activity_maps', $data);
		}

		function update_activity_map() {
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
				$this->db->where('map_id', $this->input->post('mapid'));
				$this->db->update('pt_activity_maps', $data);
		}

		function has_start_end_city($type, $activityid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', $type);
				$this->db->where('map_activity_id', $activityid);
				$nums = $this->db->get('pt_activity_maps')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

		function get_activity_map($activityid) {
				$this->db->where('map_activity_id', $activityid);
				return $this->db->get('pt_activity_maps')->result();
		}

		function delete_map_item($mapid) {
				$this->db->where('map_id', $mapid);
				$this->db->delete('pt_activity_maps');
		}

// get related activity for front-end
		function get_related_activity($activity) {
				$id = explode(",", $activity);
				$this->db->select('pt_activity.activity_title,pt_activity.activity_slug,pt_activity.activity_id,pt_activity.activity_basic_price,pt_activity.activity_basic_discount,pt_activity_types_settings.sett_name');
				$this->db->where_in('pt_activity.activity_id', $id);
/*  $this->db->where('pt_activity_images.timg_type','default');
$this->db->join('pt_activity_images','pt_activity.activity_id = pt_activity_images.timg_activity_id','left');*/
				$this->db->join('pt_activity_types_settings', 'pt_activity.activity_type = pt_activity_types_settings.sett_id', 'left');
				return $this->db->get('pt_activity')->result();
		}

// Check activity existence
		function activity_exists($slug) {
				$this->db->select('activity_id');
				$this->db->where('activity_slug', $slug);
				$this->db->where('activity_status', 'Yes');
				$nums = $this->db->get('pt_activity')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

// List all activity on front listings page
		function list_activity_front($sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_activity.activity_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_activity.activity_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_activity.activity_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_activity.activity_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_activity.activity_order', 'asc');
				}
				$this->db->select('activity_id');
				$this->db->group_by('activity_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_activity.activity_adult_price >=', $minp);
						$this->db->where('pt_activity.activity_adult_price <=', $maxp);
				}

				$this->db->where('activity_status', 'Yes');
				$query = $this->db->get('pt_activity', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all activity on front listings page by location
		function showActivityByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_activity.activity_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_activity.activity_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_activity.activity_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_activity.activity_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_activity.activity_order', 'asc');
				}
				$this->db->select('activity_id');
				$this->db->group_by('activity_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_activity.activity_adult_price >=', $minp);
						$this->db->where('pt_activity.activity_adult_price <=', $maxp);
				}

				if(is_array($locs)){
                $this->db->where_in('pt_activity.activity_location',$locs);
                }else{
                $this->db->where('pt_activity.activity_location',$locs);
                }

				$this->db->where('activity_status', 'Yes');
				$query = $this->db->get('pt_activity', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// Search activity from home page
		function search_activity_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$this->load->helper('activity_front');
				$data = array();

				//$location = $this->input->get('location');

				$adults = $this->input->get('adults');
				$type = $this->input->get('type');

				//$sprice = $this->input->get('price');
				$stars = $this->input->get('stars');

				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_activity.activity_id,activity_type,activity_location,activity_adult_price,activity_title,activity_max_adults,activity_status,pt_activity_locations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_activity.activity_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_activity.activity_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_activity.activity_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_activity.activity_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_activity.activity_order', 'asc');
				}
				elseif ($orderby == "p_lh") {
						$this->db->order_by('pt_activity.activity_adult_price', 'asc');
				}
				elseif ($orderby == "p_hl") {
						$this->db->order_by('pt_activity.activity_adult_price', 'desc');
				}

				if(!empty($location)){
					//$this->db->like('pt_activity.activity_location', $location);
					$this->db->where('pt_activity_locations.location_id', $location);

				}


				if (!empty ($adults)) {
						$this->db->where('pt_activity.activity_max_adults >=', $adults);
				}

				if (!empty ($stars)) {
						$this->db->where('activity_stars', $stars);
				}



				if (!empty ($type)) {
						$this->db->where('pt_activity.activity_type', $type);
				}

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_activity.activity_adult_price >=', $minp);
						$this->db->where('pt_activity.activity_adult_price <=', $maxp);
				}
				$this->db->group_by('pt_activity.activity_id');
				$this->db->join('pt_activity_locations', 'pt_activity.activity_id = pt_activity_locations.activity_id');
				$this->db->where('pt_activity.activity_status', 'Yes');


		if(!empty($perpage)){

				$query = $this->db->get('pt_activity', $perpage, $offset);

				}else{

				$query = $this->db->get('pt_activity');

				}

				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

		function max_map_order($activityid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_activity_id', $activityid);
				return $this->db->get('pt_activity_maps')->num_rows();
		}

// get default image of activity
		function default_activity_img($id) {
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_approved', '1');
				$this->db->where('timg_activity_id', $id);
				$res = $this->db->get('pt_activity_images')->result();
				if (!empty ($res)) {
						return $res[0]->timg_image;
				}
				else {
						return '';
				}
		}

// update translated data os some fields in english
		function update_english($id) {
				$cslug = create_url_slug($this->input->post('title'));
				$this->db->where('activity_slug', $cslug);
				$this->db->where('activity_id !=', $id);
				$nums = $this->db->get('pt_activity')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('activity_title' => $this->input->post('title'), 'activity_slug' => $cslug, 'activity_desc' => $this->input->post('desc'), 'activity_policy' => $this->input->post('policy'));
				$this->db->where('activity_id', $id);
				$this->db->update('pt_activity', $data);
				return $cslug;
		}

// Adds translation of some fields data
		function add_translation($postdata, $activityid) {
		foreach($postdata as $lang => $val){
		     if(array_filter($val)){
		        $title = $val['title'];
                $desc = $val['desc'];
                $metatitle = $val['metatitle'];
				$metadesc = $val['metadesc'];
				$keywords = $val['keywords'];
				$policy = $val['policy'];
                $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_policy' => $policy,
                'metatitle' => $metatitle,
                'metadesc' => $metadesc,
                'metakeywords' => $keywords,
                'item_id' => $activityid,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_activity_translation', $data);
                }

                }
		}

// Update translation of some fields data
		function update_translation($postdata, $id) {
	 foreach($postdata as $lang => $val){
		     if(array_filter($val)){
		        $title = $val['title'];
                $desc = $val['desc'];
                $metatitle = $val['metatitle'];
				$metadesc = $val['metadesc'];
				$kewords = $val['keywords'];
				$policy = $val['policy'];
                $transAvailable = $this->getBackTranslation($lang,$id);

                if(empty($transAvailable)){
                   $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_policy' => $policy,
                'metatitle' => $metatitle,
                'metadesc' => $metadesc,
                'metakeywords' => $kewords,
                'item_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_activity_translation', $data);

                }else{
                 $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_policy' => $policy,
                'metatitle' => $metatitle,
                'metadesc' => $metadesc,
                'metakeywords' => $kewords,
                );
				$this->db->where('item_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_activity_translation', $data);
                }


              }

                }

		}

		 function getBackTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('item_id',$id);
            return $this->db->get('pt_activity_translation')->result();

        }

         function activityGallery($slug){

          $this->db->select('pt_activity.thumbnail_image as thumbnail,pt_activity_images.timg_id as id,pt_activity_images.timg_activity_id as itemid,pt_activity_images.timg_type as type,pt_activity_images.timg_image as image,pt_activity_images.timg_order as imgorder,pt_activity_images.timg_image as image,pt_activity_images.timg_approved as approved');
          $this->db->where('pt_activity.activity_slug',$slug);
          $this->db->join('pt_activity_images', 'pt_activity.activity_id = pt_activity_images.timg_activity_id', 'left');
          $this->db->order_by('pt_activity_images.timg_id','desc');
          return $this->db->get('pt_activity')->result();

        }

        function addPhotos($id,$filename){

         $this->db->select('thumbnail_image');
         $this->db->where('activity_id',$id);
         $rs = $this->db->get('pt_activity')->result();
         if($rs[0]->thumbnail_image == PT_BLANK_IMG){

               $data = array('thumbnail_image' => $filename);
               $this->db->where('activity_id',$id);
               $this->db->update('pt_activity',$data);
         }

        //add photos to activity images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_activity_id', $id);
        $imgorder = $this->db->get('pt_activity_images')->num_rows();
        $imgorder = $imgorder + 1;

				$approval = pt_admin_gallery_approve();

		    	$insdata = array(
                'timg_activity_id' => $id,
                'timg_type' => 'slider',
                'timg_image' => $filename,
                'timg_order' => $imgorder,
                'timg_approved' => $approval
                );

				$this->db->insert('pt_activity_images', $insdata);


        }

        function assignActivity($activity,$userid){

          if(!empty($activity)){
          $useractivity = $this->userOwnedActivity($userid);
                foreach($useractivity as $tt){
                   if(!in_array($tt,$activity)){
                    $ddata = array(
                   'activity_owned_by' => '1'
                   );
                   $this->db->where('activity_id',$tt);
                   $this->db->update('pt_activity',$ddata);
                   }
                }

                foreach($activity as $t){
                   $data = array(
                   'activity_owned_by' => $userid
                   );
                   $this->db->where('activity_id',$t);
                   $this->db->update('pt_activity',$data);

                 }

                 }
        }

        function userOwnedActivity($id){
          $result = array();
          if(!empty($id)){
          $this->db->where('activity_owned_by',$id);
          }

          $rs = $this->db->get('pt_activity')->result();
          if(!empty($rs)){
            foreach($rs as $r){
              $result[] = $r->activity_id;
            }
          }
          return $result;
        }

        // get number of photos of activity
		function photos_count($activityid) {
				$this->db->where('timg_activity_id', $activityid);
				return $this->db->get('pt_activity_images')->num_rows();
		}

		function updateActivitySettings() {
				$ufor = $this->input->post('updatefor');

				$data = array('front_icon' => $this->input->post('page_icon'),
                'front_homepage' => $this->input->post('home'),
                'front_homepage_order' => $this->input->post('homeorder'),
                'front_related' => $this->input->post('related'),
                //'front_popular' => $this->input->post('popular'),
                //'front_popular_order' => $this->input->post('popularorder'),
                'front_latest' => $this->input->post('latest'),
                'front_listings' => $this->input->post('listings'),
                'front_listings_order' => $this->input->post('listingsorder'),
                'front_search' => $this->input->post('searchresult'),
                'front_search_order' => $this->input->post('searchorder'),
                'front_search_min_price' => $this->input->post('minprice'),
                'front_search_max_price' => $this->input->post('maxprice'),
                'front_txtsearch' => '1',
				'linktarget' => $this->input->post('target'),
				'header_title' => $this->input->post('headertitle'),
				'meta_keywords' => $this->input->post('keywords'),
				'meta_description' => $this->input->post('description')
				);
				$this->db->where('front_for', $ufor);
				$this->db->update('pt_front_settings', $data);
				$this->session->set_flashdata('flashmsgs', "Updated Successfully");
		}

		// get popular activity
		function popular_activity_front() {
				$settings = $this->Settings_model->get_front_settings('activity');
				$limit = $settings[0]->front_popular;
				$orderby = $settings[0]->front_popular_order;

                $this->db->select('pt_activity.activity_id,pt_activity.activity_status,pt_reviews.review_overall,pt_reviews.review_itemid');

                $this->db->select_avg('pt_reviews.review_overall', 'overall');
				$this->db->order_by('overall', 'desc');
				$this->db->group_by('pt_activity.activity_id');
				$this->db->join('pt_reviews', 'pt_activity.activity_id = pt_reviews.review_itemid');
				$this->db->where('activity_status', 'yes');
				$this->db->limit($limit);
			   	return $this->db->get('pt_activity')->result();
		}



		function addSettingsData() {
		        $filename = "";
                $type = $this->input->post('typeopt');
				$data = array(
                'sett_name' => $this->input->post('name'),
                'sett_status' => $this->input->post('statusopt'),
                'sett_selected' => $this->input->post('setselect'),
                'sett_type' => $type,
                'sett_img' => $filename
                );
				$this->db->insert('pt_activity_types_settings', $data);
                return $this->db->insert_id();
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");

		}

// update activity settings data
		function updateSettingsData() {
				$id = $this->input->post('settid');
                $type = $this->input->post('typeopt');
                 $filename = "";

				$data = array('sett_name' => $this->input->post('name'),
                'sett_status' => $this->input->post('statusopt'),
                'sett_selected' => $this->input->post('setselect'),
                'sett_img' => $filename

                );
				$this->db->where('sett_id', $id);
				$this->db->update('pt_activity_types_settings', $data);
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");
		}


		 function updateSettingsTypeTranslation($postdata,$id) {

       foreach($postdata as $lang => $val){
		     if(array_filter($val)){
		        $name = $val['name'];

                $transAvailable = $this->getBackSettingsTranslation($lang,$id);

                if(empty($transAvailable)){
                 $data = array(
                'trans_name' => $name,
                'sett_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_activity_types_settings_translation', $data);

                }else{

                 $data = array(
                'trans_name' => $name
                );
				$this->db->where('sett_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_activity_types_settings_translation', $data);

              }


              }

                }
		}


         function getBackSettingsTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_activity_types_settings_translation')->result();

        }

        // Delete hotel settings
		function deleteTypeSettings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_activity_types_settings');

                $this->db->where('sett_id', $id);
				$this->db->delete('pt_activity_types_settings_translation');
		}

				// Delete multiple activity settings
		function deleteMultiplesettings($id, $type) {
				$this->db->where('sett_id', $id);
				$this->db->where('sett_type',$type);
				$this->db->delete('pt_activity_types_settings');

				$rowsDeleted = $this->db->affected_rows();

				if($rowsDeleted > 0){
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_activity_types_settings_translation');
				}


		}

         function getTypesTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_activity_types_settings_translation')->result();

        }

        function updateActivityLocations($locations, $activityid){

        	$this->db->where('activity_id',$activityid);
        	$this->db->delete('pt_activity_locations');
        	$position = 0;

        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$position++;
        			$data = array('position' => $position,'location_id' => $loc, 'activity_id' => $activityid);
        			$this->db->insert('pt_activity_locations', $data);
        		}
        	}

        }

        function isActivityLocation($i, $locid, $activityid){
        	$this->db->where('position', $i);
        	$this->db->where('location_id', $locid);
        	$this->db->where('activity_id', $activityid);
        	$rs = $this->db->get('pt_activity_locations')->num_rows();
        	if($rs > 0){
        		return "selected";
        	}else{
        		return "";
        	}
        }

        function activitySelectedLocations($activityid){
          $result = array();
          $this->db->where('activity_id', $activityid);
          $res = $this->db->get('pt_activity_locations')->result();
          foreach($res as $r){
            $locInfo = pt_LocationsInfo($r->location_id);
            $result[$r->position] = (object)array('id' => $r->location_id,'name' => $locInfo->city.", ".$locInfo->country);
          }
         return $result;

        }

}
