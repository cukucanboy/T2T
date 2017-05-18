<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Entertainmentback extends MX_Controller {
	    public $accType = "";
		private $langdef;
		public  $editpermission = true;
        public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'entertainment');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'entertainment');
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
/*   $chk = modules::run('home/is_module_enabled','entertainment');
if(!$chk){
redirect('admin');
}*/
				$this->data['c_model'] = $this->countries_model;
				if (!pt_permissions('entertainment', $this->data['userloggedin'])) {
						redirect('admin');
				}

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Entertainment/Entertainment_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Entertainment/entertainment');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editentertainment", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deleteentertainment", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addentertainment", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_entertainment');

                if($this->role == "supplier"){
                $xcrud->where('entertainment_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('entertainment_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_entertainment.entertainment_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {entertainment_owned_by}');
				$xcrud->label('entertainment_title', 'Name')->label('entertainment_stars', 'Stars')->label('entertainment_is_featured', '')->label('entertainment_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/entertainment/manage/{entertainment_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('entertainment_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/entertainment/manage/{entertainment_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'entertainment/entertainmentajaxcalls/delEntertainment';
                $xcrud->multiDelUrl = base_url().'entertainment/entertainmentajaxcalls/delMultipleEntertainment';
                $xcrud->button("javascript: delfunc('{entertainment_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{entertainment_id}'));
                }
                $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('entertainment_order', '7%');
				$xcrud->columns('entertainment_is_featured,thumbnail_image,entertainment_title,entertainment_stars,Owned By,entertainment_slug,entertainment_order,entertainment_status');

				$xcrud->search_columns('entertainment_title,entertainment_stars,Owned By,entertainment_order,entertainment_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('entertainment_slug', 'Gallery');
				$xcrud->label('entertainment_status', 'Status');
				$xcrud->column_callback('entertainment_stars', 'create_stars');
				$xcrud->column_callback('pt_entertainment.entertainment_order', 'orderInputEntertainment');
				$xcrud->column_callback('pt_entertainment.entertainment_is_featured', 'feature_starsEntertainment');
				$xcrud->column_callback('entertainment_slug', 'entertainmentGallery');
				$xcrud->column_callback('entertainment_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_ENTERTAINMENT_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Entertainment Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Entertainment Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/entertainment/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                 backError_404($this->data);

				  }else{

				$this->data['data_relate'] = $this->Entertainment_model->data_for_relate_near_by(); //add by poy
				$addentertainment = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";

                $this->data['submittype'] = "add";

				if (!empty ($addentertainment)) {
						$this->form_validation->set_rules('entertainmentname', 'Entertainment Name', 'trim|required');
						$this->form_validation->set_rules('entertainmenttype', 'Entertainment Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');


						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$entertainmentlocations = $this->entertainmentLocationsCheck($this->input->post('locations'));
							if(empty($entertainmentlocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$entertainmentid = $this->Entertainment_model->add_entertainment($this->data['userloggedin']);
							$this->Entertainment_model->add_translation($this->input->post('translated'), $entertainmentid);
							$this->session->set_flashdata('flashmsgs', 'Entertainment added Successfully');
							echo "done";
						}


						}

				}
				else {

						$this->data['entertainmenttypes'] = $this->Entertainment_model->get_tsettings_data("ttypes");
						$this->data['entertainmentcategories'] = $this->Entertainment_model->get_tsettings_data("tcategory");
						$this->data['entertainmentratings'] = $this->Entertainment_model->get_tsettings_data("tratings");
						$this->data['entertainmentinclusions'] = $this->Entertainment_model->get_tsettings_data("tamenities");
						$this->data['entertainmentexclusions'] = $this->Entertainment_model->get_tsettings_data("texclusions");
						$this->data['entertainmentpayments'] = $this->Entertainment_model->get_tsettings_data("tpayments");
						$this->data['all_countries'] = $this->Countries_model->get_all_countries();
						$this->data['all_entertainment'] = $this->Entertainment_model->select_related_entertainment($this->data['userloggedin']);

						$this->load->model('Admin/Locations_model');

						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();

						$this->data['main_content'] = 'Entertainment/manage';
						$this->data['page_title'] = 'Add Entertainment';
						$this->data['headingText'] = 'Add Entertainment';
						$this->load->view('Admin/template', $this->data);
				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/entertainment/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Entertainment_model->updateEntertainmentSettings();
						redirect('admin/entertainment/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Entertainment_model->addSettingsData();
                    $this->Entertainment_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/entertainment/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Entertainment_model->updateSettingsData();
                   $this->Entertainment_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/entertainment/settings');

				}

				$this->LoadXcrudEntertainmentSettings("tamenities");
				$this->LoadXcrudEntertainmentSettings("ttypes");
				$this->LoadXcrudEntertainmentSettings("tpayments");
				$this->LoadXcrudEntertainmentSettings("texclusions");

                $this->data['typeSettings'] = $this->Entertainment_model->get_entertainment_settings_data();

				@ $this->data['settings'] = $this->Settings_model->get_front_settings("entertainment");
				$this->data['main_content'] = 'Entertainment/settings';
				$this->data['page_title'] = 'Entertainment Settings';
				$this->load->view('Admin/template', $this->data);

		/*
				$this->load->model('admin/settings_model');
				$this->data['all_countries'] = $this->Countries_model->get_all_countries();
				$updatesett = $this->input->post('updatesettings');
				if (!empty ($updatesett)) {
						$this->Settings_model->update_front_settings();
						redirect('admin/entertainment/settings');
				}
				$this->data['entertainmenttypes'] = $this->Entertainment_model->get_entertainment_settings_data("ttypes");
				$this->data['entertainmentcategories'] = $this->Entertainment_model->get_entertainment_settings_data("tcategory");
				$this->data['entertainmentratings'] = $this->Entertainment_model->get_entertainment_settings_data("tratings");
				$this->data['entertainmentinclusions'] = $this->Entertainment_model->get_entertainment_settings_data("tamenities");
				$this->data['entertainmentexclusions'] = $this->Entertainment_model->get_entertainment_settings_data("texclusions");
				$this->data['entertainmentpayments'] = $this->Entertainment_model->get_entertainment_settings_data("tpayments");
				$this->data['settings'] = $this->Settings_model->get_front_settings("entertainment");
				$this->data['main_content'] = 'Entertainment/settings';
				$this->data['page_title'] = 'Entertainment Settings';
				$this->load->view('Admin/template', $this->data);*/
		}

		function manage($entertainmentname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Entertainment/Entertainment_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($entertainmentname)) {
						redirect($this->data['adminsegment'] . '/entertainment/');
				}
				$updateentertainment = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$entertainmentid = $this->input->post('entertainmentid');
				if (!empty ($updateentertainment)) {
						$this->form_validation->set_rules('entertainmentname', 'Entertainment Name', 'trim|required');
						$this->form_validation->set_rules('entertainmenttype', 'Entertainment Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$entertainmentlocations = $this->entertainmentLocationsCheck($this->input->post('locations'));
							if(empty($entertainmentlocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Entertainment_model->update_entertainment($entertainmentid);
							$this->Entertainment_model->update_translation($this->input->post('translated'), $entertainmentid);
							$this->session->set_flashdata('flashmsgs', 'Entertainment Updated Successfully');
							echo "done";

							}



						}
				}
				else {
						$this->data['tdata'] = $this->Entertainment_model->get_entertainment_data($entertainmentname);

						if (empty ($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/entertainment/');
						}
                       $comfixed = $this->data['tdata'][0]->entertainment_comm_fixed;
                       $comper = $this->data['tdata'][0]->entertainment_comm_percentage;
                       if($comfixed > 0){
                         $this->data['entertainmentdepositval'] = $comfixed;
                         $this->data['entertainmentdeposittype'] = "fixed";
                       }else{
                         $this->data['entertainmentdepositval'] = $comper;
                         $this->data['entertainmentdeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->entertainment_tax_fixed;
                       $taxper = $this->data['tdata'][0]->entertainment_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['entertainmenttaxval'] = $taxfixed;
                         $this->data['entertainmenttaxtype'] = "fixed";
                       }else{
                         $this->data['entertainmenttaxval'] = $taxper;
                         $this->data['entertainmenttaxtype'] = "percentage";
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

						$this->data['all_entertainment'] = $this->Entertainment_model->select_related_entertainment($this->data['tdata'][0]->entertainment_id);
						$this->data['data_relate'] = $this->Entertainment_model->data_for_relate_near_by(); //add by poy
						$this->data['map_data'] = $this->Entertainment_model->get_entertainment_map($this->data['tdata'][0]->entertainment_id);
						$this->data['maxmaporder'] = $this->Entertainment_model->max_map_order($this->data['tdata'][0]->entertainment_id);
						$this->data['has_start'] = $this->Entertainment_model->has_start_end_city("start", $this->data['tdata'][0]->entertainment_id);
						$this->data['has_end'] = $this->Entertainment_model->has_start_end_city("end", $this->data['tdata'][0]->entertainment_id);
						$this->data['offers_data'] = $this->Entertainment_model->offers_data($this->data['tdata'][0]->entertainment_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->entertainment_owned_by);
						$this->data['entertainmenttypes'] = $this->Entertainment_model->get_tsettings_data("ttypes");
						$this->data['entertainmentcategories'] = $this->Entertainment_model->get_tsettings_data("tcategory");
						$this->data['entertainmentratings'] = $this->Entertainment_model->get_tsettings_data("tratings");
						$this->data['entertainmentinclusions'] = $this->Entertainment_model->get_tsettings_data("tamenities");
						$this->data['entertainmentexclusions'] = $this->Entertainment_model->get_tsettings_data("texclusions");
						$this->data['entertainmentpayments'] = $this->Entertainment_model->get_tsettings_data("tpayments");
						$this->data['entertainmentid'] = $this->data['tdata'][0]->entertainment_id;

						$this->load->model('Admin/Locations_model');
						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->data['entertainmentlocations'] = $this->Entertainment_model->entertainmentSelectedLocations($this->data['tdata'][0]->entertainment_id);

						$this->data['main_content'] = 'Entertainment/manage';
						$this->data['page_title'] = 'Manage Entertainment';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Entertainment/Entertainment_lib');
				$this->Entertainment_lib->set_entertainmentid($id);
				$this->data['itemid'] = $this->Entertainment_lib->get_id();
				$this->data['images'] = $this->Entertainment_model->entertainmentGallery($id);
                $this->data['imgorderUrl'] = base_url().'entertainment/entertainmentajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'entertainment/entertainmentback/galleryUpload/entertainment/';
                $this->data['delimgUrl'] = base_url().'entertainment/entertainmentajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'entertainment/entertainmentajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'entertainment/entertainmentajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'entertainment/entertainmentajaxcalls/deleteMultipleEntertainmentImages';
                $this->data['fullImgDir'] = PT_ENTERTAINMENT_SLIDER;
                $this->data['thumbsDir'] = PT_ENTERTAINMENT_SLIDER_THUMB;
				$this->data['main_content'] = 'Entertainment/gallery';
				$this->data['page_title'] = 'Entertainment Gallery';
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
						$targetPath = PT_ENTERTAINMENT_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
                        $config['new_image'] = PT_ENTERTAINMENT_SLIDER_THUMB_UPLOAD;

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
						$this->Entertainment_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete entertainment images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_ENTERTAINMENT_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_ENTERTAINMENT_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_entertainment_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($entertainmentlug, $lang = null) {
				$this->load->library('Entertainment/Entertainment_lib');
				$this->Entertainment_lib->set_entertainmentid($entertainmentlug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($entertainmentlug)) {
						redirect($this->data['adminsegment'] . '/entertainment/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$entertainmentid = $this->input->post('entertainmentid');
						$this->Entertainment_model->add_translation($language, $entertainmentid);
						redirect($this->data['adminsegment'] . "/entertainment/translate/" . $entertainmentlug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Entertainment_model->update_translation($lang, $entertainmentlug);
						redirect($this->data['adminsegment'] . "/entertainment/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Entertainment_lib->entertainment_details();
				if ($lang == $this->langdef) {
						$entertainmentdata = $this->Entertainment_lib->entertainment_short_details();
						$this->data['entertainmentdata'] = $entertainmentdata;
						$this->data['transpolicy'] = $entertainmentdata[0]->entertainment_privacy;
						$this->data['transdesc'] = $entertainmentdata[0]->entertainment_desc;
						$this->data['transtitle'] = $entertainmentdata[0]->entertainment_title;
				}
				else {
						$entertainmentdata = $this->Entertainment_lib->translated_data($lang);
						$this->data['entertainmentdata'] = $entertainmentdata;
						$this->data['transid'] = $entertainmentdata[0]->trans_id;
						$this->data['transpolicy'] = $entertainmentdata[0]->trans_policy;
						$this->data['transdesc'] = $entertainmentdata[0]->trans_desc;
						$this->data['transtitle'] = $entertainmentdata[0]->trans_title;
				}
				$this->data['entertainmentid'] = $this->Entertainment_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $entertainmentlug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->entertainment_owned_by) {
								redirect($this->data['adminsegment'] . '/entertainment/');
						}
				}
				$this->data['main_content'] = 'Entertainment/translate';
				$this->data['page_title'] = 'Translate Entertainment';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudEntertainmentSettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_entertainment_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'entertainment/entertainmentajaxcalls/delTypeSettings';
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

				$xc->multiDelUrl = base_url().'entertainment/entertainmentajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierEntertainment = $this->Entertainment_model->all_entertainment($this->data['userloggedin']);
			 $allentertainment = $this->Entertainment_model->all_entertainment();

         echo  modules :: run('Admin/extras/listings','entertainment',$allentertainment,$supplierEntertainment);

		}else{

			$entertainment = $this->Entertainment_model->all_entertainment();
         echo  modules :: run('Admin/extras/listings','entertainment',$entertainment);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','entertainment');
        }


        function entertainmentLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
