<?php

class Public_Controller extends CI_Controller
{
  function __construct()
  {
    parent::__construct();
    echo 'This is from admin controller';
  }
}