<?php

class Wish_model extends CI_Model {
		public $langdef;

		function __construct() {
// Call the Model constructor
				parent :: __construct();
				$this->langdef = DEFLANG;
		}



		//get wish list admin panel
		function getWishBackend($id){

			$this->db->where('wish_status','Yes');
			$this->db->order_by('wish_id','desc');
			return $this->db->get('pt_advertising_wish')->result();

		}


// Search wish from home page
		function search_wish_front($perpage = null, $offset = null, $orderby = null, $cities = null) {
				$data = array();
				$text = $this->input->get('s');
//$days = pt_count_days($checkin,$checkout);
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_advertising_wish.*,pt_advertising_wish_translation.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_advertising_wish.wish_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_advertising_wish.wish_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_advertising_wish.wish_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				}
				$this->db->like('pt_advertising_wish.wish_title', $text);
				$this->db->or_like('pt_advertising_wish.wish_desc', $text);
				$this->db->or_like('pt_advertising_wish_translation.trans_title', $text);
				$this->db->or_like('pt_advertising_wish_translation.trans_desc', $text);
				$this->db->group_by('pt_advertising_wish.wish_id');
				$this->db->join('pt_advertising_wish_translation', 'pt_advertising_wish.wish_id = pt_advertising_wish_translation.item_id', 'left');
				$this->db->where('pt_advertising_wish.wish_status', 'Yes');
				$query = $this->db->get('pt_advertising_wish', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}



// get popular wish for front-end on number of visits
		function get_popular_wish($limit) {
				$this->db->select('pt_advertising_wish.wish_title,pt_advertising_wish.wish_slug,pt_advertising_wish.wish_id');
				$this->db->where('wish_visits >', '0');
				$this->db->order_by('wish_visits', 'desc');
				$this->db->limit($limit);
				return $this->db->get('pt_advertising_wish')->result();
		}

//update wish visits count
		function update_visits($id, $hits) {
				$data = array('wish_visits' => $hits);
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
		}

// get related wish for front-end
		function get_related_wish($wish, $limit) {
				$id = explode(",", $wish);
				$this->db->select('pt_advertising_wish.wish_title,pt_advertising_wish.wish_slug,pt_advertising_wish.wish_id');
				$this->db->where_in('pt_advertising_wish.wish_id', $id);
				$this->db->limit($limit);
				return $this->db->get('pt_advertising_wish')->result();
		}

// get default image of post
		function wish_thumbnail($id) {
				$this->db->where('wish_id', $id);
				$res = $this->db->get('pt_advertising_wish')->result();
				if (!empty ($res)) {
						return $res[0]->wish_images;
				}
				else {
						return '';
				}
		}

// List all wish on front listings page
		function list_wish_front($perpage = null, $offset = null, $orderby = null) {
				$data = array();
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_advertising_wish.*');
				if ($orderby == "za") {
						$this->db->order_by('pt_advertising_wish.wish_title', 'desc');
				}
				elseif ($orderby == "az") {
						$this->db->order_by('pt_advertising_wish.wish_title', 'asc');
				}
				elseif ($orderby == "oldf") {
						$this->db->order_by('pt_advertising_wish.wish_id', 'asc');
				}
				elseif ($orderby == "newf") {
						$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				}
				elseif ($orderby == "ol") {
						$this->db->order_by('pt_advertising_wish.wish_order', 'asc');
				}
				$this->db->group_by('pt_advertising_wish.wish_id');
					$this->db->where('pt_advertising_wish.wish_status', 'Yes');
				$query = $this->db->get('pt_advertising_wish', $perpage, $offset);
				$data['all'] = $query->result();
				$data['rows'] = $query->num_rows();
				return $data;
		}

// List all Home Wish
		function home_wish($limit) {
				$this->db->select('pt_advertising_wish.wish_id,pt_advertising_wish.wish_order,pt_advertising_wish.wish_title,pt_advertising_wish.wish_slug,pt_advertising_wish.wish_desc,pt_advertising_wish.wish_status');
				$this->db->order_by('pt_advertising_wish.wish_order', 'asc');
				$this->db->where('pt_advertising_wish.wish_status', 'Yes');
				$this->db->limit($limit);
				$res = $this->db->get('pt_advertising_wish')->result();
				return $res;
		}

// List all wish for API
		function api_list_wish() {
				$this->db->select('pt_advertising_wish.*');
				$this->db->group_by('pt_advertising_wish.wish_id');

				$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				$this->db->where('pt_advertising_wish.wish_status', 'Yes');
				return $this->db->get('pt_advertising_wish')->result();
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

// get all wish info
		function get_all_wish_back() {
				$this->db->select('pt_advertising_wish.*');
				$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				$query = $this->db->get('pt_advertising_wish');
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all wish info with limit
		function get_all_wish_back_limit($perpage = null, $offset = null, $orderby = null) {
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_advertising_wish.*');
				$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				$query = $this->db->get('pt_advertising_wish', $perpage, $offset);
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// get all wish info  by advance search
		function adv_search_all_wish_back_limit($data, $perpage = null, $offset = null, $orderby = null) {
				$status = $data["status"];
				$wishtitle = $data["posttitle"];
				$category = $data["category"];
				if ($offset != null) {
						$offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
				}
				$this->db->select('pt_advertising_wish.*');
				if (!empty ($wishtitle)) {
						$this->db->like('pt_advertising_wish.wish_title', $wishtitle);
				}

				$this->db->where('pt_advertising_wish.wish_status', $status);
				$this->db->order_by('pt_advertising_wish.wish_id', 'desc');
				$query = $this->db->get('pt_advertising_wish', $perpage, $offset);
				$data['all'] = $query->result();
				$data['nums'] = $query->num_rows();
				return $data;
		}

// add Destination data
		function add_wish($filename_db = null) {
				if (empty ($filename_db)) {
						$filename_db = "";
				}
				$this->db->select("wish_id");
				$this->db->order_by("wish_id", "desc");
				$query = $this->db->get('pt_advertising_wish');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$wishlastid = 1;
				}
				else {
						$wishlastid = $lastid[0]->wish_id + 1;
				}

              $wishlug = $this->input->post('slug');
              if(empty($wishlug)){
              $wishlug = $this->makeSlug($this->input->post('title'),$wishlastid);
              }else{
              $wishlug = $this->makeSlug($wishlug,$wishlastid);
              }


				$wishcount = $query->num_rows();
				$wishorder = $wishcount + 1;

				$relatedwish = @ implode(",", $this->input->post('relatedwish'));
				$data = array('wish_title' => $this->input->post('title'),
                'wish_slug' => $wishlug,
                'wish_desc' => $this->input->post('desc'),
                'wish_meta_keywords' => $this->input->post('keywords'),
                'wish_meta_desc' => $this->input->post('metadesc'),
                'wish_status' => $this->input->post('status'),
                'wish_order' => $wishorder,
                'wish_images' => $filename_db
							);
				$this->db->insert('pt_advertising_wish', $data);
                $wishid = $this->db->insert_id();
                $this->add_translation($this->input->post('translated'),$wishid);
		}

// update Post data
		function update_wish($id, $filename_db = null) {
				if (empty ($filename_db)) {
						$filename_db = $this->input->post('defimg');
				}
				$this->db->select("wish_id");
				$this->db->order_by("wish_id", "desc");
				$query = $this->db->get('pt_advertising_wish');
				$lastid = $query->result();
				if (empty ($lastid)) {
						$wishlastid = 1;
				}
				else {
						$wishlastid = $lastid[0]->wish_id + 1;
				}
				$wishcount = $query->num_rows();
				$wishorder = $wishcount + 1;
				$slug = $this->input->post('slug');
				if (empty ($slug)) {
						$this->db->select("wish_id");
						$this->db->where("wish_id !=", $id);
						$this->db->where("wish_title", $this->input->post('title'));
						$queryc = $this->db->get('pt_advertising_wish')->num_rows();
						if ($queryc > 0) {
								$wishlug = create_url_slug($this->input->post('title')) . "-" . $wishlastid;
						}
						else {
								$wishlug = create_url_slug($this->input->post('title'));
						}
				}
				else {
						$this->db->select("wish_id");
						$this->db->where("wish_id !=", $id);
						$this->db->where("wish_slug", $this->input->post('slug'));
						$queryc = $this->db->get('pt_advertising_wish')->num_rows();
						if ($queryc > 0) {
								$wishlug = create_url_slug($this->input->post('slug')) . "-" . $wishlastid;
						}
						else {
								$wishlug = create_url_slug($this->input->post('slug'));
						}
				}
				$relatedwish = @ implode(",", $this->input->post('relatedwish'));
				$data = array('wish_title' => $this->input->post('title'),
				'wish_slug' => $wishlug,
				'wish_desc' => $this->input->post('desc'),
				 'wish_meta_keywords' => $this->input->post('keywords'),
				  'wish_meta_desc' => $this->input->post('metadesc'),
					 'wish_status' => $this->input->post('status'),
					 'wish_images' => $filename_db
				 );
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
      $this->update_translation($this->input->post('translated'),$id);
		}





// Disable post

		public function disable_post($id) {
				$data = array('wish_status' => 'No');
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
		}
// Enable category


// Enable post

		public function enable_post($id) {
				$data = array('wish_status' => 'Yes');
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
		}

// get all wish for related selection for backend
		function select_related_wish($id = null) {
				$this->db->select('wish_title,wish_id');
				if (!empty ($id)) {
						$this->db->where('wish_id !=', $id);
				}
				return $this->db->get('pt_advertising_wish')->result();
		}

		function wish_photo($id = null) {

        $tempFile = $_FILES['defaultphoto']['tmp_name'];
						$fileName = $_FILES['defaultphoto']['name'];
						$fileName = str_replace(" ", "-", $_FILES['defaultphoto']['name']);
						$fig = rand(1, 999999);

						if (strpos($fileName,'php') !== false) {

						}else{

						$saveFile = $fig . '_' . $fileName;

						$targetPath = PT_WISH_IMAGES_UPLOAD;

						$targetFile = $targetPath . $saveFile;
						move_uploaded_file($tempFile, $targetFile);
							if (!empty ($id)) {
										$this->update_wish($id, $saveFile);
										$oldimg = $this->input->wish('defimg');
										if (!empty ($oldimg)) {
												@ unlink(PT_WISH_IMAGES_UPLOAD . $oldimg);
										}

								}
								else {
										$this->add_wish($saveFile);

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

// update wish order
		function update_wish_order($id, $order) {
				$data = array('wish_order' => $order);
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
		}

// get all data of single wish by slug
		function get_wish_data($slug) {
				$this->db->where('wish_slug', $slug);
				return $this->db->get('pt_advertising_wish')->result();
		}

		function delete_wish($id) {
				$this->delete_image($id);
				$this->db->where('wish_id', $id);
				$this->db->delete('pt_advertising_wish');

                $this->db->where('item_id', $id);
				$this->db->delete('pt_advertising_wish_translation');
		}

// Delete wish Images
		function delete_image($id) {
				$this->db->where('wish_id', $id);
				$res = $this->db->get('pt_advertising_wish')->result();
				$img = $res[0]->wish_images;
				if (!empty ($img)) {
						@ unlink(PT_WISH_IMAGES_UPLOAD . $img);
				}
		}

// update translated data os some fields in english
		function update_english($id) {
				$cslug = create_url_slug($this->input->post('title'));
				$this->db->where('wish_slug', $cslug);
				$this->db->where('wish_id !=', $id);
				$nums = $this->db->get('pt_advertising_wish')->num_rows();
				if ($nums > 0) {
						$cslug = $cslug . "-" . $id;
				}
				else {
						$cslug = $cslug;
				}
				$data = array('wish_title' => $this->input->post('title'), 'wish_slug' => $cslug, 'wish_desc' => $this->input->post('desc'));
				$this->db->where('wish_id', $id);
				$this->db->update('pt_advertising_wish', $data);
				return $cslug;
		}


      // Adds translation of some fields data
		function add_translation($wishdata,$id) {
		  foreach($wishdata as $lang => $val){
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
				$this->db->insert('pt_advertising_wish_translation', $data);

                }

                }


		}

        // Update translation of some fields data
		function update_translation($wishdata,$id){

       foreach($wishdata as $lang => $val){
		     if(array_filter($val)){
		        $title = $val['title'];
                $desc = $val['desc'];
				$metadesc = $val['metadesc'];
				$kewords = $val['keywords'];
                $transAvailable = $this->getBackWishTranslation($lang,$id);

                if(empty($transAvailable)){
                 $data = array(
                'trans_title' => $title,
                'trans_desc' => $desc,
                'trans_meta_desc' => $metadesc,
                'trans_keywords' => $kewords,
                'item_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_advertising_wish_translation', $data);

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
				$this->db->update('pt_advertising_wish_translation', $data);

                }


              }

                }
		}

    function getBackWishTranslation($lang, $id) {
				$this->db->where('trans_lang', $lang);
				$this->db->where('item_id', $id);
				return $this->db->get('pt_advertising_wish_translation')->result();
		}

    function makeSlug($title,$wishlastid = null){
                        $slug = create_url_slug($title);
                        $this->db->select("wish_id");
						$this->db->where("wish_slug", $slug);
                        if(!empty($wishlastid)){
                         $this->db->where('wish_id !=',$wishlastid);
                        }
						$queryc = $this->db->get('pt_advertising_wish')->num_rows();
						if ($queryc > 0) {
								$slug = $slug."-".$wishlastid;
						}
                        return $slug;
    }





}
