<?php
// require APPPATH . '/libraries/ImplementJwt.php';

class Auth extends MY_Controller
{
  public function __construct()
  {
      parent::__construct();
      $this->objOfJwt = new ImplementJwt();
      header('Content-Type: application/json');
      $this->load->model('universal_model');
  }

  /////////// Generating Token and put user data into  token ///////////

  public function LoginToken()
  {
    $post_data = (array)json_decode(file_get_contents('php://input'));
    $filtered_data = $this->filter_data($post_data);

    $user = $this->universal_model->get_item_where('users', array('username' => $filtered_data['username'], 'password' => md5($filtered_data['password'])), 'id, name, surname, user_type, user_type_now');

    if (@$user && @$user->id) {
      if ($user->user_type == $user->user_type_now) {
        $tokenData['user_id'] = $user->id;
        $tokenData['timestamp'] = Date('Y-m-d h:i:s');
        $tokenData['iat'] = time();
        $tokenData['exp'] = time() + (3600*24);

        $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

        echo json_encode(array('token' => $jwtToken, 'id' => $user->id, 'user_type' => $user->user_type_now, 'status' => 1));
      } else {
        $user = $this->universal_model->get_item_where('users', array('username' => $filtered_data['username'], 'password' => md5($filtered_data['password']), 'user_type' => $user->user_type_now), 'id, name, surname, user_type, user_type_now');

        $tokenData['user_id'] = $user->id;
        $tokenData['timestamp'] = Date('Y-m-d h:i:s');
        $tokenData['iat'] = time();
        $tokenData['exp'] = time() + (3600*24);

        $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

        echo json_encode(array('token' => $jwtToken, 'id' => $user->id, 'user_type' => $user->user_type_now, 'status' => 1));
      }
    } else {
      echo json_encode(array('status' => 0));
    }
   }

   public function switch_func()
   {
     $post_data = (array)json_decode(file_get_contents('php://input'));
     $filtered_data = $this->filter_data($post_data);

     $user_id = (int) $filtered_data['user_id'];

     if ($this->check_token($user_id)) {
       $user = $this->universal_model->get_item_where('users', array('id' => $user_id), 'username, user_type');
       $user_type = !(int)$user->user_type;

       $check = $this->universal_model->get_item_where('users', array('username' => $user->username, 'user_type' => $user_type), 'id, username, user_type');

       if (@$check->id) {
         $this->universal_model->update_table('users', array('username' => $check->username), array('user_type_now' => $user_type));

         $tokenData['user_id'] = $check->id;
         $tokenData['timestamp'] = Date('Y-m-d h:i:s');
         $tokenData['iat'] = time();
         $tokenData['exp'] = time() + (3600*24);

         $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

         echo json_encode(array('token' => $jwtToken, 'id' => $check->id, 'user_type' => $check->user_type, 'status' => 1));
       } else {
         $this->load->model('auth_model');
         $this->universal_model->update_table('users', array('id' => $user_id), array('user_type_now' => $user_type));
         $result = $this->auth_model->switch_func($user_id, $user_type);

         $tokenData['user_id'] = $result;
         $tokenData['timestamp'] = Date('Y-m-d h:i:s');
         $tokenData['iat'] = time();
         $tokenData['exp'] = time() + (3600*24);

         $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

         echo json_encode(array('token' => $jwtToken, 'id' => $result, 'user_type' => $user_type, 'status' => 1));
       }
     }
   }

    //////// get data from token ////////////

    public function GetTokenData()
    {
        $received_Token = $this->input->request_headers('Authorization');
        try
        {
          $jwtData = $this->objOfJwt->DecodeToken($received_Token['Token']);
          echo json_encode($jwtData);
        }
        catch (Exception $e)
        {
          http_response_code('401');
          echo json_encode(array("status" => false, "message" => $e->getMessage()));
          exit;
        }
    }

    public function registration()
    {
      if($this->input->server('REQUEST_METHOD') === 'POST')
      {
        $this->load->model('auth_model');
        $post_data = (array)json_decode(file_get_contents('php://input'));
        $filtered_data = $this->filter_data($post_data);

        $username = $filtered_data['username'];
        $check_username = $this->auth_model->check_username($username);

        if (@$check_username && @$check_username->username) {
          echo json_encode(array('message' => 'User is not unique', 'status' => 'error'));
        } else {
          $vars = array(
            'username' => $filtered_data['username'],
            'email' => $filtered_data['mail'],
            'user_type' => (int) $filtered_data['user_type'],
            'user_type_now' => (int) $filtered_data['user_type'],
            'password' => md5($filtered_data['password']),
            'create_date' => date('Y-m-d H:i:s')
          );

          if ($filtered_data['user_type'] == 1)
            $vars['active'] = 1;

          $result = $this->universal_model->add_item($vars, 'users');

          if ($result)
            echo json_encode(array('message' => 'User is successfully added', 'status' => 'success'));
        }
      }
    }

    public function logout()
    {
      $this->load->model('auth_model');
      $post_data = (array)json_decode(file_get_contents('php://input'));

      $result = $this->auth_model->add_to_blacklist($post_data['token']);

      echo $result?json_encode(array('status' => 1)):'';
    }

    public function test()
    {
      $user_id = 11;
      $user = $this->universal_model->get_item_where('users', array('id' => $user_id), 'username, user_type');
      $user_type = !(int)$user->user_type;

      $check = $this->universal_model->get_item_where('users', array('username' => $user->username, 'user_type' => $user_type), 'id, user_type');

      if (@$check->id) {
        $tokenData['user_id'] = $check->id;
        $tokenData['timestamp'] = Date('Y-m-d h:i:s');
        $tokenData['iat'] = time();
        $tokenData['exp'] = time() + (3600*24);

        $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

        echo json_encode(array('token' => $jwtToken, 'id' => $check->id, 'user_type' => $check->user_type, 'status' => 1));
      } else {
        $this->load->model('auth_model');
        $this->universal_model->update_table('users', array('id' => $user_id), array('user_type_now' => $user_type));
        $result = $this->auth_model->switch_func($user_id, $user_type);

        $tokenData['user_id'] = $result;
        $tokenData['timestamp'] = Date('Y-m-d h:i:s');
        $tokenData['iat'] = time();
        $tokenData['exp'] = time() + (3600*24);

        $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

        echo json_encode(array('token' => $jwtToken, 'id' => $result, 'user_type' => $user_type, 'status' => 1));
      }
    }
}
