<?php

  class Search extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('search_model');
        $this->load->model('universal_model');
      }

      public function get_search_data()
      {
        // $this->output->enable_profiler(true);
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $page = isset($filtered_data['page'])?(int)$filtered_data['page']:1;
        $user_id = (int) $filtered_data['user_id'];

        $communal = -1;
        $gender = $max_price = $lat = $lng = $home_type = '';
        $bed_type = $settle_date = $details = $permitted_list = '';
        $features = [];
        $order = 'create_date desc';

        $lat = (float) $filtered_data['lat'];
        $lng = (float) $filtered_data['lng'];

        if (isset($filtered_data['gender'])) {
          $gender = $filtered_data['gender'] !== ''?(int) $filtered_data['gender']:'';
          $max_price = (float) $filtered_data['max_price'];
          $communal = (int) $filtered_data['communal'];
          $home_type = (int) $filtered_data['home_type'];
          $bed_type = (int) $filtered_data['bed_type'];
          $settle_date = $filtered_data['settle_date']=='now'?date('Y-m-d H:i:s'):($filtered_data['settle_date']?date('Y-m-d H:i:s', $filtered_data['settle_date']):'');
          $details = $filtered_data['details'];
          $permitted_list = $filtered_data['permitted_list'];
          $order = $filtered_data['order'];
        }

        $array = array(
          'gender' => $gender,
          'max_price' => $max_price,
          'communal' => $communal,
          'lat' => $lat,
          'lng' => $lng,
          'home_type' => $home_type,
          'bed_type' => $bed_type,
          'settle_date' => $settle_date,
          'details' => $details,
          'permitted_list' => $permitted_list,
          'order' => $order
        );

        $data = $this->search_model->get_search_data($page, 20, $array, $user_id);
        $count = $this->search_model->get_search_data_count($array);

        for ($i = 0; $i < count($data); $i++) {
          $images = $this->search_model->get_photos($data[$i]['id']);
          $data[$i]['renew_date_formatted'] = date('d-m-Y', strtotime($data[$i]['renew_date']));
          $data[$i]['images'] = $images;
          $data[$i]['last_check'] = $data[$i]['last_check']?strtotime(date('Y-m-d H:i:s')) - strtotime($data[$i]['last_check']):-1;
        }

        for ($i = 0; $i < count($data); $i++) {
          $features[$i]['type'] = 'Feature';
          $features[$i]['geometry'] = (object) ['type' => 'Point', 'coordinates' => [$data[$i]['lng'], $data[$i]['lat']]];
          $features[$i]['properties'] = (object) [
            'id' => $data[$i]['id'],
            'mag' => 1,
            'title' => $data[$i]['title'],
            'price' => $data[$i]['price'],
            'home_type' => $data[$i]['home_type'],
            'communal' => $data[$i]['communal'],
            'living' => (int) $data[$i]['living_man'] + (int) $data[$i]['living_woman'],
            'image' => $data[$i]['images'][0]['name']
          ];
        }

        $bed_type = $this->universal_model->get_more_item('bed_type', '1=1', 1);
        $details = $this->universal_model->get_more_item_select('details', 'id, name_1 as "name", img, "0.3" as "clicked"', '1=1', 1, array('order_by', 'asc'));

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'blocks' => $data,
          'features' => $features,
          'count' => (int) $count->count/20,
          'bed_type' => $bed_type,
          'details' => $details,
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }
  }
