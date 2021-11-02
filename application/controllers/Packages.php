<?php

  class Packages extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('packages_model');
        $this->load->model('universal_model');
      }

      public function get_package_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $data = $this->packages_model->get_package_data($user_id);
          $header_data = $this->get_site_header_data($user_id);
          $vars = array(
            'packages' => $data,
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function select_package()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $package_id = (int) $filtered_data['package_id'];

          $check = $this->universal_model->get_more_item_select_row('user_package', 'id', array('user_id' => $user_id, 'active' => 1, 'banned' => 0, 'deleted' => 0));

          if (@$check->id)
            $this->universal_model->update_table('user_package', array('id' => $check->id), array('active' => 0));

          $vars = array(
            'package_id' => $package_id,
            'user_id' => $user_id,
            'active' => 1,
            'banned' => 0,
            'deleted' => 0,
            'create_date' => date('Y-m-d H:i:s')
          );

          $result = $this->universal_model->add_item($vars, 'user_package');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
        }
      }

      public function get_packages_list()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $packages = $this->packages_model->get_packages();
          $packages_count = $this->universal_model->get_item_where('packages', '1=1', 'count(*) as "count"');

          $page_count = $packages_count->count%30==0?$packages_count->count/30:((int)($packages_count->count/30) + 1 );

          $vars = array(
            'packages' => $packages,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function get_filtered_packages()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $page = isset($filtered_data['page'])?(int)$filtered_data['page']:1;
          $id = $filtered_data['package_id'];
          $phone = $filtered_data['phone'];
          $date1 = $filtered_data['date1']?date('Y-m-d H:i:s', strtotime($filtered_data['date1'])):'';
          $date2 = $filtered_data['date2']?date('Y-m-d H:i:s', strtotime($filtered_data['date2'])):'';

          $start = ($page - 1)*30;

          $packages = $this->packages_model->get_filtered_packages($start, 30, $id, $phone, $date1, $date2);
          $packages_count = $this->packages_model->get_filtered_packages_count($id, $phone, $date1, $date2);
          $page_count = $packages_count->count%30==0?$packages_count->count/30:((int)($packages_count->count/30) + 1 );

          $vars = array(
            "packages" => $packages,
            "page_count" => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function active_passive_package()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];
          $type = (int) $filtered_data['type'];

          $active = $filtered_data['active']?1:0;

          if ($type == 1)
            $result = $this->universal_model->update_table('user_package', array('id' => $id), array('active' => $active));
          else if ($type == 2)
            $result = $this->universal_model->update_table('user_package', array('id' => $id), array('banned' => $active));
          else
            $result = $this->universal_model->update_table('user_package', array('id' => $id), array('deleted' => $active));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function package_settings()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $packages = $this->universal_model->select_result('packages', '*, 0 as "edit"');

          $vars = array(
            'settings' => $packages
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function edit_package()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array(
            'title' => $filtered_data['edit_title'],
            'price' => $filtered_data['edit_price'],
            'currency' => $filtered_data['edit_currency'],
            'text' => $filtered_data['edit_text'],
            'ads' => $filtered_data['edit_ads'],
            'days' => $filtered_data['edit_days']
          );

          $result = $this->universal_model->update_table('packages', array('id' => (int) $filtered_data['id']), $vars);

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else
          echo json_encode(array('status' => 0));
      }

      public function create_new_contact()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $active_package_check = $this->universal_model->get_item_where('user_package', array('user_id' => $user_id, 'active' => 1), 'id, package_id');
          $seller_id = (int) $filtered_data['seller_id'];

          if (@$active_package_check && @$active_package_check->id) {
            $this->load->model('adv_model');
            $contact_with_check = $this->adv_model->contact_with_check($user_id, $seller_id);
            if (@!$contact_with_check || @!$contact_with_check->id) {

              $contacts_count = $this->universal_model->get_item_where('user_package_contacts', array('user_package_id' => $active_package_check->id), 'count(*) as "count"');
              $package_count = $this->universal_model->get_item_where('packages', array('id' => $active_package_check->package_id), 'ads');

              if ((int) $contacts_count->count < (int) $package_count->ads ) {
                $vars = array(
                  'user_package_id' => $active_package_check->id,
                  'seller_id' => $seller_id,
                  'create_date' => date('Y-m-d H:i:s')
                );

                $this->universal_model->add_item($vars, 'user_package_contacts');

                if (((int) $package_count->ads - (int) $contacts_count->count) == 1)
                  $this->universal_model->update_table('user_package', array('id' => $active_package_check->package_id), array('active' => 0));

                $data = $this->universal_model->get_item_where('users', array('id' => $seller_id), 'img, name, surname, phone, facebook');
                echo json_encode(array('status' => 1, 'data' => $data));
              } else {
                $this->universal_model->update_table('user_package', array('id' => $active_package_check->package_id), array('active' => 0));
                echo json_encode(array('status' => 0));
              }
            } else {
              $data = $this->universal_model->get_item_where('users', array('id' => $seller_id), 'img, name, surname, phone, facebook');
              echo json_encode(array('status' => 1, 'data' => $data));
            }
          } else {
            echo json_encode(array('status' => 0));
          }
        }
      }

  }
