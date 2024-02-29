	
	
	<!-- Page content -->
	<div class="page-content">

		<!-- Main content -->
		<div class="content-wrapper">

			<!-- Content area -->
			<div class="content d-flex justify-content-center align-items-center">

				<!-- Login form -->
				<form class="login-form" action="<?= base_url('auth/login')?>" method="POST">
					<div class="card mb-0">
						<div class="card-body">
							<div class="text-center mb-3">
								<i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"></i>
								<h5 class="mb-0">Ingreso al Administrador</h5>
								<span class="d-block text-muted"><?php echo lang('login_subheading');?></span>
							</div>
								<div id="infoMessage"><?php echo $message;?></div>
							<div class="form-group form-group-feedback form-group-feedback-left">
								<input type="text" class="form-control" placeholder="Username" name="identity" id="identity">
								<div class="form-control-feedback">
									<i class="icon-user text-muted"></i>
								</div>
							</div>

							<div class="form-group form-group-feedback form-group-feedback-left">
								<input type="password" class="form-control" placeholder="Password"  name="password" id="password">
								<div class="form-control-feedback">
									<i class="icon-lock2 text-muted"></i>
								</div>
							</div>

							<div class="form-group">
								<button name="submit" type="submit" class="btn btn-primary btn-block">Ingresar<i class="icon-circle-right2 ml-2"></i></button>
							</div>

							<div class="text-center">
						<p><a href="<?= base_url('/auth/forgot_password') ?>"><?php echo lang('login_forgot_password');?></a></p>
							</div>
						</div>
					</div>
				</form>
				<!-- /login form -->

			</div>
			<!-- /content area -->


		</div>
		<!-- /main content -->

	</div>
	<!-- /page content -->


