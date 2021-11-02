<?php

  class Auto extends MY_Controller
  {
    public function __construct()
    {
      parent::__construct();
      $this->load->model('auto_model');
      $this->load->model('universal_model');
    }

    public function log_user_type_day($secret_key)
    {
      if ($secret_key == 'Mys1cr1tkiy')
      {
        $landlord = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'user_type' => 0));
        $tenant = $this->universal_model->get_more_item_select_row('users', 'count(*) as "count"', array('active' => 1, 'user_type' => 1));

        $vars = array(
          'landlord' => (int) $landlord->count,
          'tenant' => (int) $tenant->count,
          'day' => date('Y-m-d')
        );

        $this->universal_model->add_item($vars, 'user_type_day');
      }
    }
  }
