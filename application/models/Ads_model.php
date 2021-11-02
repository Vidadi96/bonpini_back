<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ads_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function get_all_ads($page = 1, $count = 1, $vars)
   {
		 $where = '';
     $where .= $vars['id']?' and a.id = '.$vars['id']:'';
		 $where .= ($vars['lat'] && $vars['lng'])?' and ( ACOS( COS( RADIANS('.$vars['lat'].')) * COS( RADIANS(a.lat)) * COS( RADIANS(a.lng) - RADIANS('.$vars['lng'].')) + SIN( RADIANS('.$vars['lat'].')) * SIN( RADIANS(a.lat))) * 6371) < 30':'';

		 if ($vars['gender'] !== '') {
			 if ($vars['gender'] == 0)
			 	 $where .= ' and a.searching_man > 0';
			 else if ($vars['gender'] == 1)
			 	 $where .= ' and a.searching_woman > 0';
			 else if ($vars['gender'] == 2)
			 	 $where .= ' and a.searching_man > 0 and a.searching_woman > 0';
		 }

		 $where .= $vars['max_price']?' and a.price <='.$vars['max_price']:'';
		 $where .= $vars['communal'] >= 0?' and a.communal ='.$vars['communal']:'';
		 $where .= $vars['home_type']?' and a.home_type ='.$vars['home_type']:'';
		 $where .= $vars['bed_type']?' and a.bed_type ='.$vars['bed_type']:'';
		 $where .= $vars['settle_date']?' and a.settle_date_from < "'.$vars['settle_date'].'"':'';
		 $where .= $vars['details']?' and a.details ="'.$vars['details'].'"':'';
		 $where .= strpos($vars['permitted_list'], '1')?' and a.cigarettes = 1':'';
		 $where .= strpos($vars['permitted_list'], '2')?' and a.pet_friendly = 1':'';
		 $where .= strpos($vars['permitted_list'], '3')?' and a.couple = 1':'';
		 $where .= strpos($vars['permitted_list'], '4')?' and a.opposite_sex = 1':'';

	 	 $start = ($page - 1)*$count;

     $query = 'SELECT a.id, a.price, u.username, u.id as "user_id", cp.name, u.active FROM ads as a
               LEFT JOIN (SELECT id, username, active FROM users) as u on u.id = a.user_id
               LEFT JOIN (SELECT cp1.ad_id, cp1.name FROM photos as cp1
                          LEFT JOIN (SELECT ad_id, min(location) as location FROM photos WHERE active = 1 and deleted = 0 GROUP BY ad_id) as cp2 on cp2.ad_id = cp1.ad_id
                          WHERE cp1.location = cp2.location) as cp on cp.ad_id = a.id
		 					 WHERE a.deleted = 0'.$where.'
               GROUP BY a.id
							 ORDER BY a.id desc
							 LIMIT '.$start.', '.$count;

     return $this->db->query($query)->result_array();
   }

	 public function get_all_ads_count($vars)
	 {
     $where = '';
		 $where .= $vars['id']?' and a.id = '.$vars['id']:'';
		 $where .= ($vars['lat'] && $vars['lng'])?' and ( ACOS( COS( RADIANS('.$vars['lat'].')) * COS( RADIANS(a.lat)) * COS( RADIANS(a.lng) - RADIANS('.$vars['lng'].')) + SIN( RADIANS('.$vars['lat'].')) * SIN( RADIANS(a.lat))) * 6371) < 30':'';

		 if ($vars['gender'] !== '') {
			 if ($vars['gender'] == 0)
			 	 $where .= ' and a.searching_man > 0';
			 else if ($vars['gender'] == 1)
			 	 $where .= ' and a.searching_woman > 0';
			 else if ($vars['gender'] == 2)
			 	 $where .= ' and a.searching_man > 0 and a.searching_woman > 0';
		 }

		 $where .= $vars['max_price']?' and a.price <='.$vars['max_price']:'';
		 $where .= $vars['communal'] >= 0?' and a.communal ='.$vars['communal']:'';
		 $where .= $vars['home_type']?' and a.home_type ='.$vars['home_type']:'';
		 $where .= $vars['bed_type']?' and a.bed_type ='.$vars['bed_type']:'';
		 $where .= $vars['settle_date']?' and a.settle_date_from < "'.$vars['settle_date'].'"':'';
		 $where .= $vars['details']?' and a.details ="'.$vars['details'].'"':'';
		 $where .= strpos($vars['permitted_list'], '1')?' and a.cigarettes = 1':'';
		 $where .= strpos($vars['permitted_list'], '2')?' and a.pet_friendly = 1':'';
		 $where .= strpos($vars['permitted_list'], '3')?' and a.couple = 1':'';
		 $where .= strpos($vars['permitted_list'], '4')?' and a.opposite_sex = 1':'';

		 $query = 'SELECT count(*) as "count" FROM ads as a
		 					 WHERE a.deleted = 0'.$where;

		 return $this->db->query($query)->row();
	 }

	 public function get_details($id)
   {
		 $query0 = 'SELECT details FROM ads WHERE id = '.$id;
		 $value = $this->db->query($query0)->row();

		 if (@$value && @$value->details) {
			 $value = $value->details;
			 $where = 'id in ('.$value.')';
			 $query = 'SELECT d.id, d.name_1 as "name", d.img as "image", IF(ISNULL(d2.id), 0.3, 1) as "clicked" FROM details as d
	               LEFT JOIN (SELECT id FROM details WHERE '.$where.') as d2 on d2.id = d.id
								 ORDER BY d.order_by asc';
		 } else {
			 $query = 'SELECT d.id, d.name_1 as "name", d.img as "image",  0.3 as "clicked" FROM details as d
			 					 ORDER BY d.order_by asc';
		 }

     return $this->db->query($query)->result();
   }
}
