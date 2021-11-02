<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Main_model extends CI_Model
{
	 public function __construct()
   {
     parent::__construct();
   }

   public function getMenuItems()
   {
     $query = "SELECT * FROM menu_items
		 					 ORDER BY title ASC";

     return $this->db->query($query)->result();
   }

	 public function get_premium_blocks()
	 {
     $query = 'SELECT id, lat, lng, title, home_type, communal, living_man, living_woman, watch, renew_date, price FROM ads
		 					 WHERE 1
							 ORDER BY create_date desc
							 LIMIT 4';

     return $this->db->query($query)->result_array();
	 }

	 public function get_photos($id)
   {
     $query = 'SELECT name FROM photos
               WHERE active = 1 and ad_id = '.$id.'
               LIMIT 3';

     return $this->db->query($query)->result_array();
   }

	 public function get_ads_count()
	 {
		 $query = 'SELECT count(*) as "count", sum(watch) as "watch" FROM ads';
		 return $this->db->query($query)->row();
	 }
}
