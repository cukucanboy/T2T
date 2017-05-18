<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



if ( ! function_exists('pt_wish_thumbnail'))
{
    function pt_wish_thumbnail($id)
    {
       $CI = get_instance();

    $CI->load->model('Wish_model');

    $res = $CI->Wish_model->wish_thumbnail($id);

   if(!empty($res)){
     return PT_WISH_IMAGES.$res;
   }else{
     return PT_BLANK;
   }


    }
}
