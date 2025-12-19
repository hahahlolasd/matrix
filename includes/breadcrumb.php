<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title"><span class="text-muted tx-17"><?= $pageCategory ?> /</span> <?= $pageName ?></h4>
		</div>
	</div>
    
    <?php if (loadAsset('breadcrumbTools', $host, $assets)) : ?>
    <div class="d-flex my-xl-auto right-content justify-content-between gap-3">
	
        <?php if (canFilterName($host)) : ?>
        <div class="d-flex">
            <input type="search" id="<?= $host ?>_search" class="form-control" placeholder="<?= $lang['search'] ?>...">
		</div>
        <?php endif; ?>
        
        <?php if (canFilterDate($host)) : ?>
        <div class="d-flex gap-3">
            <input type="text" id="<?= $host ?>_date_from" class="form-control datepicker" placeholder="<?= $lang['from_date'] ?>">
            <input type="text" id="<?= $host ?>_date_to" class="form-control datepicker" placeholder="<?= $lang['to_date'] ?>">
		</div>
        <?php endif; ?>
        
        <?php if (canCreate($host)) : ?>
        <div class="btn btn-primary align-items-center gap-2 d-flex" id="<?= $host ?>_add_new" data-bs-toggle="sidebar-right" data-bs-target=".sidebar-right">
            <span class="material-symbols-outlined">add</span> <?= $addNewText ?>
		</div>
        <?php endif; ?>
        
	</div>
	<?php endif; ?>
    
</div>