<li class="nav-item nav-item-submenu" data-seccion="usuarios">
	<a href="#" class="nav-link"><i class="icon-cog3"></i> <span>Configuracion</span></a>
	<ul class="nav nav-group-sub" data-submenu-title="<?= $this->router->fetch_class(); ?>">
		<li class="nav-item" data-seccion="listados"><a href="<?= base_url('Usuarios/listados/') ?>" class="nav-link"><i
					class="icon-users"></i>Usuarios</a></li>
		<li class="nav-item" data-seccion="profiles"><a href="<?= base_url('Usuarios/profiles') ?>" class="nav-link"><i
					class="icon-people"></i>Perfiles</a></li>
		<i class="fa-solid fa-address-card"></i>
		<li class="nav-item" data-seccion="uploader"><a href="<?= base_url('Uploader/index') ?>" class="nav-link"><i
					class="icon-upload"></i>Uploader</a></li>
		<i class="fa-solid fa-address-card"></i>
	</ul>
</li>