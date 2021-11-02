<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Profile_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

	 public function get_with_in($table, $in, $select)
	 {
		 $query = 'SELECT '.$select.' FROM '.$table.' WHERE id in ('.$in.')';

		 return $this->db->query($query)->result();
	 }
}
