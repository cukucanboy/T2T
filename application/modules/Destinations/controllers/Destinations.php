<?php
if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class Destinations extends MX_Controller {

    private $validlang;

    function __construct() {
        parent :: __construct();
        $this->frontData();

        $this->load->library("Destinations_lib");
        $this->load->model("Destinations/Destinations_model");
        $this->load->helper("destinations_front");
        $this->data['phone'] = $this->load->get_var('phone');
        $this->data['contactemail'] = $this->load->get_var('contactemail');
        $this->data['lang_set'] = $this->session->userdata('set_lang');
        $this->data['usersession'] = $this->session->userdata('pt_logged_customer');
        $this->data['destinationslib'] = $this->Destinations_lib;
        $chk = modules :: run('Home/is_main_module_enabled', 'destinations');
        if (!$chk) {
            Error_404($this);
        }



        $settings = $this->Settings_model->get_front_settings('destinations');

        $languageid = $this->uri->segment(2);
                $this->validlang = pt_isValid_language($languageid);

                if($this->validlang){
                  $this->data['lang_set'] =  $languageid;
                }else{
                  $this->data['lang_set'] = $this->session->userdata('set_lang');
                }

        $defaultlang = pt_get_default_language();
        if (empty ($this->data['lang_set'])){
            $this->data['lang_set'] = $defaultlang;
        }



        $this->lang->load("front", $this->data['lang_set']);
        $this->Destinations_lib->set_lang($this->data['lang_set']);
        $this->data['popular_destinations'] = $this->Destinations_model->get_popular_destinations($settings[0]->front_popular);

    }

    public function index() {
        $settings = $this->Settings_model->get_front_settings('destinations');
        $this->data['ptype'] = "index";
        $this->data['categoryname'] = "";

        if($this->validlang){

                    $slug = $this->uri->segment(3);

                }else{

                    $slug = $this->uri->segment(2);
                }
        if (!empty ($slug)) {
            $this->Destinations_lib->set_destinationsid($slug);
            $this->data['details'] = $this->Destinations_lib->destination_details();
            $this->data['title'] = $this->Destinations_lib->title;
            $this->data['desc'] = $this->Destinations_lib->desc;
            $this->data['thumbnail'] = $this->Destinations_lib->thumbnail;
            $this->data['date'] = $this->Destinations_lib->date;
            $hits = $this->Destinations_lib->hits + 1;
            $this->Destinations_model->update_visits($this->data['details'][0]->destination_id, $hits);
            $relatedstatus = $settings[0]->testing_mode;

            $res = $this->Settings_model->get_contact_page_details();

            $this->data['phone'] = $res[0]->contact_phone;

            $this->data['langurl'] = base_url()."destinations/{langid}/".$this->Destinations_lib->slug;

$this->setMetaData($this->Destinations_lib->title,$this->data['details'][0]->destination_meta_desc,$this->data['details'][0]->destination_meta_keywords);

            $this->theme->view('destinations/destinations', $this->data, $this);
        }
        else {
            $this->listing();
        }
    }

    function listing($offset = null) {
        $settings = $this->Settings_model->get_front_settings('destinations');
        $this->data['ptype'] = "index";
        $this->data['categoryname'] = "";
        $alldestinations = $this->Destinations_lib->show_destinations($offset);
        $this->data['alldestinations'] = $alldestinations['all_destinations'];
        $this->data['info'] = $alldestinations['paginationinfo'];
        $this->data['langurl'] = base_url()."destinations/{langid}/";
        $this->setMetaData( $settings[0]->header_title);
        $this->theme->view('destinations/index', $this->data, $this);
    }

    function search($offset = null) {
        $this->data['ptype'] = "search";
        $this->data['categoryname'] = "";
        $settings = $this->Settings_model->get_front_settings('destinations');
        $alldestinations = $this->Destinations_lib->search_destinations($offset);
        $this->data['alldestinations'] = $alldestinations['all_destinations'];
        $this->data['info'] = $alldestinations['paginationinfo'];
        $this->data['langurl'] = base_url()."destinations/{langid}/";
        $this->setMetaData( $settings[0]->header_title);
        $this->theme->view('destinations/index', $this->data, $this);
    }



    function _remap($method, $params=array()){
		$funcs = get_class_methods($this);

		if(in_array($method, $funcs)){

		return call_user_func_array(array($this, $method), $params);

		}else{
				$this->index();
		}

		}

}
