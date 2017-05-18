<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dohop_lib {

   function getUserName(){
   			$ci = & get_instance();
			$settings =  $ci->Settings_model->get_front_settings("flightsdohop");
			$result = new stdClass;
			if(!empty($settings)){
				$result->exists = TRUE;
				$result->username = $settings[0]->cid;
			}else{
				$result->exists = FALSE;
			}

			return $result;
   }

}
