<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Packages_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_package_data($user_id)
   {
     $query = 'SELECT p.*, if(isnull(up.package_id), 0, 1) as "active" FROM packages as p
               LEFT JOIN (SELECT package_id FROM user_package
                          WHERE user_id = '.$user_id.' and active = 1 and banned = 0 and deleted = 0) as up on up.package_id = p.id
               ORDER BY p.id';

     return $this->db->query($query)->result();
   }

   public function get_packages()
   {
     $query = 'SELECT p.*, u.name, u.surname, u.phone FROM user_package as p
               LEFT JOIN (SELECT id, name, surname, phone FROM users) as u on u.id = p.user_id
               ORDER BY create_date desc
               LIMIT 30';

      return $this->db->query($query)->result();
   }

   public function get_filtered_packages($start, $count, $id, $phone, $date1, $date2)
   {
     $where = '';
     $where .= $id?' and p.id = '.$id:'';
     $where .= $phone?' and u.phone like "%'.$phone.'%"':'';
     $where .= $date1?' and p.create_date > "'.$date1.'"':'';
     $where .= $date2?' and p.create_date < "'.$date2.'"':'';

     $query = 'SELECT p.*, u.name, u.surname, u.phone FROM user_package as p
               LEFT JOIN (SELECT id, name, surname, phone FROM users) as u on u.id = p.user_id
               WHERE 1'.$where.'
               ORDER BY create_date desc
               LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result();
   }

   public function get_filtered_packages_count($id, $phone, $date1, $date2)
   {
     $where = '';
     $where .= $id?' and p.id = '.$id:'';
     $where .= $phone?' and u.phone like "%'.$phone.'%"':'';
     $where .= $date1?' and p.create_date > "'.$date1.'"':'';
     $where .= $date2?' and p.create_date < "'.$date2.'"':'';

     $query = 'SELECT count(*) as "count" FROM user_package as p
               LEFT JOIN (SELECT id, phone FROM users) as u on u.id = p.user_id
               WHERE 1'.$where;

      return $this->db->query($query)->row();
   }
}
