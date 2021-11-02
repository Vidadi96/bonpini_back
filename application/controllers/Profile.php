<?php

  class Profile extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('profile_model');
        $this->load->model('universal_model');
      }

      public function get_profile_details()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $personality = $this->universal_model->get_more_item_select('personality', '*', '1=1', 1);
          $lifestyle = $this->universal_model->get_more_item_select('lifestyle', '*', '1=1', 1);
          $music = $this->universal_model->get_more_item_select('music', '*', '1=1', 1);
          $sport = $this->universal_model->get_more_item_select('sport', '*', '1=1', 1);
          $movie = $this->universal_model->get_more_item_select('movie', '*', '1=1', 1);
          $languages = $this->universal_model->get_more_item_select('languages', '*', '1=1', 1);
          $profile = $this->universal_model->get_item('users', array('id' => $user_id));

          $header_data = $this->get_site_header_data($user_id);

          $vars = array(
            'personality' => $personality,
            'lifestyle' => $lifestyle,
            'music' => $music,
            'sport' => $sport,
            'movie' => $movie,
            'languages' => $languages,
            'profile' => $profile,
            'header_data' => $header_data
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

            $last_image = $this->universal_model->get_item_where('users', array('id' => (int) $this->input->post('user_id')), 'img');

            if (@$last_image && @$last_image->img) {
              if ($last_image->img != 'male.png' && $last_image->img != 'female.ico') {
                if (file_exists($this->config->item('server_root')."/api/images/profile/".$last_image->img))
        					unlink($this->config->item("server_root")."/api/images/profile/".$last_image->img);
        				if (file_exists($this->config->item('server_root').'/api/images/'.$last_image->img))
        					unlink($this->config->item('server_root').'/api/images/'.$last_image->img);
              }
            }

            $result = $this->universal_model->update_table('users', array('id' => (int) $this->input->post('user_id')), array('img' => $img['file_name']));
            if ($result)
              echo json_encode(array('status' => 1, 'photo' => $img['file_name']));

            unlink($img['full_path']);
          }
        }
      }

      public function delete_img()
      {
        $get_data = (array) json_decode(file_get_contents('php://input'));
        $img = $get_data['photo'];
        $pathh = '/images';

				if(file_exists($this->config->item('server_root').$pathh."/profile/".$img))
					unlink($this->config->item("server_root").$pathh."/profile/".$img);
				if(file_exists($this->config->item('server_root').$pathh.'/'.$img))
					unlink($this->config->item('server_root').$pathh.'/'.$img);

				$result = $this->universal_model->update_table('users', array('id' => (int) $get_data['user_id']), array('img' => ''));

        if ($result)
          echo json_encode(array('status' => 1));
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

          $this->universal_model->update_table('users', array('id' => $user_id), $vars);

          echo json_encode(array('status' => 1, 'msg' => 1, 'active' => $active));
        } else {
          echo json_encode(array('status' => 0, 'msg' => 'Not authorized'));
        }
      }

      public function get_profile_details2()
      {
        $get_data = (array) json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $profile = $this->universal_model->get_item('users', array('id' => $user_id));

          $personality = $lifestyle = $music = $sport = $movie = $languages = [];
          if ($profile->personality)
            $personality = $this->profile_model->get_with_in('personality', $profile->personality, '*');
          if ($profile->lifestyle)
            $lifestyle = $this->profile_model->get_with_in('lifestyle', $profile->lifestyle, '*');
          if ($profile->music)
            $music = $this->profile_model->get_with_in('music', $profile->music, '*');
          if ($profile->sport)
            $sport = $this->profile_model->get_with_in('sport', $profile->sport, '*');
          if ($profile->movie)
            $movie = $this->profile_model->get_with_in('movie', $profile->movie, '*');
          if ($profile->languages)
            $languages = $this->profile_model->get_with_in('languages', $profile->languages, '*');

          $have_listing = false;
          $check_have_listing = $this->universal_model->get_item_where('ads', array('user_id' => $user_id), 'id');

          if(@$check_have_listing && @$check_have_listing->id)
            $have_listing = true;

          $age = 0;
          if ($profile->birthday)
            $age = (strtotime(date('Y-m-d')) - strtotime($profile->birthday))/(365*24*60*60);

          $header_data = $this->get_site_header_data($user_id);

          $vars = array(
            'personality' => $personality,
            'lifestyle' => $lifestyle,
            'music' => $music,
            'sport' => $sport,
            'movie' => $movie,
            'languages' => $languages,
            'profile' => $profile,
            'age' => (int) $age,
            'have_listing' => $have_listing,
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }
  }
