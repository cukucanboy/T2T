<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Spaback extends MX_Controller {
	    public $accType = "";
		private $langdef;
		public  $editpermission = true;
        public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'spa');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'spa');
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
/*   $chk = modules::run('home/is_module_enabled','spa');
if(!$chk){
redirect('admin');
}*/
				$this->data['c_model'] = @$this->countries_model;
				if (!pt_permissions('spa', $this->data['userloggedin'])) {
						redirect('admin');
				}

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Spa/Spa_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Spa/spa');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editspa", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deletespa", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addspa", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_spa');

                if($this->role == "supplier"){
                $xcrud->where('spa_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('spa_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_spa.spa_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {spa_owned_by}');
				$xcrud->label('spa_title', 'Name')->label('spa_stars', 'Stars')->label('spa_is_featured', '')->label('spa_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/spa/manage/{spa_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('spa_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/spa/manage/{spa_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'spa/spaajaxcalls/delSpa';
                $xcrud->multiDelUrl = base_url().'spa/spaajaxcalls/delMultipleSpa';
                $xcrud->button("javascript: delfunc('{spa_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{spa_id}'));
                }
                $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('spa_order', '7%');
				$xcrud->columns('spa_is_featured,thumbnail_image,spa_title,spa_stars,Owned By,spa_slug,spa_order,spa_status');

				$xcrud->search_columns('spa_title,spa_stars,Owned By,spa_order,spa_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('spa_slug', 'Gallery');
				$xcrud->label('spa_status', 'Status');
				$xcrud->column_callback('spa_stars', 'create_stars');
				$xcrud->column_callback('pt_spa.spa_order', 'orderInputSpa');
				$xcrud->column_callback('pt_spa.spa_is_featured', 'feature_starsSpa');
				$xcrud->column_callback('spa_slug', 'spaGallery');
				$xcrud->column_callback('spa_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_SPA_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Spa Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Spa Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/spa/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                 backError_404($this->data);

				  }else{


				$addspa = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";

                $this->data['submittype'] = "add";

				if (!empty ($addspa)) {
						$this->form_validation->set_rules('spaname', 'Spa Name', 'trim|required');
						$this->form_validation->set_rules('spatype', 'Spa Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');


						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$spalocations = $this->spaLocationsCheck($this->input->post('locations'));
							if(empty($spalocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$spaid = $this->Spa_model->add_spa($this->data['userloggedin']);
							$this->Spa_model->add_translation($this->input->post('translated'), $spaid);
							$this->session->set_flashdata('flashmsgs', 'Spa added Successfully');
							echo "done";
						}


						}

				}
				else {

						$this->data['spatypes'] = $this->Spa_model->get_tsettings_data("ttypes");
						$this->data['spacategories'] = $this->Spa_model->get_tsettings_data("tcategory");
						$this->data['sparatings'] = $this->Spa_model->get_tsettings_data("tratings");
						$this->data['spainclusions'] = $this->Spa_model->get_tsettings_data("tamenities");
						$this->data['spaexclusions'] = $this->Spa_model->get_tsettings_data("texclusions");
						$this->data['spapayments'] = $this->Spa_model->get_tsettings_data("tpayments");
						$this->data['all_countries'] = $this->Countries_model->get_all_countries();
						$this->data['all_spa'] = $this->Spa_model->select_related_spa($this->data['userloggedin']);

						$this->load->model('Admin/Locations_model');

						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();

						$this->data['main_content'] = 'Spa/manage';
						$this->data['page_title'] = 'Add Spa';
						$this->data['headingText'] = 'Add Spa';
						$this->load->view('Admin/template', $this->data);
				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/spa/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Spa_model->updateSpaSettings();
						redirect('admin/spa/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Spa_model->addSettingsData();
                    $this->Spa_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/spa/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Spa_model->updateSettingsData();
                   $this->Spa_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/spa/settings');

				}

				$this->LoadXcrudSpaSettings("tamenities");
				$this->LoadXcrudSpaSettings("ttypes");
				$this->LoadXcrudSpaSettings("tpayments");
				$this->LoadXcrudSpaSettings("texclusions");

                $this->data['typeSettings'] = $this->Spa_model->get_spa_settings_data();

				@ $this->data['settings'] = $this->Settings_model->get_front_settings("spa");
				$this->data['main_content'] = 'Spa/settings';
				$this->data['page_title'] = 'Spa Settings';
				$this->load->view('Admin/template', $this->data);

		/*
				$this->load->model('admin/settings_model');
				$this->data['all_countries'] = $this->Countries_model->get_all_countries();
				$updatesett = $this->input->post('updatesettings');
				if (!empty ($updatesett)) {
						$this->Settings_model->update_front_settings();
						redirect('admin/spa/settings');
				}
				$this->data['spatypes'] = $this->Spa_model->get_spa_settings_data("ttypes");
				$this->data['spacategories'] = $this->Spa_model->get_spa_settings_data("tcategory");
				$this->data['sparatings'] = $this->Spa_model->get_spa_settings_data("tratings");
				$this->data['spainclusions'] = $this->Spa_model->get_spa_settings_data("tamenities");
				$this->data['spaexclusions'] = $this->Spa_model->get_spa_settings_data("texclusions");
				$this->data['spapayments'] = $this->Spa_model->get_spa_settings_data("tpayments");
				$this->data['settings'] = $this->Settings_model->get_front_settings("spa");
				$this->data['main_content'] = 'Spa/settings';
				$this->data['page_title'] = 'Spa Settings';
				$this->load->view('Admin/template', $this->data);*/
		}

		function manage($spaname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Spa/Spa_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($spaname)) {
						redirect($this->data['adminsegment'] . '/spa/');
				}
				$updatespa = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$spaid = $this->input->post('spaid');
				if (!empty ($updatespa)) {
						$this->form_validation->set_rules('spaname', 'Spa Name', 'trim|required');
						$this->form_validation->set_rules('spatype', 'Spa Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$spalocations = $this->spaLocationsCheck($this->input->post('locations'));
							if(empty($spalocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Spa_model->update_spa($spaid);
							$this->Spa_model->update_translation($this->input->post('translated'), $spaid);
							$this->session->set_flashdata('flashmsgs', 'Spa Updated Successfully');
							echo "done";

							}



						}
				}
				else {
						$this->data['tdata'] = $this->Spa_model->get_spa_data($spaname);

						if (empty ($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/spa/');
						}
                       $comfixed = $this->data['tdata'][0]->spa_comm_fixed;
                       $comper = $this->data['tdata'][0]->spa_comm_percentage;
                       if($comfixed > 0){
                         $this->data['spadepositval'] = $comfixed;
                         $this->data['spadeposittype'] = "fixed";
                       }else{
                         $this->data['spadepositval'] = $comper;
                         $this->data['spadeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->spa_tax_fixed;
                       $taxper = $this->data['tdata'][0]->spa_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['spataxval'] = $taxfixed;
                         $this->data['spataxtype'] = "fixed";
                       }else{
                         $this->data['spataxval'] = $taxper;
                         $this->data['spataxtype'] = "percentage";
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

						$this->data['all_spa'] = $this->Spa_model->select_related_spa($this->data['tdata'][0]->spa_id);
						$this->data['map_data'] = $this->Spa_model->get_spa_map($this->data['tdata'][0]->spa_id);
						$this->data['maxmaporder'] = $this->Spa_model->max_map_order($this->data['tdata'][0]->spa_id);
						$this->data['has_start'] = $this->Spa_model->has_start_end_city("start", $this->data['tdata'][0]->spa_id);
						$this->data['has_end'] = $this->Spa_model->has_start_end_city("end", $this->data['tdata'][0]->spa_id);
						$this->data['offers_data'] = $this->Spa_model->offers_data($this->data['tdata'][0]->spa_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->spa_owned_by);
						$this->data['spatypes'] = $this->Spa_model->get_tsettings_data("ttypes");
						$this->data['spacategories'] = $this->Spa_model->get_tsettings_data("tcategory");
						$this->data['sparatings'] = $this->Spa_model->get_tsettings_data("tratings");
						$this->data['spainclusions'] = $this->Spa_model->get_tsettings_data("tamenities");
						$this->data['spaexclusions'] = $this->Spa_model->get_tsettings_data("texclusions");
						$this->data['spapayments'] = $this->Spa_model->get_tsettings_data("tpayments");
						$this->data['spaid'] = $this->data['tdata'][0]->spa_id;

						$this->load->model('Admin/Locations_model');
						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->data['spalocations'] = $this->Spa_model->spaSelectedLocations($this->data['tdata'][0]->spa_id);

						$this->data['main_content'] = 'Spa/manage';
						$this->data['page_title'] = 'Manage Spa';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Spa/Spa_lib');
				$this->Spa_lib->set_spaid($id);
				$this->data['itemid'] = $this->Spa_lib->get_id();
				$this->data['images'] = $this->Spa_model->spaGallery($id);
                $this->data['imgorderUrl'] = base_url().'spa/spaajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'spa/spaback/galleryUpload/spa/';
                $this->data['delimgUrl'] = base_url().'spa/spaajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'spa/spaajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'spa/spaajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'spa/spaajaxcalls/deleteMultipleSpaImages';
                $this->data['fullImgDir'] = PT_SPA_SLIDER;
                $this->data['thumbsDir'] = PT_SPA_SLIDER_THUMB;
				$this->data['main_content'] = 'Spa/gallery';
				$this->data['page_title'] = 'Spa Gallery';
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
						$targetPath = PT_SPA_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
                        $config['new_image'] = PT_SPA_SLIDER_THUMB_UPLOAD;

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
						$this->Spa_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete spa images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_SPA_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_SPA_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_spa_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($spalug, $lang = null) {
				$this->load->library('Spa/Spa_lib');
				$this->Spa_lib->set_spaid($spalug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($spalug)) {
						redirect($this->data['adminsegment'] . '/spa/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$spaid = $this->input->post('spaid');
						$this->Spa_model->add_translation($language, $spaid);
						redirect($this->data['adminsegment'] . "/spa/translate/" . $spalug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Spa_model->update_translation($lang, $spalug);
						redirect($this->data['adminsegment'] . "/spa/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Spa_lib->spa_details();
				if ($lang == $this->langdef) {
						$spadata = $this->Spa_lib->spa_short_details();
						$this->data['spadata'] = $spadata;
						$this->data['transpolicy'] = $spadata[0]->spa_privacy;
						$this->data['transdesc'] = $spadata[0]->spa_desc;
						$this->data['transtitle'] = $spadata[0]->spa_title;
				}
				else {
						$spadata = $this->Spa_lib->translated_data($lang);
						$this->data['spadata'] = $spadata;
						$this->data['transid'] = $spadata[0]->trans_id;
						$this->data['transpolicy'] = $spadata[0]->trans_policy;
						$this->data['transdesc'] = $spadata[0]->trans_desc;
						$this->data['transtitle'] = $spadata[0]->trans_title;
				}
				$this->data['spaid'] = $this->Spa_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $spalug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->spa_owned_by) {
								redirect($this->data['adminsegment'] . '/spa/');
						}
				}
				$this->data['main_content'] = 'Spa/translate';
				$this->data['page_title'] = 'Translate Spa';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudSpaSettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_spa_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'spa/spaajaxcalls/delTypeSettings';
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

				$xc->multiDelUrl = base_url().'spa/spaajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierSpa = $this->Spa_model->all_spa($this->data['userloggedin']);
			 $allspa = $this->Spa_model->all_spa();

         echo  modules :: run('Admin/extras/listings','spa',$allspa,$supplierSpa);

		}else{

			$spa = $this->Spa_model->all_spa();
         echo  modules :: run('Admin/extras/listings','spa',$spa);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','spa');
        }


        function spaLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
