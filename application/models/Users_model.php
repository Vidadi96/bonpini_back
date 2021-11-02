<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Users_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_users()
   {
     $query = 'SELECT id, username, email, img, user_type, active FROM users
               ORDER BY username asc
               LIMIT 30';

      return $this->db->query($query)->result();
   }

   public function get_filtered_users($start, $count, $username, $email, $phone, $user_type, $active)
   {
     $where = '';
     $where .= $username?' and username like "%'.$username.'%"':'';
     $where .= $email?' and email like "%'.$email.'%"':'';
     $where .= $phone?' and phone like "%'.$phone.'%"':'';
     $where .= $user_type == 2?'':' and user_type = '.$user_type;
     $where .= $active == 2?'':' and active = '.$active;

     $query = 'SELECT id, username, email, img, user_type, active FROM users
               WHERE 1'.$where.'
               ORDER BY username asc
               LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result();
   }

   public function get_filtered_users_count($username, $email, $phone, $user_type, $active)
   {
     $where = '';
     $where .= $username?' and username like "%'.$username.'%"':'';
     $where .= $email?' and email like "%'.$email.'%"':'';
     $where .= $phone?' and phone like "%'.$phone.'%"':'';
     $where .= $user_type == 2?'':' and user_type = '.$user_type;
     $where .= $active == 2?'':' and active = '.$active;

     $query = 'SELECT count(*) as "count" FROM users
               WHERE 1'.$where;

      return $this->db->query($query)->row();
   }
}
