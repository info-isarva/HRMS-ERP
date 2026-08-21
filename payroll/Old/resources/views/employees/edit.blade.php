@extends('layouts.master')
@section('title', 'Edit Employee')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Edit Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                        <li class="breadcrumb-item active">Edit Employee</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <form method="POST" action="{{ route('employees.update', $employee->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

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
                    <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions">Permissions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="advances-tab" data-toggle="tab" href="#advances">Advances</a>
                </li>
            </ul>

            <div class="tab-content" id="employeeTabsContent">
                <!-- Basic Details Tab -->
                <div class="tab-pane fade show active" id="basic">
                    @include('employees.tabs.basic', ['employee' => $employee])
                </div>

                <!-- Personal Details Tab -->
                <div class="tab-pane fade" id="personal">
                    @include('employees.tabs.personal', ['employee' => $employee])
                </div>

                <!-- Bank Details Tab -->
                <div class="tab-pane fade" id="bank">
                    @include('employees.tabs.bank', ['employee' => $employee])
                </div>

                <!-- Salary Components Tab -->
                <div class="tab-pane fade" id="salary">
                    @include('employees.tabs.salary', ['employee' => $employee])
                </div>

                <!-- Statutory Components Tab -->
                <div class="tab-pane fade" id="statutory">
                    @include('employees.tabs.statutory', ['employee' => $employee])
                </div>

                <!-- OT and Other Incentive Tab -->
                <div class="tab-pane fade" id="ot-incentive">
                    @include('employees.tabs.ot-incentive', ['employee' => $employee])
                </div>

                <!-- Permissions Tab -->
                <div class="tab-pane fade" id="permissions">
                    @include('employees.tabs.permissions_debug', ['employee' => $employee])
                </div>

                <!-- Advances Tab -->
                <div class="tab-pane fade" id="advances">
                    @include('employees.tabs.advances', ['employee' => $employee])
                </div>
                
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
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

    // Make the functions globally available
    window.addComponentRow = addComponentRow;
});
</script>
@endsection
