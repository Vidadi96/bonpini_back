<?php

  class Faq extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('faq_model');
        $this->load->model('universal_model');
      }

      public function get_faq()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $faq = $this->universal_model->get_more_item('faq', '1=1', 0, array('order_by', 'asc'));
        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'faq' => $faq,
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function delete_faq()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $result = $this->universal_model->delete_item(array('id' => $id), 'faq');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function edit_faq()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array(
            'title' => $filtered_data['title'],
            'answer' => $filtered_data['answer'],
            'order_by' => (int) $filtered_data['order_by']
          );

          $result = $this->universal_model->update_table('faq', array('id' => (int) $filtered_data['id']), $vars);

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else
          echo json_encode(array('status' => 0));
      }

      public function faq_adm()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $faqs = $this->universal_model->get_more_item_select('faq', '*, 0 as "edit"', '1=1', 0, array('order_by', 'asc'));

          $vars = array(
            'faqs' => $faqs
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function add_faq()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array(
            'title' => $filtered_data['title'],
            'answer' => $filtered_data['answer'],
            'order_by' => (int) $filtered_data['order_by']
          );

          $result = $this->universal_model->add_item($vars, 'faq');

          if ($result) {
            $data = $this->universal_model->get_item_where('faq', array('id' => $result), '*, 0 as "edit"');
            echo json_encode(array('status' => 1, 'data' => $data));
          } else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else
          echo json_encode(array('status' => 0));
      }
  }
