<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Blogs_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_all_blogs($page = 1, $count = 1, $where)
   {
     $start = ($page - 1)*$count;

     $query = 'SELECT * FROM blog
		 					 WHERE 1'.$where.'
		 					 ORDER BY id desc
               LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result();
   }

   public function get_all_blogs_count()
   {
     $query = 'SELECT count(*) as "count" FROM blog';

     return $this->db->query($query)->row();
   }
}
