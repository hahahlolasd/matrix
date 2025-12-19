<div id="formWrapper">
	<form id="createInvoice" method="POST" enctype="multipart/form-data">
		
		<div class="row">
			
			<div class="col-12 offset-md-3 col-md-6">
				<div class="form-group">
					<label for="invoice_date"><?= $lang['date'] ?> <span class="required">*</span></label>
					<input name="invoice_date" id="invoice_date" type="text" class="datepicker form-control" required/>
				</div>
			</div>
			
			<div class="col-12">
				<div class="form-group">
					<label for="invoice_json"><?= $lang['json_file'] ?> <span class="required">*</span></label>
					<input name="invoice_json" id="invoice_json" type="file" class="dropify" data-height="100" accept=".json" required />
				</div>
			</div>
			
			<div class="col-12 text-center mt-3">
				<button id="saveButton" class="btn btn-success waves-effect waves-light w-md" type="submit">
					<i class="bi bi-cloud-arrow-up-fill"></i> <?= $lang['upload'] ?>
				</button>
			</div>
			
		</div>
		
	</form>
</div>