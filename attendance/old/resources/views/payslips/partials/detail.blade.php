<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    {{-- Preview header --}}
    <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file-invoice text-emerald-600"></i>
            </div>
            <div class="min-w-0">
                <h2 class="font-bold text-gray-900 truncate">Payslip — {{ $detail['period_label'] }}</h2>
                <p class="text-sm text-gray-500 truncate">
                    {{ $detail['employee']['name'] }} · {{ $detail['employee']['employee_id'] }}
                </p>
            </div>
        </div>
        <a href="{{ route('payslips.download', ['month' => $detail['month'], 'year' => $detail['year']]) }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-semibold rounded-lg shadow hover:from-emerald-700 hover:to-teal-700 transition-all flex-shrink-0">
            <i class="fas fa-download mr-2"></i> Download PDF
        </a>
    </div>

    <div class="p-5 sm:p-8">
        {{-- Company header --}}
        <div class="text-center mb-8">
            @if(!empty($detail['company']['logo_url']))
                <img src="{{ $detail['company']['logo_url'] }}" alt="Company logo" class="h-14 mx-auto object-contain mb-3">
            @else
                <div class="w-14 h-14 mx-auto mb-3 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-bold">
                    LOGO
                </div>
            @endif
            <h3 class="text-lg font-bold text-gray-900 uppercase tracking-wide">{{ $detail['company']['name'] }}</h3>
            @if(!empty($detail['company']['address']))
                <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">{{ $detail['company']['address'] }}</p>
            @endif
            <span class="inline-flex items-center mt-4 px-4 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                <i class="fas fa-file-lines mr-1.5"></i> Salary Slip
            </span>
        </div>

        {{-- Employee info grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
            @foreach([
                ['label' => 'Employee Name', 'value' => $detail['employee']['name']],
                ['label' => 'Designation', 'value' => $detail['employee']['designation']],
                ['label' => 'Department', 'value' => $detail['employee']['department']],
                ['label' => 'Date of Joining', 'value' => $detail['employee']['date_of_joining']],
                ['label' => 'Pay Period', 'value' => $detail['period_label']],
                ['label' => 'Worked Days', 'value' => (float) $detail['attendance']['days_worked'].' / '.(float) $detail['attendance']['working_days']],
            ] as $field)
                <div class="bg-gray-50 rounded-lg px-4 py-3 border border-gray-100">
                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">{{ $field['label'] }}</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1 break-words">{{ $field['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Earnings & Deductions --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            {{-- Earnings --}}
            <div class="rounded-xl border border-emerald-100 overflow-hidden">
                <div class="bg-emerald-50 px-4 py-3 flex items-center border-b border-emerald-100">
                    <i class="fas fa-plus-circle text-emerald-600 mr-2"></i>
                    <span class="font-semibold text-emerald-800 text-sm">Earnings</span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($detail['earnings'] as $item)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-gray-700 font-medium">{{ $item['name'] }}</span>
                            <span class="font-semibold text-gray-900">₹{{ number_format($item['amount'], 0) }}</span>
                        </div>
                    @empty
                        <p class="px-4 py-3 text-sm text-gray-400">No earnings</p>
                    @endforelse
                </div>
                <div class="bg-emerald-600 px-4 py-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-white">Total Earnings</span>
                    <span class="font-bold text-white">₹{{ number_format($detail['total_earnings'], 0) }}/-</span>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="rounded-xl border border-red-100 overflow-hidden">
                <div class="bg-red-50 px-4 py-3 flex items-center border-b border-red-100">
                    <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                    <span class="font-semibold text-red-800 text-sm">Deductions</span>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($detail['deductions'] as $item)
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                            <span class="text-gray-700 font-medium">{{ $item['name'] }}</span>
                            <span class="font-semibold text-red-600">₹{{ number_format($item['amount'], 0) }}</span>
                        </div>
                    @empty
                        <p class="px-4 py-3 text-sm text-gray-400">No deductions</p>
                    @endforelse
                </div>
                <div class="bg-red-50 px-4 py-3 flex items-center justify-between border-t border-red-100">
                    <span class="text-sm font-semibold text-red-800">Total Deductions</span>
                    <span class="font-bold text-red-700">₹{{ number_format($detail['total_deductions'], 0) }}/-</span>
                </div>
            </div>
        </div>

        {{-- Net pay banner --}}
        <div class="rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 px-6 py-6 text-center text-white shadow-md">
            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-100 mb-1">Net Pay</p>
            <p class="text-3xl sm:text-4xl font-bold">₹{{ number_format($detail['net_pay'], 0) }}/-</p>
            @if(!empty($detail['net_pay_words']))
                <p class="text-sm text-emerald-100 mt-2 italic">{{ $detail['net_pay_words'] }}</p>
            @endif
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            This is a system generated payslip, hence no signature is required.
        </p>
    </div>
</div>
