<?php

  class Ads extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('ads_model');
        $this->load->model('universal_model');
      }

      public function all_ads()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $communal = -1;
          $gender = $max_price = $lat = $lng = $home_type = '';
          $bed_type = $settle_date = $details = $permitted_list = '';

          $array = array(
            'id' => '',
            'gender' => $gender,
            'max_price' => $max_price,
            'communal' => $communal,
            'lat' => $lat,
            'lng' => $lng,
            'home_type' => $home_type,
            'bed_type' => $bed_type,
            'settle_date' => $settle_date,
            'details' => $details,
            'permitted_list' => $permitted_list
          );

          $data = @$this->ads_model->get_all_ads(1, 30, $array);
          $count = @$this->ads_model->get_all_ads_count($array);

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $bed_type = $this->universal_model->get_more_item('bed_type', '1=1', 1);
          $details = $this->universal_model->get_more_item_select('details', 'id, name_1 as "name", img, "0.3" as "clicked"', '1=1', 1, array('order_by', 'asc'));

          $vars = array(
            'ads' => $data,
            'page_count' => $page_count,
            'bed_type' => $bed_type,
            'details' => $details
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function delete_ad()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $photos = $this->universal_model->get_more_item_select('photos', 'name', array('ad_id' => $id));

          foreach ($photos as $row) {
            $name = $row->name;
            $pathh = '/api/images/adv';

            $del_arr = explode('.', $name);
						if (file_exists($this->config->item('server_root').$pathh."/big/".$name))
							@unlink($this->config->item("server_root").$pathh."/big/".$name);
						if (file_exists($this->config->item('server_root').$pathh."/small/".$name))
							@unlink($this->config->item("server_root").$pathh."/small/".$name);
						if (file_exists($this->config->item('server_root').$pathh."/big/".$del_arr[0].'.webp'))
							@unlink($this->config->item("server_root").$pathh."/big/".$del_arr[0].'.webp');
						if (file_exists($this->config->item('server_root').$pathh."/small/".$del_arr[0].'.webp'))
							@unlink($this->config->item("server_root").$pathh."/small/".$del_arr[0].'.webp');
          }

          $this->universal_model->delete_item(array('ad_id' => $id), 'photos');
          $result = $this->universal_model->delete_item(array('id' => $id), 'ads');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_all_ads_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = $gender = $max_price = $communal = $lat = $lng = $home_type = '';
          $bed_type = $settle_date = $details = $permitted_list = '';

          $id = $filtered_data['id'];
          $lat = (float) $filtered_data['lat'];
          $lng = (float) $filtered_data['lng'];
          $gender = $filtered_data['gender'] !== ''?(int) $filtered_data['gender']:'';
          $max_price = (float) $filtered_data['max_price'];
          $communal = (int) $filtered_data['communal'];
          $home_type = (int) $filtered_data['home_type'];
          $bed_type = (int) $filtered_data['bed_type'];
          $settle_date = $filtered_data['settle_date']=='now'?date('Y-m-d H:i:s'):($filtered_data['settle_date']?date('Y-m-d H:i:s', $filtered_data['settle_date']):'');
          $details = $filtered_data['details'];
          $permitted_list = $filtered_data['permitted_list'];
          $page = (int) $filtered_data['page'];

          $array = array(
            'id' => $id,
            'gender' => $gender,
            'max_price' => $max_price,
            'communal' => $communal,
            'lat' => $lat,
            'lng' => $lng,
            'home_type' => $home_type,
            'bed_type' => $bed_type,
            'settle_date' => $settle_date,
            'details' => $details,
            'permitted_list' => $permitted_list
          );

          $data = @$this->ads_model->get_all_ads($page, 30, $array);
          $count = @$this->ads_model->get_all_ads_count($array);

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $vars = array(
            'ads' => $data,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else
          echo json_encode(array('status' => 0, 'data' => ''));
      }

      public function get_ad_data($id = 0)
      {
        $id = (int) $id;
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $data = $this->universal_model->get_more_item_select_row('ads', '*', array('id' => $id));
          $photos = $this->universal_model->get_more_item('photos', array('ad_id' => $id), 1);
          $details = $this->ads_model->get_details($id);
          $bed_type = $this->universal_model->select_result('bed_type', 'id, name_1');
          $metro = $this->universal_model->get_more_item('metro', '1=1', 0, array('order_by', 'asc'));
          $build_type = $this->universal_model->select_result('build_type', '*');

          $vars = array(
            'ad' => $data,
            'photos' => $photos,
            'details' => $details,
            'bed_type' => $bed_type,
            'metro' => $metro,
            'build_type' => $build_type
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function upload_ad_photo()
      {
        if(!empty($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none')
        {
          $img = $this->do_upload("file", $this->config->item('server_root').'/api/images/adv/', 20000, 'img', 'jpg|png|JPEG|jpeg');

          if (@$img["error"] == TRUE) {
            echo $img["error"];
          }	else {
            $deg = $this->correctImageOrientation($img['full_path']);
            $ext = $img['file_ext'];

            $this->load->library('resize');
            $this->resize->getFileInfo($img['full_path']);

            $upload_location = $this->config->item('server_root').'/api/images/adv/big/'.$img['file_name'];
            $this->resize->resizeImage(2000, 2000, 'landscape');
    				$this->resize->saveImage($upload_location, 90, $deg);

            if ($ext == '.jpg' || $ext == '.jpeg')
              $image = imagecreatefromjpeg($upload_location);

            if ($ext == '.png')
              $image = imagecreatefrompng($upload_location);

            $array = explode('.', $img['file_name']);
            $w = imagesx($image);
            $h = imagesy($image);
            $webp = imagecreatetruecolor($w,$h);
            imagecopy($webp, $image, 0, 0, 0, 0, $w, $h);
            imagewebp($webp, $this->config->item('server_root').'/api/images/adv/big/'.$array[0].'.webp', 80);
            imagedestroy($image);
            imagedestroy($webp);

            $upload_location = $this->config->item('server_root').'/api/images/adv/small/'.$img['file_name'];

            $this->resize->resizeImage(360, 360, 'auto');
            $this->resize->saveImage($upload_location, 90, $deg);

            if ($ext == '.jpg' || $ext == '.jpeg')
              $image = imagecreatefromjpeg($upload_location);

            if ($ext == '.png')
              $image = imagecreatefrompng($upload_location);

            $array = explode('.', $img['file_name']);
            $w = imagesx($image);
            $h = imagesy($image);
            $webp = imagecreatetruecolor($w,$h);
            imagecopy($webp, $image, 0, 0, 0, 0, $w, $h);
            imagewebp($webp, $this->config->item('server_root').'/api/images/adv/small/'.$array[0].'.webp', 80);
            imagedestroy($image);
            imagedestroy($webp);

            $vars = array(
              'name' => $img['file_name'],
              'location' => 0,
              'ad_id' => (int) $this->input->post('ad_id'),
              'user_id' => (int) $this->input->post('user_id'),
              'active' => 1,
              'deleted' => 0
            );

            $result = $this->universal_model->add_item($vars, 'photos');
            if ($result) {
              $vars['id'] = $result;

              echo json_encode(array('status' => 1, 'photo' => $vars));
            }

            unlink($img['full_path']);
          }
        }
      }

      public function save_ad_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $cigarettes = $pets = $couple = $opposite_sex = 0;
          $settle_date_to = $filtered_data['to_date_checked']?date('Y-m-d H:i:s', strtotime($filtered_data['settle_date_to'])):'';

          if ($filtered_data['permitted_list']) {
            $array = $filtered_data['permitted_list'];
            $cigarettes = in_array(1, $array)?1:0;
            $pets = in_array(2, $array)?1:0;
            $couple = in_array(3, $array)?1:0;
            $opposite_sex = in_array(4, $array)?1:0;
          }

          $vars = array(
            'user_id' => (int) $filtered_data['ad_user_id'],
            'address' => $filtered_data['address'],
            'metro' => (int) $filtered_data['metro'],
            'simple_address' => $filtered_data['simple_address'],
            'lat' => (float) $filtered_data['lat'],
            'lng' => (float) $filtered_data['lng'],
            'home_type' => (int) $filtered_data['home_type'],
            'living_man' => (int) $filtered_data['living_man'],
            'living_woman' => (int) $filtered_data['living_woman'],
            'lived_busyness' => (int) $filtered_data['lived_busyness'],
            'searching_man' => (int) $filtered_data['searching_man'],
            'searching_woman' => (int) $filtered_data['searching_woman'],
            'build_type' => (int) $filtered_data['build_type'],
            'number_of_floors' => (int) $filtered_data['number_of_floors'],
            'on_which_floor' => (int) $filtered_data['on_which_floor'],
            'details' => $filtered_data['details'],
            'cigarettes' => $cigarettes,
            'pet_friendly' => $pets,
            'couple' => $couple,
            'opposite_sex' => $opposite_sex,
            'bed_type' => (int) $filtered_data['bed_type'],
            'settle_date_from' => date('Y-m-d', strtotime($filtered_data['settle_date_from'])),
            'settle_date_to' => $settle_date_to,
            'min_rent_term' => (int) $filtered_data['min_rent_term'],
            'price' => (float) $filtered_data['price'],
            'currency' => (int) $filtered_data['currency'],
            'communal' => $filtered_data['communal']?1:0,
            'communal_about' => $filtered_data['communal_about'],
            'youtube' => $filtered_data['youtube'],
            'title' => $filtered_data['title'],
            'description' => $filtered_data['description'],
            'age_from' => (int) $filtered_data['age_from'],
            'age_to' => (int) $filtered_data['age_to'],
            'busyness' => (int) $filtered_data['busyness'],
            'create_date' => date('Y-m-d H:i:s'),
            'renew_date' => date('Y-m-d H:i:s'),
            'premium' => 0,
            'status' => 0,
            'verified' => 0
          );

          $this->universal_model->update_table('ads', array('id' => (int) $filtered_data['ad_id']), $vars);

          echo json_encode(array('status' => 1));
        }
      }

      public function delete_img()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $get_data = (array)json_decode(file_get_contents('php://input'));
          $filtered_data = $this->filter_data($get_data);

          $id = (int) $filtered_data['id'];
          $pathh = '/api/images/adv';

  				$raw = $this->universal_model->get_item_where('photos', array('id' => $id), 'name');

  				if ($raw)
  				{
  					$del_arr = explode('.', $raw->name);
  					if(file_exists($this->config->item('server_root').$pathh."/big/".$raw->name))
  						unlink($this->config->item("server_root").$pathh."/big/".$raw->name);
  					if(file_exists($this->config->item('server_root').$pathh."/small/".$raw->name))
  						unlink($this->config->item("server_root").$pathh."/small/".$raw->name);
  					if(file_exists($this->config->item('server_root').$pathh."/big/".$del_arr[0].'.webp'))
  						unlink($this->config->item("server_root").$pathh."/big/".$del_arr[0].'.webp');
  					if(file_exists($this->config->item('server_root').$pathh."/small/".$del_arr[0].'.webp'))
  						unlink($this->config->item("server_root").$pathh."/small/".$del_arr[0].'.webp');

  					if(file_exists($this->config->item('server_root').$pathh.'/'.$raw->name))
  						unlink($this->config->item('server_root').$pathh.'/'.$raw->name);

  					$result = $this->universal_model->delete_item_where(array("id" => $id), "photos");

            if ($result)
              echo json_encode(array('status' => 1));
  				}
        }
      }
  }
