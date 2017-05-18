<?php
if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

class Wish extends MX_Controller {

    private $validlang;

    function __construct() {
        parent :: __construct();
        $this->frontData();

        $this->load->library("Wish_lib");
        $this->load->model("Wish/Wish_model");
        $this->load->helper("wish_front");
        $this->data['phone'] = $this->load->get_var('phone');
        $this->data['contactemail'] = $this->load->get_var('contactemail');
        $this->data['lang_set'] = $this->session->userdata('set_lang');
        $this->data['usersession'] = $this->session->userdata('pt_logged_customer');
        $this->data['wishlib'] = $this->Wish_lib;
        $chk = modules :: run('Home/is_main_module_enabled', 'wish');
        if (!$chk) {
            Error_404($this);
        }



        $settings = $this->Settings_model->get_front_settings('wish');

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
        $this->Wish_lib->set_lang($this->data['lang_set']);
        $this->data['popular_wish'] = $this->Wish_model->get_popular_wish($settings[0]->front_popular);

    }

    public function index() {
        $settings = $this->Settings_model->get_front_settings('wish');
        $this->data['ptype'] = "index";
        $this->data['categoryname'] = "";

        if($this->validlang){

                    $slug = $this->uri->segment(3);

                }else{

                    $slug = $this->uri->segment(2);
                }
        if (!empty ($slug)) {
            $this->Wish_lib->set_wishid($slug);
            $this->data['details'] = $this->Wish_lib->wish_details();
            $this->data['title'] = $this->Wish_lib->title;
            $this->data['desc'] = $this->Wish_lib->desc;
            $this->data['thumbnail'] = $this->Wish_lib->thumbnail;
            $this->data['date'] = $this->Wish_lib->date;
            $hits = $this->Wish_lib->hits + 1;
            $this->Wish_model->update_visits($this->data['details'][0]->wish_id, $hits);
            $relatedstatus = $settings[0]->testing_mode;

            $res = $this->Settings_model->get_contact_page_details();

            $this->data['phone'] = $res[0]->contact_phone;

            $this->data['langurl'] = base_url()."wish/{langid}/".$this->Wish_lib->slug;

$this->setMetaData($this->Wish_lib->title,$this->data['details'][0]->wish_meta_desc,$this->data['details'][0]->wish_meta_keywords);

            $this->theme->view('wish/wish', $this->data, $this);
        }
        else {
            $this->listing();
        }
    }

    function listing($offset = null) {
        $settings = $this->Settings_model->get_front_settings('wish');
        $this->data['ptype'] = "index";
        $this->data['categoryname'] = "";
        $allwish = $this->Wish_lib->show_wish($offset);
        $this->data['allwish'] = $allwish['all_wish'];
        $this->data['info'] = $allwish['paginationinfo'];
        $this->data['langurl'] = base_url()."wish/{langid}/";
        $this->setMetaData( $settings[0]->header_title);
        $this->theme->view('wish/index', $this->data, $this);
    }

    function search($offset = null) {
        $this->data['ptype'] = "search";
        $this->data['categoryname'] = "";
        $settings = $this->Settings_model->get_front_settings('wish');
        $allwish = $this->Wish_lib->search_wish($offset);
        $this->data['allwish'] = $allwish['all_wish'];
        $this->data['info'] = $allwish['paginationinfo'];
        $this->data['langurl'] = base_url()."wish/{langid}/";
        $this->setMetaData( $settings[0]->header_title);
        $this->theme->view('wish/index', $this->data, $this);
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
