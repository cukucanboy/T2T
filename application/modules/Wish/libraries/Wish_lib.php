<?php

class Wish_lib {
/**
* Protected variables
*/
		protected $ci = NULL; //codeigniter instance
		protected $db; //database instatnce instance
		protected $wishid;
		public $title;
		public $slug;
		public $desc;
		public $date;
		public $thumbnail;
		public $hits;
		public $langdef;
		protected $lang;

		function __construct() {
//get the CI instance
			$this->ci = & get_instance();
			$this->db = $this->ci->db;
			$this->ci->load->model('Wish/Wish_model');
			$lang = $this->ci->session->userdata('set_lang');
			$defaultlang = pt_get_default_language();

			$this->set_lang($this->ci->session->userdata('set_lang'));
            $this->langdef = DEFLANG;
		}

		function set_lang($lang){
                 if (empty ($lang)) {
                   $defaultlang = pt_get_default_language();
						$this->lang = $defaultlang;
				}
				else {
						$this->lang = $lang;
				}
        }

		function set_wishid($slug) {
			$this->db->select('wish_id');
			$this->db->where('wish_slug', $slug);
			$r = $this->db->get('pt_advertising_wish')->result();
			$this->wishid = $r[0]->wish_id;
		}

//set car id by id
		function set_id($id) {
			$this->wishid = $id;
		}

		function get_id() {
			return $this->wishid;
		}

		function settings() {
			return $this->ci->Settings_model->get_front_settings('wish');
		}

		function show_wish($offset = null,$perpage = null) {
			$totalSegments = $this->ci->uri->total_segments();
			$data = array();
			$settings = $this->settings();
			if(empty($perpage)){
				$perpage = $settings[0]->front_listings;
			}

			$sortby = $this->ci->input->get('sortby');
			if (!empty ($sortby)) {
				$orderby = $sortby;
			}
			else {
				$orderby = $settings[0]->front_listings_order;
			}
			$rh = $this->ci->Wish_model->list_wish_front();
			$data['all_wish'] = $this->ci->Wish_model->list_wish_front($perpage, $offset, $orderby);
			$data['paginationinfo'] = array('base' => 'wish/listing', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
			return $data;
		}

		function wish_details() {
			$this->db->select('pt_advertising_wish.*');
			$this->db->where('pt_advertising_wish.wish_id', $this->wishid);
			$details = $this->db->get('pt_advertising_wish')->result();
			$this->slug = $details[0]->wish_slug;
			$this->title = $this->get_title($details[0]->wish_title);
			$this->desc = $this->get_description($details[0]->wish_desc);
			$this->thumbnail = $this->wish_thumbnail($details[0]->wish_id);

			$this->hits = $details[0]->wish_visits;
			return $details;
		}

		function wish_short_details() {
			$this->db->select('wish_title,wish_desc');
			$this->db->where('pt_advertising_wish.wish_id', $this->wishid);
			$details = $this->db->get('pt_advertising_wish')->result();
			$this->slug = $details[0]->wish_slug;
			$this->title = $this->get_title($details[0]->wish_title);
			$this->desc = $this->get_description($details[0]->wish_desc);
			$this->thumbnail = $this->wish_thumbnail($this->wishid);
			return $details;
		}

		function wish_thumbnail($id) {
			$res = $this->ci->Wish_model->wish_thumbnail($id);
			if (!empty ($res)) {
				return PT_WISH_IMAGES. $res;
			}
			else {
				return PT_BLANK;
			}
		}

		function search_wish($offset = null) {
			$totalSegments = $this->ci->uri->total_segments();
			$data = array();
			$settings = $this->settings();
			$perpage = $settings[0]->front_search;
			$orderby = $settings[0]->front_search_order;
			$rh = $this->ci->Wish_model->search_wish_front();
			$data['all_wish'] = $this->ci->Wish_model->search_wish_front($perpage, $offset, $orderby);
			$data['paginationinfo'] = array('base' => 'wish/search', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
			return $data;
		}

		function getWishHomepage() {
			$results = new stdClass;
			$settings = $this->settings();
			$perpage = $settings[0]->front_homepage;
			$orderby = $settings[0]->front_homepage_order;
			$wish = $this->ci->Wish_model->home_wish($perpage, $orderby);
			$results->wish  = array();

			foreach($wish as $p){
				$this->set_id($p->wish_id);
				$this->wish_short_details();
				$shortdesc = strip_tags($this->desc);
				$results->wish[] = (object)array('id' => $p->wish_id, 'title' => $this->title,'thumbnail' => $this->thumbnail,'desc' => $this->desc, 'shortDesc' =>  character_limiter($shortdesc,60),'slug' => base_url().'wish/'.$this->slug);
			}
			return $results;
		}



		function get_title($deftitle) {
			if ($this->lang == $this->langdef) {
				$title = $deftitle;
			}
			else {
				$this->db->where('item_id', $this->wishid);
				$this->db->where('trans_lang', $this->lang);
				$res = $this->db->get('pt_advertising_wish_translation')->result();
				$title = $res[0]->trans_title;
				if (empty ($title)) {
					$title = $deftitle;
				}
			}
			return $title;
		}

		function get_description($defdesc) {
			if ($this->lang == $this->langdef) {
				$desc = $defdesc;
			}
			else {
				$this->db->where('item_id', $this->wishid);
				$this->db->where('trans_lang', $this->lang);
				$res = $this->db->get('pt_advertising_wish_translation')->result();
				$desc = $res[0]->trans_desc;
				if (empty ($desc)) {
					$desc = $defdesc;
				}
			}
			return $desc;
		}


		function translated_data($lang) {
			$this->db->where('item_id', $this->wishid);
			$this->db->where('trans_lang', $lang);
			return $this->db->get('pt_advertising_wish_translation')->result();
		}

		function getLimitedResultObject($wish){
          $result = array();
          if(!empty($wish)){
          	foreach($wish as $post){


            	$result[] = (object)array('title' => $post->wish_title, 'slug' => base_url()."wish/".$post->wish_slug);


             }
          }

            return $result;
        }


		 public function siteMapData(){
          		$wishData = array();
				$this->db->select('wish_title,wish_slug');
				$this->db->where('wish_status','Yes');
				$result = $this->db->get('pt_advertising_wish');
				$wish = $result->result();
				if(!empty($wish)){

				$wishData = $this->getLimitedResultObject($wish);

				}

				return $wishData;
        }

	}
