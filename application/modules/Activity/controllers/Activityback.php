<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Activityback extends MX_Controller {
	public $accType = "";
	private $langdef;
	public  $editpermission = true;
        public  $deletepermission = true;

		function __construct() {
				$chk = modules :: run('Home/is_main_module_enabled', 'activity');
				$seturl = $this->uri->segment(3);
				if ($seturl != "settings") {
						$chk = modules :: run('Home/is_main_module_enabled', 'activity');
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
/*   $chk = modules::run('home/is_module_enabled','activity');
if(!$chk){
redirect('admin');
}*/
				$this->data['c_model'] = @$this->countries_model;
				if (!pt_permissions('activity', $this->data['userloggedin'])) {
						redirect('admin');
				}

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				$this->load->helper('settings');
				$this->load->model('Activity/Activity_model');
				$this->load->library('Ckeditor');
				$this->data['ckconfig'] = array();
				$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format','Font', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
				$this->data['ckconfig']['language'] = 'en';
				//$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';

                $this->data['ckconfig']['extraPlugins'] = 'colorbutton';
                $this->langdef = DEFLANG;
                $this->load->helper('xcrud');
                $this->load->helper('Activity/activity');
                $this->data['addpermission'] = true;
                if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editactivity", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deleteactivity", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("addactivity", $this->data['userloggedin']);
                }
                $this->data['languages'] = pt_get_languages();

        }

		function index() {

				if(!$this->data['addpermission'] && !$this->editpermission && !$this->deletepermission){
                	backError_404($this->data);

                }else{
				$xcrud = xcrud_get_instance();
				$xcrud->table('pt_activity');

                if($this->role == "supplier"){
                $xcrud->where('activity_owned_by',$this->data['userloggedin']);

                }
				$xcrud->join('activity_owned_by', 'pt_accounts', 'accounts_id');
				$xcrud->order_by('pt_activity.activity_id', 'desc');
				$xcrud->subselect('Owned By', 'SELECT CONCAT(ai_first_name, " ", ai_last_name) FROM pt_accounts WHERE accounts_id = {activity_owned_by}');
				$xcrud->label('activity_title', 'Name')->label('activity_stars', 'Stars')->label('activity_is_featured', '')->label('activity_order', 'Order');
                if($this->editpermission){
                $xcrud->button(base_url() . $this->data['adminsegment'] . '/activity/manage/{activity_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
                $xcrud->column_pattern('activity_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/activity/manage/{activity_slug}' . '">{value}</a>');
                }

                if($this->deletepermission){
                $delurl = base_url().'activity/activityajaxcalls/delActivity';
                $xcrud->multiDelUrl = base_url().'activity/activityajaxcalls/delMultipleActivity';
                $xcrud->button("javascript: delfunc('{activity_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('id' => '{activity_id}'));
                }
                $xcrud->limit(50);
				$xcrud->unset_add();
				$xcrud->unset_edit();
				$xcrud->unset_remove();
				$xcrud->unset_view();
				$xcrud->column_width('activity_order', '7%');
				$xcrud->columns('activity_is_featured,thumbnail_image,activity_title,activity_stars,Owned By,activity_slug,activity_order,activity_status');

				$xcrud->search_columns('activity_title,activity_stars,Owned By,activity_order,activity_status');
				$xcrud->label('thumbnail_image', 'Image');
				$xcrud->label('activity_slug', 'Gallery');
				$xcrud->label('activity_status', 'Status');
				$xcrud->column_callback('activity_stars', 'create_stars');
				$xcrud->column_callback('pt_activity.activity_order', 'orderInputActivity');
				$xcrud->column_callback('pt_activity.activity_is_featured', 'feature_starsActivity');
				$xcrud->column_callback('activity_slug', 'activityGallery');
				$xcrud->column_callback('activity_status', 'create_status_icon');
				$xcrud->column_class('thumbnail_image', 'zoom_img');
				$xcrud->change_type('thumbnail_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_ACTIVITY_SLIDER_THUMB_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));
				$this->data['content'] = $xcrud->render();
				$this->data['page_title'] = 'Activity Management';
				$this->data['main_content'] = 'temp_view';
				$this->data['header_title'] = 'Activity Management';
				$this->data['add_link'] = base_url(). $this->data['adminsegment'] . '/activity/add';
				$this->load->view('Admin/template', $this->data);
			}
		}

		function add() {

			if(!$this->data['addpermission']){
                	 backError_404($this->data);

				  }else{

				$addactivity = $this->input->post('submittype');
				$this->data['adultStatus'] = "";
				$this->data['childStatus'] = "readonly";
				$this->data['infantStatus'] = "readonly";
				$this->data['adultInput'] = "1";
				$this->data['childInput'] = "0";
				$this->data['infantInput'] = "0";

                		$this->data['submittype'] = "add";

				if (!empty ($addactivity)) {
						$this->form_validation->set_rules('activityname', 'Activity Name', 'trim|required');
						$this->form_validation->set_rules('activitytype', 'Activity Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {

							$activitylocations = $this->activityLocationsCheck($this->input->post('locations'));
							if(empty($activitylocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$activityid = $this->Activity_model->add_activity($this->data['userloggedin']);
							$this->Activity_model->add_translation($this->input->post('translated'), $activityid);
							$this->session->set_flashdata('flashmsgs', 'Activity added Successfully');
							echo "done";
						}


						}

				}
				else {

$this->data['activitytypes'] = $this->Activity_model->get_tsettings_data("ttypes");
$this->data['activitycategories'] = $this->Activity_model->get_tsettings_data("tcategory");
$this->data['activityratings'] = $this->Activity_model->get_tsettings_data("tratings");
$this->data['activityinclusions'] = $this->Activity_model->get_tsettings_data("tamenities");
$this->data['activityexclusions'] = $this->Activity_model->get_tsettings_data("texclusions");
$this->data['activitypayments'] = $this->Activity_model->get_tsettings_data("tpayments");
$this->data['all_countries'] = $this->Countries_model->get_all_countries();
$this->data['all_activity'] = $this->Activity_model->select_related_activity($this->data['userloggedin']);
/* product_related */
$this->data['all_hotels'] = $this->Hotels_model->select_related_hotels($this->data['userloggedin']);
$this->data['all_restaurant'] = $this->Restaurant_model->select_related_restaurant($this->data['userloggedin']);
$this->data['all_wedding'] = $this->Wedding_model->select_related_wedding($this->data['userloggedin']);
$this->data['all_tours'] = $this->Tours_model->select_related_tours($this->data['userloggedin']);
$this->data['all_spa'] = $this->Spa_model->select_related_spa($this->data['userloggedin']);
//$this->data['all_activity'] = $this->Activity_model->select_related_activity($this->data['userloggedin']);
$this->data['all_cars'] = $this->Cars_model->select_related_cars($this->data['userloggedin']);
/* product_related */
			$this->load->model('Admin/Locations_model');
			$this->data['main_content'] = 'Activity/manage';
			$this->data['page_title'] = 'Add Activity';
			$this->data['headingText'] = 'Add Activity';
			$this->load->view('Admin/template', $this->data);

				}
			}



		}

		function settings() {

			$isadmin = $this->session->userdata('pt_logged_admin');
				if (empty ($isadmin)) {
						redirect($this->data['adminsegment'] . '/activity/');
				}
				$updatesett = $this->input->post('updatesettings');
				$addsettings = $this->input->post('add');
				$updatetypesett = $this->input->post('updatetype');

				if (!empty ($updatesett)) {

						$this->Activity_model->updateActivitySettings();
						redirect('admin/activity/settings');
				}

                if (!empty ($addsettings)) {
                    $id = $this->Activity_model->addSettingsData();
                    $this->Activity_model->updateSettingsTypeTranslation($this->input->post('translated'),$id);
                    redirect('admin/activity/settings');

				}

                if (!empty ($updatetypesett)) {
                   $this->Activity_model->updateSettingsData();
                   $this->Activity_model->updateSettingsTypeTranslation($this->input->post('translated'),$this->input->post('settid'));
                    redirect('admin/activity/settings');

				}

				$this->LoadXcrudActivitySettings("tamenities");
				$this->LoadXcrudActivitySettings("ttypes");
				$this->LoadXcrudActivitySettings("tpayments");
				$this->LoadXcrudActivitySettings("texclusions");

                		$this->data['typeSettings'] = $this->Activity_model->get_activity_settings_data();

				$this->data['settings'] = $this->Settings_model->get_front_settings("activity");
				$this->data['main_content'] = 'Activity/settings';
				$this->data['page_title'] = 'Activity Settings';
				$this->load->view('Admin/template', $this->data);

		}

		function manage($activityname) {
				$this->data['upload_allowed'] = pt_can_upload();
				$this->load->model('Activity/Activity_uploads_model');
				$this->load->model('Admin/Accounts_model');
				if (empty ($activityname)) {
						redirect($this->data['adminsegment'] . '/activity/');
				}
				$updateactivity = $this->input->post('submittype');
				$this->data['submittype'] = "update";
				$activityid = $this->input->post('activityid');
				if (!empty ($updateactivity)) {
						$this->form_validation->set_rules('activityname', 'Activity Name', 'trim|required');
						$this->form_validation->set_rules('activitytype', 'Activity Type', 'trim|required');
						$this->form_validation->set_rules('adultprice', 'Adult Price', 'trim|required');

						if ($this->form_validation->run() == FALSE) {
								echo '<div class="alert alert-danger">' . validation_errors() . '</div><br>';
						}
						else {
							$activitylocations = $this->activityLocationsCheck($this->input->post('locations'));
							if(empty($activitylocations)){
								echo '<div class="alert alert-danger">Please Select at least One location</div><br>';
							}else{

							$this->Activity_model->update_activity($activityid);
							$this->Activity_model->update_translation($this->input->post('translated'), $activityid);
							$this->session->set_flashdata('flashmsgs', 'Activity Updated Successfully');
							echo "done";

							}



						}
				}
				else {
						$this->data['tdata'] = $this->Activity_model->get_activity_data($activityname);

						if (empty ($this->data['tdata'])) {
								redirect($this->data['adminsegment'] . '/activity/');
						}
                       $comfixed = $this->data['tdata'][0]->activity_comm_fixed;
                       $comper = $this->data['tdata'][0]->activity_comm_percentage;
                       if($comfixed > 0){
                         $this->data['activitydepositval'] = $comfixed;
                         $this->data['activitydeposittype'] = "fixed";
                       }else{
                         $this->data['activitydepositval'] = $comper;
                         $this->data['activitydeposittype'] = "percentage";
                       }

                       $taxfixed = $this->data['tdata'][0]->activity_tax_fixed;
                       $taxper = $this->data['tdata'][0]->activity_tax_percentage;
                       if($taxfixed > 0){
                         $this->data['activitytaxval'] = $taxfixed;
                         $this->data['activitytaxtype'] = "fixed";
                       }else{
                         $this->data['activitytaxval'] = $taxper;
                         $this->data['activitytaxtype'] = "percentage";
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


						$this->data['all_activity'] = $this->Activity_model->select_related_activity($this->data['tdata'][0]->activity_id);
					/* product_related add On  Manage Function */
					$this->data['all_hotels'] = $this->Hotels_model->select_related_hotels($this->data['userloggedin']);
					$this->data['all_restaurant'] = $this->Restaurant_model->select_related_restaurant($this->data['userloggedin']);
					$this->data['all_wedding'] = $this->Wedding_model->select_related_wedding($this->data['userloggedin']);
					$this->data['all_tours'] = $this->Tours_model->select_related_tours($this->data['userloggedin']);
					$this->data['all_spa'] = $this->Spa_model->select_related_spa($this->data['userloggedin']);
					//$this->data['all_activity'] = $this->Activity_model->select_related_activity($this->data['userloggedin']);
					$this->data['all_cars'] = $this->Cars_model->select_related_cars($this->data['userloggedin']);
					/* product_related */


						$this->data['map_data'] = $this->Activity_model->get_activity_map($this->data['tdata'][0]->activity_id);
						$this->data['maxmaporder'] = $this->Activity_model->max_map_order($this->data['tdata'][0]->activity_id);
						$this->data['has_start'] = $this->Activity_model->has_start_end_city("start", $this->data['tdata'][0]->activity_id);
						$this->data['has_end'] = $this->Activity_model->has_start_end_city("end", $this->data['tdata'][0]->activity_id);
						$this->data['offers_data'] = $this->Activity_model->offers_data($this->data['tdata'][0]->activity_id);
						$this->data['userinfo'] = $this->Accounts_model->get_profile_details($this->data['tdata'][0]->activity_owned_by);
						$this->data['activitytypes'] = $this->Activity_model->get_tsettings_data("ttypes");
						$this->data['activitycategories'] = $this->Activity_model->get_tsettings_data("tcategory");
						$this->data['activityratings'] = $this->Activity_model->get_tsettings_data("tratings");
						$this->data['activityinclusions'] = $this->Activity_model->get_tsettings_data("tamenities");
						$this->data['activityexclusions'] = $this->Activity_model->get_tsettings_data("texclusions");
						$this->data['activitypayments'] = $this->Activity_model->get_tsettings_data("tpayments");
						$this->data['activityid'] = $this->data['tdata'][0]->activity_id;

						$this->load->model('Admin/Locations_model');
						//$this->data['locations'] = $this->Locations_model->getLocationsBackend();
						$this->data['activitylocations'] = $this->Activity_model->activitySelectedLocations($this->data['tdata'][0]->activity_id);

						$this->data['main_content'] = 'Activity/manage';
						$this->data['page_title'] = 'Manage Activity';
						$this->load->view('Admin/template', $this->data);
				}
		}


				function gallery($id) {

				$this->load->library('Activity/Activity_lib');
				$this->Activity_lib->set_activityid($id);
				$this->data['itemid'] = $this->Activity_lib->get_id();
				$this->data['images'] = $this->Activity_model->activityGallery($id);
                $this->data['imgorderUrl'] = base_url().'activity/activityajaxcalls/update_image_order';
                $this->data['uploadUrl'] = base_url().'activity/activityback/galleryUpload/activity/';
                $this->data['delimgUrl'] = base_url().'activity/activityajaxcalls/delete_image';
                $this->data['appRejUrl'] = base_url().'activity/activityajaxcalls/app_rej_timages';
                $this->data['makeThumbUrl'] = base_url().'activity/activityajaxcalls/makethumb';
                $this->data['delMultipleImgsUrl'] = base_url().'activity/activityajaxcalls/deleteMultipleActivityImages';
                $this->data['fullImgDir'] = PT_ACTIVITY_SLIDER;
                $this->data['thumbsDir'] = PT_ACTIVITY_SLIDER_THUMB;
				$this->data['main_content'] = 'Activity/gallery';
				$this->data['page_title'] = 'Activity Gallery';
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
						$targetPath = PT_ACTIVITY_SLIDER_UPLOAD;
                        $targetFile = $targetPath . $saveFile;

						move_uploaded_file($tempFile, $targetFile);

						$config['image_library'] = 'gd2';
						$config['source_image'] = $targetFile;
                        $config['new_image'] = PT_ACTIVITY_SLIDER_THUMB_UPLOAD;

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
						$this->Activity_model->addPhotos($id, $saveFile);

				}
			}
		}


// Delete activity images

		public function deleteimg($file, $type) {
				if ($type == "slider") {
						@ unlink(PT_ACTIVITY_SLIDER_THUMB_UPLOAD . $file);
						@ unlink(PT_ACTIVITY_SLIDER_UPLOAD . $file);
				}
				$this->db->where('timg_image', $file);
				$this->db->delete('pt_activity_images');
				$js = array("files" => array(array($file => "true")));
				echo json_encode($js);
		}


		function translate($activitylug, $lang = null) {
				$this->load->library('Activity/Activity_lib');
				$this->Activity_lib->set_activityid($activitylug);
				$add = $this->input->post('add');
				$update = $this->input->post('update');
				if (empty ($lang)) {
						$lang = $this->langdef;
				}
				else {
						$lang = $lang;
				}
				if (empty ($activitylug)) {
						redirect($this->data['adminsegment'] . '/activity/');
				}
				if (!empty ($add)) {
						$language = $this->input->post('langname');
						$activityid = $this->input->post('activityid');
						$this->Activity_model->add_translation($language, $activityid);
						redirect($this->data['adminsegment'] . "/activity/translate/" . $activitylug . "/" . $language);
				}
				if (!empty ($update)) {
						$slug = $this->Activity_model->update_translation($lang, $activitylug);
						redirect($this->data['adminsegment'] . "/activity/translate/" . $slug . "/" . $lang);
				}
				$tdata = $this->Activity_lib->activity_details();
				if ($lang == $this->langdef) {
						$activitydata = $this->Activity_lib->activity_short_details();
						$this->data['activitydata'] = $activitydata;
						$this->data['transpolicy'] = $activitydata[0]->activity_privacy;
						$this->data['transdesc'] = $activitydata[0]->activity_desc;
						$this->data['transtitle'] = $activitydata[0]->activity_title;
				}
				else {
						$activitydata = $this->Activity_lib->translated_data($lang);
						$this->data['activitydata'] = $activitydata;
						$this->data['transid'] = $activitydata[0]->trans_id;
						$this->data['transpolicy'] = $activitydata[0]->trans_policy;
						$this->data['transdesc'] = $activitydata[0]->trans_desc;
						$this->data['transtitle'] = $activitydata[0]->trans_title;
				}
				$this->data['activityid'] = $this->Activity_lib->get_id();
				$this->data['lang'] = $lang;
				$this->data['slug'] = $activitylug;
				$this->data['language_list'] = pt_get_languages();
				if ($this->data['adminsegment'] == "supplier") {
						if ($this->data['userloggedin'] != $tdata[0]->activity_owned_by) {
								redirect($this->data['adminsegment'] . '/activity/');
						}
				}
				$this->data['main_content'] = 'Activity/translate';
				$this->data['page_title'] = 'Translate Activity';
				$this->load->view('Admin/template', $this->data);
		}


		function LoadXcrudActivitySettings($type) {
				$xc = "xcrud" . $type;
				$xc = xcrud_get_instance();
				$xc->table('pt_activity_types_settings');
				$xc->where('sett_type', $type);
				$xc->order_by('sett_id', 'desc');
				$xc->button('#sett{sett_id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('data-toggle' => 'modal'));
				$delurl = base_url().'activity/activityajaxcalls/delTypeSettings';
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

				$xc->multiDelUrl = base_url().'activity/activityajaxcalls/delMultiTypeSettings/'.$type;

				$this->data['content' . $type] = $xc->render();
		}

		function extras(){


         if($this->data['adminsegment'] == "supplier"){
			 $supplierActivity = $this->Activity_model->all_activity($this->data['userloggedin']);
			 $allactivity = $this->Activity_model->all_activity();

         echo  modules :: run('Admin/extras/listings','activity',$allactivity,$supplierActivity);

		}else{

			$activity = $this->Activity_model->all_activity();
         echo  modules :: run('Admin/extras/listings','activity',$activity);

		}

        }

        function reviews(){

         echo  modules :: run('Admin/reviews/listings','activity');
        }


        function activityLocationsCheck($locations){
        	$locArray = array();
        	foreach($locations as $loc){

        		if(!empty($loc)){
        			$locArray[] = $loc;
        		}
        	}

        	return $locArray;
        }

}
