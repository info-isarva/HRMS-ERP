<div id="rehire_employee" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rehire Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('exit-employees.rehire') }}" method="POST">
                    @csrf
                    <input type="hidden" name="emp_id" id="rehire_emp_id">
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                You are about to rehire <strong><span id="rehire_emp_name"></span></strong>. 
                                This will archive their previous employment history and reactivate their profile with a new Joining Date.
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>New Joining Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="new_joining_date" required>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea class="form-control" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button class="btn btn-primary submit-btn">Rehire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
