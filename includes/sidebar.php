<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll">
	<div class="main-sidebar-header active">
		
		<a class="desktop-logo logo-light active" href="index"><h3><?= $appNameShort ?></h3></a>
		<a class="logo-icon mobile-logo icon-light" href="index"><i class="bi bi-house-door-fill"></i></a>
	</div>
	<div class="main-sidemenu">
		<ul class="side-menu">
			
			<?php // HOME // FŐ SZEKCIÓ ?>
			
			<li class="side-item side-item-category"><?= $lang['main_section'] ?></li>
			<li class="slide">
				<a class="side-menu__item" href="index"><span class="material-symbols-outlined">home</span> <span class="side-menu__label"><?= $lang['dashboard'] ?></span></a>
			</li>
			
			<?php // USERS // FELHASZNÁLÓK ?>
			
			<?php if ($sup_admin && in_array(2, $permissions)) { ?>
				<li class="side-item side-item-category"><?= $lang['users'] ?></li>
				<li class="slide">
					<a class="side-menu__item" href="users"><span class="material-symbols-outlined">groups</span> <span class="side-menu__label"><?= $lang['users'] ?></span></a>
				</li>
			<?php } ?>
			
			<?php // INVOICES // SZÁMLÁK ?>
			
			<?php if ($sup_admin || in_array(6, $permissions)) { ?>
			<li class="side-item side-item-category"><?= $lang['invoices'] ?></li>
			<li class="slide">
				<a class="side-menu__item" href="invoices"><span class="material-symbols-outlined">receipt_long</span> <span class="side-menu__label"><?= $lang['invoices'] ?></span></a>
			</li>
			<?php } ?>
			
			<?php // LOGOUT // KILÉPÉS ?>
			
			<li class="side-item side-item-category"><?= $lang['logout'] ?></li>
			<li class="slide">
				<a class="side-menu__item" href="logout"><i class="bi bi-box-arrow-left"></i> <span class="side-menu__label"><?= $lang['logout'] ?></span></a>
			</li>
			
		</ul>
	</div>
</aside>
<!-- main-sidebar -->