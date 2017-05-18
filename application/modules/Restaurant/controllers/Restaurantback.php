<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Restaurantback extends MX_Controller {
	    public $accType = "";
		private $langdef;
		public  $editpermission = true;
        public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'restaurant');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'restaurant');
						if (!$chk) {
								backError_404($this->data);
						}
				}
				$checkingadmin = $this->session->userdata('pt_logged_admin');
				$this->accType = $this->session->userdata('pt_accountType');
				$this->role = $this->session->userdata('pt_role');
				$this->data['isadmin'] = $this->session->userdata('pt_logged_admin');
				$this->data['isSuperAdmin'] = $this->session->userdata('pt_logged_super_admin');

				if (!empty ($checkingadmin)) {
						$this->data['userloggedin'] = $this->session->userdata('pt_logged_admin');
				}
				else {
						$this->data['userloggedin'] = $this->session->userdata('pt_logged_supplier');
				}
				if (empty ($this->data['userloggedin'])) {
						redirect("admin");
				}
				if (!empty ($checkingadmin)) {
						$this->data['adminsegment'] = "admin";
				}
				else {
						$this->data['adminsegment'] = "supplier";
				}
				if ($this->data['adminsegment'] == "admin") {
						$chkadmin = modules :: run('Admin/validadmin');
						if (!$chkadmin) {
								redirect('admin');
						}
				}
				else {
						$chksupplier = modules :: run('Supplier/validsupplier');
						if (!$chksupplier) {
								redirect('supplier');
						}
				}
/*   $chk = modules::run('home/is_module_enabled','restaurant');
if(!$chk){
redirect('admin');
}*/
				$this->data['c_model'] = $this->countries_model;
				if (!pt_permissions('restaurant', $this->data['userloggedin'])) {
						redirect('admin');
				}

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Restaurant/Restaurant_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Restaurant/restaurant');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editrestaurant", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deleterestaurant", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addrestaurant", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_restaurant');

                if($this->role == "supplier"){
                $xcrud->where('restaurant_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('restaurant_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_restaurant.restaurant_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {restaurant_owned_by}');
				$xcrud->label('restaurant_title', 'Name')->label('restaurant_stars', 'Stars')->label('restaurant_is_featured', '')->label('restaurant_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/restaurant/manage/{restaurant_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('restaurant_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/restaurant/manage/{restaurant_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'restaurant/restaurantajaxcalls/delRestaurant';
                $xcrud->multiDelUrl = base_url().'restaurant/restaurantajaxcalls/delMultipleRestaurant';
                $xcrud->button("javascript: delfunc('{restaurant_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{restaurant_id}'));
                }
        $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('restaurant_order', '7%');
				$xcrud->columns('restaurant_is_featured,thumbnail_image,restaurant_title,restaurant_stars,Owned By,restaurant_slug,restaurant_order,restaurant_status');

				$xcrud->search_columns('restaurant_title,restaurant_stars,Owned By,restaurant_order,restaurant_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('restaurant_slug', 'Gallery');
				$xcrud->label('restaurant_status', 'Status');
				$xcrud->column_callback('restaurant_stars', 'create_stars');
				$xcrud->column_callback('pt_restaurant.restaurant_order', 'orderInputRestaurant');
				$xcrud->column_callback('pt_restaurant.restaurant_is_featured', 'feature_starsRestaurant');
				$xcrud->column_callback('restaurant_slug', 'restaurantGallery');
				$xcrud->column_callback('restaurant_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_RESTAURANT_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Restaurant Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Restaurant Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/restaurant/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                 backError_404($this->data);

				  }else{

				$this->data['data_relate'] = $this->Restaurant_model->data_for_relate_near_by(); //add by poy
				$addrestaurant = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";

                $this->data['submittype'] = "add";

				if (!empty ($addrestaurant)) {
						$this->form_validation->set_rules('restaurantname', 'Restaurant Name', 'trim|required');
						$this->form_validation->set_rules('restauranttype', 'Restaurant Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');


						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$restaurantlocations = $this->restaurantLocationsCheck($this->input->post('locations'));
							if(empty($restaurantlocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$restaurantid = $this->Restaurant_model->add_restaurant($this->data['userloggedin']);
							$this->Restaurant_model->add_translation($this->input->post('translated'), $restaurantid);
							$this->session->set_flashdata('flashmsgs', 'Restaurant added Successfully');
							echo "done";
						}


						}

				}
				else {

						$this->data['restauranttypes'] = $this->Restaurant_model->get_tsettings_data("ttypes");
						$this->data['restaurantcategories'] = $this->Restaurant_model->get_tsettings_data("tcategory");
						$this->data['restaurantratings'] = $this->Restaurant_model->get_tsettings_data("tratings");
						$this->data['restaurantinclusions'] = $this->Restaurant_model->get_tsettings_data("tamenities");
						$this->data['restaurantexclusions'] = $this->Restaurant_model->get_tsettings_data("texclusions");
						$this->data['restaurantpayments'] = $this->Restaurant_model->get_tsettings_data("tpayments");
						$this->data['all_countries'] = $this->Countries_model->get_all_countries();
						$this->data['all_restaurant'] = $this->Restaurant_model->select_related_restaurant($this->data['userloggedin']);

						$this->load->model('Admin/Locations_model');

						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();

						$this->data['main_content'] = 'Restaurant/manage';
						$this->data['page_title'] = 'Add Restaurant';
						$this->data['headingText'] = 'Add Restaurant';
						$this->load->view('Admin/template', $this->data);
				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/restaurant/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Restaurant_model->updateRestaurantSettings();
						redirect('admin/restaurant/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Restaurant_model->addSettingsData();
                    $this->Restaurant_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/restaurant/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Restaurant_model->updateSettingsData();
                   $this->Restaurant_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/restaurant/settings');

				}

				$this->LoadXcrudRestaurantSettings("tamenities");
				$this->LoadXcrudRestaurantSettings("ttypes");
				$this->LoadXcrudRestaurantSettings("tpayments");
				$this->LoadXcrudRestaurantSettings("texclusions");

                $this->data['typeSettings'] = $this->Restaurant_model->get_restaurant_settings_data();

				@ $this->data['settings'] = $this->Settings_model->get_front_settings("restaurant");
				$this->data['main_content'] = 'Restaurant/settings';
				$this->data['page_title'] = 'Restaurant Settings';
				$this->load->view('Admin/template', $this->data);

		/*
				$this->load->model('admin/settings_model');
				$this->data['all_countries'] = $this->Countries_model->get_all_countries();
				$updatesett = $this->input->post('updatesettings');
				if (!empty ($updatesett)) {
						$this->Settings_model->update_front_settings();
						redirect('admin/restaurant/settings');
				}
				$this->data['restauranttypes'] = $this->Restaurant_model->get_restaurant_settings_data("ttypes");
				$this->data['restaurantcategories'] = $this->Restaurant_model->get_restaurant_settings_data("tcategory");
				$this->data['restaurantratings'] = $this->Restaurant_model->get_restaurant_settings_data("tratings");
				$this->data['restaurantinclusions'] = $this->Restaurant_model->get_restaurant_settings_data("tamenities");
				$this->data['restaurantexclusions'] = $this->Restaurant_model->get_restaurant_settings_data("texclusions");
				$this->data['restaurantpayments'] = $this->Restaurant_model->get_restaurant_settings_data("tpayments");
				$this->data['settings'] = $this->Settings_model->get_front_settings("restaurant");
				$this->data['main_content'] = 'Restaurant/settings';
				$this->data['page_title'] = 'Restaurant Settings';
				$this->load->view('Admin/template', $this->data);*/
		}

		function manage($restaurantname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Restaurant/Restaurant_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($restaurantname)) {
						redirect($this->data['adminsegment'] . '/restaurant/');
				}
				$updaterestaurant = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$restaurantid = $this->input->post('restaurantid');
				if (!empty ($updaterestaurant)) {
						$this->form_validation->set_rules('restaurantname', 'Restaurant Name', 'trim|required');
						$this->form_validation->set_rules('restauranttype', 'Restaurant Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$restaurantlocations = $this->restaurantLocationsCheck($this->input->post('locations'));
							if(empty($restaurantlocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Restaurant_model->update_restaurant($restaurantid);
							$this->Restaurant_model->update_translation($this->input->post('translated'), $restaurantid);
							$this->session->set_flashdata('flashmsgs', 'Restaurant Updated Successfully');
							echo "done";

							}



						}
				}
				else {
						$this->data['tdata'] = $this->Restaurant_model->get_restaurant_data($restaurantname);

						if (empty ($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/restaurant/');
						}
                       $comfixed = $this->data['tdata'][0]->restaurant_comm_fixed;
                       $comper = $this->data['tdata'][0]->restaurant_comm_percentage;
                       if($comfixed > 0){
                         $this->data['restaurantdepositval'] = $comfixed;
                         $this->data['restaurantdeposittype'] = "fixed";
                       }else{
                         $this->data['restaurantdepositval'] = $comper;
                         $this->data['restaurantdeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->restaurant_tax_fixed;
                       $taxper = $this->data['tdata'][0]->restaurant_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['restauranttaxval'] = $taxfixed;
                         $this->data['restauranttaxtype'] = "fixed";
                       }else{
                         $this->data['restauranttaxval'] = $taxper;
                         $this->data['restauranttaxtype'] = "percentage";
                       }

                        $adultStatus = $this->data['tdata'][0]->adult_status;
                        $childStatus = $this->data['tdata'][0]->child_status;
                        $infantStatus = $this->data['tdata'][0]->infant_status;

                        if($adultStatus == "0"){
                        	$this->data['adultStatus'] = "readonly";
                        	$this->data['adultInput'] = "0";
                        }else{
                        	$this->data['adultStatus'] = "";
                        	$this->data['adultInput'] = "1";
                        }

                        if($childStatus == "0"){
                        	$this->data['childStatus'] = "readonly";
                        	$this->data['childInput'] = "0";
                        }else{
                        	$this->data['childStatus'] = "";
                        	$this->data['childInput'] = "1";
                        }

                        if($infantStatus == "0"){

                        	$this->data['infantStatus'] = "readonly";
                        	$this->data['infantInput'] = "0";
                        }else{

                        	$this->data['infantStatus'] = "";
                        	$this->data['infantInput'] = "1";
                        }

						$this->data['all_restaurant'] = $this->Restaurant_model->select_related_restaurant($this->data['tdata'][0]->restaurant_id);
						$this->data['data_relate'] = $this->Restaurant_model->data_for_relate_near_by(); //add by poy
						$this->data['map_data'] = $this->Restaurant_model->get_restaurant_map($this->data['tdata'][0]->restaurant_id);
						$this->data['maxmaporder'] = $this->Restaurant_model->max_map_order($this->data['tdata'][0]->restaurant_id);
						$this->data['has_start'] = $this->Restaurant_model->has_start_end_city("start", $this->data['tdata'][0]->restaurant_id);
						$this->data['has_end'] = $this->Restaurant_model->has_start_end_city("end", $this->data['tdata'][0]->restaurant_id);
						$this->data['offers_data'] = $this->Restaurant_model->offers_data($this->data['tdata'][0]->restaurant_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->restaurant_owned_by);
						$this->data['restauranttypes'] = $this->Restaurant_model->get_tsettings_data("ttypes");
						$this->data['restaurantcategories'] = $this->Restaurant_model->get_tsettings_data("tcategory");
						$this->data['restaurantratings'] = $this->Restaurant_model->get_tsettings_data("tratings");
						$this->data['restaurantinclusions'] = $this->Restaurant_model->get_tsettings_data("tamenities");
						$this->data['restaurantexclusions'] = $this->Restaurant_model->get_tsettings_data("texclusions");
						$this->data['restaurantpayments'] = $this->Restaurant_model->get_tsettings_data("tpayments");
						$this->data['restaurantid'] = $this->data['tdata'][0]->restaurant_id;

						$this->load->model('Admin/Locations_model');
						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->data['restaurantlocations'] = $this->Restaurant_model->restaurantSelectedLocations($this->data['tdata'][0]->restaurant_id);

						$this->data['main_content'] = 'Restaurant/manage';
						$this->data['page_title'] = 'Manage Restaurant';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Restaurant/Restaurant_lib');
				$this->Restaurant_lib->set_restaurantid($id);
				$this->data['itemid'] = $this->Restaurant_lib->get_id();
				$this->data['images'] = $this->Restaurant_model->restaurantGallery($id);
                $this->data['imgorderUrl'] = base_url().'restaurant/restaurantajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'restaurant/restaurantback/galleryUpload/restaurant/';
                $this->data['delimgUrl'] = base_url().'restaurant/restaurantajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'restaurant/restaurantajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'restaurant/restaurantajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'restaurant/restaurantajaxcalls/deleteMultipleRestaurantImages';
                $this->data['fullImgDir'] = PT_RESTAURANT_SLIDER;
                $this->data['thumbsDir'] = PT_RESTAURANT_SLIDER_THUMB;
				$this->data['main_content'] = 'Restaurant/gallery';
				$this->data['page_title'] = 'Restaurant Gallery';
				$this->load->view('Admin/template', $this->data);
		}


		function galleryUpload($type, $id) {
				$this->load->library('image_lib');
				if (!empty ($_FILES)) {

						$tempFile = $_FILES['file']['tmp_name'];
						$fileName = $_FILES['file']['name'];
						$fileName = str_replace(" ", "-", $_FILES['file']['name']);
						$fig = rand(1, 999999);
						$saveFile = $fig . '_' . $fileName;
                        if (strpos($fileName,'php') !== false) {

						}else{
						$targetPath = PT_RESTAURANT_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
                        $config['new_image'] = PT_RESTAURANT_SLIDER_THUMB_UPLOAD;

						$config['thumb_marker'] = '';
						$config['create_thumb'] = TRUE;
						$config['maintain_ratio'] = TRUE;
						$config['width'] = THUMB_WIDTH;
						$config['height'] = THUMB_HEIGHT;
						$this->image_lib->clear();
						$this->image_lib->initialize($config);
						$this->image_lib->resize();

						modules :: run('Admin/watermark/apply',$targetFile);

                        /* Add images name to database with respective hotel id */
						$this->Restaurant_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete restaurant images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_RESTAURANT_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_RESTAURANT_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_restaurant_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($restaurantlug, $lang = null) {
				$this->load->library('Restaurant/Restaurant_lib');
				$this->Restaurant_lib->set_restaurantid($restaurantlug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($restaurantlug)) {
						redirect($this->data['adminsegment'] . '/restaurant/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$restaurantid = $this->input->post('restaurantid');
						$this->Restaurant_model->add_translation($language, $restaurantid);
						redirect($this->data['adminsegment'] . "/restaurant/translate/" . $restaurantlug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Restaurant_model->update_translation($lang, $restaurantlug);
						redirect($this->data['adminsegment'] . "/restaurant/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Restaurant_lib->restaurant_details();
				if ($lang == $this->langdef) {
						$restaurantdata = $this->Restaurant_lib->restaurant_short_details();
						$this->data['restaurantdata'] = $restaurantdata;
						$this->data['transpolicy'] = $restaurantdata[0]->restaurant_privacy;
						$this->data['transdesc'] = $restaurantdata[0]->restaurant_desc;
						$this->data['transtitle'] = $restaurantdata[0]->restaurant_title;
				}
				else {
						$restaurantdata = $this->Restaurant_lib->translated_data($lang);
						$this->data['restaurantdata'] = $restaurantdata;
						$this->data['transid'] = $restaurantdata[0]->trans_id;
						$this->data['transpolicy'] = $restaurantdata[0]->trans_policy;
						$this->data['transdesc'] = $restaurantdata[0]->trans_desc;
						$this->data['transtitle'] = $restaurantdata[0]->trans_title;
				}
				$this->data['restaurantid'] = $this->Restaurant_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $restaurantlug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->restaurant_owned_by) {
								redirect($this->data['adminsegment'] . '/restaurant/');
						}
				}
				$this->data['main_content'] = 'Restaurant/translate';
				$this->data['page_title'] = 'Translate Restaurant';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudRestaurantSettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_restaurant_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'restaurant/restaurantajaxcalls/delTypeSettings';
               	$xc->button("javascript: delfunc('{sett_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('target'=>'_self','id' => '{sett_id}'));


                if($type == "ttypes"){
                $xc->columns('sett_name,sett_status');
                }else{

                $xc->columns('sett_name,sett_selected,sett_status');
                }

                $xc->search_columns('sett_name,sett_selected,sett_status');
                $xc->label('sett_name', 'Name')->label('sett_selected', 'Selected')->label('sett_status', 'Status')->label('sett_img', 'Icon');
                $xc->unset_add();
				$xc->unset_edit();
				$xc->unset_remove();
				$xc->unset_view();

				$xc->multiDelUrl = base_url().'restaurant/restaurantajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierRestaurant = $this->Restaurant_model->all_restaurant($this->data['userloggedin']);
			 $allrestaurant = $this->Restaurant_model->all_restaurant();

         echo  modules :: run('Admin/extras/listings','restaurant',$allrestaurant,$supplierRestaurant);

		}else{

			$restaurant = $this->Restaurant_model->all_restaurant();
         echo  modules :: run('Admin/extras/listings','restaurant',$restaurant);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','restaurant');
        }


        function restaurantLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
