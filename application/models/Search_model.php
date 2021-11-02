<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Search_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_search_data($page = 1, $count = 1, $vars, $user_id)
   {
		 $where = '';
		 $where .= ($vars['lat'] && $vars['lng'])?' and ( ACOS( COS( RADIANS('.$vars['lat'].')) * COS( RADIANS(lat)) * COS( RADIANS(lng) - RADIANS('.$vars['lng'].')) + SIN( RADIANS('.$vars['lat'].')) * SIN( RADIANS(lat))) * 6371) < 30':'';

		 if ($vars['gender'] !== '') {
			 if ($vars['gender'] == 0)
				 $where_gender .= ' and a.searching_man > 0';
			 else if ($vars['gender'] == 1)
				 $where_gender .= ' and a.searching_woman > 0';
			 else if ($vars['gender'] == 2)
				 $where_gender .= ' and a.searching_man > 0 and a.searching_woman > 0';
		 }

		 $where .= $vars['max_price']?' and price <='.$vars['max_price']:'';
		 $where .= $vars['communal'] >= 0?' and communal ='.$vars['communal']:'';
		 $where .= $vars['home_type']?' and home_type ='.$vars['home_type']:'';
		 $where .= $vars['bed_type']?' and bed_type ='.$vars['bed_type']:'';
		 $where .= $vars['settle_date']?' and settle_date_from < "'.$vars['settle_date'].'"':'';
		 $where .= $vars['details']?' and details ="'.$vars['details'].'"':'';
		 $where .= strpos($vars['permitted_list'], '1')?' and cigarettes = 1':'';
		 $where .= strpos($vars['permitted_list'], '2')?' and pet_friendly = 1':'';
		 $where .= strpos($vars['permitted_list'], '3')?' and couple = 1':'';
		 $where .= strpos($vars['permitted_list'], '4')?' and opposite_sex = 1':'';

	 	 $start = ($page - 1)*$count;

     $query = 'SELECT a.id, a.user_id, a.lat, a.lng, a.title, a.home_type, a.searching_man, a.searching_woman, a.communal, a.living_man, a.living_woman, a.watch, a.renew_date, a.price, a.address,
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
																				  ) as l on l.ad_id = a.id
		 					 WHERE deleted = 0 and show_ad = 1 '.$where.'
							 ORDER BY '.$vars['order'].'
							 LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result_array();
   }

	 public function get_search_data_count($vars)
	 {
		 $where = '';
		 $where .= ($vars['lat'] && $vars['lng'])?' and ( ACOS( COS( RADIANS('.$vars['lat'].')) * COS( RADIANS(lat)) * COS( RADIANS(lng) - RADIANS('.$vars['lng'].')) + SIN( RADIANS('.$vars['lat'].')) * SIN( RADIANS(lat))) * 6371) < 30':'';

		 if ($vars['gender'] !== '') {
			 if ($vars['gender'] == 0)
				 $where_gender .= ' and a.searching_man > 0';
			 else if ($vars['gender'] == 1)
				 $where_gender .= ' and a.searching_woman > 0';
			 else if ($vars['gender'] == 2)
				 $where_gender .= ' and a.searching_man > 0 and a.searching_woman > 0';
		 }

		 $where .= $vars['max_price']?' and price <='.$vars['max_price']:'';
		 $where .= $vars['communal'] >= 0?' and communal ='.$vars['communal']:'';
		 $where .= $vars['home_type']?' and home_type ='.$vars['home_type']:'';
		 $where .= $vars['bed_type']?' and bed_type ='.$vars['bed_type']:'';
		 $where .= $vars['settle_date']?' and settle_date_from < "'.$vars['settle_date'].'"':'';
		 $where .= $vars['details']?' and details ="'.$vars['details'].'"':'';
		 $where .= strpos($vars['permitted_list'], '1')?' and cigarettes = 1':'';
		 $where .= strpos($vars['permitted_list'], '2')?' and pet_friendly = 1':'';
		 $where .= strpos($vars['permitted_list'], '3')?' and couple = 1':'';
		 $where .= strpos($vars['permitted_list'], '4')?' and opposite_sex = 1':'';

		 $query = 'SELECT count(*) as "count" FROM ads
		 					 WHERE deleted = 0 and show_ad = 1 '.$where;

		 return $this->db->query($query)->row();
	 }

   public function get_photos($id)
   {
     $query = 'SELECT name FROM photos
               WHERE active = 1 and ad_id = '.$id.'
               LIMIT 3';

     return $this->db->query($query)->result_array();
   }
}
