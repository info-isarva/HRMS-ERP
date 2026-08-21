@extends('layouts.master')
@section('title', 'PaySlips')
@section('content')

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <h2>Payslip</h2>
                <a class="btn btn-custom " href="{{ route('payroll/payslip-pdf') }}" target="_blank" >payslips </a>

            </div>
            @include('payroll.payslips.payslip-form')
        </div>
    </div>


@endsection