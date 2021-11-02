<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class My_controller_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

	 public function get_notifications($user_id, $page = 1, $count = 10)
	 {
		 $start = ($page - 1)*10;

		 $query = 'SELECT n.*, u.img, u.name, u.surname FROM notifications as n
							 LEFT JOIN (SELECT id, img, name, surname FROM users) as u on u.id = n.user_id
							 WHERE n.to_user = '.$user_id.'
							 ORDER BY n.id desc
							 LIMIT '.$start.', '.$count;

		 return $this->db->query($query)->result_array();
	 }

	 public function get_notifications_count($user_id)
	 {
		 $query = 'SELECT count(*) as "count" FROM notifications as n
							 WHERE n.to_user = '.$user_id;

		 return $this->db->query($query)->row();
	 }

	 public function get_seen_or_not($user_id)
	 {
		 $query = 'SELECT IF(m.user_id = '.$user_id.', 1, m.seen) as "seen", m.last_date FROM inbox_id as ii
							 LEFT JOIN (SELECT ii.inbox_id, ii.user_id, ii.seen, ii.create_date as "last_date" FROM inbox as ii
							            LEFT JOIN (SELECT MAX(id) as "id", inbox_id FROM inbox
							                       GROUP BY inbox_id) as ii1 on ii1.inbox_id = ii.inbox_id
							            WHERE ii.id = ii1.id
							           ) as m on m.inbox_id = ii.id
							 WHERE status != 0 and m.seen = 0 and (ii.buyer_id = '.$user_id.' or ii.seller_id = '.$user_id.')
							 ORDER BY m.last_date desc';

			return $this->db->query($query)->result();
	 }
}
