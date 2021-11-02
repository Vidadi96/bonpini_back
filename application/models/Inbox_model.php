<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Inbox_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_inbox_data($user_id, $filter_status)
   {
     $where = $filter_status !== ''?' and i.status = '.$filter_status:'';

     $query = 'SELECT i.id, i.status, i.ad_id, a.price, a.communal, a.currency, m.message, m.was_online, m.user_id as "user_id",
		 									IF(m.user_id = '.$user_id.', 1, m.seen) as "seen",
											IF(isnull(c.count), 0, c.count) as "unseen_count",
											IF(u.id = '.$user_id.', b.id, u.id) as "to_user_id",
											IF(u.id = '.$user_id.', b.online, u.online) as "online",
		 									IF(u.id = '.$user_id.', b.name, u.name) as "name",
											IF(u.id = '.$user_id.', b.birthday, u.birthday) as "birthday",
											IF(u.id = '.$user_id.', b.img, u.img) as "img",
                      IF(ISNULL(m.last_date), i.create_date, m.last_date) as "date",
											IF(ISNULL(m.last_date), i.create_date, m.last_date) as "create_date" FROM inbox_id as i
               LEFT JOIN (SELECT id, price, communal, currency FROM ads) as a on a.id = i.ad_id
               LEFT JOIN (SELECT id, name, surname, birthday, img, online FROM users) as u on u.id = i.seller_id
							 LEFT JOIN (SELECT id, name, surname, birthday, img, online FROM users) as b on b.id = i.buyer_id
               LEFT JOIN (SELECT ii.inbox_id, ii.create_date as last_date, ii.message, ii.user_id, ii.seen, ii.was_online FROM inbox as ii
								 					LEFT JOIN (SELECT MAX(id) as "id", inbox_id FROM inbox
								            				 GROUP BY inbox_id) as ii1 on ii1.inbox_id = ii.inbox_id
								 					WHERE ii.id = ii1.id
							 					 ) as m on m.inbox_id = i.id
							 LEFT JOIN (SELECT count(inbox_id) as "count", inbox_id FROM inbox
							 						WHERE seen = 0
						 							GROUP BY inbox_id) as c on c.inbox_id = i.id
               WHERE i.status != 0 and (i.buyer_id = '.$user_id.' or i.seller_id = '.$user_id.')'.$where.'
               GROUP BY i.id
               ORDER BY m.last_date desc';

     return $this->db->query($query)->result_array();
   }

	 public function get_messages($page, $count, $id)
	 {
		 $start = ($page - 1)*$count;

		 $query = 'SELECT a.* FROM
			 					 (SELECT i.*, u.name, u.img as "user_img" FROM inbox as i
			 					 LEFT JOIN (SELECT id, name, img FROM users) as u on u.id = i.user_id
			 					 WHERE inbox_id = '.$id.'
								 ORDER BY create_date desc
								 LIMIT '.$start.', '.$count.') as a
							 ORDER BY a.create_date asc';

		 return $this->db->query($query)->result_array();
	 }

	 public function get_messages2($page, $count, $id)
	 {
		 $start = ($page - 1)*$count;

		 $query = 'SELECT a.* FROM
			 					 (SELECT i.* FROM inbox as i
			 					 WHERE inbox_id = '.$id.'
								 ORDER BY create_date desc
								 LIMIT '.$start.', '.$count.') as a
							 ORDER BY a.create_date asc';

		 return $this->db->query($query)->result_array();
	 }

	 public function get_obj($id)
	 {
		 $query = 'SELECT i.*, u.name, u.img as "user_img" FROM inbox as i
		 					 LEFT JOIN (SELECT id, name, img FROM users) as u on u.id = i.user_id
							 WHERE i.id ='.$id;

		 return $this->db->query($query)->row();
	 }

	 public function check_seen($id, $user_id)
	 {
		 $query = 'UPDATE inbox
							 SET seen = 1
							 WHERE inbox_id = '.$id.' and user_id != '.$user_id.' and seen = 0';

		 $this->db->query($query);
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

	 public function check_if_seen($user_id, $inbox_id)
	 {
		 $query = 'SELECT * FROM open_inbox as oi
		 					 LEFT JOIN (SELECT IF(buyer_id = '.$user_id.', seller_id, buyer_id) as "user_id", id FROM inbox_id
							 						WHERE id = '.$inbox_id.') as ii on ii.id = oi.inbox_id
							 WHERE oi.inbox_id = '.$inbox_id.' and oi.user_id = ii.user_id';

		 return $this->db->query($query)->row();
	 }

	 public function set_inbox_online($user_id)
	 {
		 $query = 'UPDATE inbox as i
							 LEFT JOIN (SELECT id, if(buyer_id = '.$user_id.', seller_id, buyer_id) as "user_id" FROM inbox_id
							 						WHERE buyer_id = '.$user_id.' or seller_id = '.$user_id.'
												 ) as ii on ii.id = i.inbox_id
							 SET was_online = 1
							 WHERE i.inbox_id = ii.id and i.was_online = 0 and i.user_id = ii.user_id';

		 $this->db->query($query);
	 }

	 public function check_online($user_id, $inbox_id)
	 {
		 $query = 'SELECT u.online FROM users as u
		 					 LEFT JOIN (SELECT IF(buyer_id = '.$user_id.', seller_id, buyer_id) as "user_id", id FROM inbox_id
							 						WHERE id = '.$inbox_id.') as ii on ii.user_id = u.id
							 WHERE ii.user_id is not null';

		 return $this->db->query($query)->row();
	 }

	 public function check_contact($user_id, $seller_id)
	 {
		 $query = 'SELECT up.id FROM user_package as up
		 					 LEFT JOIN (SELECT user_package_id, seller_id FROM user_package_contacts
							 					  WHERE seller_id = '.$seller_id.') as upc on upc.user_package_id = up.id
		 					 WHERE user_id = '.$user_id.' and upc.user_package_id is not null';

		 return $this->db->query($query)->row();
	 }
}
