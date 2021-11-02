<?php
// require APPPATH . '/libraries/ImplementJwt.php';

class Auth_adm extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->objOfJwt = new ImplementJwt();
        header('Content-Type: application/json');
    }

    /////////// Generating Token and put user data into  token ///////////

    public function LoginToken()
    {
      $post_data = (array)json_decode(file_get_contents('php://input'));
      $filtered_data = $this->filter_data($post_data);

      $form_response = $filtered_data['captcha_response'];
      $url = 'https://www.google.com/recaptcha/api/siteverify';
      $secret = 'xxxx';
      $response = file_get_contents($url."?secret=".$secret."&response=".$form_response."&remoteip=".$_SERVER['REMOTE_ADDR']);
      $data = json_decode($response);

      if (isset($data->success) && $data->success=="true") {
        $this->load->model('universal_model');
        $hashed_password = $this->get_hashed_password($filtered_data['password']);
        $user = $this->universal_model->get_item_where('admin_users', array('username' => $filtered_data['username'], 'password' => $hashed_password, 'deleted' => 0, 'active' => 1), '*');

        if (@$user && @$user->id) {
          $tokenData['user_id'] = $user->id;
          $tokenData['timestamp'] = Date('Y-m-d H:i:s');
          $tokenData['iat'] = time();
          $tokenData['exp'] = time() + (3600*24);

          $jwtToken = $this->objOfJwt->GenerateToken($tokenData);

          echo json_encode(array('status' => 1, 'token' => $jwtToken, 'id' => $user->id, 'username' => $user->username));
        } else {
          echo json_encode(array('status' => 0, 'data' => array('error' => 'Wrong user or password')));
        }
      } else {
        echo json_encode(array('status' => 0, 'data' => array('error' => 'Check a captcha')));
      }
    }

    public function logout()
    {
      $this->load->model('auth_model');
      $post_data = (array)json_decode(file_get_contents('php://input'));

      $result = $this->auth_model->add_to_blacklist($post_data['token']);

      echo $result?json_encode(array('status' => 1)):'';
    }
}
