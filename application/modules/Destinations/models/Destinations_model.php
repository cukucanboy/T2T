<?php

class Destinations_model extends CI_Model {
		public $langdef;

		function __construct() {
// Call the Model constructor
				parent :: __construct();
				$this->langdef = DEFLANG;
		}



		//get destinations list admin panel
		function getDestinationBackend($id){

			$this->db->where('destination_status','Yes');
			$this->db->order_by('destination_id','desc');
			return $this->db->get('pt_destinations')->result();

		}


// Search destinations from home page
		function search_destinations_front($perpage = null, $offset = null, $orderby = null, $cities = null) {
				$data = array();
				$text = $this->input->get('s');
//$days = pt_count_days($checkin,$checkout);
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_destinations.*,pt_destinations_translation.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_destinations.destination_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_destinations.destination_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_destinations.destination_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_destinations.destination_id', 'desc');
				}
				$this->db->like('pt_destinations.destination_title', $text);
				$this->db->or_like('pt_destinations.destination_desc', $text);
				$this->db->or_like('pt_destinations_translation.trans_title', $text);
				$this->db->or_like('pt_destinations_translation.trans_desc', $text);
				$this->db->group_by('pt_destinations.destination_id');
				$this->db->join('pt_destinations_translation', 'pt_destinations.destination_id = pt_destinations_translation.item_id', 'left');
				$this->db->where('pt_destinations.destination_status', 'Yes');
				$query = $this->db->get('pt_destinations', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}



// get popular destinations for front-end on number of visits
		function get_popular_destinations($limit) {
				$this->db->select('pt_destinations.destination_title,pt_destinations.destination_slug,pt_destinations.destination_id');
				$this->db->where('destination_visits >', '0');
				$this->db->order_by('destination_visits', 'desc');
				$this->db->limit($limit);
				return $this->db->get('pt_destinations')->result();
		}

//update destinations visits count
		function update_visits($id, $hits) {
				$data = array('destination_visits' => $hits);
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
		}

// get related destinations for front-end
		function get_related_destinations($destinations, $limit) {
				$id = explode(",", $destinations);
				$this->db->select('pt_destinations.destination_title,pt_destinations.destination_slug,pt_destinations.destination_id');
				$this->db->where_in('pt_destinations.destination_id', $id);
				$this->db->limit($limit);
				return $this->db->get('pt_destinations')->result();
		}

// get default image of post
		function destination_thumbnail($id) {
				$this->db->where('destination_id', $id);
				$res = $this->db->get('pt_destinations')->result();
				if (!empty ($res)) {
						return $res[0]->destination_images;
				}
				else {
						return '';
				}
		}

// List all destinations on front listings page
		function list_destinations_front($perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_destinations.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_destinations.destination_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_destinations.destination_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_destinations.destination_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_destinations.destination_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_destinations.destination_order', 'asc');
				}
				$this->db->group_by('pt_destinations.destination_id');
					$this->db->where('pt_destinations.destination_status', 'Yes');
				$query = $this->db->get('pt_destinations', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all Home Destinations
		function home_destinations($limit) {
				$this->db->select('pt_destinations.destination_id,pt_destinations.destination_order,pt_destinations.destination_title,pt_destinations.destination_slug,pt_destinations.destination_desc,pt_destinations.destination_status');
				$this->db->order_by('pt_destinations.destination_order', 'asc');
				$this->db->where('pt_destinations.destination_status', 'Yes');
				$this->db->limit($limit);
				$res = $this->db->get('pt_destinations')->result();
				return $res;
		}

// List all destinations for API
		function api_list_destinations() {
				$this->db->select('pt_destinations.*');
				$this->db->group_by('pt_destinations.destination_id');

				$this->db->order_by('pt_destinations.destination_id', 'desc');
				$this->db->where('pt_destinations.destination_status', 'Yes');
				return $this->db->get('pt_destinations')->result();
		}

// update front settings
		function update_front_settings() {
				$ufor = $this->input->post('updatefor');
				$data = array('front_icon' => $this->input->post('page_icon'), 'front_popular' => $this->input->post('popular'), 'front_homepage' => $this->input->post('home'), 'front_homepage_order' => $this->input->post('order'), 'front_latest' => $this->input->post('latest'),
				'front_listings' => $this->input->post('listings'), 'front_listings_order' => $this->input->post('listingsorder'), 'front_search' => $this->input->post('searchresult'), 'front_search_order' => $this->input->post('searchorder'), 'front_related' => $this->input->post('related'),
				'testing_mode' => $this->input->post('relatedstatus'), 'linktarget' => $this->input->post('target'), 'header_title' => $this->input->post('headertitle'), 'front_homepage_hero' => $this->input->post('showonhomepage'));
				$this->db->where('front_for', $ufor);
				$this->db->update('pt_front_settings', $data);
				$this->session->set_flashdata('flashmsgs', "Updated Successfully");
		}

// get all destinations info
		function get_all_destinations_back() {
				$this->db->select('pt_destinations.*');
				$this->db->order_by('pt_destinations.destination_id', 'desc');
				$query = $this->db->get('pt_destinations');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all destinations info with limit
		function get_all_destinations_back_limit($perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_destinations.*');
				$this->db->order_by('pt_destinations.destination_id', 'desc');
				$query = $this->db->get('pt_destinations', $perpage, $offset);
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all destinations info  by advance search
		function adv_search_all_destinations_back_limit($data, $perpage = null, $offset = null, $orderby = null) {
				$status = $data["status"];
				$destinationtitle = $data["posttitle"];
				$category = $data["category"];
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_destinations.*');
				if (!empty ($destinationtitle)) {
						$this->db->like('pt_destinations.destination_title', $destinationtitle);
				}

				$this->db->where('pt_destinations.destination_status', $status);
				$this->db->order_by('pt_destinations.destination_id', 'desc');
				$query = $this->db->get('pt_destinations', $perpage, $offset);
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// add Destination data
		function add_destination($filename_db = null) {
				if (empty ($filename_db)) {
						$filename_db = "";
				}
				$this->db->select("destination_id");
				$this->db->order_by("destination_id", "desc");
				$query = $this->db->get('pt_destinations');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$destinationlastid = 1;
				}
				else {
						$destinationlastid = $lastid[0]->destination_id + 1;
				}

              $destinationslug = $this->input->post('slug');
              if(empty($destinationslug)){
              $destinationslug = $this->makeSlug($this->input->post('title'),$destinationlastid);
              }else{
              $destinationslug = $this->makeSlug($destinationslug,$destinationlastid);
              }


				$destinationcount = $query->num_rows();
				$destinationorder = $destinationcount + 1;

				$relateddestinations = @ implode(",", $this->input->post('relateddestinations'));
				$data = array('destination_title' => $this->input->post('title'),
                'destination_slug' => $destinationslug,
                'destination_desc' => $this->input->post('desc'),
                'destination_meta_keywords' => $this->input->post('keywords'),
                'destination_meta_desc' => $this->input->post('metadesc'),
                'destination_status' => $this->input->post('status'),
                'destination_order' => $destinationorder,
                'destination_images' => $filename_db,
								'latitude' => $this->input->post('latitude'),
								'longitude' => $this->input->post('longitude')
							);
				$this->db->insert('pt_destinations', $data);
                $destinationid = $this->db->insert_id();
                $this->add_translation($this->input->post('translated'),$destinationid);
		}

// update Post data
		function update_destination($id, $filename_db = null) {
				if (empty ($filename_db)) {
						$filename_db = $this->input->post('defimg');
				}
				$this->db->select("destination_id");
				$this->db->order_by("destination_id", "desc");
				$query = $this->db->get('pt_destinations');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$destinationlastid = 1;
				}
				else {
						$destinationlastid = $lastid[0]->destination_id + 1;
				}
				$destinationcount = $query->num_rows();
				$destinationorder = $destinationcount + 1;
				$slug = $this->input->post('slug');
				if (empty ($slug)) {
						$this->db->select("destination_id");
						$this->db->where("destination_id !=", $id);
						$this->db->where("destination_title", $this->input->post('title'));
						$queryc = $this->db->get('pt_destinations')->num_rows();
						if ($queryc > 0) {
								$destinationslug = create_url_slug($this->input->post('title')) . "-" . $destinationlastid;
						}
						else {
								$destinationslug = create_url_slug($this->input->post('title'));
						}
				}
				else {
						$this->db->select("destination_id");
						$this->db->where("destination_id !=", $id);
						$this->db->where("destination_slug", $this->input->post('slug'));
						$queryc = $this->db->get('pt_destinations')->num_rows();
						if ($queryc > 0) {
								$destinationslug = create_url_slug($this->input->post('slug')) . "-" . $destinationlastid;
						}
						else {
								$destinationslug = create_url_slug($this->input->post('slug'));
						}
				}
				$relateddestinations = @ implode(",", $this->input->post('relateddestinations'));
				$data = array('destination_title' => $this->input->post('title'),
				'destination_slug' => $destinationslug,
				'destination_desc' => $this->input->post('desc'),
				 'destination_meta_keywords' => $this->input->post('keywords'),
				  'destination_meta_desc' => $this->input->post('metadesc'),
					 'destination_status' => $this->input->post('status'),
					 'destination_images' => $filename_db ,
					 'latitude' => $this->input->post('latitude'),
					 'longitude' => $this->input->post('longitude')
				 );
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
      $this->update_translation($this->input->post('translated'),$id);
		}





// Disable post

		public function disable_post($id) {
				$data = array('destination_status' => 'No');
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
		}
// Enable category


// Enable post

		public function enable_post($id) {
				$data = array('destination_status' => 'Yes');
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
		}

// get all destinations for related selection for backend
		function select_related_destinations($id = null) {
				$this->db->select('destination_title,destination_id');
				if (!empty ($id)) {
						$this->db->where('destination_id !=', $id);
				}
				return $this->db->get('pt_destinations')->result();
		}

		function destinations_photo($id = null) {

        $tempFile = $_FILES['defaultphoto']['tmp_name'];
						$fileName = $_FILES['defaultphoto']['name'];
						$fileName = str_replace(" ", "-", $_FILES['defaultphoto']['name']);
						$fig = rand(1, 999999);

						if (strpos($fileName,'php') !== false) {

						}else{

						$saveFile = $fig . '_' . $fileName;

						$targetPath = PT_DESTINATION_IMAGES_UPLOAD;

						$targetFile = $targetPath . $saveFile;
						move_uploaded_file($tempFile, $targetFile);
							if (!empty ($id)) {
										$this->update_destination($id, $saveFile);
										$oldimg = $this->input->destination('defimg');
										if (!empty ($oldimg)) {
												@ unlink(PT_DESTINATION_IMAGES_UPLOAD . $oldimg);
										}

								}
								else {
										$this->add_destination($saveFile);

								}

							}


		}

// get file extension
		function __getExtension($str) {
				$i = strrpos($str, ".");
				if (!$i) {
						return "";
				}
				$l = strlen($str) - $i;
				$ext = substr($str, $i + 1, $l);
				return $ext;
		}

// update destination order
		function update_destination_order($id, $order) {
				$data = array('destination_order' => $order);
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
		}

// get all data of single destination by slug
		function get_destination_data($slug) {
				$this->db->where('destination_slug', $slug);
				return $this->db->get('pt_destinations')->result();
		}

		function delete_destination($id) {
				$this->delete_image($id);
				$this->db->where('destination_id', $id);
				$this->db->delete('pt_destinations');

                $this->db->where('item_id', $id);
				$this->db->delete('pt_destinations_translation');
		}

// Delete destination Images
		function delete_image($id) {
				$this->db->where('destination_id', $id);
				$res = $this->db->get('pt_destinations')->result();
				$img = $res[0]->destination_images;
				if (!empty ($img)) {
						@ unlink(PT_DESTINATION_IMAGES_UPLOAD . $img);
				}
		}

// update translated data os some fields in english
		function update_english($id) {
				$cslug = create_url_slug($this->input->post('title'));
				$this->db->where('destination_slug', $cslug);
				$this->db->where('destination_id !=', $id);
				$nums = $this->db->get('pt_destinations')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('destination_title' => $this->input->post('title'), 'destination_slug' => $cslug, 'destination_desc' => $this->input->post('desc'));
				$this->db->where('destination_id', $id);
				$this->db->update('pt_destinations', $data);
				return $cslug;
		}


      // Adds translation of some fields data
		function add_translation($destinationdata,$id) {
		  foreach($destinationdata as $lang => $val){
		     if(array_filter($val)){
		        $title = $val['title'];
                $desc = $val['desc'];
                $metadesc = $val['metadesc'];
				$kewords = $val['keywords'];

                  $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_meta_desc' => $metadesc,
                'trans_keywords' => $kewords,
                'item_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_destinations_translation', $data);

                }

                }


		}

        // Update translation of some fields data
		function update_translation($destinationdata,$id){

       foreach($destinationdata as $lang => $val){
		     if(array_filter($val)){
		        $title = $val['title'];
                $desc = $val['desc'];
				$metadesc = $val['metadesc'];
				$kewords = $val['keywords'];
                $transAvailable = $this->getBackDestinationsTranslation($lang,$id);

                if(empty($transAvailable)){
                 $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_meta_desc' => $metadesc,
                'trans_keywords' => $kewords,
                'item_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_destinations_translation', $data);

                }else{
                 $data = array(
                'content_page_title' => $title,
                'content_body' => $desc,
                'content_meta_desc' => $metadesc,
                'content_meta_keywords' => $kewords,
                );
				$this->db->where('content_page_id', $id);
				$this->db->where('content_lang_id', $lang);
			    $this->db->update('pt_cms_content', $data);

                 $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_meta_desc' => $metadesc,
                'trans_keywords' => $kewords,
                       );
				$this->db->where('item_id', $id);
				$this->db->where('trans_lang', $lang);
				$this->db->update('pt_destinations_translation', $data);

                }


              }

                }
		}

    function getBackDestinationsTranslation($lang, $id) {
				$this->db->where('trans_lang', $lang);
				$this->db->where('item_id', $id);
				return $this->db->get('pt_destinations_translation')->result();
		}

    function makeSlug($title,$destinationlastid = null){
                        $slug = create_url_slug($title);
                        $this->db->select("destination_id");
						$this->db->where("destination_slug", $slug);
                        if(!empty($destinationlastid)){
                         $this->db->where('destination_id !=',$destinationlastid);
                        }
						$queryc = $this->db->get('pt_destinations')->num_rows();
						if ($queryc > 0) {
								$slug = $slug."-".$destinationlastid;
						}
                        return $slug;
    }


		//get details of Destination
		function getDestinationDetails($id, $lang = null){
			$this->db->where('destination_id',$id);
			$result = $this->db->get('pt_destinations')->result();
			$response = new stdClass;
			if(!empty($result[0]->destination_title)){
				$response->isValid = TRUE;
			}else{
				$response->isValid = FALSE;
			}

			if(empty($lang) || $lang == DEFLANG){
			$response->destinations = $result[0]->destination_title;

			}else{

			$this->db->where('item_id',$id);
			$this->db->where('trans_lang',$lang);
			$Transresult = $this->db->get('pt_destinations_translation')->result();
			if(empty($Transresult[0]->trans_title)){
			$response->destinations = $result[0]->destination_title;
			}else{
			$response->destinations = $Transresult[0]->trans_title;

			}


			}
      $response->destination_slug = $result[0]->destination_slug;
			$response->latitude = $result[0]->latitude;
			$response->longitude = $result[0]->longitude;
			$response->status = $result[0]->destination_status;
			$response->destination_id = $id;
			return $response;

		}


}
