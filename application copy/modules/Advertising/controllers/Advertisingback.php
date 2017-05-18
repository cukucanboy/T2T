<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Advertisingback extends MX_Controller {
	 public $accType = "";
	private $langdef;
	public  $editpermission = true;
  public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'advertising');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'advertising');
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

				/*
 $chk = modules::run('home/is_module_enabled','advertisings');
if(!$chk){
redirect('admin');
}


				$this->data['c_model'] = $this->countries_model;
				if (!pt_permissions('advertising', $this->data['userloggedin'])) {
						redirect('admin');
				}
*/

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Advertising/Advertising_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Advertising/advertising');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editadvertising", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deleteadvertising", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addadvertising", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_advertising');

                if($this->role == "supplier"){
                $xcrud->where('advertising_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('advertising_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_advertising.advertising_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {advertising_owned_by}');
				$xcrud->label('advertising_title', 'Name')->label('advertising_stars', 'Stars')->label('advertising_is_featured', '')->label('advertising_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/advertising/manage/{advertising_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('advertising_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/advertising/manage/{advertising_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'advertising/advertisingajaxcalls/delAdvertising';
                $xcrud->multiDelUrl = base_url().'advertising/advertisingajaxcalls/delMultipleAdvertising';
                $xcrud->button("javascript: delfunc('{advertising_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{advertising_id}'));
                }
                $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('advertising_order', '7%');
				$xcrud->columns('advertising_is_featured,thumbnail_image,advertising_title,advertising_stars,Owned By,advertising_slug,advertising_order,advertising_status');

				$xcrud->search_columns('advertising_title,advertising_stars,Owned By,advertising_order,advertising_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('advertising_slug', 'Gallery');
				$xcrud->label('advertising_status', 'Status');
				$xcrud->column_callback('advertising_stars', 'create_stars');
				$xcrud->column_callback('pt_advertising.advertising_order', 'orderInputAdvertising');
				$xcrud->column_callback('pt_advertising.advertising_is_featured', 'feature_starsAdvertising');
				$xcrud->column_callback('advertising_slug', 'advertisingGallery');
				$xcrud->column_callback('advertising_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_ADVERTISING_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Advertising Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Advertising Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/advertising/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                 backError_404($this->data);
				  }else{
				$addadvertising = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";
        $this->data['submittype'] = "add";

				if (!empty ($addadvertising)) {
						$this->form_validation->set_rules('advertisingname', 'Advertising Name', 'trim|required');
						$this->form_validation->set_rules('advertisingtype', 'Advertising Type', 'trim|required');
				// วางได้		$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$advertisinglocations = $this->advertisingLocationsCheck($this->input->post('locations'));

							if(empty($advertisinglocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{
							$advertisingid = $this->Advertising_model->add_advertising($this->data['userloggedin']);
							$this->Advertising_model->add_translation($this->input->post('translated'), $advertisingid);
							$this->session->set_flashdata('flashmsgs', 'Advertising added Successfully');
							echo "done";
						}


						}

				}
				else {

						$this->data['advertisingtypes'] = $this->Advertising_model->get_tsettings_data("ttypes");
						$this->data['advertisingcategories'] = $this->Advertising_model->get_tsettings_data("tcategory");
						$this->data['advertisingratings'] = $this->Advertising_model->get_tsettings_data("tratings");
						$this->data['advertisinginclusions'] = $this->Advertising_model->get_tsettings_data("tamenities");
						$this->data['advertisingexclusions'] = $this->Advertising_model->get_tsettings_data("texclusions");
						$this->data['advertisingpayments'] = $this->Advertising_model->get_tsettings_data("tpayments");
						$this->data['all_countries'] = $this->Countries_model->get_all_countries();
						$this->data['all_advertising'] = $this->Advertising_model->select_related_advertising($this->data['userloggedin']);

						$this->load->model('Admin/Locations_model');
						$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->load->model('Destinations/Destinations_model');
						$this->data['destinations'] = $this->Destinations_model->getDestinationBackend();
						$this->load->model('Wish/Wish_model');
						$this->data['wish'] = $this->Wish_model->getWishBackend();

				  	$this->data['main_content'] = 'Advertising/manage';
						$this->data['page_title'] = 'Add Advertising';
						$this->data['headingText'] = 'Add Advertising';
		      	$this->load->view('Admin/template', $this->data);
				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/advertising/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Advertising_model->updateAdvertisingSettings();
						redirect('admin/advertising/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Advertising_model->addSettingsData();
                    $this->Advertising_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/advertising/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Advertising_model->updateSettingsData();
                   $this->Advertising_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/advertising/settings');

				}

				$this->LoadXcrudAdvertisingSettings("tamenities");
				$this->LoadXcrudAdvertisingSettings("ttypes");
				$this->LoadXcrudAdvertisingSettings("tpayments");
				$this->LoadXcrudAdvertisingSettings("texclusions");

                $this->data['typeSettings'] = $this->Advertising_model->get_advertising_settings_data();

				@ $this->data['settings'] = $this->Settings_model->get_front_settings("advertising");
				$this->data['main_content'] = 'Advertising/settings';
				$this->data['page_title'] = 'Advertising Settings';
				$this->load->view('Admin/template', $this->data);

		/*
				$this->load->model('admin/settings_model');
				$this->data['all_countries'] = $this->Countries_model->get_all_countries();
				$updatesett = $this->input->post('updatesettings');
				if (!empty ($updatesett)) {
						$this->Settings_model->update_front_settings();
						redirect('admin/advertisings/settings');
				}
				$this->data['advertisingtypes'] = $this->Advertising_model->get_advertising_settings_data("ttypes");
				$this->data['advertisingcategories'] = $this->Advertising_model->get_advertising_settings_data("tcategory");
				$this->data['advertisingratings'] = $this->Advertising_model->get_advertising_settings_data("tratings");
				$this->data['advertisinginclusions'] = $this->Advertising_model->get_advertising_settings_data("tamenities");
				$this->data['advertisingexclusions'] = $this->Advertising_model->get_advertising_settings_data("texclusions");
				$this->data['advertisingpayments'] = $this->Advertising_model->get_advertising_settings_data("tpayments");
				$this->data['settings'] = $this->Settings_model->get_front_settings("advertisings");
				$this->data['main_content'] = 'Advertising/settings';
				$this->data['page_title'] = 'Advertising Settings';
				$this->load->view('Admin/template', $this->data);*/
		}

		function manage($advertisingname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Advertising/Advertising_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($advertisingname)) {
						redirect($this->data['adminsegment'] . '/advertising/');
				}
				$updateadvertising = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$advertisingid = $this->input->post('advertisingid');
				if (!empty ($updateadvertising)) {
						$this->form_validation->set_rules('advertisingname', 'Advertising Name', 'trim|required');
						$this->form_validation->set_rules('advertisingtype', 'Advertising Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$advertisinglocations = $this->advertisingLocationsCheck($this->input->post('locations'));
							if(empty($advertisinglocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Advertising_model->update_advertising($advertisingid);
							$this->Advertising_model->update_translation($this->input->post('translated'), $advertisingid);
							$this->session->set_flashdata('flashmsgs', 'Advertising Updated Successfully');
							echo "done";

							}



						}
				}
				else {



						$this->data['tdata'] = $this->Advertising_model->get_advertising_data($advertisingname);

						if (empty($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/advertising/');
						}
                       $comfixed = $this->data['tdata'][0]->advertising_comm_fixed;
                       $comper = $this->data['tdata'][0]->advertising_comm_percentage;
                       if($comfixed > 0){
                         $this->data['advertisingdepositval'] = $comfixed;
                         $this->data['advertisingdeposittype'] = "fixed";
                       }else{
                         $this->data['advertisingdepositval'] = $comper;
                         $this->data['advertisingdeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->advertising_tax_fixed;
                       $taxper = $this->data['tdata'][0]->advertising_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['advertisingtaxval'] = $taxfixed;
                         $this->data['advertisingtaxtype'] = "fixed";
                       }else{
                         $this->data['advertisingtaxval'] = $taxper;
                         $this->data['advertisingtaxtype'] = "percentage";
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

						$this->data['all_advertising'] = $this->Advertising_model->select_related_advertising($this->data['tdata'][0]->advertising_id);
						$this->data['map_data'] = $this->Advertising_model->get_advertising_map($this->data['tdata'][0]->advertising_id);
						$this->data['maxmaporder'] = $this->Advertising_model->max_map_order($this->data['tdata'][0]->advertising_id);
						$this->data['has_start'] = $this->Advertising_model->has_start_end_city("start", $this->data['tdata'][0]->advertising_id);
						$this->data['has_end'] = $this->Advertising_model->has_start_end_city("end", $this->data['tdata'][0]->advertising_id);
						$this->data['offers_data'] = $this->Advertising_model->offers_data($this->data['tdata'][0]->advertising_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->advertising_owned_by);
						$this->data['advertisingtypes'] = $this->Advertising_model->get_tsettings_data("ttypes");
						$this->data['advertisingcategories'] = $this->Advertising_model->get_tsettings_data("tcategory");
						$this->data['advertisingratings'] = $this->Advertising_model->get_tsettings_data("tratings");
						$this->data['advertisinginclusions'] = $this->Advertising_model->get_tsettings_data("tamenities");
						$this->data['advertisingexclusions'] = $this->Advertising_model->get_tsettings_data("texclusions");
						$this->data['advertisingpayments'] = $this->Advertising_model->get_tsettings_data("tpayments");
						$this->data['advertisingid'] = $this->data['tdata'][0]->advertising_id;

						$this->load->model('Admin/Locations_model');
						$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->load->model('Destinations/Destinations_model');
						$this->data['destinations'] = $this->Destinations_model->getDestinationBackend();

						$this->load->model('Wish/Wish_model');
						$this->data['wish'] = $this->Wish_model->getWishBackend();



						$this->data['main_content'] = 'Advertising/manage';
						$this->data['page_title'] = 'Manage Advertising';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Advertising/Advertising_lib');
				$this->Advertising_lib->set_advertisingid($id);
				$this->data['itemid'] = $this->Advertising_lib->get_id();
				$this->data['images'] = $this->Advertising_model->advertisingGallery($id);
                $this->data['imgorderUrl'] = base_url().'advertising/advertisingajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'advertising/advertisingback/galleryUpload/advertising/';
                $this->data['delimgUrl'] = base_url().'advertising/advertisingajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'advertising/advertisingajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'advertising/advertisingajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'advertising/advertisingajaxcalls/deleteMultipleAdvertisingImages';
                $this->data['fullImgDir'] = PT_ADVERTISING_SLIDER;
                $this->data['thumbsDir'] = PT_ADVERTISING_SLIDER_THUMB;
				$this->data['main_content'] = 'Advertising/gallery';
				$this->data['page_title'] = 'Advertising Gallery';
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
						$targetPath = PT_ADVERTISING_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
            $config['new_image'] = PT_ADVERTISING_SLIDER_THUMB_UPLOAD;

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
						$this->Advertising_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete advertising images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_ADVERTISING_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_ADVERTISING_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_advertising_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($advertisinglug, $lang = null) {
				$this->load->library('Advertising/Advertising_lib');
				$this->Advertising_lib->set_advertisingid($advertisinglug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($advertisinglug)) {
						redirect($this->data['adminsegment'] . '/advertising/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$advertisingid = $this->input->post('advertisingid');
						$this->Advertising_model->add_translation($language, $advertisingid);
						redirect($this->data['adminsegment'] . "/advertising/translate/" . $advertisinglug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Advertising_model->update_translation($lang, $advertisinglug);
						redirect($this->data['adminsegment'] . "/advertising/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Advertising_lib->advertising_details();
				if ($lang == $this->langdef) {
						$advertisingdata = $this->Advertising_lib->advertising_short_details();
						$this->data['advertisingdata'] = $advertisingdata;
						$this->data['transpolicy'] = $advertisingdata[0]->advertising_privacy;
						$this->data['transdesc'] = $advertisingdata[0]->advertising_desc;
						$this->data['transtitle'] = $advertisingdata[0]->advertising_title;
				}
				else {
						$advertisingdata = $this->Advertising_lib->translated_data($lang);
						$this->data['advertisingdata'] = $advertisingdata;
						$this->data['transid'] = $advertisingdata[0]->trans_id;
						$this->data['transpolicy'] = $advertisingdata[0]->trans_policy;
						$this->data['transdesc'] = $advertisingdata[0]->trans_desc;
						$this->data['transtitle'] = $advertisingdata[0]->trans_title;
				}
				$this->data['advertisingid'] = $this->Advertising_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $advertisinglug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->advertising_owned_by) {
								redirect($this->data['adminsegment'] . '/advertising/');
						}
				}
				$this->data['main_content'] = 'Advertising/translate';
				$this->data['page_title'] = 'Translate Advertising';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudAdvertisingSettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_advertising_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'advertising/advertisingajaxcalls/delTypeSettings';
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

				$xc->multiDelUrl = base_url().'advertising/advertisingajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierAdvertising = $this->Advertising_model->all_advertising($this->data['userloggedin']);
			 $alladvertisings = $this->Advertising_model->all_advertising();

         echo  modules :: run('Admin/extras/listings','advertising',$alladvertising,$supplierAdvertising);

		}else{

			$advertisings = $this->Advertising_model->all_advertising();
         echo  modules :: run('Admin/extras/listings','advertising',$advertising);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','advertising');
        }


        function advertisingLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
