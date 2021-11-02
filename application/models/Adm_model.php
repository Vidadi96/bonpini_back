<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Adm_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_user_list()
   {
     $query = 'SELECT * FROM admin_users
               WHERE deleted = 0
               LIMIT 20';

     return $this->db->query($query)->result();
   }

	 public function get_user_type_day()
	 {
		 $query = 'SELECT * FROM user_type_day
		 					 ORDER BY day desc
							 LIMIT 30';

		 return $this->db->query($query)->result();
	 }
}
