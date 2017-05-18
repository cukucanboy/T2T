<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Weddingback extends MX_Controller {
	    public $accType = "";
		private $langdef;
		public  $editpermission = true;
        public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'wedding');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'wedding');
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
/*   $chk = modules::run('home/is_module_enabled','wedding');
if(!$chk){
redirect('admin');
}*/
				$this->data['c_model'] = $this->countries_model;
				if (!pt_permissions('wedding', $this->data['userloggedin'])) {
						redirect('admin');
				}

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Wedding/Wedding_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Wedding/wedding');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editwedding", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deletewedding", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addwedding", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_wedding');

                if($this->role == "supplier"){
                $xcrud->where('wedding_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('wedding_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_wedding.wedding_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {wedding_owned_by}');
				$xcrud->label('wedding_title', 'Name')->label('wedding_stars', 'Stars')->label('wedding_is_featured', '')->label('wedding_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/wedding/manage/{wedding_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('wedding_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/wedding/manage/{wedding_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'wedding/weddingajaxcalls/delWedding';
                $xcrud->multiDelUrl = base_url().'wedding/weddingajaxcalls/delMultipleWedding';
                $xcrud->button("javascript: delfunc('{wedding_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{wedding_id}'));
                }
                $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('wedding_order', '7%');
				$xcrud->columns('wedding_is_featured,thumbnail_image,wedding_title,wedding_stars,Owned By,wedding_slug,wedding_order,wedding_status');

				$xcrud->search_columns('wedding_title,wedding_stars,Owned By,wedding_order,wedding_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('wedding_slug', 'Gallery');
				$xcrud->label('wedding_status', 'Status');
				$xcrud->column_callback('wedding_stars', 'create_stars');
				$xcrud->column_callback('pt_wedding.wedding_order', 'orderInputWedding');
				$xcrud->column_callback('pt_wedding.wedding_is_featured', 'feature_starsWedding');
				$xcrud->column_callback('wedding_slug', 'weddingGallery');
				$xcrud->column_callback('wedding_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_WEDDING_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Wedding Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Wedding Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/wedding/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                 backError_404($this->data);

				  }else{

				$this->data['data_relate'] = $this->Wedding_model->data_for_relate_near_by(); //add by poy
				$addwedding = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";

                $this->data['submittype'] = "add";

				if (!empty ($addwedding)) {
						$this->form_validation->set_rules('weddingname', 'Wedding Name', 'trim|required');
						$this->form_validation->set_rules('weddingtype', 'Wedding Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');


						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$weddinglocations = $this->weddingLocationsCheck($this->input->post('locations'));
							if(empty($weddinglocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$weddingid = $this->Wedding_model->add_wedding($this->data['userloggedin']);
							$this->Wedding_model->add_translation($this->input->post('translated'), $weddingid);
							$this->session->set_flashdata('flashmsgs', 'Wedding added Successfully');
							echo "done";
						}


						}

				}
				else {

						$this->data['weddingtypes'] = $this->Wedding_model->get_tsettings_data("ttypes");
						$this->data['weddingcategories'] = $this->Wedding_model->get_tsettings_data("tcategory");
						$this->data['weddingratings'] = $this->Wedding_model->get_tsettings_data("tratings");
						$this->data['weddinginclusions'] = $this->Wedding_model->get_tsettings_data("tamenities");
						$this->data['weddingexclusions'] = $this->Wedding_model->get_tsettings_data("texclusions");
						$this->data['weddingpayments'] = $this->Wedding_model->get_tsettings_data("tpayments");
						$this->data['all_countries'] = $this->Countries_model->get_all_countries();
						$this->data['all_wedding'] = $this->Wedding_model->select_related_wedding($this->data['userloggedin']);

						$this->load->model('Admin/Locations_model');

						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();

						$this->data['main_content'] = 'Wedding/manage';
						$this->data['page_title'] = 'Add Wedding';
						$this->data['headingText'] = 'Add Wedding';
						$this->load->view('Admin/template', $this->data);
				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/wedding/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Wedding_model->updateWeddingSettings();
						redirect('admin/wedding/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Wedding_model->addSettingsData();
                    $this->Wedding_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/wedding/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Wedding_model->updateSettingsData();
                   $this->Wedding_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/wedding/settings');

				}

				$this->LoadXcrudWeddingSettings("tamenities");
				$this->LoadXcrudWeddingSettings("ttypes");
				$this->LoadXcrudWeddingSettings("tpayments");
				$this->LoadXcrudWeddingSettings("texclusions");

                $this->data['typeSettings'] = $this->Wedding_model->get_wedding_settings_data();

				@ $this->data['settings'] = $this->Settings_model->get_front_settings("wedding");
				$this->data['main_content'] = 'Wedding/settings';
				$this->data['page_title'] = 'Wedding Settings';
				$this->load->view('Admin/template', $this->data);

		/*
				$this->load->model('admin/settings_model');
				$this->data['all_countries'] = $this->Countries_model->get_all_countries();
				$updatesett = $this->input->post('updatesettings');
				if (!empty ($updatesett)) {
						$this->Settings_model->update_front_settings();
						redirect('admin/wedding/settings');
				}
				$this->data['weddingtypes'] = $this->Wedding_model->get_wedding_settings_data("ttypes");
				$this->data['weddingcategories'] = $this->Wedding_model->get_wedding_settings_data("tcategory");
				$this->data['weddingratings'] = $this->Wedding_model->get_wedding_settings_data("tratings");
				$this->data['weddinginclusions'] = $this->Wedding_model->get_wedding_settings_data("tamenities");
				$this->data['weddingexclusions'] = $this->Wedding_model->get_wedding_settings_data("texclusions");
				$this->data['weddingpayments'] = $this->Wedding_model->get_wedding_settings_data("tpayments");
				$this->data['settings'] = $this->Settings_model->get_front_settings("wedding");
				$this->data['main_content'] = 'Wedding/settings';
				$this->data['page_title'] = 'Wedding Settings';
				$this->load->view('Admin/template', $this->data);*/
		}

		function manage($weddingname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Wedding/Wedding_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($weddingname)) {
						redirect($this->data['adminsegment'] . '/wedding/');
				}
				$updatewedding = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$weddingid = $this->input->post('weddingid');
				if (!empty ($updatewedding)) {
						$this->form_validation->set_rules('weddingname', 'Wedding Name', 'trim|required');
						$this->form_validation->set_rules('weddingtype', 'Wedding Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$weddinglocations = $this->weddingLocationsCheck($this->input->post('locations'));
							if(empty($weddinglocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Wedding_model->update_wedding($weddingid);
							$this->Wedding_model->update_translation($this->input->post('translated'), $weddingid);
							$this->session->set_flashdata('flashmsgs', 'Wedding Updated Successfully');
							echo "done";

							}



						}
				}
				else {
						$this->data['tdata'] = $this->Wedding_model->get_wedding_data($weddingname);

						if (empty ($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/wedding/');
						}
                       $comfixed = $this->data['tdata'][0]->wedding_comm_fixed;
                       $comper = $this->data['tdata'][0]->wedding_comm_percentage;
                       if($comfixed > 0){
                         $this->data['weddingdepositval'] = $comfixed;
                         $this->data['weddingdeposittype'] = "fixed";
                       }else{
                         $this->data['weddingdepositval'] = $comper;
                         $this->data['weddingdeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->wedding_tax_fixed;
                       $taxper = $this->data['tdata'][0]->wedding_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['weddingtaxval'] = $taxfixed;
                         $this->data['weddingtaxtype'] = "fixed";
                       }else{
                         $this->data['weddingtaxval'] = $taxper;
                         $this->data['weddingtaxtype'] = "percentage";
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

						$this->data['all_wedding'] = $this->Wedding_model->select_related_wedding($this->data['tdata'][0]->wedding_id);
						$this->data['data_relate'] = $this->Wedding_model->data_for_relate_near_by(); //add by poy
						$this->data['map_data'] = $this->Wedding_model->get_wedding_map($this->data['tdata'][0]->wedding_id);
						$this->data['maxmaporder'] = $this->Wedding_model->max_map_order($this->data['tdata'][0]->wedding_id);
						$this->data['has_start'] = $this->Wedding_model->has_start_end_city("start", $this->data['tdata'][0]->wedding_id);
						$this->data['has_end'] = $this->Wedding_model->has_start_end_city("end", $this->data['tdata'][0]->wedding_id);
						$this->data['offers_data'] = $this->Wedding_model->offers_data($this->data['tdata'][0]->wedding_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->wedding_owned_by);
						$this->data['weddingtypes'] = $this->Wedding_model->get_tsettings_data("ttypes");
						$this->data['weddingcategories'] = $this->Wedding_model->get_tsettings_data("tcategory");
						$this->data['weddingratings'] = $this->Wedding_model->get_tsettings_data("tratings");
						$this->data['weddinginclusions'] = $this->Wedding_model->get_tsettings_data("tamenities");
						$this->data['weddingexclusions'] = $this->Wedding_model->get_tsettings_data("texclusions");
						$this->data['weddingpayments'] = $this->Wedding_model->get_tsettings_data("tpayments");
						$this->data['weddingid'] = $this->data['tdata'][0]->wedding_id;

						$this->load->model('Admin/Locations_model');
						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->data['weddinglocations'] = $this->Wedding_model->weddingSelectedLocations($this->data['tdata'][0]->wedding_id);

						$this->data['main_content'] = 'Wedding/manage';
						$this->data['page_title'] = 'Manage Wedding';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Wedding/Wedding_lib');
				$this->Wedding_lib->set_weddingid($id);
				$this->data['itemid'] = $this->Wedding_lib->get_id();
				$this->data['images'] = $this->Wedding_model->weddingGallery($id);
                $this->data['imgorderUrl'] = base_url().'wedding/weddingajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'wedding/weddingback/galleryUpload/wedding/';
                $this->data['delimgUrl'] = base_url().'wedding/weddingajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'wedding/weddingajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'wedding/weddingajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'wedding/weddingajaxcalls/deleteMultipleWeddingImages';
                $this->data['fullImgDir'] = PT_WEDDING_SLIDER;
                $this->data['thumbsDir'] = PT_WEDDING_SLIDER_THUMB;
				$this->data['main_content'] = 'Wedding/gallery';
				$this->data['page_title'] = 'Wedding Gallery';
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
						$targetPath = PT_WEDDING_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
                        $config['new_image'] = PT_WEDDING_SLIDER_THUMB_UPLOAD;

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
						$this->Wedding_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete wedding images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_WEDDING_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_WEDDING_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_wedding_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($weddinglug, $lang = null) {
				$this->load->library('Wedding/Wedding_lib');
				$this->Wedding_lib->set_weddingid($weddinglug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($weddinglug)) {
						redirect($this->data['adminsegment'] . '/wedding/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$weddingid = $this->input->post('weddingid');
						$this->Wedding_model->add_translation($language, $weddingid);
						redirect($this->data['adminsegment'] . "/wedding/translate/" . $weddinglug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Wedding_model->update_translation($lang, $weddinglug);
						redirect($this->data['adminsegment'] . "/wedding/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Wedding_lib->wedding_details();
				if ($lang == $this->langdef) {
						$weddingdata = $this->Wedding_lib->wedding_short_details();
						$this->data['weddingdata'] = $weddingdata;
						$this->data['transpolicy'] = $weddingdata[0]->wedding_privacy;
						$this->data['transdesc'] = $weddingdata[0]->wedding_desc;
						$this->data['transtitle'] = $weddingdata[0]->wedding_title;
				}
				else {
						$weddingdata = $this->Wedding_lib->translated_data($lang);
						$this->data['weddingdata'] = $weddingdata;
						$this->data['transid'] = $weddingdata[0]->trans_id;
						$this->data['transpolicy'] = $weddingdata[0]->trans_policy;
						$this->data['transdesc'] = $weddingdata[0]->trans_desc;
						$this->data['transtitle'] = $weddingdata[0]->trans_title;
				}
				$this->data['weddingid'] = $this->Wedding_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $weddinglug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->wedding_owned_by) {
								redirect($this->data['adminsegment'] . '/wedding/');
						}
				}
				$this->data['main_content'] = 'Wedding/translate';
				$this->data['page_title'] = 'Translate Wedding';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudWeddingSettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_wedding_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'wedding/weddingajaxcalls/delTypeSettings';
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

				$xc->multiDelUrl = base_url().'wedding/weddingajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierWedding = $this->Wedding_model->all_wedding($this->data['userloggedin']);
			 $allwedding = $this->Wedding_model->all_wedding();

         echo  modules :: run('Admin/extras/listings','wedding',$allwedding,$supplierWedding);

		}else{

			$wedding = $this->Wedding_model->all_wedding();
         echo  modules :: run('Admin/extras/listings','wedding',$wedding);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','wedding');
        }


        function weddingLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
