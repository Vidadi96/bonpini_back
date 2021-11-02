<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Universal_model extends CI_Model
{
	function __construct()
  {
      parent::__construct();
  }
  function get_all_item($table_name, $is_array=0, $deleted=FALSE)
	{
		$this->db->select("*");
		$this->db->from($table_name);
		if($deleted)
		$this->db->where("deleted", 0);
		if ($is_array)
			return $this->db->get()->result_array();
		else
			return $this->db->get()->result();
	}
  function get_item($table_name, $where)
	{
		$this->db->select("*");
		$this->db->from($table_name);
		if($where)
		$this->db->where($where);
		return $this->db->get()->row();
	}
  function get_item_where($table_name, $where, $select)
  {
      $this->db->select($select);
      $this->db->from($table_name);
      $this->db->where($where);

      return $this->db->get()->row();
  }
	function select_result($table_name, $select)
  {
    $this->db->select($select);
    $this->db->from($table_name);
    return $this->db->get()->result();
  }
  function get_more_item($table_name, $where, $isarray=0, $order_by=false)
	{
		$this->db->select("*");
		$this->db->from($table_name);
		if($where)
		$this->db->where($where);

		if($order_by)
		$this->db->order_by($order_by[0], $order_by[1]);

		if($isarray)
			return $this->db->get()->result_array();
		else
			return $this->db->get()->result();
	}

	function get_more_item_select($table_name, $select, $where, $isarray=0, $order_by=false)
	{
		$this->db->select($select);
		$this->db->from($table_name);
		if($where)
			$this->db->where($where);

		if($order_by)
			$this->db->order_by($order_by[0], $order_by[1]);

		if($isarray)
			return $this->db->get()->result_array();
		else
			return $this->db->get()->result();
	}

	function get_more_item_select_row($table_name, $select, $where, $order_by=false)
	{
		$this->db->select($select);
		$this->db->from($table_name);
		if ($where)
			$this->db->where($where);

		if ($order_by)
			$this->db->order_by($order_by[0], $order_by[1]);

		return $this->db->get()->row();
	}

	function item_edit_save($table_name, $where, $vars)
	{
		$this->db->where($where);
		$this->db->update($table_name, $vars);
		return $this->db->get_where($table_name, $where)->row();
	}

	function update_table($table_name, $where, $vars)
	{
		$this->db->where($where);
		return $this->db->update($table_name, $vars);
	}

	function item_edit_save_where($vars, $where, $table_name)
	{
		$this->db->where($where);
		$this->db->update($table_name, $vars);
		return $this->db->get_where($table_name, $where)->row();
	}

	function delete_relations($cat_id)
	{
		$prefix = $this->db->dbprefix;
		$query = 'delete FROM '.$prefix.'relations WHERE rel_type_id=2 and rel_item_id = '.$cat_id;

		$this->db->query($query);
	}

	function add_item($vars, $table_name)
	{
		$this->db->insert($table_name, $vars);
		return $this->db->insert_id();
	}
	function add_more_item($vars, $table_name)
	{
		return $this->db->insert_batch($table_name, $vars);
	}
	function add_to_trash_item($id, $table_name)
	{
		$this->db->where("id", $id);
		return $this->db->update($table_name, array("deleted"=>1));
	}
	function delete_item($where, $table_name)
	{
		$this->db->where($where);
		return $this->db->delete($table_name);
	}
	function delete_item_where($where, $table_name)
	{
		$this->db->where($where);
		return $this->db->delete($table_name);
	}
	function get_more_item_where($where, $table_name)
	{
		$this->db->select("*");
		$this->db->where($where);
		$this->db->from($table_name);
		return $this->db->get()->result();
	}

	function run_insert_query($sql)
	{
		$this->db->query($sql);
    return $this->db->insert_id();
	}











}
