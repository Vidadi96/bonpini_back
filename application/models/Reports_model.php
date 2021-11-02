<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Reports_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_all_reports($page = 1, $count = 1, $vars)
   {
		 $where = '';
		 $where .= $vars['type'] >= 0?' and r.type ='.$vars['type']:'';
		 $where .= $vars['user_name']?' and u.username like "%'.$vars['user_name'].'%"':'';

	 	 $start = ($page - 1)*$count;

     $query = 'SELECT u.username as "user_name", ru.username as "reported_user_name", r.* FROM report as r
               LEFT JOIN (SELECT id, username FROM users) as u on u.id = r.user_id
               LEFT JOIN (SELECT id, username FROM users) as ru on ru.id = r.reported_user_id
		 					 WHERE 1'.$where.'
							 ORDER BY create_date desc
							 LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result_array();
   }

	 public function get_all_reports_count($vars)
	 {
     $where = '';
		 $where .= $vars['type'] >= 0?' and r.type ='.$vars['type']:'';
     $where .= $vars['user_name']?' and u.username like "%'.$vars['user_name'].'%"':'';

		 $query = 'SELECT count(*) as "count" FROM report as r
               LEFT JOIN (SELECT id, username FROM users) as u on u.id = r.user_id
		 					 WHERE 1'.$where;

		 return $this->db->query($query)->row();
	 }
}
