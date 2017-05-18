<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('pt_default_wedding_image'))
{
    function pt_default_wedding_image($weddingid)
    {

      $CI = get_instance();

    $CI->load->model('wedding_model');

    $res = $CI->Wedding_model->default_wedding_img($weddingid);


    return $res;

    }
}if (!function_exists('pt_max_adults')) {

    function pt_max_adults() {
        $CI = get_instance();
        $CI->db->select_max('wedding_max_adults');
        $adults = $CI->db->get('pt_wedding')->result();
        return $adults[0]->wedding_max_adults;
    }

}

if ( ! function_exists('pt_get_tsettings_data'))
{
    function pt_get_tsettings_data($type)
    {
       $CI = get_instance();

    $CI->load->model('wedding_model');

    $res = $CI->Wedding_model->get_tsettings_data($type);


    return $res;

    }
}



if ( ! function_exists('pt_get_wedding_types_details'))
{
    function pt_get_wedding_types_details($type,$items)
    {
       $CI = get_instance();

    $CI->load->model('wedding_model');

    $res = $CI->Wedding_model->get_tsettings_data_front($type,$items);

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

if ( ! function_exists('pt_wedding_has_map'))
{

function pt_wedding_has_map($weddingid){
    $CI = get_instance();
    $rslt = array();
    $CI->db->select('map_city_name');
    $CI->db->where('map_wedding_id',$weddingid);
    $res = $CI->db->get('pt_wedding_maps')->result();
    if(!empty($res)){
      return true;
    }else{
      return false;
    }

}

}

if ( ! function_exists('pt_wedding_start_end_map'))
{

function pt_wedding_start_end_map($weddingid,$type){
    $CI = get_instance();
    $rslt = array();
    $CI->db->select('map_city_name,map_city_lat,map_city_long');
    $CI->db->where('map_city_type',$type);
    $CI->db->where('map_wedding_id',$weddingid);
    $res = $CI->db->get('pt_wedding_maps')->result();
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

if ( ! function_exists('pt_wedding_visiting_map'))
{

function pt_wedding_visiting_map($weddingid){
    $CI = get_instance();
    $megareslt = array();

    $CI->db->select('map_city_name,map_city_lat,map_city_long');
    $CI->db->where('map_city_type','visit');
    $CI->db->where('map_wedding_id',$weddingid);
    $CI->db->order_by('map_order','asc');
    $res = $CI->db->get('pt_wedding_maps')->result();

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

if ( ! function_exists('pt_wedding_commission'))
{
    function pt_wedding_commission($id)
    {
      $res = array();
       $CI = get_instance();

    $CI->db->select('wedding_comm_fixed,wedding_comm_percentage');
    $CI->db->where('wedding_id',$id);
    $result = $CI->db->get('pt_wedding')->result();
    $res['fixed_com'] = $result[0]->wedding_comm_fixed;
    $res['per_com'] = $result[0]->wedding_comm_percentage;

      return $res;

    }
}


if ( ! function_exists('pt_loop_prices'))
{
    function pt_loop_prices($app_settings,$mulcur,$details,$id,$count,$geo)
    {

    if(empty($mulcur)){
          $adultprice = $app_settings[0]->currency_sign.$details[0]->wedding_adult_price * $count;
          $childprice = $app_settings[0]->currency_sign.$details[0]->wedding_child_price * $count;
          $infantprice = $app_settings[0]->currency_sign.$details[0]->wedding_infant_price * $count;

     }else{
          $adultprice = $geo->pt_convert($details[0]->wedding_adult_price * $count);
          $childprice = $geo->pt_convert($details[0]->wedding_child_price * $count);
          $infantprice = $geo->pt_convert($details[0]->wedding_infant_price * $count);

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
