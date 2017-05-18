<?php
if (!defined('BASEPATH'))
		exit ('No direct script access allowed');
        if (!function_exists('getBackWeddingTranslation')) {

		function getBackWeddingTranslation($lang, $id) {
		  if(!empty($id)){
          $CI = get_instance();
          $CI->load->model('Wedding/Wedding_model');
          $res = $CI->Wedding_model->getBackTranslation($lang,$id);
          return $res;
		  }else{
            return '';
		  }

		}

} if (!function_exists('getBackRoomTranslation')) {

		function getBackRoomTranslation($lang, $id) {
		  if(!empty($id)){
          $CI = get_instance();
          $CI->load->model('Hotels/Rooms_model');
          $res = $CI->Rooms_model->getBackTranslation($lang,$id);
          return $res;
		  }else{
            return '';
		  }

		}

}if (!function_exists('GetRoomQuantity')) {

		function GetRoomQuantity($id) {
		  if(!empty($id)){
          $CI = get_instance();
          $CI->load->model('Hotels/Rooms_model');
          $res = $CI->Rooms_model->getRoomQuantity($id);
          return $res;
		  }else{
            return '0';
		  }

		}

}if (!function_exists('getTypesTranslation')) {

		function getTypesTranslation($lang, $id) {
		  if(!empty($id)){
          $CI = get_instance();
          $CI->load->model('Wedding/Wedding_model');
          $res = $CI->Wedding_model->getTypesTranslation($lang,$id);
          return $res;
		  }else{
            return '';
		  }

		}

}if (!function_exists('isWeddingLocation')) {

		function isWeddingLocation($i, $locid, $weddingid) {
		  if(!empty($locid)){

          $CI = get_instance();
          $CI->load->model('Wedding/Wedding_model');
          $res = $CI->Wedding_model->isWeddingLocation($i, $locid, $weddingid);

          if($res > 0){

          	return $res;

          }else{

          	return $res;
          }


		  }else{
            return FALSE;
		  }

		}

}
