<?php

  class Users extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('universal_model');
      }

      public function get_users_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $received_Token = $this->input->request_headers('Authorization');

        if (@$this->check_token($user_id)) {
          $users = $this->users_model->get_users();
          $users_count = $this->universal_model->get_item_where('users', '1=1', 'count(*) as "count"');

          $page_count = $users_count->count%30==0?$users_count->count/30:((int)($users_count->count/30) + 1 );

          $vars = array(
            'users' => $users,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function get_filtered_users()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $received_Token = $this->input->request_headers('Authorization');

        if (@$this->check_token($user_id)) {
          $page = isset($filtered_data['page'])?(int)$filtered_data['page']:1;
          $username = $filtered_data['username'];
          $email = $filtered_data['email'];
          $phone = $filtered_data['phone'];
          $user_type = (int) $filtered_data['user_type'];
          $active = (int) $filtered_data['active'];

          $start = ($page - 1)*30;

          $users = $this->users_model->get_filtered_users($start, 30, $username, $email, $phone, $user_type, $active);
          $users_count = $this->users_model->get_filtered_users_count($username, $email, $phone, $user_type, $active);
          $page_count = $users_count->count%30==0?$users_count->count/30:((int)($users_count->count/30) + 1 );

          $vars = array(
            "users" => $users,
            "page_count" => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function active_passive_user()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];
          $active = $filtered_data['active']?1:0;

          $result = $this->universal_model->update_table('users', array('id' => $id), array('active' => $active));

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

          $result = $this->universal_model->delete_item(array('id' => $id), 'users');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_user_data($id = 0)
      {
        $id = (int) $id;
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $personality = $this->universal_model->get_more_item_select('personality', 'id, name_1', '1=1', 1);
          $lifestyle = $this->universal_model->get_more_item_select('lifestyle', 'id, name_1', '1=1', 1);
          $music = $this->universal_model->get_more_item_select('music', 'id, name_1', '1=1', 1);
          $sport = $this->universal_model->get_more_item_select('sport', 'id, name_1', '1=1', 1);
          $movie = $this->universal_model->get_more_item_select('movie', 'id, name_1', '1=1', 1);
          $languages = $this->universal_model->get_more_item_select('languages', 'id, name_1', '1=1', 1);
          $profile = $this->universal_model->get_item('users', array('id' => $id));

          $vars = array(
            'personality' => $personality,
            'lifestyle' => $lifestyle,
            'music' => $music,
            'sport' => $sport,
            'movie' => $movie,
            'languages' => $languages,
            'profile' => $profile,
            'id' => $id
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function upload_photo()
      {
        if (!empty($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none')
        {
          $img = $this->do_upload("file", $this->config->item('server_root').'/api/images/', 20000, 'img', 'jpg|png|JPEG|jpeg');

          if (@$img["error"] == TRUE) {
            echo $img["error"];
          }	else {
            $deg = $this->correctImageOrientation($img['full_path']);

            $this->load->library('resize');
            $this->resize->getFileInfo($img['full_path']);

            $upload_location = $this->config->item('server_root').'/api/images/profile/'.$img['file_name'];
            $this->resize->resizeImage(400, 400, 'landscape');
    				$this->resize->saveImage($upload_location, 90, $deg);

            $last_image = $this->universal_model->get_item_where('users', array('id' => (int) $this->input->post('id')), 'img');

            if (@$last_image && @$last_image->img) {
              if ($last_image->img != 'male.png' && $last_image->img != 'female.ico') {
                if (file_exists($this->config->item('server_root')."/api/images/profile/".$last_image->img))
        					unlink($this->config->item("server_root")."/api/images/profile/".$last_image->img);
        				if (file_exists($this->config->item('server_root').'/api/images/'.$last_image->img))
        					unlink($this->config->item('server_root').'/api/images/'.$last_image->img);
              }
            }

            $result = $this->universal_model->update_table('users', array('id' => (int) $this->input->post('id')), array('img' => $img['file_name']));
            if ($result)
              echo json_encode(array('status' => 1, 'data' => array('photo' => $img['file_name'])));

            unlink($img['full_path']);
          }
        }
      }

      public function delete_img()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if ($this->check_token($user_id)) {
          $get_data = (array) json_decode(file_get_contents('php://input'));
          $img = $get_data['photo'];
          $pathh = '/images';

  				if(file_exists($this->config->item('server_root').$pathh."/profile/".$img))
  					unlink($this->config->item("server_root").$pathh."/profile/".$img);
  				if(file_exists($this->config->item('server_root').$pathh.'/'.$img))
  					unlink($this->config->item('server_root').$pathh.'/'.$img);

  				$result = $this->universal_model->update_table('users', array('id' => (int) $filtered_data['id']), array('img' => ''));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
        }
      }

      public function save_profile_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if ($this->check_token($user_id)) {
          $active = 0;

          if ($filtered_data['name'] && $filtered_data['surname'] && $filtered_data['year'] && $filtered_data['work_status'] && $filtered_data['languages'] && $filtered_data['personality'] && $filtered_data['lifestyle'] && $filtered_data['music'] && $filtered_data['sport'] && $filtered_data['movie'] && $filtered_data['img'])
              $active = 1;

          $vars = array(
            'name' => $filtered_data['name'],
            'surname' => $filtered_data['surname'],
            'birthday' => (int)$filtered_data['year'].'-'.(int)$filtered_data['last_month'].'-'.(int)$filtered_data['last_day'],
            'gender' => (int) $filtered_data['gender'],
            'work_status' => $filtered_data['work_status'],
            'languages' => $filtered_data['languages'],
            'personality' => $filtered_data['personality'],
            'lifestyle' => $filtered_data['lifestyle'],
            'music' => $filtered_data['music'],
            'sport' => $filtered_data['sport'],
            'movie' => $filtered_data['movie'],
            'img' => $filtered_data['img'],
            'active' => $active
          );

          $this->universal_model->update_table('users', array('id' => $filtered_data['id']), $vars);

          echo json_encode(array('status' => 1, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

  }
