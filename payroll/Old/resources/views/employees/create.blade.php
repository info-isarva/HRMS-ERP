@extends('layouts.master')
@section('title', 'Add Employees')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <h2>Create Employee</h2>
            
            <form method="POST" action="{{ route('employees.save') }}">
                @csrf

                <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic">Basic Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="personal-tab" data-toggle="tab" href="#personal">Personal Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="bank-tab" data-toggle="tab" href="#bank">Bank Details</a>
                    </li>                    
                    <li class="nav-item">
                        <a class="nav-link" id="salary-tab" data-toggle="tab" href="#salary">Salary Components</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="statutory-tab" data-toggle="tab" href="#statutory">Statutory Components</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ot-incentive-tab" data-toggle="tab" href="#ot-incentive">OT & Incentives</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="advances-tab" data-toggle="tab" href="#advances">Advances</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions">Permissions</a>
                    </li>
                </ul>

                <div class="tab-content" id="employeeTabsContent">
                    <!-- Basic Details Tab -->
                    <div class="tab-pane fade show active" id="basic">
                        @include('employees.tabs.basic')
                    </div>

                    <!-- Personal Details Tab -->
                    <div class="tab-pane fade" id="personal">
                        @include('employees.tabs.personal')
                    </div>

                    <!-- Bank Details Tab -->
                    <div class="tab-pane fade" id="bank">
                        @include('employees.tabs.bank')
                    </div>

                    <!-- Salary Components Tab -->
                    <div class="tab-pane fade" id="salary">
                        @include('employees.tabs.salary')
                    </div>

                    <!-- Statutory Components Tab -->
                    <div class="tab-pane fade" id="statutory">
                        @include('employees.tabs.statutory')
                    </div>
                    <!-- OT and Other Incentive Tab -->
                    <div class="tab-pane fade" id="ot-incentive">
                        @include('employees.tabs.ot-incentive')
                    </div>

                    <!-- Advances Tab -->
                    <div class="tab-pane fade" id="advances">
                        @include('employees.tabs.advances')
                    </div>

                    <!-- Permissions Tab -->
                    <div class="tab-pane fade" id="permissions">
                        @include('employees.tabs.permissions_debug')
                    </div>
                    
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching and error focus
    @if($errors->any())
        let firstError = document.querySelector('.is-invalid');
        if (firstError) {
            let tabPane = firstError.closest('.tab-pane');
            if (tabPane) {
                let tabLink = document.querySelector(`a[href="#${tabPane.id}"]`);
                tabLink.click();
            }
        }
    @endif

    // Add dynamic rows for components
    function addComponentRow(containerId, templateId) {
        const container = document.querySelector(`#${containerId}`);
        const template = document.querySelector(`#${templateId}`);
        const clone = template.content.cloneNode(true);
        const index = container.children.length;
        
        clone.querySelectorAll('input, select').forEach(element => {
            const name = element.getAttribute('name').replace('0', index);
            element.setAttribute('name', name);
        });
        
        container.appendChild(clone);
    }

    window.addStatutoryRow = () => addComponentRow('statutoryComponents', 'statutoryTemplate');
    window.addSalaryRow = () => addComponentRow('salaryComponents', 'salaryTemplate');
});
</script>

@endsection