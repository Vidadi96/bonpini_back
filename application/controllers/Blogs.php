<?php

  class Blogs extends MY_Controller
  {
      public function __construct()
      {
        parent::__construct();
        $this->load->model('blogs_model');
        $this->load->model('universal_model');
      }

      public function get_blogs()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $page = isset($filtered_data['page'])?(int)$filtered_data['page']:1;

          $data = @$this->blogs_model->get_all_blogs($page, 30, '');
          $count = @$this->blogs_model->get_all_blogs_count();

          $page_count = $count->count%30==0?$count->count/30:((int)($count->count/30) + 1 );

          $vars = array(
            'blogs' => $data,
            'page_count' => $page_count
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function new_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array();
          echo json_encode(array('status' => 1, 'data' => $vars));
        }
      }

      public function add_new_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $html = $this->my_own_filter($get_data['html']);

        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $photo = $filtered_data['photo'];
          $title = $filtered_data['title'];

          $vars = array(
            'img' => $photo,
            'title' => $title,
            'html' => $html,
            'create_date' => date('Y-m-d H:i:s')
          );

          $result = $this->universal_model->add_item($vars, 'blog');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function delete_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $id = (int) $filtered_data['id'];

          $photos = $this->universal_model->get_item_where('blog', array('id' => $id), 'img');

          $name = $photos->img;
          $pathh = '/api/images/blog/';

          // $del_arr = explode('.', $name);
					if (file_exists($this->config->item('server_root').$pathh.$name))
						@unlink($this->config->item("server_root").$pathh.$name);
					// if (file_exists($this->config->item('server_root').$pathh.$del_arr[0].'.webp'))
					// 	@unlink($this->config->item("server_root").$pathh.$del_arr[0].'.webp');

          $result = $this->universal_model->delete_item(array('id' => $id), 'blog');

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
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

            $upload_location = $this->config->item('server_root').'/api/images/blog/'.$img['file_name'];
            $this->resize->resizeImage(800, 800, 'landscape');
    				$this->resize->saveImage($upload_location, 90, $deg);

            echo json_encode(array('status' => 1, 'photo' => $img['file_name']));

            unlink($img['full_path']);
          }
        }
      }

      public function edit_photo()
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

            if (@file_exists($this->config->item('server_root')."/api/images/blog/".$this->input->post('photo')))
    					unlink($this->config->item("server_root")."/api/images/blog/".$this->input->post('photo'));

            $upload_location = $this->config->item('server_root').'/api/images/blog/'.$img['file_name'];
            $this->resize->resizeImage(800, 800, 'landscape');
    				$this->resize->saveImage($upload_location, 90, $deg);

            $this->universal_model->update_table('blog', array('id' => (int)$this->input->post('id')), array('img' => $img['file_name']));

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

				if(file_exists($this->config->item('server_root').$pathh."/blog/".$img))
					unlink($this->config->item("server_root").$pathh."/blog/".$img);
				if(file_exists($this->config->item('server_root').$pathh.'/'.$img))
					unlink($this->config->item('server_root').$pathh.'/'.$img);

        echo json_encode(array('status' => 1));
      }

      public function edit_blog($id = 0)
      {
        $id = (int) $id;
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        if ($this->check_token((int) $filtered_data['user_id'])) {
          $data = $this->universal_model->get_more_item_select_row('blog', '*', array('id' => $id));

          $vars = array(
            'blog' => $data
          );

          echo json_encode(array('status' => 1, 'data' => $vars));
        } else {
          echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function save_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $html = $this->my_own_filter($get_data['html']);

        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $title = $filtered_data['title'];

          $vars = array(
            'title' => $title,
            'html' => $html
          );

          $result = $this->universal_model->update_table('blog', array('id' => (int) $filtered_data['id']), $vars);

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function update_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        if (@$this->check_token($user_id)) {
          $vars = array('show_in' => (int) $filtered_data['show_in']);

          $result = $this->universal_model->update_table('blog', array('id' => (int) $filtered_data['id']), $vars);

          if ($result)
            echo json_encode(array('status' => 1, 'data' => ''));
          else
            echo json_encode(array('status' => 0, 'data' => ''));
        }
      }

      public function get_site_blog()
      {
        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];
        $blogs = $this->blogs_model->get_all_blogs(1, 15, ' and show_in = 0');
        $header_blogs = $this->universal_model->get_more_item_where(array('show_in' => 1), 'blog');

        if ($blogs) {
          for ($i = 0; $i < count($blogs); $i++)
            $blogs[$i]->create_date = date('d-m-Y', strtotime($blogs[$i]->create_date));
        }

        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'header_data' => $header_data,
          'blogs' => $blogs,
          'header_blogs' => $header_blogs
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }

      public function blog_in($id)
      {
        $id = (int) $id;

        $get_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($get_data);

        $user_id = (int) $filtered_data['user_id'];

        $blog = $this->universal_model->get_item_where('blog', array('id' => $id), '*');
        $header_data = $this->get_site_header_data($user_id);

        $vars = array(
          'blog' => $blog,
          'header_data' => $header_data
        );

        echo json_encode(array('status' => 1, 'data' => $vars));
      }
  }
