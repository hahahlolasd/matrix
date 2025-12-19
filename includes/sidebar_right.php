<!-- Sidebar-right-->
<div class="sidebar sidebar-right sidebar-animate">
	<div class="panel panel-primary card mb-0 box-shadow">
		
		<div class="tab-menu-heading border-0 sidebar-right-title">
			<div class="card-title mb-0"><i class="bi bi-plus"></i><?= $lang['create_new_item'] ?></div>
			<div class="card-options ms-auto">
				<a href="#" class="sidebar-remove btn btn-danger"><i class="bi bi-x-lg"></i> <?= $lang['cancel']?></a>
			</div>
		</div>
		
		<div class="panel-body sidebar-right-body border-0" id="sidebar_right_body">
			
			<?php 
				if (loadAsset('create_user_form', $host, $assets)) {
					include './includes/users/create_user_form.php';
				} 
				if (loadAsset('create_invoice_form', $host, $assets)) {
					include './includes/invoices/create_invoice_form.php';
				} 
			?>
			
		</div>
		
	</div>
</div>
<!--/Sidebar-right-->