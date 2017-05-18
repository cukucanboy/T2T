<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Destinationsback extends MX_Controller {
	public $accType = "";
	public $langdef;
	public  $editpermission = true;
	public  $deletepermission = true;
	public $role;

	function __construct() {
		$seturl = $this->uri->segment(3);
		if ($seturl != "settings") {
			$chk = modules :: run('Home/is_main_module_enabled', 'destinations');
			if (!$chk) {
				redirect("admin");
			}
		}
		$checkingadmin = $this->session->userdata('pt_logged_admin');
		$this->accType = $this->session->userdata('pt_accountType');
		$this->data['isadmin'] = $this->session->userdata('pt_logged_admin');
    	$this->data['isSuperAdmin'] = $this->session->userdata('pt_logged_super_admin');

		$this->role = $this->session->userdata('pt_role');
		$this->data['role'] = $this->role;

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
		if (empty($this->data['isSuperAdmin'])) {

				redirect('admin');
		}


		//$this->data['c_model'] = $this->countries_model;
		$this->data['addpermission'] = true;
		if($this->accType == "supplier"){
			$this->editpermission = pt_permissions("editdestinations", $this->data['userloggedin']);
			$this->deletepermission = pt_permissions("deletedestinations", $this->data['userloggedin']);
			$this->data['addpermission'] = pt_permissions("adddestinations", $this->data['userloggedin']);
		}
		$this->load->helper('settings');
		$this->load->helper('Destinations/destinations_front');
		$this->load->model('Destinations/Destinations_model');
		$this->load->library('Ckeditor');
		$this->data['ckconfig'] = array();
		$this->data['ckconfig']['toolbar'] = array(array('Source', '-', 'Bold', 'Italic', 'Underline', 'Strike', 'Format', 'Styles'), array('NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote'), array('Image', 'Link', 'Unlink', 'Anchor', 'Table', 'HorizontalRule', 'SpecialChar', 'Maximize'), array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', 'Find', 'Replace', '-', 'SelectAll', '-', 'SpellChecker', 'Scayt'),);
		$this->data['ckconfig']['language'] = 'en';
		$this->data['ckconfig']['height'] = '350px';
		$this->data['ckconfig']['filebrowserUploadUrl'] =  base_url().'home/cmsupload';
		$this->langdef = DEFLANG;
		$this->data['languages'] = pt_get_languages();
	}

	function index() {

		$this->load->helper('xcrud');
		$xcrud = xcrud_get_instance();
		$xcrud->table('pt_destinations');
		$xcrud->order_by('destination_id','desc');
		$xcrud->columns('destination_images,destination_title,latitude,longitude,destination_order,destination_status');
		//$xcrud->label('destination_title','Name')->label('pt_destinations_categories.cat_name','Category')->label('latitude','Date')->label('destination_order','Order')->label('destination_status','Status')->label('destination_images','Thumb');
		//$xcrud->fields('destination_images,destination_title,pt_destinations_categories.cat_name,destination_desc,latitude,destination_status');
		$xcrud->change_type('destination_images', 'image', false, array(
			'width' => 200,
			'path' => '../../'.PT_DESTINATION_IMAGES_UPLOAD,
			'thumbs' => array(array(
				'crop' => true,
				'marker' => ''))));
		$xcrud->unset_add();
		$xcrud->unset_view();
		$xcrud->unset_edit();
		$xcrud->unset_remove();
		$this->data['add_link'] = base_url().'admin/destinations/add';
		if($this->editpermission){
			$xcrud->button(base_url() . $this->data['adminsegment'] . '/destinations/manage/{destination_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
			$xcrud->column_pattern('destination_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/destinations/manage/{destination_slug}' . '">{value}</a>');
		}

		if($this->deletepermission){
		$delurl = base_url().'admin/ajaxcalls/delDestinations';
        $xcrud->button("javascript: delfunc('{destination_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('target'=>'_self'));
       }
		$xcrud->search_columns('destination_title,destination_order,destination_status');
		$xcrud->column_callback('destination_order', 'orderInputDestination');
		//$xcrud->column_callback('latitude','fmtDate');
		$xcrud->column_class('destination_images','zoom_img');

		$xcrud->multiDelUrl = base_url().'destinations/destinationsajaxcalls/delMultipleDestinations';

		$this->data['content'] = $xcrud->render();
		$this->data['page_title'] = 'Destinations Management';
		$this->data['main_content'] = 'temp_view';
		$this->data['header_title'] = 'Destinations Management';
		$this->load->view('template', $this->data);

	}

	function add() {

		$addpost = $this->input->post('action');
		if ($addpost == "add") {
			$this->form_validation->set_rules('title', 'Destination Title', 'trim|required');
			$this->form_validation->set_rules('pageslug', 'Destination Slug', 'trim');
			$this->form_validation->set_rules('keywords', 'Meta Keywords', 'trim');
			$this->form_validation->set_rules('metadesc', 'Meta Description', 'trim');
			$this->form_validation->set_rules('desc', 'Destination Content', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
			}
			else {
				if (isset ($_FILES['defaultphoto']) && !empty ($_FILES['defaultphoto']['name'])) {
					$result = $this->Destinations_model->destinations_photo();
						$this->session->set_flashdata('flashmsgs', 'Destination added Successfully');
						redirect('admin/destinations');
				}
				else {
					$postid = $this->Destinations_model->add_post();

					$this->session->set_flashdata('flashmsgs', 'Destination added Successfully');
					redirect('admin/destinations');
				}
			}
		}
		$this->data['action'] = "add";
		$this->data['all_destinations'] = $this->Destinations_model->select_related_destinations();

		$this->data['main_content'] = 'Destinations/manage';
		$this->data['page_title'] = 'Add Destinations';
		$this->load->view('Admin/template', $this->data);
	}

	function settings() {
		$this->load->model('admin/settings_model');
		$updatesett = $this->input->post('updatesettings');
		if (!empty ($updatesett)) {
			$this->Destinations_model->update_front_settings();
			redirect('admin/destinations/settings');
		}
		$this->data['settings'] = $this->Settings_model->get_front_settings("destinations");
		$this->data['main_content'] = 'Destinations/settings';
		$this->data['page_title'] = 'Destinations Settings';
		$this->load->view('Admin/template', $this->data);
	}

	function manage($slug) {

		if (empty ($slug)) {
			redirect('admin/destinations');
		}
		$updatepost = $this->input->post('action');


		$postid = $this->input->post('destinationid');

		$this->data['action'] = "update";
		if ($updatepost == "update" ) {
			$this->form_validation->set_rules('title', 'Destination Title', 'trim|required');
			$this->form_validation->set_rules('pageslug', 'Destination Slug', 'trim');
			$this->form_validation->set_rules('keywords', 'Meta Keywords', 'trim');
			$this->form_validation->set_rules('metadesc', 'Meta Description', 'trim');
     $this->form_validation->set_rules('desc', 'Destination Content', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
									var_dump($postid);
			}
			else {
									var_dump($postid);
				if (isset ($_FILES['defaultphoto']) && !empty ($_FILES['defaultphoto']['name'])) {
					$this->Destinations_model->destinations_photo($postid);
					$this->session->set_flashdata('flashmsgs', 'Destination Updated Successfully');
					redirect('admin/destinations');
				}
				else {

					$this->Destinations_model->update_destination($postid);
					$this->session->set_flashdata('flashmsgs', 'Destination Updated Successfully');
					redirect('admin/destinations');
				}
			}
		}
		else {
			$this->data['pdata'] = $this->Destinations_model->get_destination_data($slug);
			if (empty ($this->data['pdata'])) {
				redirect('admin/destinations');
			}


		//	$this->data['all_destinations'] = $this->Destinations_model->select_related_destinations($this->data['pdata'][0]->destination_id);
			$this->data['main_content'] = 'Destinations/manage';
			$this->data['page_title'] = 'Manage Destination';
			$this->load->view('Admin/template', $this->data);
		}
	}


	function translate($destinationslug, $lang = null) {
		$this->load->library('Destinations/Destinations_lib');
		$this->Destinations_lib->set_destinationsid($destinationslug);
		$add = $this->input->post('add');
		$update = $this->input->post('update');
		if (empty ($lang)) {
			$lang = $this->langdef;
		}
		else {
			$lang = $lang;
		}
		if (empty ($destinationslug)) {
			redirect('admin/destinations/');
		}
		if (!empty ($add)) {
			$language = $this->input->post('langname');
			$postid = $this->input->post('postid');
			$this->Destinations_model->add_translation($language, $postid);
			redirect("admin/destinations/translate/" . $destinationslug . "/" . $language);
		}
		if (!empty ($update)) {
			$slug = $this->Destinations_model->update_translation($lang, $destinationslug);
			redirect("admin/destinations/translate/" . $slug . "/" . $lang);
		}
		$cdata = $this->Destinations_lib->destination_details();
		if ($lang == $this->langdef) {
			$destinationsdata = $this->Destinations_lib->destination_short_details();
			$this->data['destinationsdata'] = $destinationsdata;
			$this->data['transdesc'] = $destinationsdata[0]->destination_desc;
			$this->data['transtitle'] = $destinationsdata[0]->destination_title;
		}
		else {
			$destinationsdata = $this->Destinations_lib->translated_data($lang);
			$this->data['destinationsdata'] = $destinationsdata;
			$this->data['transid'] = $destinationsdata[0]->trans_id;
			$this->data['transdesc'] = $destinationsdata[0]->trans_desc;
			$this->data['transtitle'] = $destinationsdata[0]->trans_title;
		}
		$this->data['postid'] = $this->Destinations_lib->get_id();
		$this->data['lang'] = $lang;
		$this->data['slug'] = $destinationslug;
		$this->data['language_list'] = pt_get_languages();
		$this->data['main_content'] = 'Destinations/translate';
		$this->data['page_title'] = 'Translate Destination';
		$this->load->view('Admin/template', $this->data);
	}

}
