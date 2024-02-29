<div class="content d-flex justify-content-center align-items-center">

      <!-- Password recovery form -->
      <style>
            .login-form {}
      </style>
      <?php
      $parameters = array(
            'class' => "login-form"
      );

      ?>

      <?php echo form_open("auth/forgot_password", $parameters); ?>
      <div class="card mb-0">
            <div class="card-body">
                  <div class="text-center mb-3">
                        <i class="icon-spinner11 icon-2x text-warning border-warning border-3 rounded-pill p-3 mb-3 mt-1"></i>
                        <h5 class="mb-0"><?php echo lang('forgot_password_heading'); ?></h5>
                        <?php echo sprintf(lang('forgot_password_subheading'), $identity_label); ?>
                  </div>
                  <div id="infoMessage"><?php echo $message; ?></div>
                  <div class="form-group form-group-feedback form-group-feedback-right">
                        <?php
                        $identity['class'] = 'form-control';
                        $identity['placeholder'] = (($type == 'email') ? sprintf(lang('forgot_password_email_label'), $identity_label) : sprintf(lang('forgot_password_identity_label'), $identity_label));
                        echo form_input($identity); ?>
                        <!-- <input type="email" class="form-control" placeholder="Your email"> -->
                        <div class="form-control-feedback">
                              <i class="icon-mail5 text-muted"></i>
                        </div>
                  </div>

                  <button type="submit" class="btn btn-primary btn-block"><i class="icon-spinner11 mr-2"></i> Reset password</button>
            </div>
      </div>
      <?php echo form_close(); ?>
      <!-- /password recovery form -->

</div>