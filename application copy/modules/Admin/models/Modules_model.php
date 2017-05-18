<?php

class Modules_model extends CI_Model
{

    function __construct()
    {
// Call the Model constructor
        parent:: __construct();
    }

    function get_all_modules()
    {
        $this->db->where('module_front !=', '2');
        return $this->db->get('pt_modules')->result();
    }

    function get_all_enabled_modules()
    {
        $this->db->where('module_status', '1');
        return $this->db->get('pt_modules')->result();
    }

// Get Enabled front end modules
    function get_front_modules()
    {
        $this->db->where('module_status', '1');
        $this->db->where('module_front', '1');
        $rs = $this->db->get('pt_modules')->result();
        return $rs;
    }

// check availability of module
    function check_module($module)
    {
        $this->db->where('module_name', $module);
        $this->db->where('module_status', '1');
        $num = $this->db->get('pt_modules')->num_rows();
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }

    function disable_module($name)
    {
        $data = array('module_status' => '0');
        $this->db->where('module_name', $name);
        $this->db->update('pt_modules', $data);
        $data2 = array('page_status' => 'No');
        $this->db->where('page_slug', $name);
        $this->db->update('pt_cms', $data2);
    }

    function enable_module($name)
    {
        $data = array('module_status' => '1');
        $this->db->where('module_name', $name);
        $this->db->update('pt_modules', $data);
        $data2 = array('page_status' => 'Yes');
        $this->db->where('page_slug', $name);
        $this->db->update('pt_cms', $data2);
    }

    function enable_main_module($menutitle)
    {
        $notallowed = array("tripadvisor");
        $this->db->select("page_id");
        $this->db->where("page_slug", $menutitle);
        $rs = $this->db->get('pt_cms')->result();
        $pageid = $rs[0]->page_id;
        $data1 = array('page_status' => 'Yes');
        $this->db->where("page_slug", $menutitle);
        $this->db->update("pt_cms", $data1);
        if (!in_array($menutitle, $notallowed)) {
            $this->db->where('coltype', 'header');
            $menudetail = $this->db->get('pt_menus')->result();
            $menuitems = json_decode($menudetail[0]->menu_items);
            $p = array();
            $c = array();
            foreach ($menuitems as $mmm) {
                $p[] = $mmm->id;
                if (!empty ($mmm->children)) {
                    foreach ($mmm->children as $mc) {
                        $c[] = $mc->id;
                    }
                }
            }
            $d = array_merge($p, $c);
            if (!in_array($pageid, $d)) {
                $addItem = new stdClass();
                $addItem->id = $pageid;
                $menuitems[] = $addItem;
                $string = json_encode($menuitems);
                $this->Menus_model->update_menu($string, $menudetail[0]->menu_id);
            }
        }
    }

    function disable_main_module($menutitle)
    {
        $this->db->select("page_id");
        $this->db->where("page_slug", $menutitle);
        $rs = $this->db->get('pt_cms')->result();
        $pageid = $rs[0]->page_id;
        $data1 = array('page_status' => 'No');
        $this->db->where("page_slug", $menutitle);
        $this->db->update("pt_cms", $data1);
        /*
        $this->db->where('coltype','header');
        $menudetail = $this->db->get('pt_menus')->result();
        $mm = json_decode($menudetail[0]->menu_items);
        foreach($mm as $k => $v){
        if($v->id == $pageid){
        unset($mm[$k]->id);
        }
        if(!empty($v->children)){

        foreach($v->children as $kk => $vv){
        if($vv->id == $pageid){
        unset($v->children[$kk]->id);
        }
        }

        }

        }


        $string = json_encode($mm);

        $this->Menus_model->update_menu($string,$menudetail[0]->menu_id); */
    }

// get modules names
    function get_module_names()
    {
        $this->load->library('ptmodules');
        $mod1 = $this->ptmodules->moduleslist;
        $mod2 = $this->ptmodules->integratedmoduleslist;
        $mergedmods = array_merge($mod1, $mod2);
        return $mergedmods;
        /*   $this->db->select('module_name');
        $this->db->where('module_front','1');
        $this->db->where('module_status','1');

        return $this->db->get('pt_modules')->result();*/
    }

// check availability of main module
    function check_main_module($module)
    {
        $this->load->library('ptmodules');
        return $this->ptmodules->is_main_module_enabled($module);
    }

//get selected module all items
    function get_module_items_all($module)
    {
        $rslt;
        if ($module == "hotels") {
            $this->db->select('hotel_id AS id,hotel_title AS title');
            $rslt = $this->db->get('pt_hotels')->result();
        } elseif ($module == "tours") {
            $this->db->select('tour_id AS id,tour_title AS title');
            $rslt = $this->db->get('pt_tours')->result();
        }
        //add Modules
//advertising

        elseif ($module == "advertising") {
            $this->db->select('advertising_id AS id,advertising_title AS title');
            $rslt = $this->db->get('pt_advertising')->result();
        } //Mod #1
        elseif ($module == "activity") {
            $this->db->select('activity_id AS id,activity_title AS title');
            $rslt = $this->db->get('pt_activity')->result();
        } //Mod #2
        elseif ($module == "entertainment") {
            $this->db->select('entertainment_id AS id,entertainment_title AS title');
            $rslt = $this->db->get('pt_entertainment')->result();
        } //Mod #3
        elseif ($module == "restaurant") {
            $this->db->select('restaurant_id AS id,restaurant_title AS title');
            $rslt = $this->db->get('pt_restaurant')->result();
        } //Mod #4
        elseif ($module == "spa") {
            $this->db->select('spa_id AS id,spa_title AS title');
            $rslt = $this->db->get('pt_spa')->result();
        } //Mod #5
        elseif ($module == "wedding") {
            $this->db->select('wedding_id AS id,wedding_title AS title');
            $rslt = $this->db->get('pt_wedding')->result();
        } //End Modules

        elseif ($module == "cruises") {
            $this->db->select('cruise_id AS id,cruise_title AS title');
            $rslt = $this->db->get('pt_cruises')->result();
        }
        return $rslt;
    }

//get selected module all items of a user
    function get_supplier_module_items_all($module, $user)
    {
        $rslt;
        if ($module == "hotels") {
            $this->db->select('hotel_id AS id,hotel_title AS title');
            $this->db->where('hotel_owned_by', $user);
            $rslt = $this->db->get('pt_hotels')->result();
        } elseif ($module == "tours") {
            $this->db->select('tour_id AS id,tour_title AS title');
            $this->db->where('tour_owned_by', $user);
            $rslt = $this->db->get('pt_tours')->result();
        } elseif ($module == "advertising") {
            $this->db->select('advertising_id AS id,advertising_title AS title');
            $this->db->where('advertising_owned_by', $user);
            $rslt = $this->db->get('pt_advertising')->result();
        } //Mod #1
        elseif ($module == "activity") {
            $this->db->select('activity_id AS id,activity_title AS title');
            $this->db->where('activity_owned_by', $user);
            $rslt = $this->db->get('pt_activity')->result();
        } //Mod #2
        elseif ($module == "entertainment") {
            $this->db->select('entertainment_id AS id,entertainment_title AS title');
            $this->db->where('entertainment_owned_by', $user);
            $rslt = $this->db->get('pt_entertainment')->result();
        } //Mod #3
        elseif ($module == "restaurant") {
            $this->db->select('restaurant_id AS id,restaurant_title AS title');
            $this->db->where('restaurant_owned_by', $user);
            $rslt = $this->db->get('pt_restaurant')->result();
        } //Mod #4
        elseif ($module == "spa") {
            $this->db->select('spa_id AS id,spa_title AS title');
            $this->db->where('spa_owned_by', $user);
            $rslt = $this->db->get('pt_spa')->result();
        } //Mod #5
        elseif ($module == "wedding") {
            $this->db->select('wedding_id AS id,wedding_title AS title');
            $this->db->where('wedding_owned_by', $user);
            $rslt = $this->db->get('pt_wedding')->result();
        } elseif ($module == "cruises") {
            $this->db->select('cruise_id AS id,cruise_title AS title');
            $this->db->where('cruise_owned_by', $user);
            $rslt = $this->db->get('pt_cruises')->result();
        }
        return $rslt;
    }

//get selected module items
    function get_module_items($module)
    {
        $HTML = "";
        if ($module == "hotels") {
            $this->db->select('hotel_id AS id,hotel_title AS title');
            $this->db->order_by('hotel_id', 'desc');
            $rslt = $this->db->get('pt_hotels')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } elseif ($module == "tours") {
            $this->db->select('tour_id AS id,tour_title AS title');
            $this->db->order_by('tour_id', 'desc');
            $rslt = $this->db->get('pt_tours')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } elseif ($module == "advertising") {
            $this->db->select('advertising_id AS id,advertising_title AS title');
            $this->db->order_by('advertising_id', 'desc');
            $rslt = $this->db->get('pt_advertising')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //Mod#1
        elseif ($module == "activity") {
            $this->db->select('activity_id AS id,activity_title AS title');
            $this->db->order_by('activity_id', 'desc');
            $rslt = $this->db->get('pt_activity')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //Mod#2
        elseif ($module == "entertainment") {
            $this->db->select('entertainment_id AS id,entertainment_title AS title');
            $this->db->order_by('entertainment_id', 'desc');
            $rslt = $this->db->get('pt_entertainment')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //Mod#3
        elseif ($module == "restaurant") {
            $this->db->select('restaurant_id AS id,restaurant_title AS title');
            $this->db->order_by('restaurant_id', 'desc');
            $rslt = $this->db->get('pt_restaurant')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //Mod#4
        elseif ($module == "spa") {
            $this->db->select('spa_id AS id,spa_title AS title');
            $this->db->order_by('spa_id', 'desc');
            $rslt = $this->db->get('pt_spa')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //Mod#5
        elseif ($module == "wedding") {
            $this->db->select('wedding_id AS id,wedding_title AS title');
            $this->db->order_by('wedding_id', 'desc');
            $rslt = $this->db->get('pt_wedding')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //End Module

        elseif ($module == "cruises") {
            $this->db->select('cruise_id AS id,cruise_title AS title');
            $this->db->order_by('cruise_id', 'desc');
            $rslt = $this->db->get('pt_cruises')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        }
        return $HTML;
    }

//get selected module items for specific user
    function get_supplier_module_items($module, $id)
    {
        $HTML = "";
        if ($module == "hotels") {
            $this->db->select('hotel_id AS id,hotel_title AS title');
            $this->db->where('hotel_owned_by', $id);
            $this->db->order_by('hotel_id', 'desc');
            $rslt = $this->db->get('pt_hotels')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } elseif ($module == "tours") {
            $this->db->select('tour_id AS id,tour_title AS title');
            $this->db->where('tour_owned_by', $id);
            $this->db->order_by('tour_id', 'desc');
            $rslt = $this->db->get('pt_tours')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } //advertising

        elseif ($module == "advertising") {
            $this->db->select('advertising_id AS id,advertising_title AS title');
            $this->db->where('advertising_owned_by', $id);
            $this->db->order_by('advertising_id', 'desc');
            $rslt = $this->db->get('pt_advertising')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        } elseif ($module == "cruises") {
            $this->db->select('cruise_id AS id,cruise_title AS title');
            $this->db->where('cruise_owned_by', $id);
            $this->db->order_by('cruise_id', 'desc');
            $rslt = $this->db->get('pt_cruises')->result();
            foreach ($rslt as $r) {
                $HTML .= "<option value='" . $r->id . "'>" . $r->title . "</option>";
            }
        }
        return $HTML;
    }

//get details of item of a given module
    function get_for_details($module, $id)
    {
        if ($module == "hotels") {
            $this->db->select('hotel_id,hotel_title,hotel_slug');
            $this->db->where('hotel_id', $id);
            $this->db->order_by('hotel_id', 'desc');
            $rslt = $this->db->get('pt_hotels')->result();
            $result['id'] = $rslt[0]->hotel_id;
            $result['title'] = $rslt[0]->hotel_title;
            $result['slug'] = "hotels/" . $rslt[0]->hotel_slug;
        } //Mod#1
        elseif ($module == "activity") {
            $this->db->select('activity_id,activity_title,tactivity_slug');
            $this->db->where('activity_id', $id);
            $this->db->order_by('activity_id', 'desc');
            $rslt = $this->db->get('pt_activity')->result();
            $result['id'] = $rslt[0]->activity_id;
            $result['title'] = $rslt[0]->activity_title;
            $result['slug'] = "activity/" . $rslt[0]->activity_slug;
        } //Mod#2
        elseif ($module == "entertainment") {
            $this->db->select('entertainment_id,entertainment_title,entertainment_slug');
            $this->db->where('entertainment_id', $id);
            $this->db->order_by('entertainment_id', 'desc');
            $rslt = $this->db->get('pt_entertainment')->result();
            $result['id'] = $rslt[0]->entertainment_id;
            $result['title'] = $rslt[0]->entertainment_title;
            $result['slug'] = "entertainment/" . $rslt[0]->entertainment_slug;
        } //Mod#3
        elseif ($module == "restaurant") {
            $this->db->select('restaurant_id,restaurant_title,restaurant_slug');
            $this->db->where('restaurant_id', $id);
            $this->db->order_by('restaurant_id', 'desc');
            $rslt = $this->db->get('pt_restaurant')->result();
            $result['id'] = $rslt[0]->restaurant_id;
            $result['title'] = $rslt[0]->restaurant_title;
            $result['slug'] = "restaurant/" . $rslt[0]->restaurant_slug;
        } //Mod#4
        elseif ($module == "spa") {
            $this->db->select('spa_id,spa_title,spa_slug');
            $this->db->where('spa_id', $id);
            $this->db->order_by('spa_id', 'desc');
            $rslt = $this->db->get('pt_spa')->result();
            $result['id'] = $rslt[0]->spa_id;
            $result['title'] = $rslt[0]->spa_title;
            $result['slug'] = "spa/" . $rslt[0]->spa_slug;
        } //Mod#5
        elseif ($module == "wedding") {
            $this->db->select('wedding_id,wedding_title,wedding_slug');
            $this->db->where('wedding_id', $id);
            $this->db->order_by('wedding_id', 'desc');
            $rslt = $this->db->get('pt_wedding')->result();
            $result['id'] = $rslt[0]->wedding_id;
            $result['title'] = $rslt[0]->wedding_title;
            $result['slug'] = "wedding/" . $rslt[0]->wedding_slug;
        } //End

        elseif ($module == "tours") {
            $this->db->select('tour_id,tour_title,tour_slug');
            $this->db->where('tour_id', $id);
            $this->db->order_by('tour_id', 'desc');
            $rslt = $this->db->get('pt_tours')->result();
            $result['id'] = $rslt[0]->tour_id;
            $result['title'] = $rslt[0]->tour_title;
            $result['slug'] = "tours/" . $rslt[0]->tour_slug;
        } elseif ($module == "advertising") {
            $this->db->select('advertising_id,advertising_title,advertising_slug');
            $this->db->where('advertising_id', $id);
            $this->db->order_by('advertising_id', 'desc');
            $rslt = $this->db->get('pt_advertising')->result();
            $result['id'] = $rslt[0]->advertising_id;
            $result['title'] = $rslt[0]->advertising_title;
            $result['slug'] = "advertising/" . $rslt[0]->advertising_slug;
        } elseif ($module == "cars") {
            $this->db->select('car_id,car_title,car_slug');
            $this->db->where('car_id', $id);
            $this->db->order_by('car_id', 'desc');
            $rslt = $this->db->get('pt_cars')->result();
            $result['id'] = $rslt[0]->car_id;
            $result['title'] = $rslt[0]->car_title;
            $result['slug'] = "cars/" . $rslt[0]->car_slug;
        } elseif ($module == "cruises") {
            $this->db->select('cruise_id,cruise_title,cruise_slug');
            $this->db->where('cruise_id', $id);
            $this->db->order_by('cruise_id', 'desc');
            $rslt = $this->db->get('pt_cruises')->result();
            $result['id'] = $rslt[0]->cruise_id;
            $result['title'] = $rslt[0]->cruise_title;
            $result['slug'] = "cruises/" . $rslt[0]->cruise_slug;
        }
        return $result;
    }

}
