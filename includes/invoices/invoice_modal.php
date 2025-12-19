<!-- Simplified Modal for Daily Items -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-labelledby="viewInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewInvoiceModalLabel">
                    <i class="bi bi-list-check me-2"></i>Daily Items
				</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
            <div class="modal-body p-0">
                <!-- Daily Summary Bar -->
                <div class="bg-light p-3 border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Date</small>
                            <strong id="modalDate">-</strong>
						</div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Total Items</small>
                            <strong id="modalItemCount">0</strong>
						</div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Total Amount</small>
                            <strong id="modalDailyTotal">0.00</strong>
						</div>
					</div>
				</div>
                
                <!-- Items Table -->
                <div class="p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="dailyItemsTable">
                            <thead>
                                <tr class="table-light">
                                    <th width="5%">#</th>
                                    <th width="45%">Item Name</th>
                                    <th width="15%" class="text-end">Quantity</th>
                                    <th width="15%" class="text-end">Unit Price</th>
                                    <th width="20%" class="text-end">Total</th>
								</tr>
							</thead>
                            <tbody id="dailyItemsBody">
                                <!-- Items will be populated here -->
							</tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                    <td class="text-end fw-bold" id="grandTotal">0.00</td>
								</tr>
							</tfoot>
						</table>
					</div>
                    
                    <!-- Empty state -->
                    <div id="emptyState" class="text-center py-5 d-none">
                        <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No items found for this day</p>
					</div>
				</div>
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>