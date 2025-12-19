<!-- main-header -->
<div class="main-header sticky side-header nav nav-item">
	<div class="container-fluid">
		<div class="main-header-left">
			<div class="app-sidebar__toggle" data-bs-toggle="sidebar">
				<a class="open-toggle" href="#"><i class="header-icon bi bi-list"></i></a>
				<a class="close-toggle" href="#"><i class="header-icon bi bi-x"></i></a>
			</div>
		</div>
		<div class="main-header-right">
			<ul class="nav nav-item  navbar-nav-right ms-auto">
				
				<li class="nav-item full-screen fullscreen-button">
					<a class="new nav-link full-screen-link" href="#"><svg
						xmlns="http://www.w3.org/2000/svg" class="header-icon-svgs" viewBox="0 0 24 24"
						fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
						stroke-linejoin="round" class="feather feather-maximize">
							<path
							d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3">
							</path>
						</svg>
					</a>
				</li>
				
				<li class="dropdown main-profile-menu nav nav-item nav-link">
					<a class="profile-user d-flex" href="">
						
						<?php
							if (isset($_COOKIE['name'])) {
								$fullName = $_COOKIE['name'];
								echo "{$lang['welcome']} " . htmlspecialchars($fullName) . "!";
							}
						?>
					</a>
					
					<div class="dropdown-menu">
						<a class="dropdown-item" href="profile.php"><i class="bi bi-person-fill"></i><?= $lang["profile"] ?></a>
						<a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i><?= $lang["logout"] ?></a>
					</div>
				</li>
				
				<li class="dropdown main-profile-menu nav nav-item nav-link language-switcher d-flex align-items-center">
					<a href="">
						<img src="assets/img/flags/<?= $_COOKIE['lang'] ?>.png" alt="Current Language" />
					</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="setLang('hu'); return false;">
							<?= $_COOKIE['lang'] == "hu" ? "<span class='lang-selected'><img src='assets/img/flags/hu.png' /></span>" : "<img src='assets/img/flags/hu' />" ?>
						</a>
						<a class="dropdown-item" href="#" onclick="setLang('sr'); return false;">
							<?= $_COOKIE['lang'] == "sr" ? "<span class='lang-selected'><img src='assets/img/flags/sr.png' /></span>" : "<img src='assets/img/flags/sr.png' />" ?>
						</a>
					</div>
				</li>
				
			</ul>
		</div>
	</div>
</div>
<!-- /main-header -->