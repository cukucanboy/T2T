<?php

class Destinations_lib {
/**
* Protected variables
*/
		protected $ci = NULL; //codeigniter instance
		protected $db; //database instatnce instance
		protected $destinationid;
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
			$this->ci->load->model('Destinations/Destinations_model');
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

		function set_destinationsid($slug) {
			$this->db->select('destination_id');
			$this->db->where('destination_slug', $slug);
			$r = $this->db->get('pt_destinations')->result();
			$this->destinationid = $r[0]->destination_id;
		}

//set car id by id
		function set_id($id) {
			$this->destinationid = $id;
		}

		function get_id() {
			return $this->destinationid;
		}

		function settings() {
			return $this->ci->Settings_model->get_front_settings('destinations');
		}

		function show_destinations($offset = null,$perpage = null) {
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
			$rh = $this->ci->Destinations_model->list_destinations_front();
			$data['all_destinations'] = $this->ci->Destinations_model->list_destinations_front($perpage, $offset, $orderby);
			$data['paginationinfo'] = array('base' => 'destinations/listing', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
			return $data;
		}

		function destination_details() {
			$this->db->select('pt_destinations.*');
			$this->db->where('pt_destinations.destination_id', $this->destinationid);
			$details = $this->db->get('pt_destinations')->result();
			$this->slug = $details[0]->destination_slug;
			$this->title = $this->get_title($details[0]->destination_title);
			$this->desc = $this->get_description($details[0]->destination_desc);
			$this->thumbnail = $this->destination_thumbnail($details[0]->destination_id);

			$this->hits = $details[0]->destination_visits;
			return $details;
		}

		function destination_short_details() {
			$this->db->select('destination_title,destination_slug,destination_desc');
			$this->db->where('pt_destinations.destination_id', $this->destinationid);
			$details = $this->db->get('pt_destinations')->result();
			$this->slug = $details[0]->destination_slug;
			$this->title = $this->get_title($details[0]->destination_title);
			$this->desc = $this->get_description($details[0]->destination_desc);
			$this->thumbnail = $this->destination_thumbnail($this->destinationid);
			return $details;
		}

		function destination_thumbnail($id) {
			$res = $this->ci->Destinations_model->destination_thumbnail($id);
			if (!empty ($res)) {
				return PT_DESTINATION_IMAGES. $res;
			}
			else {
				return PT_BLANK;
			}
		}

		function search_destinations($offset = null) {
			$totalSegments = $this->ci->uri->total_segments();
			$data = array();
			$settings = $this->settings();
			$perpage = $settings[0]->front_search;
			$orderby = $settings[0]->front_search_order;
			$rh = $this->ci->Destinations_model->search_destinations_front();
			$data['all_destinations'] = $this->ci->Destinations_model->search_destinations_front($perpage, $offset, $orderby);
			$data['paginationinfo'] = array('base' => 'destinations/search', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
			return $data;
		}

		function getDestinationHomepage() {
			$results = new stdClass;
			$settings = $this->settings();
			$perpage = $settings[0]->front_homepage;
			$orderby = $settings[0]->front_homepage_order;
			$destinations = $this->ci->Destinations_model->home_destinations($perpage, $orderby);
			$results->destinations  = array();

			foreach($destinations as $p){
				$this->set_id($p->destination_id);
				$this->destination_short_details();
				$shortdesc = strip_tags($this->desc);
				$results->destinations[] = (object)array('id' => $p->destination_id, 'title' => $this->title,'thumbnail' => $this->thumbnail,'desc' => $this->desc, 'shortDesc' =>  character_limiter($shortdesc,60),'slug' => base_url().'advertising/'.$this->slug);
			}
			return $results;
		}



		function get_title($deftitle) {
			if ($this->lang == $this->langdef) {
				$title = $deftitle;
			}
			else {
				$this->db->where('item_id', $this->destinationid);
				$this->db->where('trans_lang', $this->lang);
				$res = $this->db->get('pt_destinations_translation')->result();
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
				$this->db->where('item_id', $this->destinationid);
				$this->db->where('trans_lang', $this->lang);
				$res = $this->db->get('pt_destinations_translation')->result();
				$desc = $res[0]->trans_desc;
				if (empty ($desc)) {
					$desc = $defdesc;
				}
			}
			return $desc;
		}


		function translated_data($lang) {
			$this->db->where('item_id', $this->destinationid);
			$this->db->where('trans_lang', $lang);
			return $this->db->get('pt_destinations_translation')->result();
		}

		function getLimitedResultObject($destinations){
          $result = array();
          if(!empty($destinations)){
          	foreach($destinations as $post){


            	$result[] = (object)array('title' => $post->destination_title, 'slug' => base_url()."destinations/".$post->destination_slug);


             }
          }

            return $result;
        }


		 public function siteMapData(){
          		$destinationsData = array();
				$this->db->select('destination_title,destination_slug');
				$this->db->where('destination_status','Yes');
				$result = $this->db->get('pt_destinations');
				$destinations = $result->result();
				if(!empty($destinations)){

				$destinationsData = $this->getLimitedResultObject($destinations);

				}

				return $destinationsData;
        }

	}
