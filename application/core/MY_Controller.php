<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . '/libraries/ImplementJwt.php';

class MY_Controller extends CI_Controller
{
	function __construct()
	{
		// $this->output->enable_profiler(TRUE);
		parent::__construct();
		$this->load->model('my_controller_model');
		$this->load->model('universal_model');
	}

	public function get_site_header_data($user_id)
	{
		$vars = [];
		$new_message = 0;
		$new_message_date = 0;

		if (@$this->check_token($user_id)) {
			$notifications = $this->my_controller_model->get_notifications($user_id);
			$notifications_count = $this->my_controller_model->get_notifications_count($user_id);

			if ($notifications) {
				for ($i = 0; $i < count($notifications); $i++) {
					$notifications[$i]['create_date'] = strtotime(date("Y-m-d H:i:s")) - strtotime($notifications[$i]['create_date']);
					$notifications[$i]['date'] = strtotime(date("Y-m-d H:i:s")) - strtotime($notifications[$i]['date']);
				}
			}

			$seen_array = $this->my_controller_model->get_seen_or_not($user_id);

			if (@$seen_array) {
				foreach ($seen_array as $row) {
					if ($row->seen == 0) {
						$new_message = 1;
						$new_message_date = strtotime(date('Y-m-d H:i:s')) - strtotime($row->last_date);
						break;
					}
				}
			}

			$profile = $this->universal_model->get_item_where('users', array('id' => $user_id), 'img, name, surname');

			// $langs_arr = $this->universal_model->get_more_item_select('admin_langs', '*', array('where_for' => 0), 0, array('lang_key', 'asc'));
			// $langs = [];
			//
			// foreach ($langs_arr as $row)
			// 	$langs[$row->lang_key] = array( "lang_1" => $row->lang_1, "lang_2" => $row->lang_2, "lang_3" => $row->lang_3, "lang_4" => $row->lang_4);


			$vars = array(
				'notifications' => $notifications,
				'new_message' => $new_message,
				'new_message_date' => $new_message_date,
				'count' => $notifications_count->count,
				'profile' => $profile
				// 'langs' => $langs
			);
		}

		return $vars;
	}

	public function get_hashed_password($password)
	{
		$key = $this->config->item('encryption_key');
		$salt1 = hash('sha512', $key.$password);
		$salt2 = hash('sha512', $password.$key);
		$hashed_password = md5(hash('sha512', $salt1.$password.$salt2));

		return $hashed_password;
	}

	function check_token($user_id)
	{
		$this->objOfJwt = new ImplementJwt();

		$received_Token = $this->input->request_headers('Authorization');

		$jwtData = $this->objOfJwt->DecodeToken($received_Token['Token']);

		if (@isset($jwtData['message']) && @$jwtData['message'] == 'Expired token') {
			return false;
		} else {
			if ($jwtData['user_id'] == $user_id)
				return true;
			else
				return false;
		}
	}

	function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

  function do_upload($inputname, $upload_path, $file_size=20000, $img_name="", $types='txt|gif|jpg|png|TIF|TIFF|pdf|JPEG|jpeg|doc|docx|xls|xlsx|ppt|pptx')
  {
	 	$data = array();
		$config['upload_path'] = $upload_path;
		$config['overwrite'] = 1;
		$config['allowed_types'] = $types;
		$config["max_size"] = $file_size;
		$config['file_name'] = $img_name."_".strtotime(date("Y-m-d H:i:s"));
		$this->load->library('upload', $config);
		if (!$this->upload->do_upload($inputname))
			$data = array('error' => $this->upload->display_errors());
		else
			$data = $this->upload->data();

    return $data;
  }

	function resize_image($image_path=null, $resizeDirName="certificate", $width=null, $height=null, $maintain_ratio=FALSE,$if_width_max=FALSE, $attributes=null, $return_direct_link=false)
	{
		$CI =& get_instance();
		$path_parts = pathinfo($image_path);
		$image_name = $path_parts['filename'];
		@$image_thumb = $resizeDirName.'/'.$image_name.'.'.$path_parts['extension'];
		$config="";
		$img_width = "";
		$CI->load->library('image_lib');
		//$CI->image_lib->clear();
		if(!file_exists($image_thumb))
		{
			$config['image_library'] = 'GD2';
			$config['source_image'] = $image_path;
			$config['new_image'] = $image_thumb;
			$config['maintain_ratio'] = $maintain_ratio;

			if($if_width_max==TRUE)
			{
				list($img_width, $img_height) = getimagesize($image_path);
				if($img_width > $width)
				{
					$config['height'] = $height;
					$config['width'] = $width;
				}
			}else
			{
				$config['height'] = $height;
				$config['width'] = $width;
			}
			$CI->image_lib->initialize($config);
			if(!$CI->image_lib->resize())
			{
				return $CI->image_lib->display_errors();
			}
			$CI->image_lib->clear();
		}
	}

	function convert_str($str)
	{
	    $search_tr = array('ı', 'İ', 'Ğ', 'ğ', 'Ü', 'ü', 'Ş', 'ş', 'Ö', 'ö', 'Ç', 'ç', 'Ə','ə','*','!','`','~','@','"','#','$','%','^','&','?',',','|','\\','/','.',']','[','+','-',')','(',';',"'", ' ', '&nbsp;','“','”','№');
	    $replace_tr = array('i', 'I', 'G', 'g', 'U', 'u', 'S', 's', 'O', 'o', 'C', 'c','E','e','','','','','','','','','','','','','','','','','','','','','','','','',"", '-', '-','','','');
	    $str = str_replace($search_tr, $replace_tr, $str);
	    $str = strip_tags($str);
		return $str;
	}

	function filter_data($array)
	{
		$data = array();
		foreach ($array as $key => $value) {
			if(is_array($value))
				$data[$key]= $value;
			else
				$data[$key]= filter_var(str_replace(array("'", '"',"`", ')', '('), array("","","","",""), $this->security->xss_clean(strip_tags(rawurldecode($value)))), FILTER_SANITIZE_STRING);
		}
		return $data;
	}

  /***********GALLERY**************/
  private function resize_all_image($full_path, $img_name)
  {
      if(!file_exists($this->config->item('server_root').'/assets/img/car_photos/90x90/'.$img_name))
      {
          $this->load->library('resize');
          $this->resize->getFileInfo($full_path);
          $this->resize->resizeImage(90, 90, 'crop');
          $this->resize->saveImage($this->config->item('server_root').'/assets/img/car_photos/90x90/'.$img_name, 75);
					$this->resize->resizeImage(1000, 1000, 'landscape');
          $this->resize->saveImage($this->config->item('server_root').'/assets/img/car_photos/800xauto/'.$img_name, 90);
      }
  }

  function getRealFile($file) {
      $uploadDir ="/img/products/";
      $realUploadDir = $this->config->item("server_root").'/img/products/';

      return str_replace($uploadDir, $realUploadDir, $file);
  }

	function ajax($pathh)
  {
      require($this->config->item("server_root").'/class.fileuploader.php');
      $_action = isset($_GET['type']) ? $_GET['type'] : '';

			$uploadDir = $this->config->item("server_root").$pathh.'/';
			$realUploadDir = $this->config->item("server_root").$pathh.'/';

			if($this->input->get())
				$filtered_get = $this->filter_data($this->input->get());

			$product_id = ($this->input->get('product_id'))?(int)$filtered_get['product_id']:0;

      // upload
      if ($_action == 'upload') {
          $id = false;
					$generated_name = $this->generate_name();
          $title = 'img_'.$generated_name;

					//$_FILES['files']['name']
          // initialize FileUploader
          $FileUploader = new FileUploader('files', array(
              'limit' => 1,
              'fileMaxSize' => 20,
              'extensions' => array('image/*'),
              'uploadDir' => $realUploadDir,
              'required' => true,
              'title' => $title,
              'replace' => $id,
              'editor' => array(
                  'maxWidth' => 1980,
                  'maxHeight' => 1980,
                  'crop' => false,
                  'quality' => 90
              )
          ));

          $upload = $FileUploader->upload();

          if (count($upload['files']) == 1) {
              $item = $upload['files'][0];
              $file = $uploadDir.'/'.$item['name'];
							$deg = $this->correctImageOrientation($file);

							$location_arr = $this->universal_model->get_item_where('carphoto', array('caradid' => $product_id), 'MAX(location) as "location"');
							$location = $location_arr?$location_arr->location:0;

							$query = $this->universal_model->add_item(array('name' => $item['name'], 'location' => ($location + 1), 'caradid' => $product_id, 'active' => 1, 'deleted' => 0, 'pincode' => ''), "carphoto");

              if ($query) {
                  $upload['files'][0] = array(
                      'title' => $item['title'],
                      'thumb'=> $pathh.'/'.$item['name'],
                      'name' => $item['name'],
                      'size' => $item['size'],
                      'size2' => $item['size2'],
                      'url' => $file,
                      'id' => $query
                  );
              } else {
                  unset($upload['files'][0]);
                  $upload['hasWarnings'] = true;
                  $upload['warnings'][] = 'An error occured.';
              }

							$this->load->library('resize');
	    				$this->resize->getFileInfo($this->config->item('server_root').$pathh.'/'.$item['name']);
	    				$this->resize->resizeImage(1000, 1000, 'landscape');
	    				$this->resize->saveImage($this->config->item('server_root').'/assets/img/car_photos/800xauto/'.$item['name'], 90, $deg);

							$ext = $item['extension'];
							$upload_location = $this->config->item('server_root').'/assets/img/car_photos/800xauto/'.$item['name'];
	            $watermark_image = imagecreatefrompng($this->config->item('server_root').'/assets/img/car_photos/logo/otomoto_logo_transparent_big.png');
	            if ($ext == 'jpg' || $ext == 'jpeg')
	              $image = imagecreatefromjpeg($upload_location);

	            if ($ext == 'png')
	              $image = imagecreatefrompng($upload_location);

	            $watermark_image_width = imagesx($watermark_image);
	            $watermark_image_height = imagesy($watermark_image);
	            imagecopy($image, $watermark_image, (imagesx($image) - $watermark_image_width)/2, (imagesy($image) - $watermark_image_height)/2, 0, 0, $watermark_image_width, $watermark_image_height);
	            imagepng($image, $upload_location);

							$array = explode('.', $item['name']);
							$jpg = $image;
							$w = imagesx($jpg);
							$h = imagesy($jpg);
							$webp = imagecreatetruecolor($w,$h);
							imagecopy($webp, $jpg, 0, 0, 0, 0, $w, $h);
							imagewebp($webp, $this->config->item('server_root').'/assets/img/car_photos/800xauto/'.$array[0].'.webp', 80);
							imagedestroy($jpg);
							imagedestroy($webp);

	    				$this->resize->resizeImage(360, 360, 'auto');
	    				$this->resize->saveImage($this->config->item('server_root').'/assets/img/car_photos/90x90/'.$item['name'], 90, $deg);

							$upload_location = $this->config->item('server_root').'/assets/img/car_photos/90x90/'.$item['name'];
	            $watermark_image = imagecreatefrompng($this->config->item('server_root').'/assets/img/car_photos/logo/otomoto_logo_transparent0.png');
	            if ($ext == 'jpg' || $ext == 'jpeg')
	              $image = imagecreatefromjpeg($upload_location);

	            if ($ext == 'png')
	              $image = imagecreatefrompng($upload_location);

	            $watermark_image_width = imagesx($watermark_image);
	            $watermark_image_height = imagesy($watermark_image);
							imagecopy($image, $watermark_image, (imagesx($image) - $watermark_image_width)/2, (imagesy($image) - $watermark_image_height)/2, 0, 0, $watermark_image_width, $watermark_image_height);
	            imagepng($image, $upload_location);

							$array = explode('.', $item['name']);
							$jpg = $image;
							$w = imagesx($jpg);
							$h = imagesy($jpg);
							$webp = imagecreatetruecolor($w,$h);
							imagecopy($webp, $jpg, 0, 0, 0, 0, $w, $h);
							imagewebp($webp, $this->config->item('server_root').'/assets/img/car_photos/90x90/'.$array[0].'.webp', 80);
							imagedestroy($jpg);
							imagedestroy($webp);

	    				unlink($this->config->item('server_root').$pathh.'/'.$item['name']);
          }

					$upload['otomoto'] = $this->security->get_csrf_hash();
          echo json_encode($upload);
          exit;
      }

      // preload
      if ($_action == 'preload') {
          $preloadedFiles = array();

          $query = $this->universal_model->get_more_item("carphoto", array('caradid' => $product_id, 'active' => 1), 0, array("location", "asc"));
          if ($query) {
              foreach($query as $row) {
                  $preloadedFiles[] = array(
                      'name' => $row->name,
                      'type' => getimagesize($this->config->item("server_root").$pathh."/90x90/".$row->name)['mime'],
                      'size' => filesize($this->config->item("server_root").$pathh."/90x90/".$row->name),
                      'file' => $pathh."/90x90/".$row->name,
                      'data' => array(
                          'readerForce' => true,
                          'url' => $pathh."/90x90/".$row->name,
                          'listProps' => array(
                              'id' => $row->id,
                          )
                      )
                  );
              }
              echo json_encode($preloadedFiles);
          }
					else
            echo "[]";

          exit;
      }

      // sort
      if ($_action == 'sort') {
          $id = 0;
          if (isset($_POST['list'])) {
            $list = json_decode($_POST['list'], true);

            for($i=0; $i<count($list); $i++) {
              if (!isset($list[$i]['id']) || !isset($list[$i]['index']))
                break;
              $id = (int)$list[$i]['id'];
              $result = $this->universal_model->item_edit_save_where(array("location"=>$list[$i]['index']), array("id" => $id), "carphoto");
            }
          }
          exit;
      }

      // asmain
      if ($_action == 'asmain') {
      }

      // remove
      if ($_action == 'remove') {
        if (isset($_POST['id']) && isset($_POST['name'])) {
          $id = $this->input->post('id');
          $img = $_POST['name'];

					$raw = $this->universal_model->get_item_where('carphoto', array('id' => $id), 'name');

					if($raw)
					{
						$del_arr = explode('.', $raw->name);
						if(file_exists($this->config->item('server_root').$pathh."/800xauto/".$raw->name))
							unlink($this->config->item("server_root").$pathh."/800xauto/".$raw->name);
						if(file_exists($this->config->item('server_root').$pathh."/90x90/".$raw->name))
							unlink($this->config->item("server_root").$pathh."/90x90/".$raw->name);
						if(file_exists($this->config->item('server_root').$pathh."/800xauto/".$del_arr[0].'.webp'))
							unlink($this->config->item("server_root").$pathh."/800xauto/".$del_arr[0].'.webp');
						if(file_exists($this->config->item('server_root').$pathh."/90x90/".$del_arr[0].'.webp'))
							unlink($this->config->item("server_root").$pathh."/90x90/".$del_arr[0].'.webp');
						$img_base_name = explode(".", $raw->name);
						$img_name = md5("or".@$img_base_name[0]).".".@$img_base_name[1];
						if(file_exists($this->config->item('server_root').$pathh.'/'.$img_name))
							unlink($this->config->item('server_root').$pathh.'/'.$img_name);

						$this->universal_model->delete_item_where(array("id" => $id), "carphoto");
					}
        }
        exit;
      }
  }

	function correctImageOrientation($filename)
  {
    $deg = 0;
    $exif = @exif_read_data($filename);
    if($exif && isset($exif['Orientation'])) {
      $orientation = $exif['Orientation'];
      if($orientation != 1){
        switch ($orientation) {
          case 3:
            $deg = 180;
            break;
          case 6:
            $deg = 270;
            break;
          case 8:
            $deg = 90;
            break;
        }
      }
    }
    return $deg;
  }

	function my_own_filter($html)
	{
		$html = preg_replace('#(onabort|onactivate|onafterprint|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onblur|onbounce|oncellchange|onchange|onclick|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavaible|ondatasetchanged|ondatasetcomplete|ondblclick|ondeactivate|ondrag|ondragdrop|ondragend|ondragenter|ondragleave|ondragover|ondragstart|ondrop|onerror|onerrorupdate|onfilterupdate|onfinish|onfocus|onfocusin|onfocusout|onhelp|onkeydown|onkeypress|onkeyup|onlayoutcomplete|onload|onlosecapture|onmousedown|onmouseenter|onmouseleave|onmousemove|onmoveout|onmouseover|onmouseup|onmousewheel|onmove|onmoveend|onmovestart|onpaste|onpropertychange|onreadystatechange|onreset|onresize|onresizeend|onresizestart|onrowexit|onrowsdelete|onrowsinserted|onscroll|onselect|onselectionchange|onselectstart|onstart|onstop|onsubmit|onunload)\\s*=\\s*".*?"#is', '', $html);

		$html = strip_tags($html, "<h1><h2><h3><h4><h5><h6><th><td><tr><tfoot><thead><tbody><table><img><br><span><u><b><i><small><strong><em><div><li><ul><ol><hr><p><footer><header><body><title><head><html>");

		return $html;
	}

}
