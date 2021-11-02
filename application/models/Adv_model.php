<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Adv_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_details($user_id, $lang)
   {
		 $query0 = 'SELECT details FROM last_user_ad WHERE user_id = '.$user_id;
		 $value = $this->db->query($query0)->row();

		 if (@$value && @$value->details) {
			 $value = $value->details;
			 $where = 'id in ('.$value.')';
			 $query = 'SELECT d.id, d.name_'.$lang.' as "name", d.img as "image", IF(ISNULL(d2.id), 0.3, 1) as "clicked" FROM details as d
	               LEFT JOIN (SELECT id FROM details WHERE '.$where.') as d2 on d2.id = d.id
								 ORDER BY d.order_by asc';
		 } else {
			 $query = 'SELECT d.id, d.name_'.$lang.' as "name", d.img as "image",  0.3 as "clicked" FROM details as d
			 					 ORDER BY d.order_by asc';
		 }

     return $this->db->query($query)->result();
   }

	 public function get_last_user_ad_data()
	 {
		 $query = 'SELECT id, name, location FROM photos
		 					 WHERE ad_id = 0 and user_id = '.$user_id;

		 return $this->db->query($query)->result_array();
	 }

	 public function publish_adv($user_id)
	 {
		 $query = 'INSERT INTO ads (user_id, address, metro, simple_address, lat, lng,	home_type, living_man, living_woman, lived_busyness, searching_man, searching_woman, build_type, number_of_floors, on_which_floor, details, cigarettes, pet_friendly, couple, opposite_sex, bed_type, settle_date_from, settle_date_to, min_rent_term, price, currency, communal, communal_about, youtube, title, description, age_from, age_to, busyness, create_date, renew_date, premium, status, verified)
		 					 SELECT user_id, address, metro, simple_address, lat, lng,	home_type, living_man, living_woman, lived_busyness, searching_man, searching_woman, build_type, number_of_floors, on_which_floor, details, cigarettes, pet_friendly, couple, opposite_sex, bed_type, settle_date_from, settle_date_to, min_rent_term, price, currency, communal, communal_about, youtube, title, description, age_from, age_to, busyness, create_date, renew_date, premium, status, verified FROM last_user_ad
							 WHERE user_id = '.$user_id;

		 $query2 = 'DELETE FROM last_user_ad WHERE user_id = '.$user_id;

		 $result = $this->db->query($query);

		 if (@$result) {
			 $this->db->query($query2);
			 return $result;
		 } else {
			 return 0;
		 }
	 }

	 public function get_adv_data($id)
	 {
		 $query = 'SELECT a.*, bt.name_1 as "bed_type_name" FROM ads as a
		 					 LEFT JOIN (SELECT id, active FROM users) as u on u.id = a.user_id
							 LEFT JOIN (SELECT id, name_1 FROM bed_type) as bt on bt.id = a.bed_type
							 WHERE u.active = 1 and a.id = '.$id;

		 return $this->db->query($query)->result_array();
	 }

	 public function get_details2($in)
	 {
		 $query = 'SELECT name_1, img FROM details WHERE id in ('.$in.')
		 					 ORDER BY order_by asc';

		 return $this->db->query($query)->result_array();
	 }

	 public function get_with_in($table, $in, $select)
	 {
		 $query = 'SELECT '.$select.' FROM '.$table.' WHERE id in ('.$in.')';
		 return $this->db->query($query)->result_array();
	 }

	 public function get_similar($vars, $id, $user_id)
	 {
		 $select = 'SELECT a.id, a.user_id, a.address, a.living_man, a.living_woman, a.currency, a.lat, a.lng, a.title, a.home_type, a.watch, a.renew_date, a.price,
		 									 if(isnull(m.last_check), 0, m.last_check) as "last_check", if(isnull(l.check_sended), 0, 1) as "check_sended" FROM ads as a
							 	LEFT JOIN (SELECT n.ad_id, n.date as "last_check" FROM notifications as n
												 	 LEFT JOIN (SELECT MAX(id) as "id", ad_id FROM notifications
													 						WHERE type = 1 and response in (1,2)
																		  GROUP BY ad_id) as n1 on n1.ad_id = n.ad_id
												   WHERE n.id = n1.id
												 ) as m on m.ad_id = a.id
							  LEFT JOIN (SELECT n.ad_id, n.create_date as "check_sended" FROM notifications as n
													 LEFT JOIN (SELECT MAX(id) as "id", ad_id FROM notifications
																		  WHERE type = 1 and response = 0 and user_id = '.$user_id.'
																		  GROUP BY ad_id) as n1 on n1.ad_id = n.ad_id
													 WHERE n.id = n1.id
												  ) as l on l.ad_id = a.id';

		 $where_gender = ' and a.searching_man = '.(int) $vars->searching_man.' and a.searching_woman = '.(int) $vars->searching_woman;

     $query1 = $select.' WHERE a.show_ad = 1 and a.id != '.$id.' and a.home_type = '.(int)$vars->home_type.' and a.price = '.(float)$vars->price.$where_gender.' and a.busyness = '.(int)$vars->busyness.'
							 ORDER BY a.create_date desc
							 LIMIT 3';

     $result1 = $this->db->query($query1)->result_array();

		 if (count($result1) > 2) {
			 return $result1;
		 } else {
			 $not = '';
			 foreach ($result1 as $row)
				 $not .= $row['id'].',';

			 $where = $not?' and a.id not in ('.substr($not, 0, (strlen($not) - 1)).')':'';

			 $query2 = $select.' WHERE a.show_ad = 1 and a.id != '.$id.' and a.home_type = '.(int)$vars->home_type.' and a.price = '.(float)$vars->price.$where_gender.$where.'
								  ORDER BY a.create_date desc
								  LIMIT 3';

	     $result2 = $this->db->query($query2)->result_array();

			 $result2 = array_merge($result1, $result2);

			 if (count($result2) > 2) {
				 return $result2;
			 } else {
				 $not = '';
				 foreach ($result2 as $row)
					 $not .= $row['id'].',';

				 $where = $not?' and a.id not in ('.substr($not, 0, (strlen($not) - 1)).')':'';

				 $query3 = $select.' WHERE a.show_ad = 1 and a.id != '.$id.' and a.home_type = '.(int)$vars->home_type.' and a.price = '.(float)$vars->price.$where.'
									  ORDER BY a.create_date desc
									  LIMIT 3';

		     $result3 = $this->db->query($query3)->result_array();

				 $result3 = array_merge($result2, $result3);

				 if (count($result3) > 2) {
					 return $result3;
				 } else {
					 $not = '';
					 foreach ($result3 as $row)
						 $not .= $row['id'].',';

					 $where = $not?' and a.id not in ('.substr($not, 0, (strlen($not) - 1)).')':'';

					 $query4 = $select.' WHERE a.show_ad = 1 and a.id != '.$id.' and a.home_type = '.(int)$vars->home_type.$where.'
										  ORDER BY a.create_date desc
										  LIMIT 3';

			     $result4 = $this->db->query($query4)->result_array();

					 $result4 = array_merge($result3, $result4);

					 return $result4;
				 }
			 }
		 }
	 }

	 public function get_photos($id)
   {
     $query = 'SELECT name FROM photos
               WHERE active = 1 and ad_id = '.$id.'
               LIMIT 3';

     return $this->db->query($query)->result_array();
   }

	 public function get_premium($id)
	 {
		 $query = 'SELECT cp.name as "image", a.id, a.title, a.home_type, a.communal, a.living_man, a.living_woman, a.watch, a.renew_date, a.price FROM ads as a
							 LEFT JOIN (SELECT cp1.ad_id, cp1.name FROM photos as cp1
												 LEFT JOIN (SELECT ad_id, min(location) as location FROM photos WHERE active = 1 and deleted = 0 GROUP BY ad_id) as cp2 on cp2.ad_id = cp1.ad_id
												 WHERE cp1.location = cp2.location) as cp on cp.ad_id = a.id
							 WHERE a.id != '.$id.'
							 GROUP BY a.id
							 ORDER BY create_date desc
							 LIMIT 5';

		 return $this->db->query($query)->result_array();
	 }

	 public function refresh_watch($id)
	 {
		 $query = 'UPDATE ads AS a
		 					 LEFT JOIN ads as b on b.id = a.id
							 SET a.watch = (b.watch + 1)
							 WHERE a.id = '.(int)$id;

		 $this->db->query($query);
	 }

	 public function contact_with_check($user_id, $seller_id)
	 {
		 $query = 'SELECT up.id FROM user_package as up
		 					 LEFT JOIN (SELECT user_package_id, seller_id FROM user_package_contacts
							 					  WHERE seller_id = '.$seller_id.') as upc on upc.user_package_id = up.id
		 					 WHERE user_id = '.$user_id.' and upc.user_package_id is not null';

		 return $this->db->query($query)->row();
	 }

	 public function get_all_ads($page = 1, $count = 1, $show, $user_id)
   {
	 	 $start = ($page - 1)*$count;

     $query = 'SELECT * FROM ads
		 					 WHERE deleted = 0 and show_ad = '.$show.' and user_id = '.$user_id.'
							 ORDER BY create_date desc
							 LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result_array();
   }

	 public function get_all_ads_count($show, $user_id)
	 {
		 $query = 'SELECT count(*) as "count" FROM ads
		 					 WHERE deleted = 0 and show_ad = '.$show.' and user_id = '.$user_id;

		 return $this->db->query($query)->row();
	 }
}
