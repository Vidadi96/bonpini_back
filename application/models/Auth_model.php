<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Auth_model extends CI_Model
{
	function __construct()
  {
      parent::__construct();
  }

  function insert_user($vars)
  {
    $query = 'INSERT INTO users (username, email, user_type, password, create_date)
              VALUES ("'.$vars['username'].'", "'.$vars['email'].'", '.$vars['user_type'].', "'.$vars['password'].'", "'.$vars['create_date'].'")';

    return $this->db->query($query);
  }

  function check_username($username)
  {
    $query = 'SELECT username FROM users WHERE username = "'.$username.'"';
    return $this->db->query($query)->row();
  }

	function add_to_blacklist($token)
	{
		$query = 'INSERT INTO blacklist (token) VALUES ("'.$token.'")';
		return $this->db->query($query);
	}

	function switch_func($user_id, $user_type)
	{
		$query = 'INSERT INTO users (username, password, email, name, surname, birthday, gender, work_status, languages, personality, lifestyle, music, sport, movie, img, phone, facebook, google, user_type, user_type_now, create_date, active, online)
							SELECT username, password, email, name, surname, birthday, gender, work_status, languages, personality, lifestyle, music, sport, movie, img, phone, facebook, google, '.$user_type.', '.$user_type.', create_date, active, online FROM users as u
							WHERE u.id = '.$user_id;

		$this->db->query($query);

		return $this->db->insert_id();
	}

}
