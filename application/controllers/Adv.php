<?php

  class Adv extends MY_Controller
  {

      public function __construct()
      {
        parent::__construct();
        $this->load->model('adv_model');
        $this->load->model('universal_model');
        // $this->output->set_content_type('application/json');
      }

      public function get_last_user_ad_data_func()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $data = $this->universal_model->get_more_item('last_user_ad', array('user_id' => (int) $filtered_data['user_id']), 1);
          if (@$data && @$data[0]) {
            $photos = $this->universal_model->get_more_item('photos', array('user_id' => (int) $filtered_data['user_id'], 'ad_id' => 0), 1);
            $data[0]['photos'] = @$photos?$photos:[];
            $data[0]['active'] = $this->universal_model->get_item_where('users', array('id' => (int) $filtered_data['user_id']), 'active');
            $data[0]['metro_list'] = $this->universal_model->get_more_item('metro', '1=1', 0, array('order_by', 'asc'));

            echo json_encode(array('status' => 1, 'data' => $data[0]));
          } else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function save_adv_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $check = $this->universal_model->get_item_where('last_user_ad', array('user_id' => (int) $filtered_data['user_id']), 'id');

          $cigarettes = $pets = $couple = $opposite_sex = 0;
          $settle_date_to = $filtered_data['to_date_checked']?date('Y-m-d H:i:s', strtotime($filtered_data['settle_date_to'])):'';

          if ($filtered_data['permitted_list']) {
            $array = $filtered_data['permitted_list'];
            $cigarettes  = in_array(1, $array)?1:0;
            $pets  = in_array(2, $array)?1:0;
            $couple = in_array(3, $array)?1:0;
            $opposite_sex = in_array(4, $array)?1:0;
          }

          $vars = array(
            'user_id' => (int) $filtered_data['user_id'],
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

          if (@$check && @$check->id) {
            $this->universal_model->update_table('last_user_ad', array('user_id' => (int) $filtered_data['user_id']), $vars);
          } else {
            $this->universal_model->add_item($vars, 'last_user_ad');
          }

          echo json_encode(array('status' => 1, 'msg' => 1));
        } else {
          echo json_encode(array('status' => 0, 'msg' => 'Not authorized'));
        }
      }

      public function get_details()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $lang = (int) $filtered_data['lang'];

        $details = $this->adv_model->get_details((int)$filtered_data['user_id'], $lang);
        $bed_type = $this->universal_model->select_result('bed_type', 'id, name_'.$lang);
        $build_type = $this->universal_model->select_result('build_type', 'id, name_'.$lang);

        echo json_encode(array('details' => $details, 'bed_type' => $bed_type, 'build_type' => $build_type));
      }

      public function upload_photo()
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
              'ad_id' => 0,
              'user_id' => (int)$this->input->post('user_id'),
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

      public function delete_img()
      {
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
					$img_base_name = explode(".", $raw->name);

					if(file_exists($this->config->item('server_root').$pathh.'/'.$raw->name))
						unlink($this->config->item('server_root').$pathh.'/'.$raw->name);

					$result = $this->universal_model->delete_item_where(array("id" => $id), "photos");

          if ($result)
            echo json_encode(array('status' => 1));
				}
      }

      public function publish_adv()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $data = $this->universal_model->get_item('last_user_ad', array('user_id' => $user_id));
          if (@$data && @$data->id && @$data->address && @$data->lat && @$data->lng && @$data->home_type && @$data->bed_type && @$data->settle_date_from && @$data->min_rent_term && @$data->price && @$data->title && @$data->description) {

            $result = $this->adv_model->publish_adv($user_id);
            $id = $this->universal_model->get_more_item_select_row('ads', 'MAX(id) as "id"', array('user_id' => $user_id));
            $this->universal_model->update_table('photos', array('user_id' => $user_id, 'ad_id' => 0), array('ad_id' => $id->id));

            if (@$result)
              echo json_encode(array('status' => 1, 'msg' => 'Successfully added'));
            else
              echo json_encode(array('status' => 0, 'msg' => 'Database error'));
          } else {
            echo json_encode(array('status' => 0, 'msg' => 'Not full data'));
          }
        } else {
          echo json_encode(array('status' => 0, 'msg' => 'Not authorized'));
        }
      }

      public function get_adv_data($id)
      {
        $id = (int) $id;

        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $adv = $this->adv_model->get_adv_data($id);
        if (@$adv && @$adv[0]['id']) {
          $images = $this->universal_model->get_more_item_select('photos', 'name', array('user_id' => $adv[0]['user_id'], 'ad_id' => $id, 'active' => 1), 1, array('location', 'asc'));
          $availability = (strtotime($adv[0]['settle_date_from']) < strtotime(date('Y-m-d')))?'Now':$adv[0]['settle_date_from'];
          $details = $this->adv_model->get_details2($adv[0]['details']);
          $user_data = $this->universal_model->get_more_item_select_row('users', 'id, languages, personality, lifestyle, name, surname, img, work_status, birthday, gender, phone, facebook', array('id' => $adv[0]['user_id']));
          $personality = $this->adv_model->get_with_in('personality', $user_data->personality, 'name_1');
          $lifestyle = $this->adv_model->get_with_in('lifestyle', $user_data->lifestyle, 'name_1');
          $languages = $this->adv_model->get_with_in('languages', $user_data->languages, 'name_1');
          $reviews = $this->universal_model->get_more_item_select('ad_reviews', 'stars, text, create_date', array('ad_id' => $id), 1, array('create_date', 'desc'));

          $age = 0;
          if ($user_data->birthday)
            $age = (strtotime(date('Y-m-d')) - strtotime($user_data->birthday))/(365*24*60*60);

          $for_similar = $this->universal_model->get_more_item_select_row('ads', 'home_type, price, searching_man, searching_woman, busyness', array('id' => $id));
          $similar = $this->adv_model->get_similar($for_similar, $id, $user_id);

          for ($i = 0; $i < count($similar); $i++) {
            $images2 = $this->adv_model->get_photos($similar[$i]['id']);
            $similar[$i]['renew_date_formatted'] = date('d-m-Y', strtotime($similar[$i]['renew_date']));
            $similar[$i]['images'] = $images2;
            $similar[$i]['last_check'] = $similar[$i]['last_check']?strtotime(date('Y-m-d H:i:s')) - strtotime($similar[$i]['last_check']):-1;
          }

          $premium = $this->adv_model->get_premium($id);

          $last_check_date = -1;
          $last_check = $this->universal_model->get_more_item_select_row('notifications', 'date', 'ad_id = '.$id.' and response in (1,2) and type = 1', array('id', 'desc'));
          if (@$last_check->date)
            $last_check_date = strtotime(date('Y-m-d H:i:s')) - strtotime($last_check->date);

          $check_sended = $this->universal_model->get_more_item_select_row('notifications', 'id', array('ad_id' => $id, 'user_id' => (int) $filtered_data['user_id'], 'response' => 0));

          $header_data = $this->get_site_header_data($user_id);

          $active_package = false;
          $contact_with = false;
          $check_inbox = false;

          if ($user_id) {
            $active_package_check = $this->universal_model->get_item_where('user_package', array('user_id' => $user_id, 'active' => 1), 'id');
            if (@$active_package_check && @$active_package_check->id)
              $active_package = true;

            $contact_with_check = $this->adv_model->contact_with_check($user_id, $user_data->id);
            if (@$contact_with_check && @$contact_with_check->id)
              $contact_with = true;

            $check_inbox = $this->universal_model->get_more_item_select_row('inbox_id', 'id', 'buyer_id = '.$user_id.' and seller_id = '.$user_data->id.' and ad_id = '.$id.' and status in (1,2)');
            if (@$check_inbox && @$check_inbox->id)
              $check_inbox = true;
          }

          if (!$contact_with && $user_data->id != $user_id) {
            $user_data->img = 'profile_picture.svg';
            $user_data->name = 'Xxxxx';
            $user_data->surname = 'Xxxxx';
            $user_data->phone = '';
            $user_data->facebook = '';
          }

          $vars = array(
            'adv' => $adv[0],
            'images' => $images,
            'reviews' => $reviews,
            'settle_date_from' => date('d-m-Y', strtotime($adv[0]['settle_date_from'])),
            'settle_date_to' => (strtotime($adv[0]['settle_date_to']) > 0)?date('d-m-Y', strtotime($adv[0]['settle_date_to'])):0,
            'details' => $details,
            'user_data' => $user_data,
            'personality' => $personality,
            'lifestyle' => $lifestyle,
            'languages' => $languages,
            'similar' => $similar,
            'premium' => $premium,
            'inbox' => $check_inbox,
            'active_package' => $active_package,
            'contact_with' => $contact_with,
            'age' => (int) $age,
            'last_check_date' => $last_check_date,
            'check_sended' => $check_sended,
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0));
        }
      }

      public function refresh_watch()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $id = $filtered_data['id'];
        $this->adv_model->refresh_watch($id);
      }

      public function check_listing()
     	{
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array(
            'user_id' => $user_id,
            'to_user' => (int) $filtered_data['to_user'],
            'type' => 1,
            'ad_id' => (int) $filtered_data['ad_id'],
            'text' => '',
            'seen' => 0,
            'response' => 0,
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

          echo json_encode(array('status' => 1, 'notification' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'obj' => ''));
        }
     	}

      public function report()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $reported_user_id = $this->universal_model->get_item_where('ads', array('id' => (int) $filtered_data['ad_id']), 'user_id');

          $vars = array(
            'user_id' => $user_id,
            'type' => (int) $filtered_data['report_type'],
            'report_id' => (int) $filtered_data['report_id'],
            'text' => $filtered_data['report_text'],
            'create_date' => date('Y-m-d H:i:s'),
            'reported_ad_id' => (int) $filtered_data['ad_id'],
            'reported_user_id' => $reported_user_id->user_id
          );

          $result = $this->universal_model->add_item($vars, 'report');
          if (@$result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_adv_list()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $page = isset($filtered_data['page'])?(int) $filtered_data['page']:1;
          $show = isset($filtered_data['show_ad'])?(int) $filtered_data['show_ad']:1;

          $data = @$this->adv_model->get_all_ads($page, 30, $show, $user_id);
          $count = @$this->adv_model->get_all_ads_count($show, $user_id);

          for ($i = 0; $i < count($data); $i++) {
            $photos = $this->universal_model->get_more_item_select_row('photos', 'name', array('ad_id' => $data[$i]['id']), array('location', 'asc'));
            $data[$i]['image'] = @$photos?$photos:[];
          }

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $header_data = $this->get_site_header_data($user_id);

          $vars = array(
            'ads' => $data,
            'page_count' => $page_count,
            'header_data' => $header_data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function publish_func()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['ad_id'];
          $show_ad = $filtered_data['show_ad']?1:0;

          $result = $this->universal_model->update_table('ads', array('id' => $id), array('show_ad' => $show_ad));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function delete_func()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['ad_id'];
          $result = $this->universal_model->update_table('ads', array('id' => $id), array('deleted' => 1));

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_edit_ad_data($id)
      {
        $id = (int) $id;

        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {

          $check = $this->universal_model->get_item_where('ads', array('id' => $id, 'user_id' => $user_id), 'id');
          if (@$check->id) {
            $data = $this->universal_model->get_item_where('ads', array('user_id' => $user_id, 'id' => $id), '*');
            $photos = $this->universal_model->get_more_item('photos', array('ad_id' => $id), 1);
            $data->photos = @$photos?$photos:[];
            $data->active = $this->universal_model->get_item_where('users', array('id' => $user_id), 'active');
            $data->metro_list = $this->universal_model->get_more_item('metro', '1=1', 0, array('order_by', 'asc'));

            echo json_encode(array('status' => 1, 'data' => $data));
          } else {
            echo json_encode(array('status' => 0, 'data' => ''));
          }
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function save_edit_ad_data()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $id = (int) $filtered_data['ad_id'];
        $user_id = (int) $filtered_data['user_id'];

        if ($this->check_token($user_id)) {
          $check = $this->universal_model->get_item_where('ads', array('user_id' => $user_id, 'id' => $id ), 'id');

          $cigarettes = $pets = $couple = $opposite_sex = 0;
          $settle_date_to = $filtered_data['to_date_checked']?date('Y-m-d H:i:s', strtotime($filtered_data['settle_date_to'])):'';

          if ($filtered_data['permitted_list']) {
            $array = $filtered_data['permitted_list'];
            $cigarettes  = in_array(1, $array)?1:0;
            $pets  = in_array(2, $array)?1:0;
            $couple = in_array(3, $array)?1:0;
            $opposite_sex = in_array(4, $array)?1:0;
          }

          $vars = array(
            'user_id' => (int) $filtered_data['user_id'],
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

          if (@$check && @$check->id) {
            $this->universal_model->update_table('ads', array('user_id' => $user_id, 'id' => $id), $vars);
            echo json_encode(array('status' => 1, 'msg' => 1, 'data' => ''));
          } else {
            echo json_encode(array('status' => 0, 'msg' => 'You have not permission to do this operation!', 'data' => ''));
          }
        } else {
          echo json_encode(array('status' => 0, 'msg' => 'Not authorized', 'data' => ''));
        }
      }

      public function upload_edit_photo()
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
              'ad_id' => (int)$this->input->post('ad_id'),
              'user_id' => (int)$this->input->post('user_id'),
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
  }
