<?php

class Spa_model extends CI_Model {
        public $langdef;
		function __construct() {
// Call the Model constructor
				parent :: __construct();
                $this->langdef = DEFLANG;
		}

// Get all enabled spa short info
		function shortInfo($id = null) {
				$result = array();
				$this->db->select('spa_id,spa_title,spa_slug');
				if (!empty ($id)) {
						$this->db->where('spa_owned_by', $id);
				}
				$this->db->where('spa_status', 'Yes');
				$this->db->order_by('spa_id', 'desc');
				$spa = $this->db->get('pt_spa')->result();
				foreach($spa as $spa){
					$result[] = (object)array('id' => $spa->spa_id, 'title' => $spa->spa_title, 'slug' => $spa->spa_slug);
				}

				return $result;
		}

// Get all spa id and names only
		function all_spa_names($id = null) {
				$this->db->select('spa_id,spa_title');
				if (!empty ($id)) {
						$this->db->where('spa_owned_by', $id);
				}
				$this->db->order_by('spa_id', 'desc');
				return $this->db->get('pt_spa')->result();
		}

		// Get all spa for extras
		function all_spa($id = null) {
				$this->db->select('spa_id as id,spa_title as title');
				if (!empty ($id)) {
						$this->db->where('spa_owned_by', $id);
				}
				$this->db->order_by('spa_id', 'desc');
				return $this->db->get('pt_spa')->result();
		}

		function convert_price($amount) {

		}

// get latest spa
		function latest_spa_front() {
				$settings = $this->Settings_model->get_front_settings('spa');
				$limit = $settings[0]->front_latest;
				$this->db->select('pt_spa.spa_status,pt_spa.spa_basic_price,pt_spa.spa_basic_discount,pt_spa.spa_id,pt_spa.spa_desc,pt_spa.spa_title,pt_spa.spa_slug,pt_spa.spa_type,pt_spa_types_settings.sett_name');
				$this->db->order_by('pt_spa.spa_id', 'desc');
				$this->db->where('pt_spa.spa_status', 'Yes');
				$this->db->join('pt_spa_types_settings', 'pt_spa.spa_type = pt_spa_types_settings.sett_id', 'left');
				$this->db->limit($limit);
				return $this->db->get('pt_spa')->result();
		}

// get all data of single spa by slug
		function get_spa_data($spaname) {
				$this->db->select('pt_spa.*');
				$this->db->where('pt_spa.spa_slug', $spaname);

				return $this->db->get('pt_spa')->result();
		}

// get all spa info
		function get_all_spa_back($id = null) {
				$this->db->select('pt_spa.spa_featured_forever,pt_spa.spa_id,pt_spa.spa_title,pt_spa.spa_slug,pt_spa.spa_owned_by,pt_spa.spa_order,pt_spa.spa_status,pt_spa.spa_is_featured,
    pt_spa.spa_featured_from,pt_spa.spa_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_spa_types_settings.sett_name');
// $this->db->where('pt_spa_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_spa.spa_owned_by', $id);
				}
				$this->db->order_by('pt_spa.spa_id', 'desc');
				$this->db->join('pt_accounts', 'pt_spa.spa_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_spa_images','pt_spa.spa_id = pt_spa_images.timg_spa_id','left');
				$query = $this->db->get('pt_spa');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all spa info with limit
		function get_all_spa_back_limit($id = null, $perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_spa.spa_featured_forever,pt_spa.spa_id,pt_spa.spa_title,pt_spa.spa_slug,pt_spa.spa_created_at,pt_spa.spa_owned_by,pt_spa.spa_order,pt_spa.spa_status,pt_spa.spa_is_featured,
    pt_spa.spa_featured_from,pt_spa.spa_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_spa_types_settings.sett_name');
// $this->db->where('pt_spa_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_spa.spa_owned_by', $id);
				}
				$this->db->order_by('pt_spa.spa_id', 'desc');
				$this->db->join('pt_accounts', 'pt_spa.spa_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_spa_images','pt_spa.spa_id = pt_spa_images.timg_spa_id','left');
				$query = $this->db->get('pt_spa', $perpage, $offset);
				$data['all'] = $query->result();
				return $data;
		}

// add spa data
		function add_spa($user = null) {
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

                $this->db->select("spa_id");
				$this->db->order_by("spa_id", "desc");
				$query = $this->db->get('pt_spa');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$spalastid = 1;
				}
				else {
						$spalastid = $lastid[0]->spa_id + 1;
				}

				$spacount = $query->num_rows();
				$spaorder = $spacount + 1;
				$this->db->select("spa_id");
				$this->db->where("spa_title", $this->input->post('spaname'));
				$queryc = $this->db->get('pt_spa')->num_rows();
				if ($queryc > 0) {
						$spalug = create_url_slug($this->input->post('spaname')) . "-" . $spalastid;
				}
				else {
						$spalug = create_url_slug($this->input->post('spaname'));
				}
				$amenities = @ implode(",", $this->input->post('spaamenities'));
				$exclusions = @ implode(",", $this->input->post('spaexclusions'));
				$paymentopt = @ implode(",", $this->input->post('spapayments'));
				$relatedspa = @ implode(",", $this->input->post('relatedspa'));


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
				$spaLocation = $location[0];

				$stars = $this->input->post('spatars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('spa_title' => $this->input->post('spaname'),
					'spa_slug' => $spalug, 'spa_desc' => $this->input->post('spadesc'),
					'spa_stars' => intval($stars),
					'spa_is_featured' => $featured,
					'spa_featured_from' => convert_to_unix($ffrom),
					'spa_featured_to' => convert_to_unix($fto),
					'spa_owned_by' => $user,
					'spa_type' => $this->input->post('spatype'),
					'spa_location' => $spaLocation,
					'spa_latitude' => $this->input->post('latitude'),
					'spa_longitude' => $this->input->post('longitude'),
					'spa_mapaddress' => $this->input->post('spamapaddress'),
	                //'spa_basic_price' => $this->input->post('basic'),
					//'spa_basic_discount' => $this->input->post('discount'),
					'spa_meta_title' => $this->input->post('spametatitle'),
					'spa_meta_keywords' => $this->input->post('spakeywords'),
					'spa_meta_desc' => $this->input->post('spametadesc'), 'spa_amenities' => $amenities,
					'spa_exclusions' => $exclusions, 'spa_payment_opt' => $paymentopt,
					'spa_max_adults' => intval($this->input->post('maxadult')),
					'spa_max_child' => intval($this->input->post('maxchild')),
					'spa_max_infant' => intval($this->input->post('maxinfant')),
					'spa_adult_price' => floatval($this->input->post('adultprice')),
					'spa_child_price' => floatval($this->input->post('childprice')),
					'spa_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'spa_days' => intval($this->input->post('spadays')),
					'spa_nights' => intval($this->input->post('spanights')),
					'spa_privacy' => $this->input->post('spaprivacy'),
					'spa_status' => $this->input->post('spatatus'),
					'spa_related' => $relatedspa, 'spa_order' => $spaorder,
					'spa_comm_fixed' => $commfixed, 'spa_comm_percentage' => $commper,
					'spa_tax_fixed' => $taxfixed, 'spa_tax_percentage' => $taxper,
					'spa_email' => $this->input->post('spaemail'),
					'spa_phone' => $this->input->post('spaphone'),
					'spa_website' => $this->input->post('spawebsite'),
					'spa_fulladdress' => $this->input->post('spafulladdress'),
					'spa_featured_forever' => $isforever,
					'spa_created_at' => time());
				$this->db->insert('pt_spa', $data);
				$spaid = $this->db->insert_id();
				$this->updateSpaLocations($this->input->post('locations'), $spaid);
				return $spaid;
		}

// update spa data
		function update_spa($id) {

				$spacomm = $this->input->post('deposit');
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


				$this->db->select("spa_id");
				$this->db->where("spa_id !=", $id);
				$this->db->where("spa_title", $this->input->post('spaname'));
				$queryc = $this->db->get('pt_spa')->num_rows();
				if ($queryc > 0) {
						$spalug = create_url_slug($this->input->post('spaname')) . "-" . $id;
				}
				else {
						$spalug = create_url_slug($this->input->post('spaname'));
				}
				$amenities = @ implode(",", $this->input->post('spaamenities'));
				$exclusions = @ implode(",", $this->input->post('spaexclusions'));
				$paymentopt = @ implode(",", $this->input->post('spapayments'));
				$relatedspa = @ implode(",", $this->input->post('relatedspa'));

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
				$spaLocation = $location[0];

				$stars = $this->input->post('spatars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('spa_title' => $this->input->post('spaname'),
					'spa_slug' => $spalug, 'spa_desc' => $this->input->post('spadesc'),
					'spa_stars' => intval($stars),
					'spa_is_featured' => $featured,
					'spa_featured_from' => convert_to_unix($ffrom),
					'spa_featured_to' => convert_to_unix($fto),
					'spa_type' => $this->input->post('spatype'),
					'spa_location' => $spaLocation,
					'spa_latitude' => $this->input->post('latitude'),
					'spa_longitude' => $this->input->post('longitude'),
					'spa_mapaddress' => $this->input->post('spamapaddress'),
	                //'spa_basic_price' => $this->input->post('basic'),
					//'spa_basic_discount' => $this->input->post('discount'),
					'spa_meta_title' => $this->input->post('spametatitle'),
					'spa_meta_keywords' => $this->input->post('spakeywords'),
					'spa_meta_desc' => $this->input->post('spametadesc'), 'spa_amenities' => $amenities,
					'spa_exclusions' => $exclusions, 'spa_payment_opt' => $paymentopt,
					'spa_max_adults' => intval($this->input->post('maxadult')),
					'spa_max_child' => intval($this->input->post('maxchild')),
					'spa_max_infant' => intval($this->input->post('maxinfant')),
					'spa_adult_price' => floatval($this->input->post('adultprice')),
					'spa_child_price' => floatval($this->input->post('childprice')),
					'spa_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'spa_days' => intval($this->input->post('spadays')),
					'spa_nights' => intval($this->input->post('spanights')),
					'spa_privacy' => $this->input->post('spaprivacy'),
					'spa_status' => $this->input->post('spatatus'),
					'spa_related' => $relatedspa,
					'spa_comm_fixed' => $commfixed, 'spa_comm_percentage' => $commper,
					'spa_tax_fixed' => $taxfixed, 'spa_tax_percentage' => $taxper,
					'spa_email' => $this->input->post('spaemail'),
					'spa_phone' => $this->input->post('spaphone'),
					'spa_website' => $this->input->post('spawebsite'),
					'spa_fulladdress' => $this->input->post('spafulladdress'),
					'spa_featured_forever' => $isforever);
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);

				$this->updateSpaLocations($this->input->post('locations'), $id);
	}

// Add spa settings data
		function add_settings_data() {
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
				$this->db->insert('pt_spa_types_settings', $data);
		}

// update spa settings data
		function update_settings_data() {
				$id = $this->input->post('id');
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
				$this->db->where('sett_id', $id);
				$this->db->update('pt_spa_types_settings', $data);
		}

// Disable spa settings
		function disable_settings($id) {
				$data = array('sett_status' => 'No');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_spa_types_settings', $data);
		}

// Enable spa settings
		function enable_settings($id) {
				$data = array('sett_status' => 'Yes');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_spa_types_settings', $data);
		}

// Delete spa settings
		function delete_settings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_spa_types_settings');
		}

// get all spa for related selection for backend
		function select_related_spa($id = null) {
				$this->db->select('spa_title,spa_id');
				if (!empty ($id)) {
						$this->db->where('spa_id !=', $id);
				}
				return $this->db->get('pt_spa')->result();
		}

// Get spa settings data
		function get_spa_settings_data($type) {
			if(!empty($type)){
             	$this->db->where('sett_type', $type);
		  }

				$this->db->order_by('sett_id', 'desc');
				return $this->db->get('pt_spa_types_settings')->result();
		}

// Get spa settings data for adding spa
		function get_tsettings_data($type) {
				$this->db->where('sett_type', $type);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_spa_types_settings')->result();
		}

// Get spa settings data for adding spa
		function get_tsettings_data_front($type, $items) {
				$this->db->where('sett_type', $type);
				$this->db->where_in('sett_id', $items);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_spa_types_settings')->result();
		}

// add Spa images by type
		function add_spa_image($type, $filename, $spaid) {
				$imgorder = 0;
				if ($type == "slider") {
						$this->db->where('timg_type', 'slider');
						$this->db->where('timg_spa_id', $spaid);
						$imgorder = $this->db->get('pt_spa_images')->num_rows();
						$imgorder = $imgorder + 1;
				}
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_spa_id', $spaid);
				$hasdefault = $this->db->get('pt_spa_images')->num_rows();
				if ($hasdefault < 1) {
						$type = 'default';
				}
				$approval = pt_admin_gallery_approve();
				$data = array('timg_spa_id' => $spaid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
				$this->db->insert('pt_spa_images', $data);
		}

// update spa map order
		function update_map_order($id, $order) {
				$data = array('map_order' => $order);
				$this->db->where('map_id', $id);
				$this->db->update('pt_spa_maps', $data);
		}


// update spa order
		function update_spa_order($id, $order) {
				$data = array('spa_order' => $order);
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);
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



			 $data = array('spa_is_featured' => $isfeatured, 'spa_featured_forever' => $isforever);
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);
		}
// Disable Spa

		public function disable_spa($id) {
				$data = array('spa_status' => 'No');
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);
		}
// Enable Spa

		public function enable_spa($id) {
				$data = array('spa_status' => 'Yes');
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);
		}

// Delete spa
		function delete_spa($spaid) {
				$spaimages = $this->spa_images($spaid);
				foreach ($spaimages['all_slider'] as $sliderimg) {
						$this->delete_image($sliderimg->timg_image,$sliderimg->timg_id,$spaid);
				}


				$this->db->where('review_itemid', $spaid);
				$this->db->where('review_module', 'spa');
				$this->db->delete('pt_reviews');
				$this->db->where('map_spa_id', $spaid);
				$this->db->delete('pt_spa_maps');

				$this->db->where('item_id', $spaid);
                $this->db->delete('pt_spa_translation');

                $this->db->where('spa_id',$spaid);
            	$this->db->delete('pt_spa_locations');

				$this->db->where('spa_id', $spaid);
				$this->db->delete('pt_spa');
		}

// Get Spa Images
		function spa_images($id) {
				$this->db->where('timg_spa_id', $id);
				$this->db->where('timg_type', 'default');
				$q = $this->db->get('pt_spa_images');
				$data['def_image'] = $q->result();
				$this->db->where('timg_type', 'slider');
				$this->db->order_by('timg_id', 'desc');
				$this->db->having('timg_spa_id', $id);
				$q = $this->db->get('pt_spa_images');
				$data['all_slider'] = $q->result();
				$data['slider_counts'] = $q->num_rows();
				return $data;
		}

//update spa thumbnail
		function update_thumb($oldthumb, $newthumb, $spaid) {
				$data = array('timg_type' => 'slider');
				$this->db->where('timg_id', $oldthumb);
				$this->db->where('timg_spa_id', $spaid);
				$this->db->update('pt_spa_images', $data);
				$data2 = array('timg_type' => 'default');
				$this->db->where('timg_id', $newthumb);
				$this->db->where('timg_spa_id', $spaid);
				$this->db->update('pt_spa_images', $data2);
		}

// Approve or reject Hotel Images
		function approve_reject_images() {
				$data = array('timg_approved' => $this->input->post('apprej'));
				$this->db->where('timg_id', $this->input->post('imgid'));
				$this->db->update('pt_spa_images', $data);
		}

// update image order
		function update_image_order($imgid, $order) {
				$data = array('timg_order' => $order);
				$this->db->where('timg_id', $imgid);
				$this->db->update('pt_spa_images', $data);
		}


// Delete spa Images
		function delete_image($imgname, $imgid, $spaid) {
				$this->db->where('timg_id', $imgid);
				$this->db->delete('pt_spa_images');
                $this->updateSpaThumb($spaid,$imgname,"delete");
                @ unlink(PT_SPA_SLIDER_THUMB_UPLOAD . $imgname);
				@ unlink(PT_SPA_SLIDER_UPLOAD . $imgname);
		}

//update spa thumbnail
		function updateSpaThumb($spaid,$imgname,$action) {
		  if($action == "delete"){
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image',$imgname);
            $this->db->where('spa_id',$spaid);
            $rs = $this->db->get('pt_spa')->num_rows();
            if($rs > 0){
              $data = array(
              'thumbnail_image' => PT_BLANK_IMG
              );
              $this->db->where('spa_id',$spaid);
              $this->db->update('pt_spa',$data);
            }
            }else{
              $data = array(
              'thumbnail_image' => $imgname
              );
              $this->db->where('spa_id',$spaid);
              $this->db->update('pt_spa',$data);
            }

		}




		function offers_data($id) {
				/*$this->db->where('offer_module', 'spa');
				$this->db->where('offer_item', $id);
				return $this->db->get('pt_special_offers')->result();*/
		}

		function add_to_map() {
				$maporder = 0;
				$spaid = $this->input->post('spaid');
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_spa_id', $spaid);
				$res = $this->db->get('pt_spa_maps')->num_rows();
				$addtype = $this->input->post('addtype');
				if ($addtype == "visit") {
						$maporder = $res + 1;
				}
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_spa_id' => $spaid, 'map_order' => $maporder);
				$this->db->insert('pt_spa_maps', $data);
		}

		function update_spa_map() {
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
				$this->db->where('map_id', $this->input->post('mapid'));
				$this->db->update('pt_spa_maps', $data);
		}

		function has_start_end_city($type, $spaid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', $type);
				$this->db->where('map_spa_id', $spaid);
				$nums = $this->db->get('pt_spa_maps')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

		function get_spa_map($spaid) {
				$this->db->where('map_spa_id', $spaid);
				return $this->db->get('pt_spa_maps')->result();
		}

		function delete_map_item($mapid) {
				$this->db->where('map_id', $mapid);
				$this->db->delete('pt_spa_maps');
		}

// get related spa for front-end
		function get_related_spa($spa) {
				$id = explode(",", $spa);
				$this->db->select('pt_spa.spa_title,pt_spa.spa_slug,pt_spa.spa_id,pt_spa.spa_basic_price,pt_spa.spa_basic_discount,pt_spa_types_settings.sett_name');
				$this->db->where_in('pt_spa.spa_id', $id);
/*  $this->db->where('pt_spa_images.timg_type','default');
$this->db->join('pt_spa_images','pt_spa.spa_id = pt_spa_images.timg_spa_id','left');*/
				$this->db->join('pt_spa_types_settings', 'pt_spa.spa_type = pt_spa_types_settings.sett_id', 'left');
				return $this->db->get('pt_spa')->result();
		}

// Check spa existence
		function spa_exists($slug) {
				$this->db->select('spa_id');
				$this->db->where('spa_slug', $slug);
				$this->db->where('spa_status', 'Yes');
				$nums = $this->db->get('pt_spa')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

// List all spa on front listings page
		function list_spa_front($sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_spa.spa_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_spa.spa_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_spa.spa_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_spa.spa_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_spa.spa_order', 'asc');
				}
				$this->db->select('spa_id');
				$this->db->group_by('spa_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_spa.spa_adult_price >=', $minp);
						$this->db->where('pt_spa.spa_adult_price <=', $maxp);
				}

				$this->db->where('spa_status', 'Yes');
				$query = $this->db->get('pt_spa', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all spa on front listings page by location
		function showSpaByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_spa.spa_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_spa.spa_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_spa.spa_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_spa.spa_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_spa.spa_order', 'asc');
				}
				$this->db->select('spa_id');
				$this->db->group_by('spa_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_spa.spa_adult_price >=', $minp);
						$this->db->where('pt_spa.spa_adult_price <=', $maxp);
				}

				if(is_array($locs)){
                $this->db->where_in('pt_spa.spa_location',$locs);
                }else{
                $this->db->where('pt_spa.spa_location',$locs);
                }

				$this->db->where('spa_status', 'Yes');
				$query = $this->db->get('pt_spa', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// Search spa from home page
		function search_spa_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$this->load->helper('spa_front');
				$data = array();

				//$location = $this->input->get('location');

				$adults = $this->input->get('adults');
				$type = $this->input->get('type');

				//$sprice = $this->input->get('price');
				$stars = $this->input->get('stars');

				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_spa.spa_id,spa_type,spa_location,spa_adult_price,spa_title,spa_max_adults,spa_status,pt_spa_locations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_spa.spa_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_spa.spa_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_spa.spa_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_spa.spa_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_spa.spa_order', 'asc');
				}
				elseif ($orderby == "p_lh") {
						$this->db->order_by('pt_spa.spa_adult_price', 'asc');
				}
				elseif ($orderby == "p_hl") {
						$this->db->order_by('pt_spa.spa_adult_price', 'desc');
				}

				if(!empty($location)){
					//$this->db->like('pt_spa.spa_location', $location);
					$this->db->where('pt_spa_locations.location_id', $location);

				}


				if (!empty ($adults)) {
						$this->db->where('pt_spa.spa_max_adults >=', $adults);
				}

				if (!empty ($stars)) {
						$this->db->where('spa_stars', $stars);
				}



				if (!empty ($type)) {
						$this->db->where('pt_spa.spa_type', $type);
				}

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_spa.spa_adult_price >=', $minp);
						$this->db->where('pt_spa.spa_adult_price <=', $maxp);
				}
				$this->db->group_by('pt_spa.spa_id');
				$this->db->join('pt_spa_locations', 'pt_spa.spa_id = pt_spa_locations.spa_id');
				$this->db->where('pt_spa.spa_status', 'Yes');


		if(!empty($perpage)){

				$query = $this->db->get('pt_spa', $perpage, $offset);

				}else{

				$query = $this->db->get('pt_spa');

				}

				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

		function max_map_order($spaid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_spa_id', $spaid);
				return $this->db->get('pt_spa_maps')->num_rows();
		}

// get default image of spa
		function default_spa_img($id) {
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_approved', '1');
				$this->db->where('timg_spa_id', $id);
				$res = $this->db->get('pt_spa_images')->result();
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
				$this->db->where('spa_slug', $cslug);
				$this->db->where('spa_id !=', $id);
				$nums = $this->db->get('pt_spa')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('spa_title' => $this->input->post('title'), 'spa_slug' => $cslug, 'spa_desc' => $this->input->post('desc'), 'spa_policy' => $this->input->post('policy'));
				$this->db->where('spa_id', $id);
				$this->db->update('pt_spa', $data);
				return $cslug;
		}

// Adds translation of some fields data
		function add_translation($postdata, $spaid) {
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
                'item_id' => $spaid,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_spa_translation', $data);
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
				$this->db->insert('pt_spa_translation', $data);

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
			    $this->db->update('pt_spa_translation', $data);
                }


              }

                }

		}

		 function getBackTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('item_id',$id);
            return $this->db->get('pt_spa_translation')->result();

        }

         function spaGallery($slug){

          $this->db->select('pt_spa.thumbnail_image as thumbnail,pt_spa_images.timg_id as id,pt_spa_images.timg_spa_id as itemid,pt_spa_images.timg_type as type,pt_spa_images.timg_image as image,pt_spa_images.timg_order as imgorder,pt_spa_images.timg_image as image,pt_spa_images.timg_approved as approved');
          $this->db->where('pt_spa.spa_slug',$slug);
          $this->db->join('pt_spa_images', 'pt_spa.spa_id = pt_spa_images.timg_spa_id', 'left');
          $this->db->order_by('pt_spa_images.timg_id','desc');
          return $this->db->get('pt_spa')->result();

        }

        function addPhotos($id,$filename){

         $this->db->select('thumbnail_image');
         $this->db->where('spa_id',$id);
         $rs = $this->db->get('pt_spa')->result();
         if($rs[0]->thumbnail_image == PT_BLANK_IMG){

               $data = array('thumbnail_image' => $filename);
               $this->db->where('spa_id',$id);
               $this->db->update('pt_spa',$data);
         }

        //add photos to spa images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_spa_id', $id);
        $imgorder = $this->db->get('pt_spa_images')->num_rows();
        $imgorder = $imgorder + 1;

				$approval = pt_admin_gallery_approve();

		    	$insdata = array(
                'timg_spa_id' => $id,
                'timg_type' => 'slider',
                'timg_image' => $filename,
                'timg_order' => $imgorder,
                'timg_approved' => $approval
                );

				$this->db->insert('pt_spa_images', $insdata);


        }

        function assignSpa($spa,$userid){

          if(!empty($spa)){
          $userspa = $this->userOwnedSpa($userid);
                foreach($userspa as $tt){
                   if(!in_array($tt,$spa)){
                    $ddata = array(
                   'spa_owned_by' => '1'
                   );
                   $this->db->where('spa_id',$tt);
                   $this->db->update('pt_spa',$ddata);
                   }
                }

                foreach($spa as $t){
                   $data = array(
                   'spa_owned_by' => $userid
                   );
                   $this->db->where('spa_id',$t);
                   $this->db->update('pt_spa',$data);

                 }

                 }
        }

        function userOwnedSpa($id){
          $result = array();
          if(!empty($id)){
          $this->db->where('spa_owned_by',$id);
          }

          $rs = $this->db->get('pt_spa')->result();
          if(!empty($rs)){
            foreach($rs as $r){
              $result[] = $r->spa_id;
            }
          }
          return $result;
        }

        // get number of photos of spa
		function photos_count($spaid) {
				$this->db->where('timg_spa_id', $spaid);
				return $this->db->get('pt_spa_images')->num_rows();
		}

		function updateSpaSettings() {
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

		// get popular spa
		function popular_spa_front() {
				$settings = $this->Settings_model->get_front_settings('spa');
				$limit = $settings[0]->front_popular;
				$orderby = $settings[0]->front_popular_order;

                $this->db->select('pt_spa.spa_id,pt_spa.spa_status,pt_reviews.review_overall,pt_reviews.review_itemid');

                $this->db->select_avg('pt_reviews.review_overall', 'overall');
				$this->db->order_by('overall', 'desc');
				$this->db->group_by('pt_spa.spa_id');
				$this->db->join('pt_reviews', 'pt_spa.spa_id = pt_reviews.review_itemid');
				$this->db->where('spa_status', 'yes');
				$this->db->limit($limit);
			   	return $this->db->get('pt_spa')->result();
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
				$this->db->insert('pt_spa_types_settings', $data);
                return $this->db->insert_id();
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");

		}

// update spa settings data
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
				$this->db->update('pt_spa_types_settings', $data);
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
				$this->db->insert('pt_spa_types_settings_translation', $data);

                }else{

                 $data = array(
                'trans_name' => $name
                );
				$this->db->where('sett_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_spa_types_settings_translation', $data);

              }


              }

                }
		}


         function getBackSettingsTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_spa_types_settings_translation')->result();

        }

        // Delete hotel settings
		function deleteTypeSettings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_spa_types_settings');

                $this->db->where('sett_id', $id);
				$this->db->delete('pt_spa_types_settings_translation');
		}

				// Delete multiple spa settings
		function deleteMultiplesettings($id, $type) {
				$this->db->where('sett_id', $id);
				$this->db->where('sett_type',$type);
				$this->db->delete('pt_spa_types_settings');

				$rowsDeleted = $this->db->affected_rows();

				if($rowsDeleted > 0){
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_spa_types_settings_translation');
				}


		}

         function getTypesTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_spa_types_settings_translation')->result();

        }

        function updateSpaLocations($locations, $spaid){

        	$this->db->where('spa_id',$spaid);
        	$this->db->delete('pt_spa_locations');
        	$position = 0;

        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$position++;
        			$data = array('position' => $position,'location_id' => $loc, 'spa_id' => $spaid);
        			$this->db->insert('pt_spa_locations', $data);
        		}
        	}

        }

        function isSpaLocation($i, $locid, $spaid){
        	$this->db->where('position', $i);
        	$this->db->where('location_id', $locid);
        	$this->db->where('spa_id', $spaid);
        	$rs = $this->db->get('pt_spa_locations')->num_rows();
        	if($rs > 0){
        		return "selected";
        	}else{
        		return "";
        	}
        }

        function spaSelectedLocations($spaid){
          $result = array();
          $this->db->where('spa_id', $spaid);
          $res = $this->db->get('pt_spa_locations')->result();
          foreach($res as $r){
            $locInfo = pt_LocationsInfo($r->location_id);
            $result[$r->position] = (object)array('id' => $r->location_id,'name' => $locInfo->city.", ".$locInfo->country);
          }
         return $result;

        }

}
