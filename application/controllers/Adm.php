<?php

  class Adm extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('adm_model');
        $this->load->model('universal_model');
      }

      public function add_new_user()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $hashed_password = $this->get_hashed_password($filtered_data['password']);
          $vars = array(
            'username' => $filtered_data['username'],
            'email' => $filtered_data['email'],
            'password' => $hashed_password,
            'active' => 1,
            'create_date' => date('Y-m-d H:i:s')
          );

          $result = $this->universal_model->add_item($vars, 'admin_users');

          if ($result) {
            $data = $this->universal_model->get_item_where('admin_users', array('id' => $result), '*');

            echo json_encode(array('status' => 1, 'data' => $data));
          } else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_user_list()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $user_list = $this->adm_model->get_user_list();

          echo json_encode(array('status' => 1, 'data' => $user_list));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function update_user()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];
          $active = $filtered_data['active']?1:0;

          $result = $this->universal_model->update_table('admin_users', array('id' => $id), array('active' => $active));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function delete_user()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $result = $this->universal_model->update_table('admin_users', array('id' => $id), array('deleted' => 1));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function add_new_word()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $received_Token = $this->input->request_headers('Authorization');

        if (@$this->check_token($user_id)) {

          $check = $data = $this->universal_model->get_item_where('admin_langs', array('lang_key' => $filtered_data['lang_key'], 'where_for' => $filtered_data['where_for']), 'id');

          if (@$check && @$check->id) {
            echo json_encode(array('status' => 0, 'data' => ''));
          } else {
            $vars = array(
              'lang_key' => $filtered_data['lang_key'],
              'lang_1' => $filtered_data['lang_1'],
              'lang_2' => $filtered_data['lang_2'],
              'lang_3' => $filtered_data['lang_3'],
              'lang_4' => $filtered_data['lang_4'],
              'where_for' => (int) $filtered_data['where_for']
            );

            $result = $this->universal_model->add_item($vars, 'admin_langs');

            if ($result) {
              $data = $this->universal_model->get_item_where('admin_langs', array('id' => $result), '*, 0 as "edit"');

              echo json_encode(array('status' => 1, 'data' => $data));
            } else
              echo json_encode(array('status' => 0, 'data' => ''));
          }
        } else
          echo json_encode(array('status' => 0));

        // echo json_encode(array('status' => 0));
      }

      public function get_translates()
      {
        $result = $this->universal_model->get_more_item_select('admin_langs', '*, 0 as "edit"', '1=1', 0, array('lang_key', 'asc'));
        echo json_encode(array('status' => 1, 'data' => $result));
      }

      public function edit_translate()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $received_Token = $this->input->request_headers('Authorization');

        if (@$this->check_token($user_id)) {
          $vars = array(
            'lang_1' => $filtered_data['edit_lang_1'],
            'lang_2' => $filtered_data['edit_lang_2'],
            'lang_3' => $filtered_data['edit_lang_3'],
            'lang_4' => $filtered_data['edit_lang_4']
          );

          $result = $this->universal_model->update_table('admin_langs', array('id' => (int) $filtered_data['id']), $vars);

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else
          echo json_encode(array('status' => 0));
      }

      public function delete_word()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $result = $this->universal_model->delete_item(array('id' => $id), 'admin_langs');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function read_translates($where_for)
      {
        $where_for = (int) $where_for;
        $result = $this->universal_model->get_more_item_select('admin_langs', '*', array('where_for' => $where_for), 0, array('lang_key', 'asc'));
        echo json_encode(array('status' => 1, 'data' => $result));
      }

      public function get_dashboard_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $received_Token = $this->input->request_headers('Authorization');

        if (@$this->check_token($user_id)) {
          $map = $this->universal_model->get_more_item_select('ads', 'id, lat, lng', '1=1', 1);

          for ($i = 0; $i < count($map); $i++) {
            $row = $map[$i];
            $features[$i]['type'] = 'Feature';
            $features[$i]['geometry'] = (object) ['type' => 'Point', 'coordinates' => [$row['lng'], $row['lat']]];
            $features[$i]['properties'] = (object) [
              'id' => $row['id'],
              'mag' => 1
            ];
          }

          $man_count = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'gender' => 0));
          $woman_count = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'gender' => 1));
          $study = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'work_status' => 1));
          $work = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'work_status' => 2));
          $study_and_work = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'work_status' => 3));
          $user_type_day = $this->adm_model->get_user_type_day();

          for ($i = 0; $i < count($user_type_day); $i++) {
            $row = $user_type_day[$i];
            $row->day = date('d.m', strtotime($row->day));
          }

          $vars = array(
            'features' => $features,
            'man_count' => $man_count->count,
            'woman_count' => $woman_count->count,
            'study' => $study->count,
            'work' => $work->count,
            'study_and_work' => $study_and_work->count,
            'user_type_day' => $user_type_day
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }
  }
