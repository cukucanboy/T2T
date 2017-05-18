<?php

class Entertainment_model extends CI_Model {
        public $langdef;
		function __construct() {
// Call the Model constructor
				parent :: __construct();
                $this->langdef = DEFLANG;
		}

// Get all enabled entertainment short info
		function shortInfo($id = null) {
				$result = array();
				$this->db->select('entertainment_id,entertainment_title,entertainment_slug');
				if (!empty ($id)) {
						$this->db->where('entertainment_owned_by', $id);
				}
				$this->db->where('entertainment_status', 'Yes');
				$this->db->order_by('entertainment_id', 'desc');
				$entertainment = $this->db->get('pt_entertainment')->result();
				foreach($entertainment as $entertainment){
					$result[] = (object)array('id' => $entertainment->entertainment_id, 'title' => $entertainment->entertainment_title, 'slug' => $entertainment->entertainment_slug);
				}

				return $result;
		}

// Get all entertainment id and names only
		function all_entertainment_names($id = null) {
				$this->db->select('entertainment_id,entertainment_title');
				if (!empty ($id)) {
						$this->db->where('entertainment_owned_by', $id);
				}
				$this->db->order_by('entertainment_id', 'desc');
				return $this->db->get('pt_entertainment')->result();
		}

		// Get all entertainment for extras
		function all_entertainment($id = null) {
				$this->db->select('entertainment_id as id,entertainment_title as title');
				if (!empty ($id)) {
						$this->db->where('entertainment_owned_by', $id);
				}
				$this->db->order_by('entertainment_id', 'desc');
				return $this->db->get('pt_entertainment')->result();
		}

		function convert_price($amount) {

		}

// get latest entertainment
		function latest_entertainment_front() {
				$settings = $this->Settings_model->get_front_settings('entertainment');
				$limit = $settings[0]->front_latest;
				$this->db->select('pt_entertainment.entertainment_status,pt_entertainment.entertainment_basic_price,pt_entertainment.entertainment_basic_discount,pt_entertainment.entertainment_id,pt_entertainment.entertainment_desc,pt_entertainment.entertainment_title,pt_entertainment.entertainment_slug,pt_entertainment.entertainment_type,pt_entertainment_types_settings.sett_name');
				$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				$this->db->where('pt_entertainment.entertainment_status', 'Yes');
				$this->db->join('pt_entertainment_types_settings', 'pt_entertainment.entertainment_type = pt_entertainment_types_settings.sett_id', 'left');
				$this->db->limit($limit);
				return $this->db->get('pt_entertainment')->result();
		}

// get all data of single entertainment by slug
		function get_entertainment_data($entertainmentname) {
				$this->db->select('pt_entertainment.*');
				$this->db->where('pt_entertainment.entertainment_slug', $entertainmentname);

				return $this->db->get('pt_entertainment')->result();
		}

// get all entertainment info
		function get_all_entertainment_back($id = null) {
				$this->db->select('pt_entertainment.entertainment_featured_forever,pt_entertainment.entertainment_id,pt_entertainment.entertainment_title,pt_entertainment.entertainment_slug,pt_entertainment.entertainment_owned_by,pt_entertainment.entertainment_order,pt_entertainment.entertainment_status,pt_entertainment.entertainment_is_featured,
    pt_entertainment.entertainment_featured_from,pt_entertainment.entertainment_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_entertainment_types_settings.sett_name');
// $this->db->where('pt_entertainment_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_entertainment.entertainment_owned_by', $id);
				}
				$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				$this->db->join('pt_accounts', 'pt_entertainment.entertainment_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_entertainment_images','pt_entertainment.entertainment_id = pt_entertainment_images.timg_entertainment_id','left');
				$query = $this->db->get('pt_entertainment');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all entertainment info with limit
		function get_all_entertainment_back_limit($id = null, $perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_entertainment.entertainment_featured_forever,pt_entertainment.entertainment_id,pt_entertainment.entertainment_title,pt_entertainment.entertainment_slug,pt_entertainment.entertainment_created_at,pt_entertainment.entertainment_owned_by,pt_entertainment.entertainment_order,pt_entertainment.entertainment_status,pt_entertainment.entertainment_is_featured,
    pt_entertainment.entertainment_featured_from,pt_entertainment.entertainment_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_entertainment_types_settings.sett_name');
// $this->db->where('pt_entertainment_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_entertainment.entertainment_owned_by', $id);
				}
				$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				$this->db->join('pt_accounts', 'pt_entertainment.entertainment_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_entertainment_images','pt_entertainment.entertainment_id = pt_entertainment_images.timg_entertainment_id','left');
				$query = $this->db->get('pt_entertainment', $perpage, $offset);
				$data['all'] = $query->result();
				return $data;
		}

// add entertainment data
		function add_entertainment($user = null) {
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

                $this->db->select("entertainment_id");
				$this->db->order_by("entertainment_id", "desc");
				$query = $this->db->get('pt_entertainment');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$entertainmentlastid = 1;
				}
				else {
						$entertainmentlastid = $lastid[0]->entertainment_id + 1;
				}

				$entertainmentcount = $query->num_rows();
				$entertainmentorder = $entertainmentcount + 1;
				$this->db->select("entertainment_id");
				$this->db->where("entertainment_title", $this->input->post('entertainmentname'));
				$queryc = $this->db->get('pt_entertainment')->num_rows();
				if ($queryc > 0) {
						$entertainmentlug = create_url_slug($this->input->post('entertainmentname')) . "-" . $entertainmentlastid;
				}
				else {
						$entertainmentlug = create_url_slug($this->input->post('entertainmentname'));
				}
				$amenities = @ implode(",", $this->input->post('entertainmentamenities'));
				$exclusions = @ implode(",", $this->input->post('entertainmentexclusions'));
				$paymentopt = @ implode(",", $this->input->post('entertainmentpayments'));
				$relatedentertainment = @ implode(",", $this->input->post('relatedentertainment'));

        $nearbydata = $this->input->post('nearbyrelatedentertainment');
        if(!empty($nearbydata)){
          $checknearby = $this->convert_json(@ implode(",", $nearbydata));
          $nearbyrelatedentertainment = $checknearby;
        }else{
          $nearbyrelatedentertainment = '';
        }


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
				$entertainmentLocation = $location[0];

				$stars = $this->input->post('entertainmenttars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('entertainment_title' => $this->input->post('entertainmentname'),
					'entertainment_slug' => $entertainmentlug, 'entertainment_desc' => $this->input->post('entertainmentdesc'),
					'entertainment_stars' => intval($stars),
					'entertainment_is_featured' => $featured,
					'entertainment_featured_from' => convert_to_unix($ffrom),
					'entertainment_featured_to' => convert_to_unix($fto),
					'entertainment_owned_by' => $user,
					'entertainment_type' => $this->input->post('entertainmenttype'),
					'entertainment_location' => $entertainmentLocation,
					'entertainment_latitude' => $this->input->post('latitude'),
					'entertainment_longitude' => $this->input->post('longitude'),
					'entertainment_mapaddress' => $this->input->post('entertainmentmapaddress'),
	                //'entertainment_basic_price' => $this->input->post('basic'),
					//'entertainment_basic_discount' => $this->input->post('discount'),
					'entertainment_meta_title' => $this->input->post('entertainmentmetatitle'),
					'entertainment_meta_keywords' => $this->input->post('entertainmentkeywords'),
					'entertainment_meta_desc' => $this->input->post('entertainmentmetadesc'), 'entertainment_amenities' => $amenities,
					'entertainment_exclusions' => $exclusions, 'entertainment_payment_opt' => $paymentopt,
					'entertainment_max_adults' => intval($this->input->post('maxadult')),
					'entertainment_max_child' => intval($this->input->post('maxchild')),
					'entertainment_max_infant' => intval($this->input->post('maxinfant')),
					'entertainment_adult_price' => floatval($this->input->post('adultprice')),
					'entertainment_child_price' => floatval($this->input->post('childprice')),
					'entertainment_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'entertainment_days' => intval($this->input->post('entertainmentdays')),
					'entertainment_nights' => intval($this->input->post('entertainmentnights')),
					'entertainment_privacy' => $this->input->post('entertainmentprivacy'),
					'entertainment_status' => $this->input->post('entertainmenttatus'),
					'entertainment_related' => $relatedentertainment,
          'entertainment_nearby_related' => $nearbyrelatedentertainment,
          'entertainment_order' => $entertainmentorder,
					'entertainment_comm_fixed' => $commfixed, 'entertainment_comm_percentage' => $commper,
					'entertainment_tax_fixed' => $taxfixed, 'entertainment_tax_percentage' => $taxper,
					'entertainment_email' => $this->input->post('entertainmentemail'),
					'entertainment_phone' => $this->input->post('entertainmentphone'),
					'entertainment_website' => $this->input->post('entertainmentwebsite'),
					'entertainment_fulladdress' => $this->input->post('entertainmentfulladdress'),
					'entertainment_featured_forever' => $isforever,
					'entertainment_created_at' => time());
				$this->db->insert('pt_entertainment', $data);
				$entertainmentid = $this->db->insert_id();
				$this->updateEntertainmentLocations($this->input->post('locations'), $entertainmentid);
				return $entertainmentid;
		}

// update entertainment data
		function update_entertainment($id) {

				$entertainmentcomm = $this->input->post('deposit');
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


				$this->db->select("entertainment_id");
				$this->db->where("entertainment_id !=", $id);
				$this->db->where("entertainment_title", $this->input->post('entertainmentname'));
				$queryc = $this->db->get('pt_entertainment')->num_rows();
				if ($queryc > 0) {
						$entertainmentlug = create_url_slug($this->input->post('entertainmentname')) . "-" . $id;
				}
				else {
						$entertainmentlug = create_url_slug($this->input->post('entertainmentname'));
				}
				$amenities = @ implode(",", $this->input->post('entertainmentamenities'));
				$exclusions = @ implode(",", $this->input->post('entertainmentexclusions'));
				$paymentopt = @ implode(",", $this->input->post('entertainmentpayments'));
				$relatedentertainment = @ implode(",", $this->input->post('relatedentertainment'));

        $nearbydata = $this->input->post('relatedentertainment');
        if(!empty($nearbydata)){
          $nearbytmp = @ implode(",", $nearbydata);
          $nearbyrelatedentertainment = $this->convert_json($nearbytmp);
        }else{
          $nearbyrelatedentertainment = '';
        }

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
				$entertainmentLocation = $location[0];

				$stars = $this->input->post('entertainmenttars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('entertainment_title' => $this->input->post('entertainmentname'),
					'entertainment_slug' => $entertainmentlug, 'entertainment_desc' => $this->input->post('entertainmentdesc'),
					'entertainment_stars' => intval($stars),
					'entertainment_is_featured' => $featured,
					'entertainment_featured_from' => convert_to_unix($ffrom),
					'entertainment_featured_to' => convert_to_unix($fto),
					'entertainment_type' => $this->input->post('entertainmenttype'),
					'entertainment_location' => $entertainmentLocation,
					'entertainment_latitude' => $this->input->post('latitude'),
					'entertainment_longitude' => $this->input->post('longitude'),
					'entertainment_mapaddress' => $this->input->post('entertainmentmapaddress'),
	                //'entertainment_basic_price' => $this->input->post('basic'),
					//'entertainment_basic_discount' => $this->input->post('discount'),
					'entertainment_meta_title' => $this->input->post('entertainmentmetatitle'),
					'entertainment_meta_keywords' => $this->input->post('entertainmentkeywords'),
					'entertainment_meta_desc' => $this->input->post('entertainmentmetadesc'), 'entertainment_amenities' => $amenities,
					'entertainment_exclusions' => $exclusions, 'entertainment_payment_opt' => $paymentopt,
					'entertainment_max_adults' => intval($this->input->post('maxadult')),
					'entertainment_max_child' => intval($this->input->post('maxchild')),
					'entertainment_max_infant' => intval($this->input->post('maxinfant')),
					'entertainment_adult_price' => floatval($this->input->post('adultprice')),
					'entertainment_child_price' => floatval($this->input->post('childprice')),
					'entertainment_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'entertainment_days' => intval($this->input->post('entertainmentdays')),
					'entertainment_nights' => intval($this->input->post('entertainmentnights')),
					'entertainment_privacy' => $this->input->post('entertainmentprivacy'),
					'entertainment_status' => $this->input->post('entertainmenttatus'),
					'entertainment_related' => $relatedentertainment,
          'entertainment_nearby_related' => $nearbyrelatedentertainment,
					'entertainment_comm_fixed' => $commfixed, 'entertainment_comm_percentage' => $commper,
					'entertainment_tax_fixed' => $taxfixed, 'entertainment_tax_percentage' => $taxper,
					'entertainment_email' => $this->input->post('entertainmentemail'),
					'entertainment_phone' => $this->input->post('entertainmentphone'),
					'entertainment_website' => $this->input->post('entertainmentwebsite'),
					'entertainment_fulladdress' => $this->input->post('entertainmentfulladdress'),
					'entertainment_featured_forever' => $isforever);
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);

				$this->updateEntertainmentLocations($this->input->post('locations'), $id);
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

// Add entertainment settings data
		function add_settings_data() {
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
				$this->db->insert('pt_entertainment_types_settings', $data);
		}

// update entertainment settings data
		function update_settings_data() {
				$id = $this->input->post('id');
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
				$this->db->where('sett_id', $id);
				$this->db->update('pt_entertainment_types_settings', $data);
		}

// Disable entertainment settings
		function disable_settings($id) {
				$data = array('sett_status' => 'No');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_entertainment_types_settings', $data);
		}

// Enable entertainment settings
		function enable_settings($id) {
				$data = array('sett_status' => 'Yes');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_entertainment_types_settings', $data);
		}

// Delete entertainment settings
		function delete_settings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_entertainment_types_settings');
		}

// get all entertainment for related selection for backend
		function select_related_entertainment($id = null) {
				$this->db->select('entertainment_title,entertainment_id');
				if (!empty ($id)) {
						$this->db->where('entertainment_id !=', $id);
				}
				return $this->db->get('pt_entertainment')->result();
		}

// Get entertainment settings data
		function get_entertainment_settings_data($type) {
			if(!empty($type)){
             	$this->db->where('sett_type', $type);
		  }

				$this->db->order_by('sett_id', 'desc');
				return $this->db->get('pt_entertainment_types_settings')->result();
		}

// Get entertainment settings data for adding entertainment
		function get_tsettings_data($type) {
				$this->db->where('sett_type', $type);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_entertainment_types_settings')->result();
		}

// Get entertainment settings data for adding entertainment
		function get_tsettings_data_front($type, $items) {
				$this->db->where('sett_type', $type);
				$this->db->where_in('sett_id', $items);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_entertainment_types_settings')->result();
		}

// add Entertainment images by type
		function add_entertainment_image($type, $filename, $entertainmentid) {
				$imgorder = 0;
				if ($type == "slider") {
						$this->db->where('timg_type', 'slider');
						$this->db->where('timg_entertainment_id', $entertainmentid);
						$imgorder = $this->db->get('pt_entertainment_images')->num_rows();
						$imgorder = $imgorder + 1;
				}
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_entertainment_id', $entertainmentid);
				$hasdefault = $this->db->get('pt_entertainment_images')->num_rows();
				if ($hasdefault < 1) {
						$type = 'default';
				}
				$approval = pt_admin_gallery_approve();
				$data = array('timg_entertainment_id' => $entertainmentid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
				$this->db->insert('pt_entertainment_images', $data);
		}

// update entertainment map order
		function update_map_order($id, $order) {
				$data = array('map_order' => $order);
				$this->db->where('map_id', $id);
				$this->db->update('pt_entertainment_maps', $data);
		}


// update entertainment order
		function update_entertainment_order($id, $order) {
				$data = array('entertainment_order' => $order);
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);
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



			 $data = array('entertainment_is_featured' => $isfeatured, 'entertainment_featured_forever' => $isforever);
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);
		}
// Disable Entertainment

		public function disable_entertainment($id) {
				$data = array('entertainment_status' => 'No');
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);
		}
// Enable Entertainment

		public function enable_entertainment($id) {
				$data = array('entertainment_status' => 'Yes');
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);
		}

// Delete entertainment
		function delete_entertainment($entertainmentid) {
				$entertainmentimages = $this->entertainment_images($entertainmentid);
				foreach ($entertainmentimages['all_slider'] as $sliderimg) {
						$this->delete_image($sliderimg->timg_image,$sliderimg->timg_id,$entertainmentid);
				}


				$this->db->where('review_itemid', $entertainmentid);
				$this->db->where('review_module', 'entertainment');
				$this->db->delete('pt_reviews');
				$this->db->where('map_entertainment_id', $entertainmentid);
				$this->db->delete('pt_entertainment_maps');

				$this->db->where('item_id', $entertainmentid);
                $this->db->delete('pt_entertainment_translation');

                $this->db->where('entertainment_id',$entertainmentid);
            	$this->db->delete('pt_entertainment_locations');

				$this->db->where('entertainment_id', $entertainmentid);
				$this->db->delete('pt_entertainment');
		}

// Get Entertainment Images
		function entertainment_images($id) {
				$this->db->where('timg_entertainment_id', $id);
				$this->db->where('timg_type', 'default');
				$q = $this->db->get('pt_entertainment_images');
				$data['def_image'] = $q->result();
				$this->db->where('timg_type', 'slider');
				$this->db->order_by('timg_id', 'desc');
				$this->db->having('timg_entertainment_id', $id);
				$q = $this->db->get('pt_entertainment_images');
				$data['all_slider'] = $q->result();
				$data['slider_counts'] = $q->num_rows();
				return $data;
		}

//update entertainment thumbnail
		function update_thumb($oldthumb, $newthumb, $entertainmentid) {
				$data = array('timg_type' => 'slider');
				$this->db->where('timg_id', $oldthumb);
				$this->db->where('timg_entertainment_id', $entertainmentid);
				$this->db->update('pt_entertainment_images', $data);
				$data2 = array('timg_type' => 'default');
				$this->db->where('timg_id', $newthumb);
				$this->db->where('timg_entertainment_id', $entertainmentid);
				$this->db->update('pt_entertainment_images', $data2);
		}

// Approve or reject Hotel Images
		function approve_reject_images() {
				$data = array('timg_approved' => $this->input->post('apprej'));
				$this->db->where('timg_id', $this->input->post('imgid'));
				$this->db->update('pt_entertainment_images', $data);
		}

// update image order
		function update_image_order($imgid, $order) {
				$data = array('timg_order' => $order);
				$this->db->where('timg_id', $imgid);
				$this->db->update('pt_entertainment_images', $data);
		}


// Delete entertainment Images
		function delete_image($imgname, $imgid, $entertainmentid) {
				$this->db->where('timg_id', $imgid);
				$this->db->delete('pt_entertainment_images');
                $this->updateEntertainmentThumb($entertainmentid,$imgname,"delete");
                @ unlink(PT_ENTERTAINMENT_SLIDER_THUMB_UPLOAD . $imgname);
				@ unlink(PT_ENTERTAINMENT_SLIDER_UPLOAD . $imgname);
		}

//update entertainment thumbnail
		function updateEntertainmentThumb($entertainmentid,$imgname,$action) {
		  if($action == "delete"){
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image',$imgname);
            $this->db->where('entertainment_id',$entertainmentid);
            $rs = $this->db->get('pt_entertainment')->num_rows();
            if($rs > 0){
              $data = array(
              'thumbnail_image' => PT_BLANK_IMG
              );
              $this->db->where('entertainment_id',$entertainmentid);
              $this->db->update('pt_entertainment',$data);
            }
            }else{
              $data = array(
              'thumbnail_image' => $imgname
              );
              $this->db->where('entertainment_id',$entertainmentid);
              $this->db->update('pt_entertainment',$data);
            }

		}




		function offers_data($id) {
				/*$this->db->where('offer_module', 'entertainment');
				$this->db->where('offer_item', $id);
				return $this->db->get('pt_special_offers')->result();*/
		}

		function add_to_map() {
				$maporder = 0;
				$entertainmentid = $this->input->post('entertainmentid');
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_entertainment_id', $entertainmentid);
				$res = $this->db->get('pt_entertainment_maps')->num_rows();
				$addtype = $this->input->post('addtype');
				if ($addtype == "visit") {
						$maporder = $res + 1;
				}
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_entertainment_id' => $entertainmentid, 'map_order' => $maporder);
				$this->db->insert('pt_entertainment_maps', $data);
		}

		function update_entertainment_map() {
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
				$this->db->where('map_id', $this->input->post('mapid'));
				$this->db->update('pt_entertainment_maps', $data);
		}

		function has_start_end_city($type, $entertainmentid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', $type);
				$this->db->where('map_entertainment_id', $entertainmentid);
				$nums = $this->db->get('pt_entertainment_maps')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

		function get_entertainment_map($entertainmentid) {
				$this->db->where('map_entertainment_id', $entertainmentid);
				return $this->db->get('pt_entertainment_maps')->result();
		}

		function delete_map_item($mapid) {
				$this->db->where('map_id', $mapid);
				$this->db->delete('pt_entertainment_maps');
		}

// get related entertainment for front-end
		function get_related_entertainment($entertainment) {
				$id = explode(",", $entertainment);
				$this->db->select('pt_entertainment.entertainment_title,pt_entertainment.entertainment_slug,pt_entertainment.entertainment_id,pt_entertainment.entertainment_basic_price,pt_entertainment.entertainment_basic_discount,pt_entertainment_types_settings.sett_name');
				$this->db->where_in('pt_entertainment.entertainment_id', $id);
/*  $this->db->where('pt_entertainment_images.timg_type','default');
$this->db->join('pt_entertainment_images','pt_entertainment.entertainment_id = pt_entertainment_images.timg_entertainment_id','left');*/
				$this->db->join('pt_entertainment_types_settings', 'pt_entertainment.entertainment_type = pt_entertainment_types_settings.sett_id', 'left');
				return $this->db->get('pt_entertainment')->result();
		}

// Check entertainment existence
		function entertainment_exists($slug) {
				$this->db->select('entertainment_id');
				$this->db->where('entertainment_slug', $slug);
				$this->db->where('entertainment_status', 'Yes');
				$nums = $this->db->get('pt_entertainment')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

// List all entertainment on front listings page
		function list_entertainment_front($sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_entertainment.entertainment_order', 'asc');
				}
				$this->db->select('entertainment_id');
				$this->db->group_by('entertainment_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_entertainment.entertainment_adult_price >=', $minp);
						$this->db->where('pt_entertainment.entertainment_adult_price <=', $maxp);
				}

				$this->db->where('entertainment_status', 'Yes');
				$query = $this->db->get('pt_entertainment', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all entertainment on front listings page by location
		function showEntertainmentByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_entertainment.entertainment_order', 'asc');
				}
				$this->db->select('entertainment_id');
				$this->db->group_by('entertainment_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_entertainment.entertainment_adult_price >=', $minp);
						$this->db->where('pt_entertainment.entertainment_adult_price <=', $maxp);
				}

				if(is_array($locs)){
                $this->db->where_in('pt_entertainment.entertainment_location',$locs);
                }else{
                $this->db->where('pt_entertainment.entertainment_location',$locs);
                }

				$this->db->where('entertainment_status', 'Yes');
				$query = $this->db->get('pt_entertainment', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// Search entertainment from home page
		function search_entertainment_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$this->load->helper('entertainment_front');
				$data = array();

				//$location = $this->input->get('location');

				$adults = $this->input->get('adults');
				$type = $this->input->get('type');

				//$sprice = $this->input->get('price');
				$stars = $this->input->get('stars');

				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_entertainment.entertainment_id,entertainment_type,entertainment_location,entertainment_adult_price,entertainment_title,entertainment_max_adults,entertainment_status,pt_entertainment_locations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_entertainment.entertainment_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_entertainment.entertainment_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_entertainment.entertainment_order', 'asc');
				}
				elseif ($orderby == "p_lh") {
						$this->db->order_by('pt_entertainment.entertainment_adult_price', 'asc');
				}
				elseif ($orderby == "p_hl") {
						$this->db->order_by('pt_entertainment.entertainment_adult_price', 'desc');
				}

				if(!empty($location)){
					//$this->db->like('pt_entertainment.entertainment_location', $location);
					$this->db->where('pt_entertainment_locations.location_id', $location);

				}


				if (!empty ($adults)) {
						$this->db->where('pt_entertainment.entertainment_max_adults >=', $adults);
				}

				if (!empty ($stars)) {
						$this->db->where('entertainment_stars', $stars);
				}



				if (!empty ($type)) {
						$this->db->where('pt_entertainment.entertainment_type', $type);
				}

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_entertainment.entertainment_adult_price >=', $minp);
						$this->db->where('pt_entertainment.entertainment_adult_price <=', $maxp);
				}
				$this->db->group_by('pt_entertainment.entertainment_id');
				$this->db->join('pt_entertainment_locations', 'pt_entertainment.entertainment_id = pt_entertainment_locations.entertainment_id');
				$this->db->where('pt_entertainment.entertainment_status', 'Yes');


		if(!empty($perpage)){

				$query = $this->db->get('pt_entertainment', $perpage, $offset);

				}else{

				$query = $this->db->get('pt_entertainment');

				}

				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

		function max_map_order($entertainmentid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_entertainment_id', $entertainmentid);
				return $this->db->get('pt_entertainment_maps')->num_rows();
		}

// get default image of entertainment
		function default_entertainment_img($id) {
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_approved', '1');
				$this->db->where('timg_entertainment_id', $id);
				$res = $this->db->get('pt_entertainment_images')->result();
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
				$this->db->where('entertainment_slug', $cslug);
				$this->db->where('entertainment_id !=', $id);
				$nums = $this->db->get('pt_entertainment')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('entertainment_title' => $this->input->post('title'), 'entertainment_slug' => $cslug, 'entertainment_desc' => $this->input->post('desc'), 'entertainment_policy' => $this->input->post('policy'));
				$this->db->where('entertainment_id', $id);
				$this->db->update('pt_entertainment', $data);
				return $cslug;
		}

// Adds translation of some fields data
		function add_translation($postdata, $entertainmentid) {
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
                'item_id' => $entertainmentid,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_entertainment_translation', $data);
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
				$this->db->insert('pt_entertainment_translation', $data);

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
			    $this->db->update('pt_entertainment_translation', $data);
                }


              }

                }

		}

		 function getBackTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('item_id',$id);
            return $this->db->get('pt_entertainment_translation')->result();

        }

         function entertainmentGallery($slug){

          $this->db->select('pt_entertainment.thumbnail_image as thumbnail,pt_entertainment_images.timg_id as id,pt_entertainment_images.timg_entertainment_id as itemid,pt_entertainment_images.timg_type as type,pt_entertainment_images.timg_image as image,pt_entertainment_images.timg_order as imgorder,pt_entertainment_images.timg_image as image,pt_entertainment_images.timg_approved as approved');
          $this->db->where('pt_entertainment.entertainment_slug',$slug);
          $this->db->join('pt_entertainment_images', 'pt_entertainment.entertainment_id = pt_entertainment_images.timg_entertainment_id', 'left');
          $this->db->order_by('pt_entertainment_images.timg_id','desc');
          return $this->db->get('pt_entertainment')->result();

        }

        function addPhotos($id,$filename){

         $this->db->select('thumbnail_image');
         $this->db->where('entertainment_id',$id);
         $rs = $this->db->get('pt_entertainment')->result();
         if($rs[0]->thumbnail_image == PT_BLANK_IMG){

               $data = array('thumbnail_image' => $filename);
               $this->db->where('entertainment_id',$id);
               $this->db->update('pt_entertainment',$data);
         }

        //add photos to entertainment images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_entertainment_id', $id);
        $imgorder = $this->db->get('pt_entertainment_images')->num_rows();
        $imgorder = $imgorder + 1;

				$approval = pt_admin_gallery_approve();

		    	$insdata = array(
                'timg_entertainment_id' => $id,
                'timg_type' => 'slider',
                'timg_image' => $filename,
                'timg_order' => $imgorder,
                'timg_approved' => $approval
                );

				$this->db->insert('pt_entertainment_images', $insdata);


        }

        function assignEntertainment($entertainment,$userid){

          if(!empty($entertainment)){
          $userentertainment = $this->userOwnedEntertainment($userid);
                foreach($userentertainment as $tt){
                   if(!in_array($tt,$entertainment)){
                    $ddata = array(
                   'entertainment_owned_by' => '1'
                   );
                   $this->db->where('entertainment_id',$tt);
                   $this->db->update('pt_entertainment',$ddata);
                   }
                }

                foreach($entertainment as $t){
                   $data = array(
                   'entertainment_owned_by' => $userid
                   );
                   $this->db->where('entertainment_id',$t);
                   $this->db->update('pt_entertainment',$data);

                 }

                 }
        }

        function userOwnedEntertainment($id){
          $result = array();
          if(!empty($id)){
          $this->db->where('entertainment_owned_by',$id);
          }

          $rs = $this->db->get('pt_entertainment')->result();
          if(!empty($rs)){
            foreach($rs as $r){
              $result[] = $r->entertainment_id;
            }
          }
          return $result;
        }

        // get number of photos of entertainment
		function photos_count($entertainmentid) {
				$this->db->where('timg_entertainment_id', $entertainmentid);
				return $this->db->get('pt_entertainment_images')->num_rows();
		}

		function updateEntertainmentSettings() {
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

		// get popular entertainment
		function popular_entertainment_front() {
				$settings = $this->Settings_model->get_front_settings('entertainment');
				$limit = $settings[0]->front_popular;
				$orderby = $settings[0]->front_popular_order;

                $this->db->select('pt_entertainment.entertainment_id,pt_entertainment.entertainment_status,pt_reviews.review_overall,pt_reviews.review_itemid');

                $this->db->select_avg('pt_reviews.review_overall', 'overall');
				$this->db->order_by('overall', 'desc');
				$this->db->group_by('pt_entertainment.entertainment_id');
				$this->db->join('pt_reviews', 'pt_entertainment.entertainment_id = pt_reviews.review_itemid');
				$this->db->where('entertainment_status', 'yes');
				$this->db->limit($limit);
			   	return $this->db->get('pt_entertainment')->result();
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
				$this->db->insert('pt_entertainment_types_settings', $data);
                return $this->db->insert_id();
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");

		}

// update entertainment settings data
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
				$this->db->update('pt_entertainment_types_settings', $data);
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
				$this->db->insert('pt_entertainment_types_settings_translation', $data);

                }else{

                 $data = array(
                'trans_name' => $name
                );
				$this->db->where('sett_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_entertainment_types_settings_translation', $data);

              }


              }

                }
		}


         function getBackSettingsTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_entertainment_types_settings_translation')->result();

        }

        // Delete hotel settings
		function deleteTypeSettings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_entertainment_types_settings');

                $this->db->where('sett_id', $id);
				$this->db->delete('pt_entertainment_types_settings_translation');
		}

				// Delete multiple entertainment settings
		function deleteMultiplesettings($id, $type) {
				$this->db->where('sett_id', $id);
				$this->db->where('sett_type',$type);
				$this->db->delete('pt_entertainment_types_settings');

				$rowsDeleted = $this->db->affected_rows();

				if($rowsDeleted > 0){
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_entertainment_types_settings_translation');
				}


		}

         function getTypesTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_entertainment_types_settings_translation')->result();

        }

        function updateEntertainmentLocations($locations, $entertainmentid){

        	$this->db->where('entertainment_id',$entertainmentid);
        	$this->db->delete('pt_entertainment_locations');
        	$position = 0;

        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$position++;
        			$data = array('position' => $position,'location_id' => $loc, 'entertainment_id' => $entertainmentid);
        			$this->db->insert('pt_entertainment_locations', $data);
        		}
        	}

        }

        function isEntertainmentLocation($i, $locid, $entertainmentid){
        	$this->db->where('position', $i);
        	$this->db->where('location_id', $locid);
        	$this->db->where('entertainment_id', $entertainmentid);
        	$rs = $this->db->get('pt_entertainment_locations')->num_rows();
        	if($rs > 0){
        		return "selected";
        	}else{
        		return "";
        	}
        }

        function entertainmentSelectedLocations($entertainmentid){
          $result = array();
          $this->db->where('entertainment_id', $entertainmentid);
          $res = $this->db->get('pt_entertainment_locations')->result();
          foreach($res as $r){
            $locInfo = pt_LocationsInfo($r->location_id);
            $result[$r->position] = (object)array('id' => $r->location_id,'name' => $locInfo->city.", ".$locInfo->country);
          }
         return $result;

        }

}
