<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



if ( ! function_exists('pt_destination_thumbnail'))
{
    function pt_destination_thumbnail($id)
    {
       $CI = get_instance();

    $CI->load->model('Destinations_model');

    $res = $CI->Destinations_model->destination_thumbnail($id);

   if(!empty($res)){
     return PT_DESTINATION_IMAGES.$res;
   }else{
     return PT_BLANK;
   }


    }
}
