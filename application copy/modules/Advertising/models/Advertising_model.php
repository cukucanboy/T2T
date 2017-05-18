<?php

class Advertising_model extends CI_Model
{
    public $langdef;

    function __construct()
    {
// Call the Model constructor
        parent:: __construct();
        $this->langdef = DEFLANG;
    }

// Get all enabled advertising short info
    function shortInfo($id = null)
    {
        $result = array();
        $this->db->select('advertising_id,advertising_title,advertising_slug');
        if (!empty ($id)) {
            $this->db->where('advertising_owned_by', $id);
        }
        $this->db->where('advertising_status', 'Yes');
        $this->db->order_by('advertising_id', 'desc');
        $advertising = $this->db->get('pt_advertising')->result();
        foreach ($advertising as $advertising) {
            $result[] = (object)array('id' => $advertising->advertising_id, 'title' => $advertising->advertising_title, 'slug' => $advertising->advertising_slug);
        }

        return $result;
    }

// Get all advertising id and names only
    function all_advertising_names($id)
    {
        $this->db->select('advertising_id,advertising_title');
        if (!empty ($id)) {
            $this->db->where('advertising_owned_by', $id);
        }
        $this->db->order_by('advertising_id', 'desc');
        return $this->db->get('pt_advertising')->result();
    }

    // Get all advertising for extras
    function all_advertising($id = null)
    {
        $this->db->select('advertising_id as id,advertising_title as title');
        if (!empty ($id)) {
            $this->db->where('advertising_owned_by', $id);
        }
        $this->db->order_by('advertising_id', 'desc');
        return $this->db->get('pt_advertising')->result();
    }

    function convert_price($amount)
    {

    }

// get latest advertising
    function latest_advertising_front()
    {
        $settings = $this->Settings_model->get_front_settings('advertising');
        $limit = $settings[0]->front_latest;
        $this->db->select('pt_advertising.advertising_status,pt_advertising.advertising_basic_price,pt_advertising.advertising_basic_discount,pt_advertising.advertising_id,pt_advertising.advertising_desc,pt_advertising.advertising_title,pt_advertising.advertising_slug,pt_advertising.advertising_type,pt_advertising_types_settings.sett_name');
        $this->db->order_by('pt_advertising.advertising_id', 'desc');
        $this->db->where('pt_advertising.advertising_status', 'Yes');
        $this->db->join('pt_advertising_types_settings', 'pt_advertising.advertising_type = pt_advertising_types_settings.sett_id', 'left');
        $this->db->limit($limit);
        return $this->db->get('pt_advertising')->result();
    }

// get all data of single advertising by slug
    function get_advertising_data($advertisingname)
    {

        $this->db->select('pt_advertising.*');
        $this->db->where('pt_advertising.advertising_slug', $advertisingname);
        return $this->db->get('pt_advertising')->result();
    }

// get all advertising info
    function get_all_advertising_back($id = null)
    {
        $this->db->select('pt_advertising.advertising_featured_forever,pt_advertising.advertising_id,pt_advertising.advertising_title,pt_advertising.advertising_slug,pt_advertising.advertising_owned_by,pt_advertising.advertising_order,pt_advertising.advertising_status,pt_advertising.advertising_is_featured,
    pt_advertising.advertising_featured_from,pt_advertising.advertising_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_advertising_types_settings.sett_name');
// $this->db->where('pt_advertising_images.timg_type','default');
        if (!empty ($id)) {
            $this->db->where('pt_advertising.advertising_owned_by', $id);
        }
        $this->db->order_by('pt_advertising.advertising_id', 'desc');
        $this->db->join('pt_accounts', 'pt_advertising.advertising_owned_by = pt_accounts.accounts_id', 'left');
//$this->db->join('pt_advertising_images','pt_advertising.advertising_id = pt_advertising_images.timg_advertising_id','left');
        $query = $this->db->get('pt_advertising');
        $data['all'] = $query->result();
        $data['nums'] = $query->num_rows();
        return $data;
    }

// get all advertising info with limit
    function get_all_advertising_back_limit($id = null, $perpage = null, $offset = null, $orderby = null)
    {
        if ($offset != null) {
            $offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
        }
        $this->db->select('pt_advertising.advertising_featured_forever,pt_advertising.advertising_id,pt_advertising.advertising_title,pt_advertising.advertising_slug,pt_advertising.advertising_created_at,pt_advertising.advertising_owned_by,pt_advertising.advertising_order,pt_advertising.advertising_status,pt_advertising.advertising_is_featured,
    pt_advertising.advertising_featured_from,pt_advertising.advertising_featured_to,pt_accounts.accounts_id,pt_accounts.ai_first_name,pt_accounts.ai_last_name,pt_advertising_types_settings.sett_name');
// $this->db->where('pt_advertising_images.timg_type','default');
        if (!empty ($id)) {
            $this->db->where('pt_advertising.advertising_owned_by', $id);
        }
        $this->db->order_by('pt_advertising.advertising_id', 'desc');
        $this->db->join('pt_accounts', 'pt_advertising.advertising_owned_by = pt_accounts.accounts_id', 'left');
//  $this->db->join('pt_advertising_images','pt_advertising.advertising_id = pt_advertising_images.timg_advertising_id','left');
        $query = $this->db->get('pt_advertising', $perpage, $offset);
        $data['all'] = $query->result();
        return $data;
    }

// add advertising data
    function add_advertising($user = null)
    {
        if (empty($user)) {
            $user = 1;
        }

        $depval = floatval($this->input->post('depositvalue'));
        $deptype = $this->input->post('deposittype');

        $taxval = floatval($this->input->post('taxvalue'));
        $taxtype = $this->input->post('taxtype');

        $commper = 0;
        $commfixed = 0;
        $taxper = 0;
        $taxfixed = 0;
        if ($deptype == "fixed") {
            $commfixed = $depval;
            $commper = 0;
        } else {
            $commfixed = 0;
            $commper = $depval;
        }

        if ($taxtype == "fixed") {
            $taxfixed = $taxval;
            $taxper = 0;
        } else {
            $taxfixed = 0;
            $taxper = $taxval;
        }

        $this->db->select("advertising_id");
        $this->db->order_by("advertising_id", "desc");
        $query = $this->db->get('pt_advertising');
        $lastid = $query->result();
        if (empty ($lastid)) {
            $advertisinglastid = 1;
        } else {
            $advertisinglastid = $lastid[0]->advertising_id + 1;
        }

        $advertisingcount = $query->num_rows();
        $advertisingorder = $advertisingcount + 1;
        $this->db->select("advertising_id");
        $this->db->where("advertising_title", $this->input->post('advertisingname'));
        $queryc = $this->db->get('pt_advertising')->num_rows();
        if ($queryc > 0) {
            $advertisinglug = create_url_slug($this->input->post('advertisingname')) . "-" . $advertisinglastid;
        } else {
            $advertisinglug = create_url_slug($this->input->post('advertisingname'));
        }
        $amenities = @ implode(",", $this->input->post('advertisingamenities'));
        $exclusions = @ implode(",", $this->input->post('advertisingexclusions'));
        $paymentopt = @ implode(",", $this->input->post('advertisingpayments'));
        $relatedadvertising = @ implode(",", $this->input->post('relatedadvertising'));
        $nearbyrelatedadvertising = @ implode(",", $this->input->post('nearbyrelatedadvertising'));

        $featured = $this->input->post('isfeatured');
        if (empty($featured)) {
            $featured = "no";
        }

        $ffrom = $this->input->post('ffrom');
        $fto = $this->input->post('fto');
        if (empty($ffrom) || empty($fto) && $featured == "yes") {

            $isforever = 'forever';

        } else {

            $isforever = '';
        }

        if ($featured == "no") {
            $isforever = '';
        }

        $location = $this->input->post('locations');
        $advertisingLocation = $location[0];

        $stars = $this->input->post('advertisingstars');
        if (empty($stars)) {
            $stars = 0;
        }

        $data = array('advertising_title' => $this->input->post('advertisingname'),
            'advertising_slug' => $advertisinglug, 'advertising_desc' => $this->input->post('advertisingdesc'),
            'advertising_stars' => intval($stars),
            'advertising_is_featured' => $featured,
            'advertising_featured_from' => convert_to_unix($ffrom),
            'advertising_featured_to' => convert_to_unix($fto),
            'advertising_owned_by' => $user,
            'advertising_type' => $this->input->post('advertisingtype'),
            'advertising_destination' => $this->input->post('destination'),
            'advertising_wish' => $this->input->post('wish'),
            'advertising_location' => $advertisingLocation,
            'advertising_latitude' => $this->input->post('latitude'),
            'advertising_longitude' => $this->input->post('longitude'),
            'advertising_mapaddress' => $this->input->post('advertisingmapaddress'),
            //'advertising_basic_price' => $this->input->post('basic'),
            //'advertising_basic_discount' => $this->input->post('discount'),
            'advertising_meta_title' => $this->input->post('advertisingmetatitle'),
            'advertising_meta_keywords' => $this->input->post('advertisingkeywords'),
            'advertising_meta_desc' => $this->input->post('advertisingmetadesc'), 'advertising_amenities' => $amenities,
            'advertising_exclusions' => $exclusions, 'advertising_payment_opt' => $paymentopt,
            'advertising_max_adults' => intval($this->input->post('maxadult')),
            'advertising_max_child' => intval($this->input->post('maxchild')),
            'advertising_max_infant' => intval($this->input->post('maxinfant')),
            'advertising_adult_price' => floatval($this->input->post('adultprice')),
            'advertising_child_price' => floatval($this->input->post('childprice')),
            'advertising_infant_price' => floatval($this->input->post('infantprice')),
            'adult_status' => intval($this->input->post('adultstatus')),
            'child_status' => intval($this->input->post('childstatus')),
            'infant_status' => intval($this->input->post('infantstatus')),
            'advertising_days' => intval($this->input->post('advertisingdays')),
            'advertising_nights' => intval($this->input->post('advertisingnights')),
            'advertising_privacy' => $this->input->post('advertisingprivacy'),
            'advertising_status' => $this->input->post('advertisingtatus'),
            'advertising_related' => $relatedadvertising,
            'advertising_nearby_related' => $nearbyrelatedadvertising,
            'advertising_order' => $advertisingorder,

            'advertising_comm_fixed' => $commfixed, 'advertising_comm_percentage' => $commper,
            'advertising_tax_fixed' => $taxfixed, 'advertising_tax_percentage' => $taxper,
            'advertising_email' => $this->input->post('advertisingemail'),
            'advertising_phone' => $this->input->post('advertisingphone'),
            'advertising_website' => $this->input->post('advertisingwebsite'),
            'advertising_fulladdress' => $this->input->post('advertisingfulladdress'),
            'advertising_featured_forever' => $isforever,
            'advertising_created_at' => time());
        $this->db->insert('pt_advertising', $data);
        $advertisingid = $this->db->insert_id();
        $this->updateAdvertisingLocations($this->input->post('locations'), $advertisingid);
        return $advertisingid;
    }

// update advertising data
    function update_advertising($id)
    {

        $advertisingcomm = $this->input->post('deposit');
        $depval = floatval($this->input->post('depositvalue'));
        $deptype = $this->input->post('deposittype');

        $taxval = floatval($this->input->post('taxvalue'));
        $taxtype = $this->input->post('taxtype');

        $commper = 0;
        $commfixed = 0;
        $taxper = 0;
        $taxfixed = 0;
        if ($deptype == "fixed") {
            $commfixed = $depval;
            $commper = 0;
        } else {
            $commfixed = 0;
            $commper = $depval;
        }

        if ($taxtype == "fixed") {
            $taxfixed = $taxval;
            $taxper = 0;
        } else {
            $taxfixed = 0;
            $taxper = $taxval;
        }


        $this->db->select("advertising_id");
        $this->db->where("advertising_id !=", $id);
        $this->db->where("advertising_title", $this->input->post('advertisingname'));
        $queryc = $this->db->get('pt_advertising')->num_rows();
        if ($queryc > 0) {
            $advertisinglug = create_url_slug($this->input->post('advertisingname')) . "-" . $id;
        } else {
            $advertisinglug = create_url_slug($this->input->post('advertisingname'));
        }
        $amenities = @ implode(",", $this->input->post('advertisingamenities'));
        $exclusions = @ implode(",", $this->input->post('advertisingexclusions'));
        $paymentopt = @ implode(",", $this->input->post('advertisingpayments'));
        $relatedadvertising = @ implode(",", $this->input->post('relatedadvertising'));
        $nearbyrelatedadvertising = @ implode(",", $this->input->post('nearbyrelatedadvertising'));


        $featured = $this->input->post('isfeatured');

        if (empty($featured)) {
            $featured = "no";
        }

        $ffrom = $this->input->post('ffrom');
        $fto = $this->input->post('fto');
        if (empty($ffrom) || empty($fto) && $featured == "yes") {

            $isforever = 'forever';

        } else {

            $isforever = '';
        }

        if ($featured == "no") {
            $isforever = '';
        }

        $location = $this->input->post('locations');
        $advertisingLocation = $location[0];

        $stars = $this->input->post('advertisingtars');
        if (empty($stars)) {
            $stars = 0;
        }

        $data = array('advertising_title' => $this->input->post('advertisingname'),
            'advertising_slug' => $advertisinglug, 'advertising_desc' => $this->input->post('advertisingdesc'),
            'advertising_stars' => intval($stars),
            'advertising_is_featured' => $featured,
            'advertising_featured_from' => convert_to_unix($ffrom),
            'advertising_featured_to' => convert_to_unix($fto),
            'advertising_type' => $this->input->post('advertisingtype'),
            'advertising_destination' => $this->input->post('destination'),
            'advertising_wish' => $this->input->post('wish'),
            'advertising_location' => $advertisingLocation,
            'advertising_latitude' => $this->input->post('latitude'),
            'advertising_longitude' => $this->input->post('longitude'),
            'advertising_mapaddress' => $this->input->post('advertisingmapaddress'),

            'advertising_meta_title' => $this->input->post('advertisingmetatitle'),
            'advertising_meta_keywords' => $this->input->post('advertisingkeywords'),
            'advertising_meta_desc' => $this->input->post('advertisingmetadesc'), 'advertising_amenities' => $amenities,
            'advertising_exclusions' => $exclusions, 'advertising_payment_opt' => $paymentopt,
            'advertising_max_adults' => intval($this->input->post('maxadult')),
            'advertising_max_child' => intval($this->input->post('maxchild')),
            'advertising_max_infant' => intval($this->input->post('maxinfant')),
            'advertising_adult_price' => floatval($this->input->post('adultprice')),
            'advertising_child_price' => floatval($this->input->post('childprice')),
            'advertising_infant_price' => floatval($this->input->post('infantprice')),
            'adult_status' => intval($this->input->post('adultstatus')),
            'child_status' => intval($this->input->post('childstatus')),
            'infant_status' => intval($this->input->post('infantstatus')),
            'advertising_days' => intval($this->input->post('advertisingdays')),
            'advertising_nights' => intval($this->input->post('advertisingnights')),
            'advertising_privacy' => $this->input->post('advertisingprivacy'),
            'advertising_status' => $this->input->post('advertisingtatus'),
            'advertising_related' => $relatedadvertising,
            'advertising_nearby_related' => $nearbyrelatedadvertising,
            'advertising_comm_fixed' => $commfixed, 'advertising_comm_percentage' => $commper,
            'advertising_tax_fixed' => $taxfixed, 'advertising_tax_percentage' => $taxper,
            'advertising_email' => $this->input->post('advertisingemail'),
            'advertising_phone' => $this->input->post('advertisingphone'),
            'advertising_website' => $this->input->post('advertisingwebsite'),
            'advertising_fulladdress' => $this->input->post('advertisingfulladdress'),
            'advertising_featured_forever' => $isforever);
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);

        $this->updateAdvertisingLocations($this->input->post('locations'), $id);
    }

// Add advertising settings data
    function add_settings_data()
    {
        $data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'), 'sett_type' => $this->input->post('typeopt'));
        $this->db->insert('pt_advertising_types_settings', $data);
    }

// update advertising settings data
    function update_settings_data()
    {
        $id = $this->input->post('id');
        $data = array('sett_name' => $this->input->post('name'), 'sett_status' => $this->input->post('statusopt'), 'sett_selected' => $this->input->post('selectopt'));
        $this->db->where('sett_id', $id);
        $this->db->update('pt_advertising_types_settings', $data);
    }

// Disable advertising settings
    function disable_settings($id)
    {
        $data = array('sett_status' => 'No');
        $this->db->where('sett_id', $id);
        $this->db->update('pt_advertising_types_settings', $data);
    }

// Enable advertising settings
    function enable_settings($id)
    {
        $data = array('sett_status' => 'Yes');
        $this->db->where('sett_id', $id);
        $this->db->update('pt_advertising_types_settings', $data);
    }

// Delete advertising settings
    function delete_settings($id)
    {
        $this->db->where('sett_id', $id);
        $this->db->delete('pt_advertising_types_settings');
    }

// get all advertising for related selection for backend
    function select_related_advertising($id = null)
    {
        $this->db->select('advertising_title,advertising_id');
        if (!empty ($id)) {
            $this->db->where('advertising_id !=', $id);
        }
        return $this->db->get('pt_advertising')->result();
    }

// Get advertising settings data
    function get_advertising_settings_data($type)
    {
        if (!empty($type)) {
            $this->db->where('sett_type', $type);
        }

        $this->db->order_by('sett_id', 'desc');
        return $this->db->get('pt_advertising_types_settings')->result();
    }

// Get advertising settings data for adding advertising
    function get_tsettings_data($type)
    {
        $this->db->where('sett_type', $type);
        $this->db->where('sett_status', 'Yes');
        return $this->db->get('pt_advertising_types_settings')->result();
    }

// Get advertising settings data for adding advertising
    function get_tsettings_data_front($type, $items)
    {
        $this->db->where('sett_type', $type);
        $this->db->where_in('sett_id', $items);
        $this->db->where('sett_status', 'Yes');
        return $this->db->get('pt_advertising_types_settings')->result();
    }

// add Advertising images by type
    function add_advertising_image($type, $filename, $advertisingid)
    {
        $imgorder = 0;
        if ($type == "slider") {
            $this->db->where('timg_type', 'slider');
            $this->db->where('timg_advertising_id', $advertisingid);
            $imgorder = $this->db->get('pt_advertising_images')->num_rows();
            $imgorder = $imgorder + 1;
        }
        $this->db->where('timg_type', 'default');
        $this->db->where('timg_advertising_id', $advertisingid);
        $hasdefault = $this->db->get('pt_advertising_images')->num_rows();
        if ($hasdefault < 1) {
            $type = 'default';
        }
        $approval = pt_admin_gallery_approve();
        $data = array('timg_advertising_id' => $advertisingid, 'timg_type' => $type, 'timg_image' => $filename, 'timg_order' => $imgorder, 'timg_approved' => $approval);
        $this->db->insert('pt_advertising_images', $data);
    }

// update advertising map order
    function update_map_order($id, $order)
    {
        $data = array('map_order' => $order);
        $this->db->where('map_id', $id);
        $this->db->update('pt_advertising_maps', $data);
    }


// update advertising order
    function update_advertising_order($id, $order)
    {
        $data = array('advertising_order' => $order);
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);
    }

// update featured status
    function update_featured()
    {
        $isfeatured = $this->input->post('isfeatured');
        $id = $this->input->post('id');

        if ($isfeatured == "no") {
            $isforever = '';
        } else {

            $isforever = "forever";

        }


        $data = array('advertising_is_featured' => $isfeatured, 'advertising_featured_forever' => $isforever);
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);
    }

// Disable Advertising

    public function disable_advertising($id)
    {
        $data = array('advertising_status' => 'No');
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);
    }

// Enable Advertising

    public function enable_advertising($id)
    {
        $data = array('advertising_status' => 'Yes');
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);
    }

// Delete advertising
    function delete_advertising($advertisingid)
    {
        $advertisingimages = $this->advertising_images($advertisingid);
        foreach ($advertisingimages['all_slider'] as $sliderimg) {
            $this->delete_image($sliderimg->timg_image, $sliderimg->timg_id, $advertisingid);
        }


        $this->db->where('review_itemid', $advertisingid);
        $this->db->where('review_module', 'advertising');
        $this->db->delete('pt_reviews');
        $this->db->where('map_advertising_id', $advertisingid);
        $this->db->delete('pt_advertising_maps');

        $this->db->where('item_id', $advertisingid);
        $this->db->delete('pt_advertising_translation');

        $this->db->where('advertising_id', $advertisingid);
        $this->db->delete('pt_advertising_locations');

        $this->db->where('advertising_id', $advertisingid);
        $this->db->delete('pt_advertising');
    }

// Get Advertising Images
    function advertising_images($id)
    {
        $this->db->where('timg_advertising_id', $id);
        $this->db->where('timg_type', 'default');
        $q = $this->db->get('pt_advertising_images');
        $data['def_image'] = $q->result();
        $this->db->where('timg_type', 'slider');
        $this->db->order_by('timg_id', 'desc');
        $this->db->having('timg_advertising_id', $id);
        $q = $this->db->get('pt_advertising_images');
        $data['all_slider'] = $q->result();
        $data['slider_counts'] = $q->num_rows();
        return $data;
    }

//update advertising thumbnail
    function update_thumb($oldthumb, $newthumb, $advertisingid)
    {
        $data = array('timg_type' => 'slider');
        $this->db->where('timg_id', $oldthumb);
        $this->db->where('timg_advertising_id', $advertisingid);
        $this->db->update('pt_advertising_images', $data);
        $data2 = array('timg_type' => 'default');
        $this->db->where('timg_id', $newthumb);
        $this->db->where('timg_advertising_id', $advertisingid);
        $this->db->update('pt_advertising_images', $data2);
    }

// Approve or reject Hotel Images
    function approve_reject_images()
    {
        $data = array('timg_approved' => $this->input->post('apprej'));
        $this->db->where('timg_id', $this->input->post('imgid'));
        $this->db->update('pt_advertising_images', $data);
    }

// update image order
    function update_image_order($imgid, $order)
    {
        $data = array('timg_order' => $order);
        $this->db->where('timg_id', $imgid);
        $this->db->update('pt_advertising_images', $data);
    }


// Delete advertising Images
    function delete_image($imgname, $imgid, $advertisingid)
    {
        $this->db->where('timg_id', $imgid);
        $this->db->delete('pt_advertising_images');
        $this->updateAdvertisingThumb($advertisingid, $imgname, "delete");
        @ unlink(PT_ADVERTISING_SLIDER_THUMB_UPLOAD . $imgname);
        @ unlink(PT_ADVERTISING_SLIDER_UPLOAD . $imgname);
    }

//update advertising thumbnail
    function updateAdvertisingThumb($advertisingid, $imgname, $action)
    {
        if ($action == "delete") {
            $this->db->select('thumbnail_image');
            $this->db->where('thumbnail_image', $imgname);
            $this->db->where('advertising_id', $advertisingid);
            $rs = $this->db->get('pt_advertising')->num_rows();
            if ($rs > 0) {
                $data = array(
                    'thumbnail_image' => PT_BLANK_IMG
                );
                $this->db->where('advertising_id', $advertisingid);
                $this->db->update('pt_advertising', $data);
            }
        } else {
            $data = array(
                'thumbnail_image' => $imgname
            );
            $this->db->where('advertising_id', $advertisingid);
            $this->db->update('pt_advertising', $data);
        }

    }


    function offers_data($id)
    {
        /*$this->db->where('offer_module', 'advertising');
        $this->db->where('offer_item', $id);
        return $this->db->get('pt_special_offers')->result();*/
    }

    function add_to_map()
    {
        $maporder = 0;
        $advertisingid = $this->input->post('advertisingid');
        $this->db->select('map_id');
        $this->db->where('map_city_type', 'visit');
        $this->db->where('map_advertising_id', $advertisingid);
        $res = $this->db->get('pt_advertising_maps')->num_rows();
        $addtype = $this->input->post('addtype');
        if ($addtype == "visit") {
            $maporder = $res + 1;
        }
        $data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'), 'map_city_type' => $addtype, 'map_advertising_id' => $advertisingid, 'map_order' => $maporder);
        $this->db->insert('pt_advertising_maps', $data);
    }

    function update_advertising_map()
    {
        $data = array('map_city_name' => $this->input->post('citytitle'), 'map_city_lat' => $this->input->post('citylat'), 'map_city_long' => $this->input->post('citylong'),);
        $this->db->where('map_id', $this->input->post('mapid'));
        $this->db->update('pt_advertising_maps', $data);
    }

    function has_start_end_city($type, $advertisingid)
    {
        $this->db->select('map_id');
        $this->db->where('map_city_type', $type);
        $this->db->where('map_advertising_id', $advertisingid);
        $nums = $this->db->get('pt_advertising_maps')->num_rows();
        if ($nums > 0) {
            return true;
        } else {
            return false;
        }
    }

    function get_advertising_map($advertisingid)
    {
        $this->db->where('map_advertising_id', $advertisingid);
        return $this->db->get('pt_advertising_maps')->result();
    }

    function delete_map_item($mapid)
    {
        $this->db->where('map_id', $mapid);
        $this->db->delete('pt_advertising_maps');
    }

// get related advertising for front-end
    function get_related_advertising($advertising)
    {
        $id = explode(",", $advertising);
        $this->db->select('pt_advertising.advertising_title,pt_advertising.advertising_slug,pt_advertising.advertising_id,pt_advertising.advertising_basic_price,pt_advertising.advertising_basic_discount,pt_advertising_types_settings.sett_name');
        $this->db->where_in('pt_advertising.advertising_id', $id);
        /*  $this->db->where('pt_advertising_images.timg_type','default');
        $this->db->join('pt_advertising_images','pt_advertising.advertising_id = pt_advertising_images.timg_advertising_id','left');*/
        $this->db->join('pt_advertising_types_settings', 'pt_advertising.advertising_type = pt_advertising_types_settings.sett_id', 'left');
        return $this->db->get('pt_advertising')->result();
    }

// Check advertising existence
    function advertising_exists($slug)
    {
        $this->db->select('advertising_id');
        $this->db->where('advertising_slug', $slug);
        $this->db->where('advertising_status', 'Yes');
        $nums = $this->db->get('pt_advertising')->num_rows();
        if ($nums > 0) {
            return true;
        } else {
            return false;
        }
    }

// List all advertising on front listings page
    function list_advertising_front($sprice = null, $perpage = null, $offset = null, $orderby = null)
    {
        $data = array();
        if ($offset != null) {
            $offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
        }
        if ($orderby == "za") {
            $this->db->order_by('pt_advertising.advertising_title', 'desc');
        } elseif ($orderby == "az") {
            $this->db->order_by('pt_advertising.advertising_title', 'asc');
        } elseif ($orderby == "oldf") {
            $this->db->order_by('pt_advertising.advertising_id', 'asc');
        } elseif ($orderby == "newf") {
            $this->db->order_by('pt_advertising.advertising_id', 'desc');
        } elseif ($orderby == "ol") {
            $this->db->order_by('pt_advertising.advertising_order', 'asc');
        }
        $this->db->select('advertising_id');
        $this->db->group_by('advertising_id');

        if (!empty ($sprice)) {
            $sprice = explode("-", $sprice);
            $minp = $sprice[0];
            $maxp = $sprice[1];
            $this->db->where('pt_advertising.advertising_adult_price >=', $minp);
            $this->db->where('pt_advertising.advertising_adult_price <=', $maxp);
        }

        $this->db->where('advertising_status', 'Yes');
        $query = $this->db->get('pt_advertising', $perpage, $offset);
        $data['all'] = $query->result();
        $data['rows'] = $query->num_rows();
        return $data;
    }

// List all advertising on front listings page by location
    function showAdvertisingByLocation($locs, $sprice = null, $perpage = null, $offset = null, $orderby = null)
    {
        $data = array();

        //var_dump($locs);

        if ($offset != null) {
            $offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
        }
        if ($orderby == "za") {
            $this->db->order_by('pt_advertising.advertising_title', 'desc');
        } elseif ($orderby == "az") {
            $this->db->order_by('pt_advertising.advertising_title', 'asc');
        } elseif ($orderby == "oldf") {
            $this->db->order_by('pt_advertising.advertising_id', 'asc');
        } elseif ($orderby == "newf") {
            $this->db->order_by('pt_advertising.advertising_id', 'desc');
        } elseif ($orderby == "ol") {
            $this->db->order_by('pt_advertising.advertising_order', 'asc');
        }
        $this->db->select('advertising_id');
        $this->db->group_by('advertising_id');

        if (!empty ($sprice)) {
            $sprice = explode("-", $sprice);
            $minp = $sprice[0];
            $maxp = $sprice[1];
            $this->db->where('pt_advertising.advertising_adult_price >=', $minp);
            $this->db->where('pt_advertising.advertising_adult_price <=', $maxp);
        }

        if (is_array($locs)) {
            $this->db->where_in('pt_advertising.advertising_location', $locs);
        } else {
            $this->db->where('pt_advertising.advertising_location', $locs);
        }

        $this->db->where('advertising_status', 'Yes');
        $query = $this->db->get('pt_advertising', $perpage, $offset);
        $data['all'] = $query->result();
        $data['rows'] = $query->num_rows();
        return $data;
    }

    // List all advertising on front listings page by destination

    function showAdvertisingByDestination($destination, $sprice = null, $perpage = null, $offset = null, $orderby = null)
    {
        $data = array();

        //var_dump($destination);

        if ($offset != null) {
            $offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
        }
        if ($orderby == "za") {
            $this->db->order_by('pt_advertising.advertising_title', 'desc');
        } elseif ($orderby == "az") {
            $this->db->order_by('pt_advertising.advertising_title', 'asc');
        } elseif ($orderby == "oldf") {
            $this->db->order_by('pt_advertising.advertising_id', 'asc');
        } elseif ($orderby == "newf") {
            $this->db->order_by('pt_advertising.advertising_id', 'desc');
        } elseif ($orderby == "ol") {
            $this->db->order_by('pt_advertising.advertising_order', 'asc');
        }
        $this->db->select('advertising_id');
        $this->db->group_by('advertising_id');

        if (!empty ($sprice)) {
            $sprice = explode("-", $sprice);
            $minp = $sprice[0];
            $maxp = $sprice[1];
            $this->db->where('pt_advertising.advertising_adult_price >=', $minp);
            $this->db->where('pt_advertising.advertising_adult_price <=', $maxp);
        }

        if (is_array($destination)) {
            $this->db->where_in('pt_advertising.advertising_destination', $destination);
        } else {
            $this->db->where('pt_advertising.advertising_destination', $destination);
        }

        $this->db->where('advertising_status', 'Yes');
        $query = $this->db->get('pt_advertising', $perpage, $offset);
        $data['all'] = $query->result();
        $data['rows'] = $query->num_rows();
        return $data;
    }


// Search advertising from home page
    function search_advertising_front($location = null, $sprice = null, $perpage = null, $offset = null, $orderby = null)
    {
        $this->load->helper('advertising_front');
        $data = array();

        //$location = $this->input->get('location');

        $adults = $this->input->get('adults');
        $type = $this->input->get('type');

        //$sprice = $this->input->get('price');
        $stars = $this->input->get('stars');

        if ($offset != null) {
            $offset = ($offset == 1) ? 0 : ($offset * $perpage) - $perpage;
        }
        $this->db->select('pt_advertising.advertising_id,advertising_type,advertising_location,advertising_adult_price,advertising_title,advertising_max_adults,advertising_status,pt_advertising_locations.*');
        if ($orderby == "za") {
            $this->db->order_by('pt_advertising.advertising_title', 'desc');
        } elseif ($orderby == "az") {
            $this->db->order_by('pt_advertising.advertising_title', 'asc');
        } elseif ($orderby == "oldf") {
            $this->db->order_by('pt_advertising.advertising_id', 'asc');
        } elseif ($orderby == "newf") {
            $this->db->order_by('pt_advertising.advertising_id', 'desc');
        } elseif ($orderby == "ol") {
            $this->db->order_by('pt_advertising.advertising_order', 'asc');
        } elseif ($orderby == "p_lh") {
            $this->db->order_by('pt_advertising.advertising_adult_price', 'asc');
        } elseif ($orderby == "p_hl") {
            $this->db->order_by('pt_advertising.advertising_adult_price', 'desc');
        }

        if (!empty($location)) {
            //$this->db->like('pt_advertising.advertising_location', $location);
            $this->db->where('pt_advertising_locations.location_id', $location);

        }


        if (!empty ($adults)) {
            $this->db->where('pt_advertising.advertising_max_adults >=', $adults);
        }

        if (!empty ($stars)) {
            $this->db->where('advertising_stars', $stars);
        }


        if (!empty ($type)) {
            $this->db->where('pt_advertising.advertising_type', $type);
        }

        if (!empty ($sprice)) {
            $sprice = explode("-", $sprice);
            $minp = $sprice[0];
            $maxp = $sprice[1];
            $this->db->where('pt_advertising.advertising_adult_price >=', $minp);
            $this->db->where('pt_advertising.advertising_adult_price <=', $maxp);
        }
        $this->db->group_by('pt_advertising.advertising_id');
        $this->db->join('pt_advertising_locations', 'pt_advertising.advertising_id = pt_advertising_locations.advertising_id');
        $this->db->where('pt_advertising.advertising_status', 'Yes');


        if (!empty($perpage)) {

            $query = $this->db->get('pt_advertising', $perpage, $offset);

        } else {

            $query = $this->db->get('pt_advertising');

        }

        $data['all'] = $query->result();
        $data['rows'] = $query->num_rows();
        return $data;
    }

    function max_map_order($advertisingid)
    {
        $this->db->select('map_id');
        $this->db->where('map_city_type', 'visit');
        $this->db->where('map_advertising_id', $advertisingid);
        return $this->db->get('pt_advertising_maps')->num_rows();
    }

// get default image of advertising
    function default_advertising_img($id)
    {
        $this->db->where('timg_type', 'default');
        $this->db->where('timg_approved', '1');
        $this->db->where('timg_advertising_id', $id);
        $res = $this->db->get('pt_advertising_images')->result();
        if (!empty ($res)) {
            return $res[0]->timg_image;
        } else {
            return '';
        }
    }

// update translated data os some fields in english
    function update_english($id)
    {
        $cslug = create_url_slug($this->input->post('title'));
        $this->db->where('advertising_slug', $cslug);
        $this->db->where('advertising_id !=', $id);
        $nums = $this->db->get('pt_advertising')->num_rows();
        if ($nums > 0) {
            $cslug = $cslug . "-" . $id;
        } else {
            $cslug = $cslug;
        }
        $data = array('advertising_title' => $this->input->post('title'), 'advertising_slug' => $cslug, 'advertising_desc' => $this->input->post('desc'), 'advertising_policy' => $this->input->post('policy'));
        $this->db->where('advertising_id', $id);
        $this->db->update('pt_advertising', $data);
        return $cslug;
    }

// Adds translation of some fields data
    function add_translation($postdata, $advertisingid)
    {
        foreach ($postdata as $lang => $val) {
            if (array_filter($val)) {
                $title = $val['title'];
                $desc = $val['desc'];
                $metatitle = $val['metatitle'];
                $metadesc = $val['metadesc'];
                $keywords = $val['keywords'];
                $policy = $val['policy'];
                $data = array(
                    'trans_title' => $title,
                    'trans_desc' => $desc,
                    'trans_policy' => $policy,
                    'metatitle' => $metatitle,
                    'metadesc' => $metadesc,
                    'metakeywords' => $keywords,
                    'item_id' => $advertisingid,
                    'trans_lang' => $lang
                );
                $this->db->insert('pt_advertising_translation', $data);
            }

        }
    }

// Update translation of some fields data
    function update_translation($postdata, $id)
    {
        foreach ($postdata as $lang => $val) {
            if (array_filter($val)) {
                $title = $val['title'];
                $desc = $val['desc'];
                $metatitle = $val['metatitle'];
                $metadesc = $val['metadesc'];
                $kewords = $val['keywords'];
                $policy = $val['policy'];
                $transAvailable = $this->getBackTranslation($lang, $id);

                if (empty($transAvailable)) {
                    $data = array(
                        'trans_title' => $title,
                        'trans_desc' => $desc,
                        'trans_policy' => $policy,
                        'metatitle' => $metatitle,
                        'metadesc' => $metadesc,
                        'metakeywords' => $kewords,
                        'item_id' => $id,
                        'trans_lang' => $lang
                    );
                    $this->db->insert('pt_advertising_translation', $data);

                } else {
                    $data = array(
                        'trans_title' => $title,
                        'trans_desc' => $desc,
                        'trans_policy' => $policy,
                        'metatitle' => $metatitle,
                        'metadesc' => $metadesc,
                        'metakeywords' => $kewords,
                    );
                    $this->db->where('item_id', $id);
                    $this->db->where('trans_lang', $lang);
                    $this->db->update('pt_advertising_translation', $data);
                }


            }

        }

    }

    function getBackTranslation($lang, $id)
    {

        $this->db->where('trans_lang', $lang);
        $this->db->where('item_id', $id);
        return $this->db->get('pt_advertising_translation')->result();

    }

    function advertisingGallery($slug)
    {

        $this->db->select('pt_advertising.thumbnail_image as thumbnail,pt_advertising_images.timg_id as id,pt_advertising_images.timg_advertising_id as itemid,pt_advertising_images.timg_type as type,pt_advertising_images.timg_image as image,pt_advertising_images.timg_order as imgorder,pt_advertising_images.timg_image as image,pt_advertising_images.timg_approved as approved');
        $this->db->where('pt_advertising.advertising_slug', $slug);
        $this->db->join('pt_advertising_images', 'pt_advertising.advertising_id = pt_advertising_images.timg_advertising_id', 'left');
        $this->db->order_by('pt_advertising_images.timg_id', 'desc');
        return $this->db->get('pt_advertising')->result();

    }

    function addPhotos($id, $filename)
    {

        $this->db->select('thumbnail_image');
        $this->db->where('advertising_id', $id);
        $rs = $this->db->get('pt_advertising')->result();
        if ($rs[0]->thumbnail_image == PT_BLANK_IMG) {

            $data = array('thumbnail_image' => $filename);
            $this->db->where('advertising_id', $id);
            $this->db->update('pt_advertising', $data);
        }

        //add photos to advertising images table
        $imgorder = 0;
        $this->db->where('timg_type', 'slider');
        $this->db->where('timg_advertising_id', $id);
        $imgorder = $this->db->get('pt_advertising_images')->num_rows();
        $imgorder = $imgorder + 1;

        $approval = pt_admin_gallery_approve();

        $insdata = array(
            'timg_advertising_id' => $id,
            'timg_type' => 'slider',
            'timg_image' => $filename,
            'timg_order' => $imgorder,
            'timg_approved' => $approval
        );

        $this->db->insert('pt_advertising_images', $insdata);


    }

    function assignAdvertising($advertising, $userid)
    {

        if (!empty($advertising)) {
            $useradvertising = $this->userOwnedAdvertising($userid);
            foreach ($useradvertising as $tt) {
                if (!in_array($tt, $advertising)) {
                    $ddata = array(
                        'advertising_owned_by' => '1'
                    );
                    $this->db->where('advertising_id', $tt);
                    $this->db->update('pt_advertising', $ddata);
                }
            }

            foreach ($advertising as $t) {
                $data = array(
                    'advertising_owned_by' => $userid
                );
                $this->db->where('advertising_id', $t);
                $this->db->update('pt_advertising', $data);

            }

        }
    }

    function userOwnedAdvertising($id)
    {
        $result = array();
        if (!empty($id)) {
            $this->db->where('advertising_owned_by', $id);
        }

        $rs = $this->db->get('pt_advertising')->result();
        if (!empty($rs)) {
            foreach ($rs as $r) {
                $result[] = $r->advertising_id;
            }
        }
        return $result;
    }

    // get number of photos of advertising
    function photos_count($advertisingid)
    {
        $this->db->where('timg_advertising_id', $advertisingid);
        return $this->db->get('pt_advertising_images')->num_rows();
    }

    function updateAdvertisingSettings()
    {
        $ufor = $this->input->post('updatefor');

        $data = array('front_icon' => $this->input->post('page_icon'),
            'front_homepage' => $this->input->post('home'),
            'front_homepage_order' => $this->input->post('homeorder'),
            'front_related' => $this->input->post('related'),
            //'front_popular' => $this->input->post('popular'),
            //'front_popular_order' => $this->input->post('popularorder'),
            'front_latest' => $this->input->post('latest'),
            'front_listings' => $this->input->post('listings'),
            'front_listings_order' => $this->input->post('listingsorder'),
            'front_search' => $this->input->post('searchresult'),
            'front_search_order' => $this->input->post('searchorder'),
            'front_search_min_price' => $this->input->post('minprice'),
            'front_search_max_price' => $this->input->post('maxprice'),
            'front_txtsearch' => '1',
            'linktarget' => $this->input->post('target'),
            'header_title' => $this->input->post('headertitle'),
            'meta_keywords' => $this->input->post('keywords'),
            'meta_description' => $this->input->post('description')
        );
        $this->db->where('front_for', $ufor);
        $this->db->update('pt_front_settings', $data);
        $this->session->set_flashdata('flashmsgs', "Updated Successfully");
    }

    // get popular advertising
    function popular_advertising_front()
    {
        $settings = $this->Settings_model->get_front_settings('advertising');
        //$limit = $settings[0]->front_popular;
        //$orderby = $settings[0]->front_popular_order;

        $this->db->select('pt_advertising.advertising_id,pt_advertising.advertising_status,pt_reviews.review_overall,pt_reviews.review_itemid');

        $this->db->select_avg('pt_reviews.review_overall', 'overall');
        $this->db->order_by('overall', 'desc');
        $this->db->group_by('pt_advertising.advertising_id');
        $this->db->join('pt_reviews', 'pt_advertising.advertising_id = pt_reviews.review_itemid');
        $this->db->where('advertising_status', 'yes');
        //		$this->db->limit($limit);
        return $this->db->get('pt_advertising')->result();
    }


    function addSettingsData()
    {

        $filename = "";
        $type = $this->input->post('typeopt');
        $data = array(
            'sett_name' => $this->input->post('name'),
            'sett_status' => $this->input->post('statusopt'),
            'sett_selected' => $this->input->post('setselect'),
            'sett_type' => $type,
            'sett_img' => $filename
        );

        $this->db->insert('pt_advertising_types_settings', $data);
        return $this->db->insert_id();
        $this->session->set_flashdata('flashmsgs', "Updated Successfully");

    }

// update advertising settings data
    function updateSettingsData()
    {
        $id = $this->input->post('settid');
        $type = $this->input->post('typeopt');
        $filename = "";

        $data = array('sett_name' => $this->input->post('name'),
            'sett_status' => $this->input->post('statusopt'),
            'sett_selected' => $this->input->post('setselect'),
            'sett_img' => $filename

        );
        $this->db->where('sett_id', $id);
        $this->db->update('pt_advertising_types_settings', $data);
        $this->session->set_flashdata('flashmsgs', "Updated Successfully");
    }


    function updateSettingsTypeTranslation($postdata, $id)
    {

        foreach ($postdata as $lang => $val) {
            if (array_filter($val)) {
                $name = $val['name'];

                $transAvailable = $this->getBackSettingsTranslation($lang, $id);

                if (empty($transAvailable)) {
                    $data = array(
                        'trans_name' => $name,
                        'sett_id' => $id,
                        'trans_lang' => $lang
                    );
                    $this->db->insert('pt_advertising_types_settings_translation', $data);

                } else {

                    $data = array(
                        'trans_name' => $name
                    );
                    $this->db->where('sett_id', $id);
                    $this->db->where('trans_lang', $lang);
                    $this->db->update('pt_advertising_types_settings_translation', $data);

                }


            }

        }
    }


    function getBackSettingsTranslation($lang, $id)
    {

        $this->db->where('trans_lang', $lang);
        $this->db->where('sett_id', $id);
        return $this->db->get('pt_advertising_types_settings_translation')->result();

    }

    // Delete hotel settings
    function deleteTypeSettings($id)
    {
        $this->db->where('sett_id', $id);
        $this->db->delete('pt_advertising_types_settings');

        $this->db->where('sett_id', $id);
        $this->db->delete('pt_advertising_types_settings_translation');
    }

    // Delete multiple advertising settings
    function deleteMultiplesettings($id, $type)
    {
        $this->db->where('sett_id', $id);
        $this->db->where('sett_type', $type);
        $this->db->delete('pt_advertising_types_settings');

        $rowsDeleted = $this->db->affected_rows();

        if ($rowsDeleted > 0) {
            $this->db->where('sett_id', $id);
            $this->db->delete('pt_advertising_types_settings_translation');
        }


    }

    function getTypesTranslation($lang, $id)
    {

        $this->db->where('trans_lang', $lang);
        $this->db->where('sett_id', $id);
        return $this->db->get('pt_advertising_types_settings_translation')->result();

    }

    function updateAdvertisingLocations($locations, $advertisingid)
    {

        $this->db->where('advertising_id', $advertisingid);
        $this->db->delete('pt_advertising_locations');
        $position = 0;

        foreach ($locations as $loc) {

            if (!empty($loc)) {
                $position++;
                $data = array('position' => $position, 'location_id' => $loc, 'advertising_id' => $advertisingid);
                $this->db->insert('pt_advertising_locations', $data);
            }
        }

    }

    function isAdvertisingLocation($i, $locid, $advertisingid)
    {
        $this->db->where('position', $i);
        $this->db->where('location_id', $locid);
        $this->db->where('advertising_id', $advertisingid);
        $rs = $this->db->get('pt_advertising_locations')->num_rows();
        if ($rs > 0) {
            return "selected";
        } else {
            return "";
        }
    }

}
