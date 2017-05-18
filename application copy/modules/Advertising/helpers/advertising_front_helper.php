<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



if ( ! function_exists('pt_default_advertising_image'))
{
    function pt_default_advertising_image($advertisingid)
    {
    $CI = get_instance();
    $CI->load->model('advertising_model');
    $res = $CI->Advertising_model->default_advertising_img($advertisingid);
    return $res;

    }
}if (!function_exists('pt_max_adults')) {

    function pt_max_adults() {
        $CI = get_instance();
        $CI->db->select_max('advertising_max_adults');
        $adults = $CI->db->get('pt_advertising')->result();
        return $adults[0]->advertising_max_adults;
    }

}

if ( ! function_exists('pt_get_tsettings_data'))
{
    function pt_get_tsettings_data($type)
    {
       $CI = get_instance();

    $CI->load->model('advertising_model');

    $res = $CI->Advertising_model->get_tsettings_data($type);


    return $res;

    }
}



if ( ! function_exists('pt_get_advertising_types_details'))
{
    function pt_get_advertising_types_details($type,$items)
    {
       $CI = get_instance();

    $CI->load->model('advertising_model');

    $res = $CI->Advertising_model->get_tsettings_data_front($type,$items);

      return $res;

    }
}

if ( ! function_exists('pt_days_between'))
{

function pt_days_between($start, $end){
   $dates = array();
   while($start <= $end)
   {
     array_push(
          $dates,$start

       );
       $start += 86400;
   }
   return $dates;
}

}

if ( ! function_exists('pt_advertising_has_map'))
{

function pt_advertising_has_map($advertisingid){
    $CI = get_instance();
    $rslt = array();
    $CI->db->select('map_city_name');
    $CI->db->where('map_advertising_id',$advertisingid);
    $res = $CI->db->get('pt_advertising_maps')->result();
    if(!empty($res)){
      return true;
    }else{
      return false;
    }

}

}

if ( ! function_exists('pt_advertising_start_end_map'))
{

function pt_advertising_start_end_map($advertisingid,$type){
    $CI = get_instance();
    $rslt = array();
    $CI->db->select('map_city_name,map_city_lat,map_city_long');
    $CI->db->where('map_city_type',$type);
    $CI->db->where('map_advertising_id',$advertisingid);
    $res = $CI->db->get('pt_advertising_maps')->result();
    if(!empty($res)){
    $rslt[] = $res[0]->map_city_name;
    $rslt[] = $res[0]->map_city_lat;
    $rslt[] = $res[0]->map_city_long;
    return $rslt;

    }else{
      return '';
    }

}

}

if ( ! function_exists('pt_advertising_visiting_map'))
{

function pt_advertising_visiting_map($advertisingid){
    $CI = get_instance();
    $megareslt = array();

    $CI->db->select('map_city_name,map_city_lat,map_city_long');
    $CI->db->where('map_city_type','visit');
    $CI->db->where('map_advertising_id',$advertisingid);
    $CI->db->order_by('map_order','asc');
    $res = $CI->db->get('pt_advertising_maps')->result();

    if(!empty($res)){

    foreach($res as $r){
    $reslt = array();
    $reslt[] = $r->map_city_name;
    $reslt[] = $r->map_city_lat;
    $reslt[] = $r->map_city_long;
    $megareslt[] = $reslt;
    }
    return $megareslt;
    }else{
      return '';
    }


}

}

if ( ! function_exists('pt_advertising_commission'))
{
    function pt_advertising_commission($id)
    {
      $res = array();
       $CI = get_instance();

    $CI->db->select('advertising_comm_fixed,advertising_comm_percentage');
    $CI->db->where('advertising_id',$id);
    $result = $CI->db->get('pt_advertising')->result();
    $res['fixed_com'] = $result[0]->advertising_comm_fixed;
    $res['per_com'] = $result[0]->advertising_comm_percentage;

      return $res;

    }
}


if ( ! function_exists('pt_loop_prices'))
{
    function pt_loop_prices($app_settings,$mulcur,$details,$id,$count,$geo)
    {

    if(empty($mulcur)){
          $adultprice = $app_settings[0]->currency_sign.$details[0]->advertising_adult_price * $count;
          $childprice = $app_settings[0]->currency_sign.$details[0]->advertising_child_price * $count;
          $infantprice = $app_settings[0]->currency_sign.$details[0]->advertising_infant_price * $count;

     }else{
          $adultprice = $geo->pt_convert($details[0]->advertising_adult_price * $count);
          $childprice = $geo->pt_convert($details[0]->advertising_child_price * $count);
          $infantprice = $geo->pt_convert($details[0]->advertising_infant_price * $count);

          }
      if($id == "a"){
        return $adultprice;
      }elseif($id == "c"){
          return $childprice;
      }elseif($id == "i"){
          return $infantprice;
      }

 }
}
