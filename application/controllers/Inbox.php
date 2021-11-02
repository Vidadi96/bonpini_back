<?php

  class Inbox extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('inbox_model');
        $this->load->model('universal_model');
      }

      public function send_inbox_request()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if ($this->check_token($user_id)) {
          $seller_id = $this->universal_model->get_more_item_select_row('ads', 'user_id', array('id' => (int) $filtered_data['id']));

          if (@$seller_id && @$seller_id->user_id) {
            $check_inbox = $this->universal_model->get_more_item_select_row('inbox_id', 'id', 'buyer_id = '.$user_id.' and seller_id = '.$seller_id->user_id.' and ad_id = '.(int) $filtered_data['id'].' and status in (1,2)');

            if (@!$check_inbox || @!$check_inbox->id) {
              $check_contact = $this->inbox_model->check_contact($user_id, $seller_id->user_id);

              if (@$check_contact && @$check_contact->id) {
                $vars = array(
                  'buyer_id' => $user_id,
                  'seller_id' => $seller_id->user_id,
                  'ad_id' => (int) $filtered_data['id'],
                  'status' => 1,
                  'create_date' => date('Y-m-d H:i:s')
                );

                $this->universal_model->add_item($vars, 'inbox_id');
              }
            }

            echo json_encode(array('status' => 1));
          } else
            echo json_encode(array('status' => 0));
        } else {
          echo json_encode(array('status' => 0));
        }
      }

      public function get_inbox_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $filter_status = (isset($filtered_data['filter_status']) && @$filtered_data['filter_status'] != '')?(int)$filtered_data['filter_status']:'';

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $this->universal_model->delete_item(array('user_id' => $user_id), 'open_inbox');
          $data = $this->inbox_model->get_inbox_data($user_id, $filter_status);

          if (@$data) {
            for ($i = 0; $i < count($data); $i++) {
              $row = $data[$i];
              $row['age'] = (int) ((strtotime(date('Y-m-d')) - strtotime($row['birthday']))/(365*24*60*60));
              $row['create_date'] = strtotime($row['create_date']);
              $row['date'] = date('H:i', strtotime($row['date']));
              $data[$i] = $row;
            }
          }

          $header_data = $this->get_site_header_data($user_id);

          $vars = array(
            'inbox' => @$data?$data:[],
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function delete_from_open_inbox_id()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $this->universal_model->delete_item(array('user_id' => $user_id), 'open_inbox');
          echo json_encode(array('status' => 1, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function set_online()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $online = (int) $filtered_data['status'];

          $this->universal_model->update_table('users', array('id' => $user_id), array('online' => $online));

          if ($online)
            $this->inbox_model->set_inbox_online($user_id);

          echo json_encode(array('status' => 1, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_inbox_with_data($id)
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $id = (int) $id;
        $user_id = (int) $filtered_data['user_id'];

        $page = 1;
        $count = 30;

        if (@$this->check_token($user_id)) {
          $messages = $this->inbox_model->get_messages($page, $count, $id);
          $messages_count = $this->universal_model->get_more_item_select_row('inbox', 'count(*) as "count"', array('inbox_id' => $id));
          $seller_id = $this->universal_model->get_more_item_select_row('inbox_id', 'seller_id as "id", ad_id', array('id' => $id));
          $seller_data = $this->universal_model->get_more_item_select_row('users', 'id, name, work_status, img', array('id' => $seller_id->id));
          $adv = $this->universal_model->get_more_item_select_row('ads', 'home_type, price, settle_date_from, min_rent_term, communal, address', array('id' => $seller_id->ad_id));
          $adv_img = $this->universal_model->get_more_item_select_row('photos', 'name', array('ad_id' => $seller_id->ad_id), array('location', 'asc'));

          $vars = array(
            'messages' => @$messages?$messages:[],
            'count' => $messages_count->count,
            'seller' => $seller_data,
            'adv' => $adv,
            'img' => $adv_img,
            'inbox_id' => $id
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function bring_inbox_data($id)
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $id = (int) $id;
        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $page = 1;
          $count = 30;

          $this->universal_model->delete_item(array('user_id' => $user_id), 'open_inbox');
          $this->universal_model->add_item(array('user_id' => $user_id, 'inbox_id' => $id), 'open_inbox');

          $ad_id = (int) $filtered_data['ad_id'];

          $this->inbox_model->check_seen($id, $user_id);

          $messages = $this->inbox_model->get_messages2($page, $count, $id);
          for ($i = 0; $i < count($messages); $i++) {
            if (strtotime($messages[$i]['create_date']) - strtotime(date('Y-m-d H:i:s')) < 24*60*60)
              $messages[$i]['create_date'] = date('H:i', strtotime($messages[$i]['create_date']));
            else
              $messages[$i]['create_date'] = date('d.m.y', strtotime($messages[$i]['create_date']));
          }

          $messages_count = $this->universal_model->get_more_item_select_row('inbox', 'count(*) as "count"', array('inbox_id' => $id));
          $adv = $this->universal_model->get_more_item_select_row('ads', 'id, title, home_type, price, settle_date_from, min_rent_term, communal, address, currency, searching_man, searching_woman', array('id' => $ad_id));
          $adv_img = $this->universal_model->get_more_item_select_row('photos', 'name', array('ad_id' => $ad_id), array('location', 'asc'));
          $seen_array = $this->inbox_model->get_seen_or_not($user_id);

          $new_message = $new_message_date = 0;

          if (@$seen_array) {
    				foreach ($seen_array as $row) {
    					if ($row->seen == 0) {
    						$new_message = 1;
    						$new_message_date = strtotime(date('Y-m-d H:i:s')) - strtotime($row->last_date);
    						break;
    					}
    				}
    			}

          $vars = array(
            'messages' => @$messages?$messages:[],
            'count' => $messages_count->count,
            'adv' => $adv,
            'img' => $adv_img,
    				'new_message' => $new_message,
    				'new_message_date' => $new_message_date
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function check_user()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $id = (int) $filtered_data['inbox_id'];

        $check = $this->universal_model->get_more_item_select_row('inbox_id', 'id', '(seller_id ='.$user_id.' or buyer_id = '.$user_id.') and id = '.$id);

        echo (@$check && @$check->id)?json_encode(array('status' => 1)):json_encode(array('status' => 0));
      }

     	public function add_new_message()
     	{
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $inbox_id = (int) $filtered_data['inbox_id'];
        $msg = $filtered_data['msg'];
        $img = $filtered_data['img'];

        if (@$this->check_token($user_id)) {
          $check_user = $this->universal_model->get_more_item_select_row('inbox_id', 'id, seller_id, buyer_id', '(seller_id = '.$user_id.' or buyer_id = '.$user_id.') and id ='.$inbox_id);

          if (@$check_user && @$check_user->id) {
            $check_seen = $this->inbox_model->check_if_seen($user_id, $inbox_id);
            $check_was_online = $this->inbox_model->check_online($user_id, $inbox_id);

            $seen = 0;
            if (@$check_seen && @$check_seen->id)
              $seen = 1;

            $was_online = 0;
            if (@$check_was_online && @$check_was_online->online)
              $was_online = $check_was_online->online;


            $vars = array(
              'inbox_id' => $inbox_id,
              'user_id' => $user_id,
              'message' => $msg,
              'img' => $img,
              'create_date' => date('Y-m-d H:i:s'),
              'seen' => $seen,
              'was_online' => $was_online
            );

            $id = $this->universal_model->add_item($vars, 'inbox');

            $obj = $this->inbox_model->get_obj($id);

            $obj->date = strtotime($obj->create_date);
            $obj->create_date = date('H:i', strtotime($obj->create_date));

            echo json_encode(array('status' => 1, 'obj' => $obj, 'seller_id' => $check_user->seller_id, 'buyer_id' => $check_user->buyer_id));
          } else {
            echo json_encode(array('status' => 0, 'obj' => ''));
          }
        } else {
          echo json_encode(array('status' => 0, 'obj' => ''));
        }
     	}

      public function inbox_pagination()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $id = (int) $filtered_data['inbox_id'];;
        $user_id = (int) $filtered_data['user_id'];

        $page = (int) $filtered_data['page'];
        $count = 30;

        if (@$this->check_token($user_id)) {
          $messages = $this->inbox_model->get_messages2($page, $count, $id);

          for ($i = 0; $i < count($messages); $i++)
            $messages[$i]['create_date'] = date('H:i', strtotime($messages[$i]['create_date']));

          echo json_encode(array('status' => 1, 'obj' => $messages));
        } else {
          echo json_encode(array('status' => 0, 'obj' => ''));
        }
      }

      public function upload_photo()
      {
        if(!empty($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none')
        {
          $img = $this->do_upload("file", $this->config->item('server_root').'/api/images/', 20000, 'img', 'jpg|png|JPEG|jpeg');

          if (@$img["error"] == TRUE) {
            echo $img["error"];
          }	else {
            $deg = $this->correctImageOrientation($img['full_path']);
            $ext = $img['file_ext'];

            $this->load->library('resize');
            $this->resize->getFileInfo($img['full_path']);

            $upload_location = $this->config->item('server_root').'/api/images/message_photos/'.$img['file_name'];
            $this->resize->resizeImage(300, 300, 'auto');
    				$this->resize->saveImage($upload_location, 90, $deg);

            echo json_encode(array('status' => 1, 'name' => $img['file_name']));

            unlink($img['full_path']);
          }
        }
      }

      public function delete_img()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $name = $filtered_data['name'];

				if (file_exists($this->config->item('server_root')."/api/images/message_photos/".$name))
					unlink($this->config->item("server_root")."/api/images/message_photos/".$name);

				if (file_exists($this->config->item('server_root').'/api/images/'.$name))
					unlink($this->config->item('server_root').'/api/images/'.$name);

        echo json_encode(array('status' => 1));
      }
  }
