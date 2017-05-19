<?php

class Restaurant_model extends CI_Model {
        public $langdef;
		function __construct() {
// Call the Model constructor
				parent :: __construct();
                $this->langdef = DEFLANG;
		}

// Get all enabled restaurant short info
		function shortInfo($id = null) {
				$result = array();
				$this->db->select('restaurant_id,restaurant_title,restaurant_slug');
				if (!empty ($id)) {
						$this->db->where('restaurant_owned_by', $id);
				}
				$this->db->where('restaurant_status', 'Yes');
				$this->db->order_by('restaurant_id', 'desc');
				$restaurant = $this->db->get('pt_restaurant')->result();
				foreach($restaurant as $restaurant){
					$result[] = (object)array('id' => $restaurant->restaurant_id, 'title' => $restaurant->restaurant_title, 'slug' => $restaurant->restaurant_slug);
				}

				return $result;
		}

// Get all restaurant id and names only
		function all_restaurant_names($id = null) {
				$this->db->select('restaurant_id,restaurant_title');
				if (!empty ($id)) {
						$this->db->where('restaurant_owned_by', $id);
				}
				$this->db->order_by('restaurant_id', 'desc');
				return $this->db->get('pt_restaurant')->result();
		}

		// Get all restaurant for extras
		function all_restaurant($id = null) {
				$this->db->select('restaurant_id as id,restaurant_title as title');
				if (!empty ($id)) {
						$this->db->where('restaurant_owned_by', $id);
				}
				$this->db->order_by('restaurant_id', 'desc');
				return $this->db->get('pt_restaurant')->result();
		}

		function convert_price($amount) {

		}

// get latest restaurant
		function latest_restaurant_front() {
				$settings = $this->Settings_model->get_front_settings('restaurant');
				$limit = $settings[0]->front_latest;
				$this->db->select('pt_restaurant.restaurant_status,pt_restaurant.restaurant_basic_price,pt_restaurant.restaurant_basic_discount,pt_restaurant.restaurant_id,pt_restaurant.restaurant_desc,pt_restaurant.restaurant_title,pt_restaurant.restaurant_slug,pt_restaurant.restaurant_type,pt_restaurant_types_settings.sett_name');
				$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				$this->db->where('pt_restaurant.restaurant_status', 'Yes');
				$this->db->join('pt_restaurant_types_settings', 'pt_restaurant.restaurant_type = pt_restaurant_types_settings.sett_id', 'left');
				$this->db->limit($limit);
				return $this->db->get('pt_restaurant')->result();
		}

// get all data of single restaurant by slug
		function get_restaurant_data($restaurantname) {
				$this->db->select('pt_restaurant.*');
				$this->db->where('pt_restaurant.restaurant_slug', $restaurantname);

				return $this->db->get('pt_restaurant')->result();
		}

// get all restaurant info
		function get_all_restaurant_back($id = null) {
				$this->db->select('pt_restaurant.restaurant_featured_forever,pt_restaurant.restaurant_id,pt_restaurant.restaurant_title,pt_restaurant.restaurant_slug,pt_restaurant.restaurant_owned_by,pt_restaurant.restaurant_order,pt_restaurant.restaurant_status,pt_restaurant.restaurant_is_featured,
    pt_restaurant.restaurant_featured_from,pt_restaurant.restaurant_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_restaurant_types_settings.sett_name');
// $this->db->where('pt_restaurant_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_restaurant.restaurant_owned_by', $id);
				}
				$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				$this->db->join('pt_accounts', 'pt_restaurant.restaurant_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_restaurant_images','pt_restaurant.restaurant_id = pt_restaurant_images.timg_restaurant_id','left');
				$query = $this->db->get('pt_restaurant');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all restaurant info with limit
		function get_all_restaurant_back_limit($id = null, $perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_restaurant.restaurant_featured_forever,pt_restaurant.restaurant_id,pt_restaurant.restaurant_title,pt_restaurant.restaurant_slug,pt_restaurant.restaurant_created_at,pt_restaurant.restaurant_owned_by,pt_restaurant.restaurant_order,pt_restaurant.restaurant_status,pt_restaurant.restaurant_is_featured,
    pt_restaurant.restaurant_featured_from,pt_restaurant.restaurant_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_restaurant_types_settings.sett_name');
// $this->db->where('pt_restaurant_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_restaurant.restaurant_owned_by', $id);
				}
				$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				$this->db->join('pt_accounts', 'pt_restaurant.restaurant_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_restaurant_images','pt_restaurant.restaurant_id = pt_restaurant_images.timg_restaurant_id','left');
				$query = $this->db->get('pt_restaurant', $perpage, $offset);
				$data['all'] = $query->result();
				return $data;
		}

// add restaurant data
		function add_restaurant($user = null) {
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

                $this->db->select("restaurant_id");
				$this->db->order_by("restaurant_id", "desc");
				$query = $this->db->get('pt_restaurant');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$restaurantlastid = 1;
				}
				else {
						$restaurantlastid = $lastid[0]->restaurant_id + 1;
				}

				$restaurantcount = $query->num_rows();
				$restaurantorder = $restaurantcount + 1;
				$this->db->select("restaurant_id");
				$this->db->where("restaurant_title", $this->input->post('restaurantname'));
				$queryc = $this->db->get('pt_restaurant')->num_rows();
				if ($queryc > 0) {
						$restaurantlug = create_url_slug($this->input->post('restaurantname')) . "-" . $restaurantlastid;
				}
				else {
						$restaurantlug = create_url_slug($this->input->post('restaurantname'));
				}
				$amenities = @ implode(",", $this->input->post('restaurantamenities'));
				$exclusions = @ implode(",", $this->input->post('restaurantexclusions'));
				$paymentopt = @ implode(",", $this->input->post('restaurantpayments'));
				$relatedrestaurant = @ implode(",", $this->input->post('relatedrestaurant'));

        // Set  Related Products
				$relatedProdActivity = @ implode(",", $this->input->post('relatedProdActivity'));
				$relatedProdHotels = @ implode(",", $this->input->post('relatedProdHotels'));
				$relatedProdTours = @ implode(",", $this->input->post('relatedProdTours'));
				$relatedProdWedding = @ implode(",", $this->input->post('relatedProdWedding'));
				$relatedProdCars = @ implode(",", $this->input->post('relatedProdCars'));
				$relatedProdSpa = @ implode(",", $this->input->post('relatedProdSpa'));
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
				$restaurantLocation = $location[0];

				$stars = $this->input->post('restauranttars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('restaurant_title' => $this->input->post('restaurantname'),
					'restaurant_slug' => $restaurantlug, 'restaurant_desc' => $this->input->post('restaurantdesc'),
					'restaurant_stars' => intval($stars),
					'restaurant_is_featured' => $featured,



					'restaurant_featured_from' => convert_to_unix($ffrom),
					'restaurant_featured_to' => convert_to_unix($fto),
					'restaurant_owned_by' => $user,
					'restaurant_type' => $this->input->post('restauranttype'),
					'restaurant_location' => $restaurantLocation,
					'restaurant_latitude' => $this->input->post('latitude'),
					'restaurant_longitude' => $this->input->post('longitude'),
					'restaurant_mapaddress' => $this->input->post('restaurantmapaddress'),
	                //'restaurant_basic_price' => $this->input->post('basic'),
					//'restaurant_basic_discount' => $this->input->post('discount'),
					'restaurant_meta_title' => $this->input->post('restaurantmetatitle'),
					'restaurant_meta_keywords' => $this->input->post('restaurantkeywords'),
					'restaurant_meta_desc' => $this->input->post('restaurantmetadesc'), 'restaurant_amenities' => $amenities,
					'restaurant_exclusions' => $exclusions, 'restaurant_payment_opt' => $paymentopt,
					'restaurant_max_adults' => intval($this->input->post('maxadult')),
					'restaurant_max_child' => intval($this->input->post('maxchild')),
					'restaurant_max_infant' => intval($this->input->post('maxinfant')),
					'restaurant_adult_price' => floatval($this->input->post('adultprice')),
					'restaurant_child_price' => floatval($this->input->post('childprice')),
					'restaurant_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'restaurant_days' => intval($this->input->post('restaurantdays')),
					'restaurant_nights' => intval($this->input->post('restaurantnights')),
					'restaurant_privacy' => $this->input->post('restaurantprivacy'),
					'restaurant_status' => $this->input->post('restauranttatus'),
					'restaurant_related' => $relatedrestaurant,
          /* product_related to do Save */
'product_related_activity' =>$relatedProdActivity,
'product_related_hotels' =>$relatedProdHotels,
'product_related_wedding' =>$relatedProdWedding,
'product_related_tours' => $relatedProdTours,
'product_related_spa' => $relatedProdSpa,
'product_related_cars' => $relatedProdCars,
/* product_related */


          'restaurant_order' => $restaurantorder,
					'restaurant_comm_fixed' => $commfixed, 'restaurant_comm_percentage' => $commper,
					'restaurant_tax_fixed' => $taxfixed, 'restaurant_tax_percentage' => $taxper,
					'restaurant_email' => $this->input->post('restaurantemail'),
					'restaurant_phone' => $this->input->post('restaurantphone'),
					'restaurant_website' => $this->input->post('restaurantwebsite'),
					'restaurant_fulladdress' => $this->input->post('restaurantfulladdress'),
					'restaurant_featured_forever' => $isforever,
					'restaurant_created_at' => time());
				$this->db->insert('pt_restaurant', $data);
				$restaurantid = $this->db->insert_id();
				$this->updateRestaurantLocations($this->input->post('locations'), $restaurantid);
				return $restaurantid;
		}

// update restaurant data
		function update_restaurant($id) {

				$restaurantcomm = $this->input->post('deposit');
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


				$this->db->select("restaurant_id");
				$this->db->where("restaurant_id !=", $id);
				$this->db->where("restaurant_title", $this->input->post('restaurantname'));
				$queryc = $this->db->get('pt_restaurant')->num_rows();
				if ($queryc > 0) {
						$restaurantlug = create_url_slug($this->input->post('restaurantname')) . "-" . $id;
				}
				else {
						$restaurantlug = create_url_slug($this->input->post('restaurantname'));
				}
				$amenities = @ implode(",", $this->input->post('restaurantamenities'));
				$exclusions = @ implode(",", $this->input->post('restaurantexclusions'));
				$paymentopt = @ implode(",", $this->input->post('restaurantpayments'));
				$relatedrestaurant = @ implode(",", $this->input->post('relatedrestaurant'));

        // Set  Related Products
				$relatedProdActivity = @ implode(",", $this->input->post('relatedProdActivity'));
				$relatedProdHotels = @ implode(",", $this->input->post('relatedProdHotels'));
				$relatedProdTours = @ implode(",", $this->input->post('relatedProdTours'));
				$relatedProdWedding = @ implode(",", $this->input->post('relatedProdWedding'));
				$relatedProdCars = @ implode(",", $this->input->post('relatedProdCars'));
				$relatedProdSpa = @ implode(",", $this->input->post('relatedProdSpa'));
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
				$restaurantLocation = $location[0];

				$stars = $this->input->post('restauranttars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('restaurant_title' => $this->input->post('restaurantname'),
					'restaurant_slug' => $restaurantlug, 'restaurant_desc' => $this->input->post('restaurantdesc'),
					'restaurant_stars' => intval($stars),
					'restaurant_is_featured' => $featured,
					'restaurant_featured_from' => convert_to_unix($ffrom),
					'restaurant_featured_to' => convert_to_unix($fto),
					'restaurant_type' => $this->input->post('restauranttype'),
					'restaurant_location' => $restaurantLocation,
					'restaurant_latitude' => $this->input->post('latitude'),
					'restaurant_longitude' => $this->input->post('longitude'),
					'restaurant_mapaddress' => $this->input->post('restaurantmapaddress'),
	                //'restaurant_basic_price' => $this->input->post('basic'),
					//'restaurant_basic_discount' => $this->input->post('discount'),
					'restaurant_meta_title' => $this->input->post('restaurantmetatitle'),
					'restaurant_meta_keywords' => $this->input->post('restaurantkeywords'),
					'restaurant_meta_desc' => $this->input->post('restaurantmetadesc'), 'restaurant_amenities' => $amenities,
					'restaurant_exclusions' => $exclusions, 'restaurant_payment_opt' => $paymentopt,
					'restaurant_max_adults' => intval($this->input->post('maxadult')),
					'restaurant_max_child' => intval($this->input->post('maxchild')),
					'restaurant_max_infant' => intval($this->input->post('maxinfant')),
					'restaurant_adult_price' => floatval($this->input->post('adultprice')),
					'restaurant_child_price' => floatval($this->input->post('childprice')),
					'restaurant_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'restaurant_days' => intval($this->input->post('restaurantdays')),
					'restaurant_nights' => intval($this->input->post('restaurantnights')),
					'restaurant_privacy' => $this->input->post('restaurantprivacy'),
					'restaurant_status' => $this->input->post('restauranttatus'),
					'restaurant_related' => $relatedrestaurant,

          /* product_related to do Save */
'product_related_activity' =>$relatedProdActivity,
'product_related_hotels' =>$relatedProdHotels,
'product_related_wedding' =>$relatedProdWedding,
'product_related_tours' => $relatedProdTours,
'product_related_spa' => $relatedProdSpa,
'product_related_cars' => $relatedProdCars,
/* product_related */


					'restaurant_comm_fixed' => $commfixed, 'restaurant_comm_percentage' => $commper,
					'restaurant_tax_fixed' => $taxfixed, 'restaurant_tax_percentage' => $taxper,
					'restaurant_email' => $this->input->post('restaurantemail'),
					'restaurant_phone' => $this->input->post('restaurantphone'),
					'restaurant_website' => $this->input->post('restaurantwebsite'),
					'restaurant_fulladdress' => $this->input->post('restaurantfulladdress'),
					'restaurant_featured_forever' => $isforever);
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);

				$this->updateRestaurantLocations($this->input->post('locations'), $id);
	}

// Add restaurant settings data
		function add_settings_data() {
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
				$this->db->insert('pt_restaurant_types_settings', $data);
		}

// update restaurant settings data
		function update_settings_data() {
				$id = $this->input->post('id');
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
				$this->db->where('sett_id', $id);
				$this->db->update('pt_restaurant_types_settings', $data);
		}

// Disable restaurant settings
		function disable_settings($id) {
				$data = array('sett_status' => 'No');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_restaurant_types_settings', $data);
		}

// Enable restaurant settings
		function enable_settings($id) {
				$data = array('sett_status' => 'Yes');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_restaurant_types_settings', $data);
		}

// Delete restaurant settings
		function delete_settings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_restaurant_types_settings');
		}

// get all restaurant for related selection for backend
		function select_related_restaurant($id = null) {
				$this->db->select('restaurant_title,restaurant_id');
				if (!empty ($id)) {
						$this->db->where('restaurant_id !=', $id);
				}
				return $this->db->get('pt_restaurant')->result();
		}

// Get restaurant settings data
		function get_restaurant_settings_data($type) {
			if(!empty($type)){
             	$this->db->where('sett_type', $type);
		  }

				$this->db->order_by('sett_id', 'desc');
				return $this->db->get('pt_restaurant_types_settings')->result();
		}

// Get restaurant settings data for adding restaurant
		function get_tsettings_data($type) {
				$this->db->where('sett_type', $type);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_restaurant_types_settings')->result();
		}

// Get restaurant settings data for adding restaurant
		function get_tsettings_data_front($type, $items) {
				$this->db->where('sett_type', $type);
				$this->db->where_in('sett_id', $items);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_restaurant_types_settings')->result();
		}

// add Restaurant images by type
		function add_restaurant_image($type, $filename, $restaurantid) {
				$imgorder = 0;
				if ($type == "slider") {
						$this->db->where('timg_type', 'slider');
						$this->db->where('timg_restaurant_id', $restaurantid);
						$imgorder = $this->db->get('pt_restaurant_images')->num_rows();
						$imgorder = $imgorder + 1;
				}
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_restaurant_id', $restaurantid);
				$hasdefault = $this->db->get('pt_restaurant_images')->num_rows();
				if ($hasdefault < 1) {
						$type = 'default';
				}
				$approval = pt_admin_gallery_approve();
				$data = array('timg_restaurant_id' => $restaurantid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
				$this->db->insert('pt_restaurant_images', $data);
		}

// update restaurant map order
		function update_map_order($id, $order) {
				$data = array('map_order' => $order);
				$this->db->where('map_id', $id);
				$this->db->update('pt_restaurant_maps', $data);
		}


// update restaurant order
		function update_restaurant_order($id, $order) {
				$data = array('restaurant_order' => $order);
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);
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



			 $data = array('restaurant_is_featured' => $isfeatured, 'restaurant_featured_forever' => $isforever);
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);
		}
// Disable Restaurant

		public function disable_restaurant($id) {
				$data = array('restaurant_status' => 'No');
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);
		}
// Enable Restaurant

		public function enable_restaurant($id) {
				$data = array('restaurant_status' => 'Yes');
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);
		}

// Delete restaurant
		function delete_restaurant($restaurantid) {
				$restaurantimages = $this->restaurant_images($restaurantid);
				foreach ($restaurantimages['all_slider'] as $sliderimg) {
						$this->delete_image($sliderimg->timg_image,$sliderimg->timg_id,$restaurantid);
				}


				$this->db->where('review_itemid', $restaurantid);
				$this->db->where('review_module', 'restaurant');
				$this->db->delete('pt_reviews');
				$this->db->where('map_restaurant_id', $restaurantid);
				$this->db->delete('pt_restaurant_maps');

				$this->db->where('item_id', $restaurantid);
                $this->db->delete('pt_restaurant_translation');

                $this->db->where('restaurant_id',$restaurantid);
            	$this->db->delete('pt_restaurant_locations');

				$this->db->where('restaurant_id', $restaurantid);
				$this->db->delete('pt_restaurant');
		}

// Get Restaurant Images
		function restaurant_images($id) {
				$this->db->where('timg_restaurant_id', $id);
				$this->db->where('timg_type', 'default');
				$q = $this->db->get('pt_restaurant_images');
				$data['def_image'] = $q->result();
				$this->db->where('timg_type', 'slider');
				$this->db->order_by('timg_id', 'desc');
				$this->db->having('timg_restaurant_id', $id);
				$q = $this->db->get('pt_restaurant_images');
				$data['all_slider'] = $q->result();
				$data['slider_counts'] = $q->num_rows();
				return $data;
		}

//update restaurant thumbnail
		function update_thumb($oldthumb, $newthumb, $restaurantid) {
				$data = array('timg_type' => 'slider');
				$this->db->where('timg_id', $oldthumb);
				$this->db->where('timg_restaurant_id', $restaurantid);
				$this->db->update('pt_restaurant_images', $data);
				$data2 = array('timg_type' => 'default');
				$this->db->where('timg_id', $newthumb);
				$this->db->where('timg_restaurant_id', $restaurantid);
				$this->db->update('pt_restaurant_images', $data2);
		}

// Approve or reject Hotel Images
		function approve_reject_images() {
				$data = array('timg_approved' => $this->input->post('apprej'));
				$this->db->where('timg_id', $this->input->post('imgid'));
				$this->db->update('pt_restaurant_images', $data);
		}

// update image order
		function update_image_order($imgid, $order) {
				$data = array('timg_order' => $order);
				$this->db->where('timg_id', $imgid);
				$this->db->update('pt_restaurant_images', $data);
		}


// Delete restaurant Images
		function delete_image($imgname, $imgid, $restaurantid) {
				$this->db->where('timg_id', $imgid);
				$this->db->delete('pt_restaurant_images');
                $this->updateRestaurantThumb($restaurantid,$imgname,"delete");
                @ unlink(PT_RESTAURANT_SLIDER_THUMB_UPLOAD . $imgname);
				@ unlink(PT_RESTAURANT_SLIDER_UPLOAD . $imgname);
		}

//update restaurant thumbnail
		function updateRestaurantThumb($restaurantid,$imgname,$action) {
		  if($action == "delete"){
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image',$imgname);
            $this->db->where('restaurant_id',$restaurantid);
            $rs = $this->db->get('pt_restaurant')->num_rows();
            if($rs > 0){
              $data = array(
              'thumbnail_image' => PT_BLANK_IMG
              );
              $this->db->where('restaurant_id',$restaurantid);
              $this->db->update('pt_restaurant',$data);
            }
            }else{
              $data = array(
              'thumbnail_image' => $imgname
              );
              $this->db->where('restaurant_id',$restaurantid);
              $this->db->update('pt_restaurant',$data);
            }

		}




		function offers_data($id) {
				/*$this->db->where('offer_module', 'restaurant');
				$this->db->where('offer_item', $id);
				return $this->db->get('pt_special_offers')->result();*/
		}

		function add_to_map() {
				$maporder = 0;
				$restaurantid = $this->input->post('restaurantid');
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_restaurant_id', $restaurantid);
				$res = $this->db->get('pt_restaurant_maps')->num_rows();
				$addtype = $this->input->post('addtype');
				if ($addtype == "visit") {
						$maporder = $res + 1;
				}
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_restaurant_id' => $restaurantid, 'map_order' => $maporder);
				$this->db->insert('pt_restaurant_maps', $data);
		}

		function update_restaurant_map() {
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
				$this->db->where('map_id', $this->input->post('mapid'));
				$this->db->update('pt_restaurant_maps', $data);
		}

		function has_start_end_city($type, $restaurantid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', $type);
				$this->db->where('map_restaurant_id', $restaurantid);
				$nums = $this->db->get('pt_restaurant_maps')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

		function get_restaurant_map($restaurantid) {
				$this->db->where('map_restaurant_id', $restaurantid);
				return $this->db->get('pt_restaurant_maps')->result();
		}

		function delete_map_item($mapid) {
				$this->db->where('map_id', $mapid);
				$this->db->delete('pt_restaurant_maps');
		}

// get related restaurant for front-end
		function get_related_restaurant($restaurant) {
				$id = explode(",", $restaurant);
				$this->db->select('pt_restaurant.restaurant_title,pt_restaurant.restaurant_slug,pt_restaurant.restaurant_id,pt_restaurant.restaurant_basic_price,pt_restaurant.restaurant_basic_discount,pt_restaurant_types_settings.sett_name');
				$this->db->where_in('pt_restaurant.restaurant_id', $id);
/*  $this->db->where('pt_restaurant_images.timg_type','default');
$this->db->join('pt_restaurant_images','pt_restaurant.restaurant_id = pt_restaurant_images.timg_restaurant_id','left');*/
				$this->db->join('pt_restaurant_types_settings', 'pt_restaurant.restaurant_type = pt_restaurant_types_settings.sett_id', 'left');
				return $this->db->get('pt_restaurant')->result();
		}

// Check restaurant existence
		function restaurant_exists($slug) {
				$this->db->select('restaurant_id');
				$this->db->where('restaurant_slug', $slug);
				$this->db->where('restaurant_status', 'Yes');
				$nums = $this->db->get('pt_restaurant')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

// List all restaurant on front listings page
		function list_restaurant_front($sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_restaurant.restaurant_order', 'asc');
				}
				$this->db->select('restaurant_id');
				$this->db->group_by('restaurant_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_restaurant.restaurant_adult_price >=', $minp);
						$this->db->where('pt_restaurant.restaurant_adult_price <=', $maxp);
				}

				$this->db->where('restaurant_status', 'Yes');
				$query = $this->db->get('pt_restaurant', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all restaurant on front listings page by location
		function showRestaurantByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_restaurant.restaurant_order', 'asc');
				}
				$this->db->select('restaurant_id');
				$this->db->group_by('restaurant_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_restaurant.restaurant_adult_price >=', $minp);
						$this->db->where('pt_restaurant.restaurant_adult_price <=', $maxp);
				}

				if(is_array($locs)){
                $this->db->where_in('pt_restaurant.restaurant_location',$locs);
                }else{
                $this->db->where('pt_restaurant.restaurant_location',$locs);
                }

				$this->db->where('restaurant_status', 'Yes');
				$query = $this->db->get('pt_restaurant', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// Search restaurant from home page
		function search_restaurant_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$this->load->helper('restaurant_front');
				$data = array();

				//$location = $this->input->get('location');

				$adults = $this->input->get('adults');
				$type = $this->input->get('type');

				//$sprice = $this->input->get('price');
				$stars = $this->input->get('stars');

				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_restaurant.restaurant_id,restaurant_type,restaurant_location,restaurant_adult_price,restaurant_title,restaurant_max_adults,restaurant_status,pt_restaurant_locations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_restaurant.restaurant_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_restaurant.restaurant_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_restaurant.restaurant_order', 'asc');
				}
				elseif ($orderby == "p_lh") {
						$this->db->order_by('pt_restaurant.restaurant_adult_price', 'asc');
				}
				elseif ($orderby == "p_hl") {
						$this->db->order_by('pt_restaurant.restaurant_adult_price', 'desc');
				}

				if(!empty($location)){
					//$this->db->like('pt_restaurant.restaurant_location', $location);
					$this->db->where('pt_restaurant_locations.location_id', $location);

				}


				if (!empty ($adults)) {
						$this->db->where('pt_restaurant.restaurant_max_adults >=', $adults);
				}

				if (!empty ($stars)) {
						$this->db->where('restaurant_stars', $stars);
				}



				if (!empty ($type)) {
						$this->db->where('pt_restaurant.restaurant_type', $type);
				}

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_restaurant.restaurant_adult_price >=', $minp);
						$this->db->where('pt_restaurant.restaurant_adult_price <=', $maxp);
				}
				$this->db->group_by('pt_restaurant.restaurant_id');
				$this->db->join('pt_restaurant_locations', 'pt_restaurant.restaurant_id = pt_restaurant_locations.restaurant_id');
				$this->db->where('pt_restaurant.restaurant_status', 'Yes');


		if(!empty($perpage)){

				$query = $this->db->get('pt_restaurant', $perpage, $offset);

				}else{

				$query = $this->db->get('pt_restaurant');

				}

				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

		function max_map_order($restaurantid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_restaurant_id', $restaurantid);
				return $this->db->get('pt_restaurant_maps')->num_rows();
		}

// get default image of restaurant
		function default_restaurant_img($id) {
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_approved', '1');
				$this->db->where('timg_restaurant_id', $id);
				$res = $this->db->get('pt_restaurant_images')->result();
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
				$this->db->where('restaurant_slug', $cslug);
				$this->db->where('restaurant_id !=', $id);
				$nums = $this->db->get('pt_restaurant')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('restaurant_title' => $this->input->post('title'), 'restaurant_slug' => $cslug, 'restaurant_desc' => $this->input->post('desc'), 'restaurant_policy' => $this->input->post('policy'));
				$this->db->where('restaurant_id', $id);
				$this->db->update('pt_restaurant', $data);
				return $cslug;
		}

// Adds translation of some fields data
		function add_translation($postdata, $restaurantid) {
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
                'item_id' => $restaurantid,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_restaurant_translation', $data);
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
				$this->db->insert('pt_restaurant_translation', $data);

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
			    $this->db->update('pt_restaurant_translation', $data);
                }


              }

                }

		}

		 function getBackTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('item_id',$id);
            return $this->db->get('pt_restaurant_translation')->result();

        }

         function restaurantGallery($slug){

          $this->db->select('pt_restaurant.thumbnail_image as thumbnail,pt_restaurant_images.timg_id as id,pt_restaurant_images.timg_restaurant_id as itemid,pt_restaurant_images.timg_type as type,pt_restaurant_images.timg_image as image,pt_restaurant_images.timg_order as imgorder,pt_restaurant_images.timg_image as image,pt_restaurant_images.timg_approved as approved');
          $this->db->where('pt_restaurant.restaurant_slug',$slug);
          $this->db->join('pt_restaurant_images', 'pt_restaurant.restaurant_id = pt_restaurant_images.timg_restaurant_id', 'left');
          $this->db->order_by('pt_restaurant_images.timg_id','desc');
          return $this->db->get('pt_restaurant')->result();

        }

        function addPhotos($id,$filename){

         $this->db->select('thumbnail_image');
         $this->db->where('restaurant_id',$id);
         $rs = $this->db->get('pt_restaurant')->result();
         if($rs[0]->thumbnail_image == PT_BLANK_IMG){

               $data = array('thumbnail_image' => $filename);
               $this->db->where('restaurant_id',$id);
               $this->db->update('pt_restaurant',$data);
         }

        //add photos to restaurant images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_restaurant_id', $id);
        $imgorder = $this->db->get('pt_restaurant_images')->num_rows();
        $imgorder = $imgorder + 1;

				$approval = pt_admin_gallery_approve();

		    	$insdata = array(
                'timg_restaurant_id' => $id,
                'timg_type' => 'slider',
                'timg_image' => $filename,
                'timg_order' => $imgorder,
                'timg_approved' => $approval
                );

				$this->db->insert('pt_restaurant_images', $insdata);


        }

        function assignRestaurant($restaurant,$userid){

          if(!empty($restaurant)){
          $userrestaurant = $this->userOwnedRestaurant($userid);
                foreach($userrestaurant as $tt){
                   if(!in_array($tt,$restaurant)){
                    $ddata = array(
                   'restaurant_owned_by' => '1'
                   );
                   $this->db->where('restaurant_id',$tt);
                   $this->db->update('pt_restaurant',$ddata);
                   }
                }

                foreach($restaurant as $t){
                   $data = array(
                   'restaurant_owned_by' => $userid
                   );
                   $this->db->where('restaurant_id',$t);
                   $this->db->update('pt_restaurant',$data);

                 }

                 }
        }

        function userOwnedRestaurant($id){
          $result = array();
          if(!empty($id)){
          $this->db->where('restaurant_owned_by',$id);
          }

          $rs = $this->db->get('pt_restaurant')->result();
          if(!empty($rs)){
            foreach($rs as $r){
              $result[] = $r->restaurant_id;
            }
          }
          return $result;
        }

        // get number of photos of restaurant
		function photos_count($restaurantid) {
				$this->db->where('timg_restaurant_id', $restaurantid);
				return $this->db->get('pt_restaurant_images')->num_rows();
		}

		function updateRestaurantSettings() {
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

		// get popular restaurant
		function popular_restaurant_front() {
				$settings = $this->Settings_model->get_front_settings('restaurant');
				$limit = $settings[0]->front_popular;
				$orderby = $settings[0]->front_popular_order;

                $this->db->select('pt_restaurant.restaurant_id,pt_restaurant.restaurant_status,pt_reviews.review_overall,pt_reviews.review_itemid');

                $this->db->select_avg('pt_reviews.review_overall', 'overall');
				$this->db->order_by('overall', 'desc');
				$this->db->group_by('pt_restaurant.restaurant_id');
				$this->db->join('pt_reviews', 'pt_restaurant.restaurant_id = pt_reviews.review_itemid');
				$this->db->where('restaurant_status', 'yes');
				$this->db->limit($limit);
			   	return $this->db->get('pt_restaurant')->result();
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
				$this->db->insert('pt_restaurant_types_settings', $data);
                return $this->db->insert_id();
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");

		}

// update restaurant settings data
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
				$this->db->update('pt_restaurant_types_settings', $data);
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
				$this->db->insert('pt_restaurant_types_settings_translation', $data);

                }else{

                 $data = array(
                'trans_name' => $name
                );
				$this->db->where('sett_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_restaurant_types_settings_translation', $data);

              }


              }

                }
		}


         function getBackSettingsTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_restaurant_types_settings_translation')->result();

        }

        // Delete hotel settings
		function deleteTypeSettings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_restaurant_types_settings');

                $this->db->where('sett_id', $id);
				$this->db->delete('pt_restaurant_types_settings_translation');
		}

				// Delete multiple restaurant settings
		function deleteMultiplesettings($id, $type) {
				$this->db->where('sett_id', $id);
				$this->db->where('sett_type',$type);
				$this->db->delete('pt_restaurant_types_settings');

				$rowsDeleted = $this->db->affected_rows();

				if($rowsDeleted > 0){
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_restaurant_types_settings_translation');
				}

		}

         function getTypesTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_restaurant_types_settings_translation')->result();

        }

        function updateRestaurantLocations($locations, $restaurantid){

        	$this->db->where('restaurant_id',$restaurantid);
        	$this->db->delete('pt_restaurant_locations');
        	$position = 0;

        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$position++;
        			$data = array('position' => $position,'location_id' => $loc, 'restaurant_id' => $restaurantid);
        			$this->db->insert('pt_restaurant_locations', $data);
        		}
        	}

        }

        function isRestaurantLocation($i, $locid, $restaurantid){
        	$this->db->where('position', $i);
        	$this->db->where('location_id', $locid);
        	$this->db->where('restaurant_id', $restaurantid);
        	$rs = $this->db->get('pt_restaurant_locations')->num_rows();
        	if($rs > 0){
        		return "selected";
        	}else{
        		return "";
        	}
        }

        function restaurantSelectedLocations($restaurantid){
          $result = array();
          $this->db->where('restaurant_id', $restaurantid);
          $res = $this->db->get('pt_restaurant_locations')->result();
          foreach($res as $r){
            $locInfo = pt_LocationsInfo($r->location_id);
            $result[$r->position] = (object)array('id' => $r->location_id,'name' => $locInfo->city.", ".$locInfo->country);
          }
         return $result;

        }

}
