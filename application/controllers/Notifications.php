<?php

  class Notifications extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('notifications_model');
        $this->load->model('universal_model');
      }

      public function set_as_seen()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $this->universal_model->update_table('notifications', array('id' => $id), array('seen' => 1));
          echo json_encode(array('status' => 1, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function response_func()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $answer = (int) $filtered_data['answer'];

          if ($answer == 0) {
            $this->universal_model->update_table('ads', array('id' => (int) $filtered_data['ad_id']), array('deleted' => 1));
            $this->universal_model->update_table('notifications', array('id' => (int) $filtered_data['id']), array('date' => date('Y-m-d H:i:s'), 'response' => 1));
          } else {
            $this->universal_model->update_table('ads', array('id' => (int) $filtered_data['ad_id']), array('renew_date' => date('Y-m-d H:i:s')));
            $this->universal_model->update_table('notifications', array('id' => (int) $filtered_data['id']), array('date' => date('Y-m-d H:i:s'), 'response' => 2));
          }

          $vars = array(
            'user_id' => $user_id,
            'to_user' => (int) $filtered_data['to_user_id'],
            'type' => 2,
            'ad_id' => (int) $filtered_data['ad_id'],
            'text' => '',
            'seen' => 0,
            'response' => $answer == 0?1:2,
            'date' => '',
            'create_date' => date('Y-m-d H:i:s')
          );

          $result = $this->universal_model->add_item($vars, 'notifications');
          $vars['id'] = $result;

          $obj = $this->universal_model->get_item_where('users', array('id' => $user_id), 'name, surname, img');
          $vars['create_date'] = strtotime(date('Y-m-d H:i:s')) - strtotime($vars['create_date']);
          $vars['date'] = 0;
          $vars['img'] = $obj->img;
          $vars['name'] = $obj->name;
          $vars['surname'] = $obj->surname;

          echo json_encode(array('status' => 1, 'data' => array('notification' => $vars)));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function pagination()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $this->load->model('my_controller_model');

          $page = (int) $filtered_data['page'];

          $notifications = $this->my_controller_model->get_notifications($user_id, $page);

    			if ($notifications) {
    				for ($i = 0; $i < count($notifications); $i++) {
    					$notifications[$i]['create_date'] = strtotime(date("Y-m-d H:i:s")) - strtotime($notifications[$i]['create_date']);
    					$notifications[$i]['date'] = strtotime(date("Y-m-d H:i:s")) - strtotime($notifications[$i]['date']);
    				}
    			}

          $vars = array(
            'notifications' => $notifications
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }
  }
