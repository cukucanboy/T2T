<?php
if (!defined('BASEPATH'))
	exit ('No direct script access allowed');

class Wishback extends MX_Controller {
	public $accType = "";
	public $langdef;
	public  $editpermission = true;
	public  $deletepermission = true;
	public $role;

	function __construct() {
		$seturl = $this->uri->segment(3);
		if ($seturl != "settings") {
			$chk = modules :: run('Home/is_main_module_enabled', 'wish');
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
			$this->editpermission = pt_permissions("editwish", $this->data['userloggedin']);
			$this->deletepermission = pt_permissions("deletewish", $this->data['userloggedin']);
			$this->data['addpermission'] = pt_permissions("addwish", $this->data['userloggedin']);
		}
		$this->load->helper('settings');
		$this->load->helper('Wish/wish_front');
		$this->load->model('Wish/Wish_model');
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
		$xcrud->table('pt_advertising_wish');
		$xcrud->order_by('wish_id','desc');
		$xcrud->columns('wish_images,wish_title,wish_order,wish_status');
		//$xcrud->label('wish_title','Name')->label('pt_advertising_wish_categories.cat_name','Category')->label('latitude','Date')->label('wish_order','Order')->label('wish_status','Status')->label('wish_images','Thumb');
		//$xcrud->fields('wish_images,wish_title,pt_advertising_wish_categories.cat_name,wish_desc,latitude,wish_status');
		$xcrud->change_type('wish_images', 'image', false, array(
			'width' => 200,
			'path' => '../../'.PT_WISH_IMAGES_UPLOAD,
			'thumbs' => array(array(
				'crop' => true,
				'marker' => ''))));
		$xcrud->unset_add();
		$xcrud->unset_view();
		$xcrud->unset_edit();
		$xcrud->unset_remove();
		$this->data['add_link'] = base_url().'admin/wish/add';
		if($this->editpermission){
			$xcrud->button(base_url() . $this->data['adminsegment'] . '/wish/manage/{wish_slug}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
			$xcrud->column_pattern('wish_title', '<a href="' . base_url() . $this->data['adminsegment'] . '/wish/manage/{wish_slug}' . '">{value}</a>');
		}

		if($this->deletepermission){
		$delurl = base_url().'admin/ajaxcalls/delWish';
        $xcrud->button("javascript: delfunc('{wish_id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('target'=>'_self'));
       }
		$xcrud->search_columns('wish_title,wish_order,wish_status');
		$xcrud->column_callback('wish_order', 'orderInputWish');
		//$xcrud->column_callback('latitude','fmtDate');
		$xcrud->column_class('wish_images','zoom_img');

		$xcrud->multiDelUrl = base_url().'wish/wishajaxcalls/delMultipleWish';

		$this->data['content'] = $xcrud->render();
		$this->data['page_title'] = 'Wish Management';
		$this->data['main_content'] = 'temp_view';
		$this->data['header_title'] = 'Wish Management';
		$this->load->view('template', $this->data);

	}

	function add() {

		$addpost = $this->input->post('action');
		if ($addpost == "add") {
			$this->form_validation->set_rules('title', 'Wish Title', 'trim|required');
			$this->form_validation->set_rules('pageslug', 'Wish Slug', 'trim');
			$this->form_validation->set_rules('keywords', 'Meta Keywords', 'trim');
			$this->form_validation->set_rules('metadesc', 'Meta Description', 'trim');
			$this->form_validation->set_rules('desc', 'Wish Content', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
			}
			else {
				if (isset ($_FILES['defaultphoto']) && !empty ($_FILES['defaultphoto']['name'])) {
					$result = $this->Wish_model->wish_photo();
						$this->session->set_flashdata('flashmsgs', 'Wish added Successfully');
						redirect('admin/wish');
				}
				else {
					$postid = $this->Wish_model->add_post();

					$this->session->set_flashdata('flashmsgs', 'Wish added Successfully');
					redirect('admin/wish');
				}
			}
		}
		$this->data['action'] = "add";
		$this->data['all_wish'] = $this->Wish_model->select_related_wish();

		$this->data['main_content'] = 'wish/manage';
		$this->data['page_title'] = 'Add Wish';
		$this->load->view('Admin/template', $this->data);
	}

	function settings() {
		$this->load->model('admin/settings_model');
		$updatesett = $this->input->post('updatesettings');
		if (!empty ($updatesett)) {
			$this->Wish_model->update_front_settings();
			redirect('admin/wish/settings');
		}
		$this->data['settings'] = $this->Settings_model->get_front_settings("wish");
		$this->data['main_content'] = 'Wish/settings';
		$this->data['page_title'] = 'Wish Settings';
		$this->load->view('Admin/template', $this->data);
	}

	function manage($slug) {

		if (empty ($slug)) {
			redirect('admin/wish');
		}
		$updatepost = $this->input->post('action');


		$postid = $this->input->post('wishid');

		$this->data['action'] = "update";
		if ($updatepost == "update" ) {
			$this->form_validation->set_rules('title', 'Wish Title', 'trim|required');
			$this->form_validation->set_rules('pageslug', 'Wish Slug', 'trim');
			$this->form_validation->set_rules('keywords', 'Meta Keywords', 'trim');
			$this->form_validation->set_rules('metadesc', 'Meta Description', 'trim');
     $this->form_validation->set_rules('desc', 'Wish Content', 'trim|required');
			if ($this->form_validation->run() == FALSE) {
								//	var_dump($postid);
			}
			else {
									//var_dump($postid);
				if (isset ($_FILES['defaultphoto']) && !empty ($_FILES['defaultphoto']['name'])) {
					$this->Wish_model->wish_photo($postid);
					$this->session->set_flashdata('flashmsgs', 'Wish Updated Successfully');
					redirect('admin/wish');
				}
				else {

					$this->Wish_model->update_wish($postid);
					$this->session->set_flashdata('flashmsgs', 'Wish Updated Successfully');
					redirect('admin/wish');
				}
			}
		}
		else {
			$this->data['pdata'] = $this->Wish_model->get_wish_data($slug);
			if (empty ($this->data['pdata'])) {
				redirect('admin/wish');
			}


		//	$this->data['all_wish'] = $this->Wish_model->select_related_wish($this->data['pdata'][0]->wish_id);
			$this->data['main_content'] = 'Wish/manage';
			$this->data['page_title'] = 'Manage Wish';
			$this->load->view('Admin/template', $this->data);
		}
	}


	function translate($wishlug, $lang = null) {
		$this->load->library('Wish/Wish_lib');
		$this->Wish_lib->set_wishid($wishlug);
		$add = $this->input->post('add');
		$update = $this->input->post('update');
		if (empty ($lang)) {
			$lang = $this->langdef;
		}
		else {
			$lang = $lang;
		}
		if (empty ($wishlug)) {
			redirect('admin/wish/');
		}
		if (!empty ($add)) {
			$language = $this->input->post('langname');
			$postid = $this->input->post('postid');
			$this->Wish_model->add_translation($language, $postid);
			redirect("admin/wish/translate/" . $wishlug . "/" . $language);
		}
		if (!empty ($update)) {
			$slug = $this->Wish_model->update_translation($lang, $wishlug);
			redirect("admin/wish/translate/" . $slug . "/" . $lang);
		}
		$cdata = $this->Wish_lib->wish_details();
		if ($lang == $this->langdef) {
			$wishdata = $this->Wish_lib->wish_short_details();
			$this->data['wishdata'] = $wishdata;
			$this->data['transdesc'] = $wishdata[0]->wish_desc;
			$this->data['transtitle'] = $wishdata[0]->wish_title;
		}
		else {
			$wishdata = $this->Wish_lib->translated_data($lang);
			$this->data['wishdata'] = $wishdata;
			$this->data['transid'] = $wishdata[0]->trans_id;
			$this->data['transdesc'] = $wishdata[0]->trans_desc;
			$this->data['transtitle'] = $wishdata[0]->trans_title;
		}
		$this->data['postid'] = $this->Wish_lib->get_id();
		$this->data['lang'] = $lang;
		$this->data['slug'] = $wishlug;
		$this->data['language_list'] = pt_get_languages();
		$this->data['main_content'] = 'Wish/translate';
		$this->data['page_title'] = 'Translate Wish';
		$this->load->view('Admin/template', $this->data);
	}

}
