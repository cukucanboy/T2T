<?php

class Wedding_model extends CI_Model {
        public $langdef;
		function __construct() {
// Call the Model constructor
				parent :: __construct();
                $this->langdef = DEFLANG;
		}

// Get all enabled wedding short info
		function shortInfo($id = null) {
				$result = array();
				$this->db->select('wedding_id,wedding_title,wedding_slug');
				if (!empty ($id)) {
						$this->db->where('wedding_owned_by', $id);
				}
				$this->db->where('wedding_status', 'Yes');
				$this->db->order_by('wedding_id', 'desc');
				$wedding = $this->db->get('pt_wedding')->result();
				foreach($wedding as $wedding){
					$result[] = (object)array('id' => $wedding->wedding_id, 'title' => $wedding->wedding_title, 'slug' => $wedding->wedding_slug);
				}

				return $result;
		}

// Get all wedding id and names only
		function all_wedding_names($id = null) {
				$this->db->select('wedding_id,wedding_title');
				if (!empty ($id)) {
						$this->db->where('wedding_owned_by', $id);
				}
				$this->db->order_by('wedding_id', 'desc');
				return $this->db->get('pt_wedding')->result();
		}

		// Get all wedding for extras
		function all_wedding($id = null) {
				$this->db->select('wedding_id as id,wedding_title as title');
				if (!empty ($id)) {
						$this->db->where('wedding_owned_by', $id);
				}
				$this->db->order_by('wedding_id', 'desc');
				return $this->db->get('pt_wedding')->result();
		}

		function convert_price($amount) {

		}

// get latest wedding
		function latest_wedding_front() {
				$settings = $this->Settings_model->get_front_settings('wedding');
				$limit = $settings[0]->front_latest;
				$this->db->select('pt_wedding.wedding_status,pt_wedding.wedding_basic_price,pt_wedding.wedding_basic_discount,pt_wedding.wedding_id,pt_wedding.wedding_desc,pt_wedding.wedding_title,pt_wedding.wedding_slug,pt_wedding.wedding_type,pt_wedding_types_settings.sett_name');
				$this->db->order_by('pt_wedding.wedding_id', 'desc');
				$this->db->where('pt_wedding.wedding_status', 'Yes');
				$this->db->join('pt_wedding_types_settings', 'pt_wedding.wedding_type = pt_wedding_types_settings.sett_id', 'left');
				$this->db->limit($limit);
				return $this->db->get('pt_wedding')->result();
		}

// get all data of single wedding by slug
		function get_wedding_data($weddingname) {
				$this->db->select('pt_wedding.*');
				$this->db->where('pt_wedding.wedding_slug', $weddingname);

				return $this->db->get('pt_wedding')->result();
		}

// get all wedding info
		function get_all_wedding_back($id = null) {
				$this->db->select('pt_wedding.wedding_featured_forever,pt_wedding.wedding_id,pt_wedding.wedding_title,pt_wedding.wedding_slug,pt_wedding.wedding_owned_by,pt_wedding.wedding_order,pt_wedding.wedding_status,pt_wedding.wedding_is_featured,
    pt_wedding.wedding_featured_from,pt_wedding.wedding_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_wedding_types_settings.sett_name');
// $this->db->where('pt_wedding_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_wedding.wedding_owned_by', $id);
				}
				$this->db->order_by('pt_wedding.wedding_id', 'desc');
				$this->db->join('pt_accounts', 'pt_wedding.wedding_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_wedding_images','pt_wedding.wedding_id = pt_wedding_images.timg_wedding_id','left');
				$query = $this->db->get('pt_wedding');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all wedding info with limit
		function get_all_wedding_back_limit($id = null, $perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_wedding.wedding_featured_forever,pt_wedding.wedding_id,pt_wedding.wedding_title,pt_wedding.wedding_slug,pt_wedding.wedding_created_at,pt_wedding.wedding_owned_by,pt_wedding.wedding_order,pt_wedding.wedding_status,pt_wedding.wedding_is_featured,
    pt_wedding.wedding_featured_from,pt_wedding.wedding_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_wedding_types_settings.sett_name');
// $this->db->where('pt_wedding_images.timg_type','default');
				if (!empty ($id)) {
						$this->db->where('pt_wedding.wedding_owned_by', $id);
				}
				$this->db->order_by('pt_wedding.wedding_id', 'desc');
				$this->db->join('pt_accounts', 'pt_wedding.wedding_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_wedding_images','pt_wedding.wedding_id = pt_wedding_images.timg_wedding_id','left');
				$query = $this->db->get('pt_wedding', $perpage, $offset);
				$data['all'] = $query->result();
				return $data;
		}

// add wedding data
		function add_wedding($user = null) {
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

                $this->db->select("wedding_id");
				$this->db->order_by("wedding_id", "desc");
				$query = $this->db->get('pt_wedding');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$weddinglastid = 1;
				}
				else {
						$weddinglastid = $lastid[0]->wedding_id + 1;
				}

				$weddingcount = $query->num_rows();
				$weddingorder = $weddingcount + 1;
				$this->db->select("wedding_id");
				$this->db->where("wedding_title", $this->input->post('weddingname'));
				$queryc = $this->db->get('pt_wedding')->num_rows();
				if ($queryc > 0) {
						$weddinglug = create_url_slug($this->input->post('weddingname')) . "-" . $weddinglastid;
				}
				else {
						$weddinglug = create_url_slug($this->input->post('weddingname'));
				}
				$amenities = @ implode(",", $this->input->post('weddingamenities'));
				$exclusions = @ implode(",", $this->input->post('weddingexclusions'));
				$paymentopt = @ implode(",", $this->input->post('weddingpayments'));
				$relatedwedding = @ implode(",", $this->input->post('relatedwedding'));


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
				$weddingLocation = $location[0];

				$stars = $this->input->post('weddingtars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('wedding_title' => $this->input->post('weddingname'),
					'wedding_slug' => $weddinglug, 'wedding_desc' => $this->input->post('weddingdesc'),
					'wedding_stars' => intval($stars),
					'wedding_is_featured' => $featured,
					'wedding_featured_from' => convert_to_unix($ffrom),
					'wedding_featured_to' => convert_to_unix($fto),
					'wedding_owned_by' => $user,
					'wedding_type' => $this->input->post('weddingtype'),
					'wedding_location' => $weddingLocation,
					'wedding_latitude' => $this->input->post('latitude'),
					'wedding_longitude' => $this->input->post('longitude'),
					'wedding_mapaddress' => $this->input->post('weddingmapaddress'),
	                //'wedding_basic_price' => $this->input->post('basic'),
					//'wedding_basic_discount' => $this->input->post('discount'),
					'wedding_meta_title' => $this->input->post('weddingmetatitle'),
					'wedding_meta_keywords' => $this->input->post('weddingkeywords'),
					'wedding_meta_desc' => $this->input->post('weddingmetadesc'), 'wedding_amenities' => $amenities,
					'wedding_exclusions' => $exclusions, 'wedding_payment_opt' => $paymentopt,
					'wedding_max_adults' => intval($this->input->post('maxadult')),
					'wedding_max_child' => intval($this->input->post('maxchild')),
					'wedding_max_infant' => intval($this->input->post('maxinfant')),
					'wedding_adult_price' => floatval($this->input->post('adultprice')),
					'wedding_child_price' => floatval($this->input->post('childprice')),
					'wedding_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'wedding_days' => intval($this->input->post('weddingdays')),
					'wedding_nights' => intval($this->input->post('weddingnights')),
					'wedding_privacy' => $this->input->post('weddingprivacy'),
					'wedding_status' => $this->input->post('weddingtatus'),
					'wedding_related' => $relatedwedding, 'wedding_order' => $weddingorder,
					'wedding_comm_fixed' => $commfixed, 'wedding_comm_percentage' => $commper,
					'wedding_tax_fixed' => $taxfixed, 'wedding_tax_percentage' => $taxper,
					'wedding_email' => $this->input->post('weddingemail'),
					'wedding_phone' => $this->input->post('weddingphone'),
					'wedding_website' => $this->input->post('weddingwebsite'),
					'wedding_fulladdress' => $this->input->post('weddingfulladdress'),
					'wedding_featured_forever' => $isforever,
					'wedding_created_at' => time());
				$this->db->insert('pt_wedding', $data);
				$weddingid = $this->db->insert_id();
				$this->updateWeddingLocations($this->input->post('locations'), $weddingid);
				return $weddingid;
		}

// update wedding data
		function update_wedding($id) {

				$weddingcomm = $this->input->post('deposit');
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


				$this->db->select("wedding_id");
				$this->db->where("wedding_id !=", $id);
				$this->db->where("wedding_title", $this->input->post('weddingname'));
				$queryc = $this->db->get('pt_wedding')->num_rows();
				if ($queryc > 0) {
						$weddinglug = create_url_slug($this->input->post('weddingname')) . "-" . $id;
				}
				else {
						$weddinglug = create_url_slug($this->input->post('weddingname'));
				}
				$amenities = @ implode(",", $this->input->post('weddingamenities'));
				$exclusions = @ implode(",", $this->input->post('weddingexclusions'));
				$paymentopt = @ implode(",", $this->input->post('weddingpayments'));
				$relatedwedding = @ implode(",", $this->input->post('relatedwedding'));

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
				$weddingLocation = $location[0];

				$stars = $this->input->post('weddingtars');
				if(empty($stars)){
					$stars = 0;
				}

				$data = array('wedding_title' => $this->input->post('weddingname'),
					'wedding_slug' => $weddinglug, 'wedding_desc' => $this->input->post('weddingdesc'),
					'wedding_stars' => intval($stars),
					'wedding_is_featured' => $featured,
					'wedding_featured_from' => convert_to_unix($ffrom),
					'wedding_featured_to' => convert_to_unix($fto),
					'wedding_type' => $this->input->post('weddingtype'),
					'wedding_location' => $weddingLocation,
					'wedding_latitude' => $this->input->post('latitude'),
					'wedding_longitude' => $this->input->post('longitude'),
					'wedding_mapaddress' => $this->input->post('weddingmapaddress'),
	                //'wedding_basic_price' => $this->input->post('basic'),
					//'wedding_basic_discount' => $this->input->post('discount'),
					'wedding_meta_title' => $this->input->post('weddingmetatitle'),
					'wedding_meta_keywords' => $this->input->post('weddingkeywords'),
					'wedding_meta_desc' => $this->input->post('weddingmetadesc'), 'wedding_amenities' => $amenities,
					'wedding_exclusions' => $exclusions, 'wedding_payment_opt' => $paymentopt,
					'wedding_max_adults' => intval($this->input->post('maxadult')),
					'wedding_max_child' => intval($this->input->post('maxchild')),
					'wedding_max_infant' => intval($this->input->post('maxinfant')),
					'wedding_adult_price' => floatval($this->input->post('adultprice')),
					'wedding_child_price' => floatval($this->input->post('childprice')),
					'wedding_infant_price' => floatval($this->input->post('infantprice')),
					'adult_status' => intval($this->input->post('adultstatus')),
					'child_status' => intval($this->input->post('childstatus')),
					'infant_status' => intval($this->input->post('infantstatus')),
					'wedding_days' => intval($this->input->post('weddingdays')),
					'wedding_nights' => intval($this->input->post('weddingnights')),
					'wedding_privacy' => $this->input->post('weddingprivacy'),
					'wedding_status' => $this->input->post('weddingtatus'),
					'wedding_related' => $relatedwedding,
					'wedding_comm_fixed' => $commfixed, 'wedding_comm_percentage' => $commper,
					'wedding_tax_fixed' => $taxfixed, 'wedding_tax_percentage' => $taxper,
					'wedding_email' => $this->input->post('weddingemail'),
					'wedding_phone' => $this->input->post('weddingphone'),
					'wedding_website' => $this->input->post('weddingwebsite'),
					'wedding_fulladdress' => $this->input->post('weddingfulladdress'),
					'wedding_featured_forever' => $isforever);
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);

				$this->updateWeddingLocations($this->input->post('locations'), $id);
	}

// Add wedding settings data
		function add_settings_data() {
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
				$this->db->insert('pt_wedding_types_settings', $data);
		}

// update wedding settings data
		function update_settings_data() {
				$id = $this->input->post('id');
				$data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
				$this->db->where('sett_id', $id);
				$this->db->update('pt_wedding_types_settings', $data);
		}

// Disable wedding settings
		function disable_settings($id) {
				$data = array('sett_status' => 'No');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_wedding_types_settings', $data);
		}

// Enable wedding settings
		function enable_settings($id) {
				$data = array('sett_status' => 'Yes');
				$this->db->where('sett_id', $id);
				$this->db->update('pt_wedding_types_settings', $data);
		}

// Delete wedding settings
		function delete_settings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_wedding_types_settings');
		}

// get all wedding for related selection for backend
		function select_related_wedding($id = null) {
				$this->db->select('wedding_title,wedding_id');
				if (!empty ($id)) {
						$this->db->where('wedding_id !=', $id);
				}
				return $this->db->get('pt_wedding')->result();
		}

// Get wedding settings data
		function get_wedding_settings_data($type) {
			if(!empty($type)){
             	$this->db->where('sett_type', $type);
		  }

				$this->db->order_by('sett_id', 'desc');
				return $this->db->get('pt_wedding_types_settings')->result();
		}

// Get wedding settings data for adding wedding
		function get_tsettings_data($type) {
				$this->db->where('sett_type', $type);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_wedding_types_settings')->result();
		}

// Get wedding settings data for adding wedding
		function get_tsettings_data_front($type, $items) {
				$this->db->where('sett_type', $type);
				$this->db->where_in('sett_id', $items);
				$this->db->where('sett_status', 'Yes');
				return $this->db->get('pt_wedding_types_settings')->result();
		}

// add Wedding images by type
		function add_wedding_image($type, $filename, $weddingid) {
				$imgorder = 0;
				if ($type == "slider") {
						$this->db->where('timg_type', 'slider');
						$this->db->where('timg_wedding_id', $weddingid);
						$imgorder = $this->db->get('pt_wedding_images')->num_rows();
						$imgorder = $imgorder + 1;
				}
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_wedding_id', $weddingid);
				$hasdefault = $this->db->get('pt_wedding_images')->num_rows();
				if ($hasdefault < 1) {
						$type = 'default';
				}
				$approval = pt_admin_gallery_approve();
				$data = array('timg_wedding_id' => $weddingid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
				$this->db->insert('pt_wedding_images', $data);
		}

// update wedding map order
		function update_map_order($id, $order) {
				$data = array('map_order' => $order);
				$this->db->where('map_id', $id);
				$this->db->update('pt_wedding_maps', $data);
		}


// update wedding order
		function update_wedding_order($id, $order) {
				$data = array('wedding_order' => $order);
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);
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



			 $data = array('wedding_is_featured' => $isfeatured, 'wedding_featured_forever' => $isforever);
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);
		}
// Disable Wedding

		public function disable_wedding($id) {
				$data = array('wedding_status' => 'No');
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);
		}
// Enable Wedding

		public function enable_wedding($id) {
				$data = array('wedding_status' => 'Yes');
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);
		}

// Delete wedding
		function delete_wedding($weddingid) {
				$weddingimages = $this->wedding_images($weddingid);
				foreach ($weddingimages['all_slider'] as $sliderimg) {
						$this->delete_image($sliderimg->timg_image,$sliderimg->timg_id,$weddingid);
				}


				$this->db->where('review_itemid', $weddingid);
				$this->db->where('review_module', 'wedding');
				$this->db->delete('pt_reviews');
				$this->db->where('map_wedding_id', $weddingid);
				$this->db->delete('pt_wedding_maps');

				$this->db->where('item_id', $weddingid);
                $this->db->delete('pt_wedding_translation');

                $this->db->where('wedding_id',$weddingid);
            	$this->db->delete('pt_wedding_locations');

				$this->db->where('wedding_id', $weddingid);
				$this->db->delete('pt_wedding');
		}

// Get Wedding Images
		function wedding_images($id) {
				$this->db->where('timg_wedding_id', $id);
				$this->db->where('timg_type', 'default');
				$q = $this->db->get('pt_wedding_images');
				$data['def_image'] = $q->result();
				$this->db->where('timg_type', 'slider');
				$this->db->order_by('timg_id', 'desc');
				$this->db->having('timg_wedding_id', $id);
				$q = $this->db->get('pt_wedding_images');
				$data['all_slider'] = $q->result();
				$data['slider_counts'] = $q->num_rows();
				return $data;
		}

//update wedding thumbnail
		function update_thumb($oldthumb, $newthumb, $weddingid) {
				$data = array('timg_type' => 'slider');
				$this->db->where('timg_id', $oldthumb);
				$this->db->where('timg_wedding_id', $weddingid);
				$this->db->update('pt_wedding_images', $data);
				$data2 = array('timg_type' => 'default');
				$this->db->where('timg_id', $newthumb);
				$this->db->where('timg_wedding_id', $weddingid);
				$this->db->update('pt_wedding_images', $data2);
		}

// Approve or reject Hotel Images
		function approve_reject_images() {
				$data = array('timg_approved' => $this->input->post('apprej'));
				$this->db->where('timg_id', $this->input->post('imgid'));
				$this->db->update('pt_wedding_images', $data);
		}

// update image order
		function update_image_order($imgid, $order) {
				$data = array('timg_order' => $order);
				$this->db->where('timg_id', $imgid);
				$this->db->update('pt_wedding_images', $data);
		}


// Delete wedding Images
		function delete_image($imgname, $imgid, $weddingid) {
				$this->db->where('timg_id', $imgid);
				$this->db->delete('pt_wedding_images');
                $this->updateWeddingThumb($weddingid,$imgname,"delete");
                @ unlink(PT_WEDDING_SLIDER_THUMB_UPLOAD . $imgname);
				@ unlink(PT_WEDDING_SLIDER_UPLOAD . $imgname);
		}

//update wedding thumbnail
		function updateWeddingThumb($weddingid,$imgname,$action) {
		  if($action == "delete"){
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image',$imgname);
            $this->db->where('wedding_id',$weddingid);
            $rs = $this->db->get('pt_wedding')->num_rows();
            if($rs > 0){
              $data = array(
              'thumbnail_image' => PT_BLANK_IMG
              );
              $this->db->where('wedding_id',$weddingid);
              $this->db->update('pt_wedding',$data);
            }
            }else{
              $data = array(
              'thumbnail_image' => $imgname
              );
              $this->db->where('wedding_id',$weddingid);
              $this->db->update('pt_wedding',$data);
            }

		}




		function offers_data($id) {
				/*$this->db->where('offer_module', 'wedding');
				$this->db->where('offer_item', $id);
				return $this->db->get('pt_special_offers')->result();*/
		}

		function add_to_map() {
				$maporder = 0;
				$weddingid = $this->input->post('weddingid');
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_wedding_id', $weddingid);
				$res = $this->db->get('pt_wedding_maps')->num_rows();
				$addtype = $this->input->post('addtype');
				if ($addtype == "visit") {
						$maporder = $res + 1;
				}
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_wedding_id' => $weddingid, 'map_order' => $maporder);
				$this->db->insert('pt_wedding_maps', $data);
		}

		function update_wedding_map() {
				$data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
				$this->db->where('map_id', $this->input->post('mapid'));
				$this->db->update('pt_wedding_maps', $data);
		}

		function has_start_end_city($type, $weddingid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', $type);
				$this->db->where('map_wedding_id', $weddingid);
				$nums = $this->db->get('pt_wedding_maps')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

		function get_wedding_map($weddingid) {
				$this->db->where('map_wedding_id', $weddingid);
				return $this->db->get('pt_wedding_maps')->result();
		}

		function delete_map_item($mapid) {
				$this->db->where('map_id', $mapid);
				$this->db->delete('pt_wedding_maps');
		}

// get related wedding for front-end
		function get_related_wedding($wedding) {
				$id = explode(",", $wedding);
				$this->db->select('pt_wedding.wedding_title,pt_wedding.wedding_slug,pt_wedding.wedding_id,pt_wedding.wedding_basic_price,pt_wedding.wedding_basic_discount,pt_wedding_types_settings.sett_name');
				$this->db->where_in('pt_wedding.wedding_id', $id);
/*  $this->db->where('pt_wedding_images.timg_type','default');
$this->db->join('pt_wedding_images','pt_wedding.wedding_id = pt_wedding_images.timg_wedding_id','left');*/
				$this->db->join('pt_wedding_types_settings', 'pt_wedding.wedding_type = pt_wedding_types_settings.sett_id', 'left');
				return $this->db->get('pt_wedding')->result();
		}

// Check wedding existence
		function wedding_exists($slug) {
				$this->db->select('wedding_id');
				$this->db->where('wedding_slug', $slug);
				$this->db->where('wedding_status', 'Yes');
				$nums = $this->db->get('pt_wedding')->num_rows();
				if ($nums > 0) {
						return true;
				}
				else {
						return false;
				}
		}

// List all wedding on front listings page
		function list_wedding_front($sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_wedding.wedding_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_wedding.wedding_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_wedding.wedding_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_wedding.wedding_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_wedding.wedding_order', 'asc');
				}
				$this->db->select('wedding_id');
				$this->db->group_by('wedding_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_wedding.wedding_adult_price >=', $minp);
						$this->db->where('pt_wedding.wedding_adult_price <=', $maxp);
				}

				$this->db->where('wedding_status', 'Yes');
				$query = $this->db->get('pt_wedding', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all wedding on front listings page by location
		function showWeddingByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				if ($orderby == "za") {
						$this->db->order_by('pt_wedding.wedding_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_wedding.wedding_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_wedding.wedding_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_wedding.wedding_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_wedding.wedding_order', 'asc');
				}
				$this->db->select('wedding_id');
				$this->db->group_by('wedding_id');

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_wedding.wedding_adult_price >=', $minp);
						$this->db->where('pt_wedding.wedding_adult_price <=', $maxp);
				}

				if(is_array($locs)){
                $this->db->where_in('pt_wedding.wedding_location',$locs);
                }else{
                $this->db->where('pt_wedding.wedding_location',$locs);
                }

				$this->db->where('wedding_status', 'Yes');
				$query = $this->db->get('pt_wedding', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// Search wedding from home page
		function search_wedding_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null) {
				$this->load->helper('wedding_front');
				$data = array();

				//$location = $this->input->get('location');

				$adults = $this->input->get('adults');
				$type = $this->input->get('type');

				//$sprice = $this->input->get('price');
				$stars = $this->input->get('stars');

				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_wedding.wedding_id,wedding_type,wedding_location,wedding_adult_price,wedding_title,wedding_max_adults,wedding_status,pt_wedding_locations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_wedding.wedding_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_wedding.wedding_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_wedding.wedding_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_wedding.wedding_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_wedding.wedding_order', 'asc');
				}
				elseif ($orderby == "p_lh") {
						$this->db->order_by('pt_wedding.wedding_adult_price', 'asc');
				}
				elseif ($orderby == "p_hl") {
						$this->db->order_by('pt_wedding.wedding_adult_price', 'desc');
				}

				if(!empty($location)){
					//$this->db->like('pt_wedding.wedding_location', $location);
					$this->db->where('pt_wedding_locations.location_id', $location);

				}


				if (!empty ($adults)) {
						$this->db->where('pt_wedding.wedding_max_adults >=', $adults);
				}

				if (!empty ($stars)) {
						$this->db->where('wedding_stars', $stars);
				}



				if (!empty ($type)) {
						$this->db->where('pt_wedding.wedding_type', $type);
				}

				if (!empty ($sprice)) {
						$sprice = explode("-", $sprice);
						$minp = $sprice[0];
						$maxp = $sprice[1];
						$this->db->where('pt_wedding.wedding_adult_price >=', $minp);
						$this->db->where('pt_wedding.wedding_adult_price <=', $maxp);
				}
				$this->db->group_by('pt_wedding.wedding_id');
				$this->db->join('pt_wedding_locations', 'pt_wedding.wedding_id = pt_wedding_locations.wedding_id');
				$this->db->where('pt_wedding.wedding_status', 'Yes');


		if(!empty($perpage)){

				$query = $this->db->get('pt_wedding', $perpage, $offset);

				}else{

				$query = $this->db->get('pt_wedding');

				}

				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

		function max_map_order($weddingid) {
				$this->db->select('map_id');
				$this->db->where('map_city_type', 'visit');
				$this->db->where('map_wedding_id', $weddingid);
				return $this->db->get('pt_wedding_maps')->num_rows();
		}

// get default image of wedding
		function default_wedding_img($id) {
				$this->db->where('timg_type', 'default');
				$this->db->where('timg_approved', '1');
				$this->db->where('timg_wedding_id', $id);
				$res = $this->db->get('pt_wedding_images')->result();
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
				$this->db->where('wedding_slug', $cslug);
				$this->db->where('wedding_id !=', $id);
				$nums = $this->db->get('pt_wedding')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('wedding_title' => $this->input->post('title'), 'wedding_slug' => $cslug, 'wedding_desc' => $this->input->post('desc'), 'wedding_policy' => $this->input->post('policy'));
				$this->db->where('wedding_id', $id);
				$this->db->update('pt_wedding', $data);
				return $cslug;
		}

// Adds translation of some fields data
		function add_translation($postdata, $weddingid) {
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
                'item_id' => $weddingid,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_wedding_translation', $data);
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
				$this->db->insert('pt_wedding_translation', $data);

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
			    $this->db->update('pt_wedding_translation', $data);
                }


              }

                }

		}

		 function getBackTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('item_id',$id);
            return $this->db->get('pt_wedding_translation')->result();

        }

         function weddingGallery($slug){

          $this->db->select('pt_wedding.thumbnail_image as thumbnail,pt_wedding_images.timg_id as id,pt_wedding_images.timg_wedding_id as itemid,pt_wedding_images.timg_type as type,pt_wedding_images.timg_image as image,pt_wedding_images.timg_order as imgorder,pt_wedding_images.timg_image as image,pt_wedding_images.timg_approved as approved');
          $this->db->where('pt_wedding.wedding_slug',$slug);
          $this->db->join('pt_wedding_images', 'pt_wedding.wedding_id = pt_wedding_images.timg_wedding_id', 'left');
          $this->db->order_by('pt_wedding_images.timg_id','desc');
          return $this->db->get('pt_wedding')->result();

        }

        function addPhotos($id,$filename){

         $this->db->select('thumbnail_image');
         $this->db->where('wedding_id',$id);
         $rs = $this->db->get('pt_wedding')->result();
         if($rs[0]->thumbnail_image == PT_BLANK_IMG){

               $data = array('thumbnail_image' => $filename);
               $this->db->where('wedding_id',$id);
               $this->db->update('pt_wedding',$data);
         }

        //add photos to wedding images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_wedding_id', $id);
        $imgorder = $this->db->get('pt_wedding_images')->num_rows();
        $imgorder = $imgorder + 1;

				$approval = pt_admin_gallery_approve();

		    	$insdata = array(
                'timg_wedding_id' => $id,
                'timg_type' => 'slider',
                'timg_image' => $filename,
                'timg_order' => $imgorder,
                'timg_approved' => $approval
                );

				$this->db->insert('pt_wedding_images', $insdata);


        }

        function assignWedding($wedding,$userid){

          if(!empty($wedding)){
          $userwedding = $this->userOwnedWedding($userid);
                foreach($userwedding as $tt){
                   if(!in_array($tt,$wedding)){
                    $ddata = array(
                   'wedding_owned_by' => '1'
                   );
                   $this->db->where('wedding_id',$tt);
                   $this->db->update('pt_wedding',$ddata);
                   }
                }

                foreach($wedding as $t){
                   $data = array(
                   'wedding_owned_by' => $userid
                   );
                   $this->db->where('wedding_id',$t);
                   $this->db->update('pt_wedding',$data);

                 }

                 }
        }

        function userOwnedWedding($id){
          $result = array();
          if(!empty($id)){
          $this->db->where('wedding_owned_by',$id);
          }

          $rs = $this->db->get('pt_wedding')->result();
          if(!empty($rs)){
            foreach($rs as $r){
              $result[] = $r->wedding_id;
            }
          }
          return $result;
        }

        // get number of photos of wedding
		function photos_count($weddingid) {
				$this->db->where('timg_wedding_id', $weddingid);
				return $this->db->get('pt_wedding_images')->num_rows();
		}

		function updateWeddingSettings() {
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

		// get popular wedding
		function popular_wedding_front() {
				$settings = $this->Settings_model->get_front_settings('wedding');
				$limit = $settings[0]->front_popular;
				$orderby = $settings[0]->front_popular_order;

                $this->db->select('pt_wedding.wedding_id,pt_wedding.wedding_status,pt_reviews.review_overall,pt_reviews.review_itemid');

                $this->db->select_avg('pt_reviews.review_overall', 'overall');
				$this->db->order_by('overall', 'desc');
				$this->db->group_by('pt_wedding.wedding_id');
				$this->db->join('pt_reviews', 'pt_wedding.wedding_id = pt_reviews.review_itemid');
				$this->db->where('wedding_status', 'yes');
				$this->db->limit($limit);
			   	return $this->db->get('pt_wedding')->result();
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
				$this->db->insert('pt_wedding_types_settings', $data);
                return $this->db->insert_id();
                $this->session->set_flashdata('flashmsgs', "Updated Successfully");

		}

// update wedding settings data
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
				$this->db->update('pt_wedding_types_settings', $data);
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
				$this->db->insert('pt_wedding_types_settings_translation', $data);

                }else{

                 $data = array(
                'trans_name' => $name
                );
				$this->db->where('sett_id', $id);
				$this->db->where('trans_lang', $lang);
			    $this->db->update('pt_wedding_types_settings_translation', $data);

              }


              }

                }
		}


         function getBackSettingsTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_wedding_types_settings_translation')->result();

        }

        // Delete hotel settings
		function deleteTypeSettings($id) {
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_wedding_types_settings');

                $this->db->where('sett_id', $id);
				$this->db->delete('pt_wedding_types_settings_translation');
		}

				// Delete multiple wedding settings
		function deleteMultiplesettings($id, $type) {
				$this->db->where('sett_id', $id);
				$this->db->where('sett_type',$type);
				$this->db->delete('pt_wedding_types_settings');

				$rowsDeleted = $this->db->affected_rows();

				if($rowsDeleted > 0){
				$this->db->where('sett_id', $id);
				$this->db->delete('pt_wedding_types_settings_translation');
				}


		}

         function getTypesTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('sett_id',$id);
            return $this->db->get('pt_wedding_types_settings_translation')->result();

        }

        function updateWeddingLocations($locations, $weddingid){

        	$this->db->where('wedding_id',$weddingid);
        	$this->db->delete('pt_wedding_locations');
        	$position = 0;

        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$position++;
        			$data = array('position' => $position,'location_id' => $loc, 'wedding_id' => $weddingid);
        			$this->db->insert('pt_wedding_locations', $data);
        		}
        	}

        }

        function isWeddingLocation($i, $locid, $weddingid){
        	$this->db->where('position', $i);
        	$this->db->where('location_id', $locid);
        	$this->db->where('wedding_id', $weddingid);
        	$rs = $this->db->get('pt_wedding_locations')->num_rows();
        	if($rs > 0){
        		return "selected";
        	}else{
        		return "";
        	}
        }

        function weddingSelectedLocations($weddingid){
          $result = array();
          $this->db->where('wedding_id', $weddingid);
          $res = $this->db->get('pt_wedding_locations')->result();
          foreach($res as $r){
            $locInfo = pt_LocationsInfo($r->location_id);
            $result[$r->position] = (object)array('id' => $r->location_id,'name' => $locInfo->city.", ".$locInfo->country);
          }
         return $result;

        }

}
