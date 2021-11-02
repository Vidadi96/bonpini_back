<?php

  class Main extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('main_model');
      }

      public function get_main_page_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $data = $this->main_model->get_premium_blocks();

        for ($i = 0; $i < count($data); $i++) {
          $images = $this->main_model->get_photos($data[$i]['id']);
          $data[$i]['renew_date_formatted'] = date('d-m-Y', strtotime($data[$i]['renew_date']));
          $data[$i]['images'] = $images;
        }

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'premium_blocks' => $data,
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function how_we_work()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function notifications()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if ($this->check_token($user_id)) {
          $header_data = $this->get_site_header_data($user_id);

          $vars = array(
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function contact()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function get_about_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $header_data = $this->get_site_header_data($user_id);
        $ads_count_arr = $this->main_model->get_ads_count();
        $landlords = $this->universal_model->get_item_where('users', array('active' => 1, 'user_type' => 0), 'count(*) as "count"');
        $tenants = $this->universal_model->get_item_where('users', array('active' => 1, 'user_type' => 1), 'count(*) as "count"');

        $vars = array(
          'header_data' => $header_data,
          'ads_count' => $ads_count_arr->count,
          'watch_count' => $ads_count_arr->watch,
          'landlords_count' => $landlords->count,
          'tenants_count' => $tenants->count
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function index($page = 'main-page')
      {
        $this->load->view('Pages/'.$page);
      }

      public function getMenuItems()
      {
        $menuItems[] = (object) array('title' => '', 'field' => array());

        $a = [];
        $j = $k = $l = 0;
        $arr = $this->main_model->getMenuItems();
        $a[] = $arr[0]->title;
        $menuItems[$k]->title = $a[$j];
        for($i=0; $i<count($arr); $i++)
        {
          if($arr[$i]->title == $a[$j])
          {
            $menuItems[$k]->field[] = $arr[$i]->field;
          }
          else
          {
            $a[] = $arr[$i]->title;
            $k++;
            $j++;
            @$menuItems[$k]->title = $a[$j];
            $menuItems[$k]->field[] = $arr[$i]->field;
          }
        }

        echo json_encode($menuItems);
      }

      public function send_message()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $vars = array(
          'name' => $filtered_data['name'].' '.$filtered_data['lastname'],
          'phone' => $filtered_data['phone'],
          'email' => $filtered_data['email'],
          'message' => $filtered_data['message'],
          'create_date' => date('Y-m-d H:i:s')
        );

        $result = $this->universal_model->add_item($vars, 'contact');

        if ($result)
          echo json_encode(array('status' => 1, 'data' => ''));
        else
          echo json_encode(array('status' => 0, 'data' => ''));
      }

      public function terms()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function privacy()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function test()
      {
        $this->load->library('google');
        echo $this->google->getLibraryVersion();
      }
  }
