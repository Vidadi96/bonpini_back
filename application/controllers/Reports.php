<?php

  class Reports extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('reports_model');
        $this->load->model('universal_model');
      }

      public function reports_list()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $where_for = -1;
          $user_name = '';

          $array = array(
            'type' => $where_for,
            'user_name' => $user_name
          );

          $data = @$this->reports_model->get_all_reports(1, 30, $array);
          $count = @$this->reports_model->get_all_reports_count($array);

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $vars = array(
            'reports' => $data,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function delete_report()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $result = $this->universal_model->delete_item(array('id' => $id), 'report');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_all_reports_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $where_for = -1;
          $user_name = '';

          $where_for = (int) $filtered_data['where_for'];
          $user_name = $filtered_data['user_name'];
          $page = (int) $filtered_data['page'];

          $array = array(
            'type' => $where_for,
            'user_name' => $user_name
          );

          $data = @$this->reports_model->get_all_reports($page, 30, $array);
          $count = @$this->reports_model->get_all_reports_count($array);

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $vars = array(
            'reports' => $data,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else
          echo json_encode(array('status' => 0, 'data' => ''));
      }

      public function inbox_func()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $with_user_id = (int) $filtered_data['with_user_id'];

          $check = $this->universal_model->get_item_where('inbox_id', array('buyer_id' => 99999, 'seller_id' => $with_user_id), 'id');
          $inbox_id = 0;
          if (@$check->id) {
            $inbox_id = $check->id;
          } else {
            $vars = array(
              'buyer_id' => 99999,
              'seller_id' => $with_user_id,
              'ad_id' => 0,
              'create_date' => date('Y-m-d H:i:s'),
              'status' => 1
            );

            $inbox_id = $this->universal_model->add_item($vars, 'inbox_id');
          }

          $arr = array(
            'inbox_id' => $inbox_id
          );

          echo json_encode(array('status' => 1, 'data' => $arr));
        } else
          echo json_encode(array('status' => 0, 'data' => ''));
      }

  }
