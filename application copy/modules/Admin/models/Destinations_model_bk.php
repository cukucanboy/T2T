<?php

class Destinations_model extends CI_Model {
	private $userloggedin;
	private $isSuperAdmin;

		function __construct() {
// Call the Model constructor
				parent :: __construct();
				$this->userloggedin = $this->session->userdata('pt_logged_id');
   				$this->isSuperAdmin = $this->session->userdata('pt_logged_super_admin');
		}

		//get destinations list admin panel
		function getDestinationBackend($id){

			$this->db->where('status','yes');
			$this->db->order_by('id','desc');
			return $this->db->get('pt_destinations')->result();

		}

		//get details of Destination
		function getDestinationDetails($id, $lang = null){
			$this->db->where('id',$id);

			if(!empty($this->userloggedin)){

			if(!$this->isSuperAdmin){
				$this->db->where('user',$this->userloggedin);
				$this->db->or_where('user',NULL);
			}else{
				$user = NULL;
			}
		}

			$result = $this->db->get('pt_destinations')->result();

			$response = new stdClass;
			$response->country = $result[0]->country;
			if(!empty($result[0]->destination)){
				$response->isValid = TRUE;
			}else{
				$response->isValid = FALSE;
			}

			if(empty($lang) || $lang == DEFLANG){

			$response->city = $result[0]->destinations;

			}else{

			$this->db->where('destinations_id',$id);
			$this->db->where('trans_lang',$lang);
			$Transresult = $this->db->get('pt_destinations_translation')->result();
			if(empty($Transresult[0]->destinations_name)){

			$response->city = $result[0]->destination;

			}else{

			$response->city = $Transresult[0]->destinations_name;

			}


			}

			$response->latitude = $result[0]->latitude;
			$response->longitude = $result[0]->longitude;
			$response->status = $result[0]->status;
			$response->id = $id;
			return $response;

		}

		// add destination
		function addDestinations() {
			if(!$this->isSuperAdmin){
				$user = $this->userloggedin;
			}else{
				$user = NULL;
			}


				$data = array(
					'destinations' => $this->input->post('destination'),
	'destination_image' => 'xxx',
				//	'destination_image' => $this->input->post('image'),
					'country' => $this->input->post('country'),
					'latitude' => $this->input->post('latitude'),
					'longitude' => $this->input->post('longitude'),
					'user' => $user,
					'status' => $this->input->post('status')
					);
				$this->db->insert('pt_destinations', $data);
                $locid = $this->db->insert_id();
                $this->updateDestinationTranslation($this->input->post('translated'),$locid);
		}

		// update Destination
		function updateDestination($locid) {

				$data = array(
					'destinations' => $this->input->post('destination'),

	'destination_image' => 'xxx',


					//	'destination_image' => $this->input->post('image'),
					'country' => $this->input->post('country'),
					'latitude' => $this->input->post('latitude'),
					'longitude' => $this->input->post('longitude'),
					'status' => $this->input->post('status')
					);

				$this->db->where('id', $locid);
				$this->db->update('pt_destinations', $data);

                $this->updateDestinationTranslation($this->input->post('translated'),$locid);
		}

		//delete location
		function delete_loc($id){
			$this->db->where('destinations_id', $id);
			$this->db->delete('pt_destinations_translation');
			$this->db->where('id', $id);
			$this->db->delete('pt_destinations');

		}

		//update destination translation

	   function updateDestinationTranslation($postdata,$id) {

       foreach($postdata as $lang => $val){
		     if(array_filter($val)){
		        $name = $val['name'];

                $transAvailable = $this->getDestinationsTranslation($lang,$id);

                if(empty($transAvailable)){
                 $data = array(
                'destinations_name' => $name,
								'destination_image' => $image,
                'destinations_id' => $id,
                'trans_lang' => $lang
                );
				$this->db->insert('pt_destinations_translation', $data);

                }else{

                 $data = array(
                'destinations_name' => $name
                );
				$this->db->where('destinations_id', $id);
				$this->db->where('trans_lang', $lang);
			   $this->db->update('pt_destinations_translation', $data);

              }


              }

                }
		}


		function getDestinationTranslation($lang,$id){

            $this->db->where('trans_lang',$lang);
            $this->db->where('destinations_id',$id);
            return $this->db->get('pt_destinations_translation')->result();

        }

        function isUserDestination($id, $locid){
        	$this->db->where('user',$id);
        	$this->db->where('id',$locid);
        	$nums = $this->db->get('pt_destinations')->num_rows();

        	if($nums > 0){
        		return TRUE;
        	}else{
        		return FALSE;
        	}
        }

        function alreadyExists(){

        	$this->db->where('latitude',$this->input->post('latitude'));
        	$this->db->where('longitude',$this->input->post('longitude'));
        	$nums = $this->db->get('pt_destinations')->num_rows();

        	if($nums > 0){
        		$this->session->set_flashdata('msg', 'Destinations Already Exists');
        		return TRUE;
        	}else{
        		return FALSE;
        	}
        }

}
