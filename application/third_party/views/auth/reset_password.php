<h1><?php echo lang('reset_password_heading'); ?></h1>
<?php

// echo $this->ion_auth->item('identity');die();

?>
<div id="infoMessage"><?php echo $message; ?></div>

<?php echo form_open('auth/reset_password/' . $code); ?>

<p>
      <label for="new_password"><?php echo sprintf(lang('reset_password_new_password_label'), $min_password_length); ?></label> <br />
      <?php echo form_input($new_password); ?>
</p>

<p>
      <?php echo lang('reset_password_new_password_confirm_label', 'new_password_confirm'); ?> <br />
      <?php echo form_input($new_password_confirm); ?>
</p>

<?php echo form_input($user_id); ?>
<?php echo form_hidden($csrf); ?>

<p><?php echo form_submit('submit', lang('reset_password_submit_btn')); ?></p>

<?php echo form_close(); ?>

<div class="content d-flex justify-content-center align-items-center">

</div>


<?php

// echo '<pre>';
// var_dump( $new_password ); 
// echo '</pre>';
// die();
?>

<!-- Page content -->
<div class="page-content">

      <!-- Main content -->
      <div class="content-wrapper">

            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center">

                  <!-- RESET form -->
                  <!-- <form class="login-form" action="<?= base_url('auth/login') ?>" method="POST"> -->
                  <?php 
                        $attributes = array("class" => "login-form" );

                        echo form_open('auth/reset_password/'.$code,$attributes);
                        
                  ?>
                        <div class="card mb-0">
                              <div class="card-body">
                                    <div class="text-center mb-3">
                                          <i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"></i>
                                          <h5 class="mb-0"><?php echo lang('reset_password_heading'); ?></h5>
                                    </div>
                                    <div id="infoMessage"><?php echo $message; ?></div>
                                    <div class="form-group form-group-feedback form-group-feedback-left">

                                          <?php
                                          $new_password['class'] = "form-control";
                                          $new_password['placeholder'] = "password";
                                          echo form_input($new_password);
                                          ?>

                                          <div class="form-control-feedback">
                                                <i class="icon-user text-muted"></i>
                                          </div>
                                    </div>

                                    <div class="form-group form-group-feedback form-group-feedback-left">

                                          <?php
                                          $new_password_confirm['class'] = "form-control";
                                          $new_password_confirm['placeholder'] = "re password";
                                          echo form_input($new_password_confirm);
                                          ?>
                                          <div class="form-control-feedback">
                                                <i class="icon-lock2 text-muted"></i>
                                          </div>
                                    </div>

                                    <div class="form-group">
                                          <button name="submit" type="submit" class="btn btn-primary btn-block">Ingresar<i class="icon-circle-right2 ml-2"></i></button>
                                    </div>

                                    <div class="text-center">
                                          <p><a href="<?= base_url('/auth/forgot_password') ?>"><?php echo lang('login_forgot_password'); ?></a></p>
                                    </div>
                              </div>
                        </div>
                        <?php echo form_input($user_id); ?>
                        <?php echo form_hidden($csrf); ?>
                        <?php echo form_close(); ?>
             

            </div>
            <!-- /content area -->


      </div>
      <!-- /main content -->

</div>
<!-- /page content -->