<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');

class Destinations extends MX_Controller {

		public  $role;
		public  $accType;
		public  $isSuperAdmin;
		public  $segmentUrl;
		private $langdef;
		public  $editpermission = true;
    public  $deletepermission = true;

		function __construct() {

				modules::load('Admin');
				$this->load->model('Admin/Destinations_model');

				$this->data['userloggedin'] = $this->session->userdata('pt_logged_id');
	        	//$this->data['userloggedin'] = $this->session->userdata('pt_logged_admin');
	        	$this->data['isadmin'] = $this->session->userdata('pt_logged_admin');
   				$this->isSuperAdmin = $this->session->userdata('pt_logged_super_admin');
   				$this->data['isSuperAdmin'] = $this->isSuperAdmin;
   				$this->role = $this->session->userdata('pt_role');
				$this->data['role'] = $this->role;
				$this->accType = $this->session->userdata('pt_accountType');

				$this->data['appSettings'] = modules :: run('Admin/appSettings');

				if($this->data['role'] == "supplier"){
					$this->segmentUrl = "supplier";
					$this->data['adminsegment'] = "supplier";
				}else{
					$this->segmentUrl = "admin";
					$this->data['adminsegment'] = "admin";
				}

				if(!pt_permissions('destinations',$this->data['userloggedin'])){

					redirect($this->segmentUrl);

				}

				$this->data['addpermission'] = TRUE;



				if($this->role == "supplier" || $this->role == "admin"){
                $this->editpermission = pt_permissions("editdestinations", $this->data['userloggedin']);
                $this->deletepermission = pt_permissions("deletedestinations", $this->data['userloggedin']);
                $this->data['addpermission'] = pt_permissions("adddestinations", $this->data['userloggedin']);
                }

    $this->load->helper('xcrud');

		}

		public function index() {
       	$this->load->model("Admin/Destinations_model");
       	$this->data['destinationsModel'] = $this->Destinations_model;
       	$this->data['add_link'] = base_url().$this->segmentUrl.'/destinations/add';

		$xcrud = xcrud_get_instance();
		$xcrud->table('pt_destinations');
		if(!$this->isSuperAdmin){
		$xcrud->where('user',$this->data['userloggedin']);
		}
		$xcrud->order_by('id','desc');
		$xcrud->columns('destination_image,destinations,country,latitude, longitude, status,order_num');
		$xcrud->label('destination_image','Image')->label('destinations','Name')->label('country','Country')->label('latitude','Latitude')->label('longitude','Longitude')->label('status','Status');
		$xcrud->column_callback('status', 'create_status_icon');
		if($this->editpermission){
		$xcrud->button(base_url() .$this->segmentUrl.'/destinations/edit/{id}', 'Edit', 'fa fa-edit', 'btn btn-warning', array('target' => '_self'));
		}

		$xcrud->column_class('destination_image', 'zoom_img');
		$xcrud->change_type('destination_image', 'image', false, array('width' => 200, 'path' => '../../'.PT_HOTELS_SLIDER_THUMBS_UPLOAD, 'thumbs' => array(array('height' => 150, 'width' => 120, 'crop' => true, 'marker' => ''))));

	$xcrud->column_callback('pt_destinations.order', 'orderInputDestination');

		if($this->deletepermission){
		$delurl = base_url().'admin/ajaxcalls/delDestination';
		$xcrud->multiDelUrl = base_url().'admin/ajaxcalls/delMultipleDestination';
        $xcrud->button("javascript: delfunc('{id}','$delurl')",'DELETE','fa fa-times', 'btn-danger',array('target'=>'_self'));
        }

    $xcrud->limit(50);
		$xcrud->unset_add();
		$xcrud->unset_edit();
		$xcrud->unset_remove();
		$xcrud->unset_view();
		//$this->data['destinations'] = $this->Blog_model->get_all_categories_back();
		$this->data['content'] = $xcrud->render();
		$this->data['page_title'] = 'Destinations Settings';
		$this->data['main_content'] = 'destinations_view';
		$this->data['header_title'] = 'Destinations List';
		$this->load->view('Admin/template', $this->data);


	}

	public function add(){
		$this->data['destinationModel'] = $this->Destinations_model;
		$submit = $this->input->post('submittype');
		$this->data['msg'] =  "";
		if(!empty($submit)){
       		$alreadyExists = $this->Destinations_model->alreadyExists();
       		if($alreadyExists){
       			$this->data['msg'] = "<div class='alert alert-danger'>Destination already Exists</div>";

       		}else{



       			$this->Destinations_model->addDestinations();
       			redirect($this->segmentUrl.'/destinations');
       		}


       	}

		$this->data['submittype'] = "add";
		$this->data['headingText'] = "Add";
		$this->data['countries'] = $this->Countries_model->get_all_countries();
    $this->data['languages'] = pt_get_languages();

		$this->data['main_content'] = 'settings/destinations';
		$this->data['page_title'] = $this->data['headingText'].'Destination ';
		$this->load->view('Admin/template', $this->data);


	}

	public function edit($id){
		$this->data['id'] = $id;
		$this->data['destination'] = $this->Destinations_model->getDestinationDetails($id);

		if(empty($id) || !$this->data['destination']->isValid){
       			redirect($this->segmentUrl.'/destinations');
       		}

		$submit = $this->input->post('submittype');
		if(!empty($submit)){
			$locid = $this->input->post('destinationid');
			$this->Destinations_model->updateDestination($locid);
			redirect($this->segmentUrl.'/destinations');
		}

		$this->data['destinationsModel'] = $this->Destinations_model;

		$this->data['submittype'] = "edit";
		$this->data['headingText'] = "Edit";
		$this->data['countries'] = $this->Countries_model->get_all_countries();
       	$this->data['languages'] = pt_get_languages();

		$this->data['main_content'] = 'settings/destinations';
		$this->data['page_title'] = $this->data['headingText'].' Destination Edit';
		$this->load->view('Admin/template', $this->data);


	}



}
