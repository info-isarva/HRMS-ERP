{{-- Fixed Public Holidays Partial --}}
<div class="bg-white shadow-lg rounded-xl border border-gray-300 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-300 bg-blue-50">
        <h2 class="{{ isset($fullWidth) && $fullWidth ? 'text-2xl' : 'text-xl' }} font-bold text-gray-900 flex items-center">
            <i class="fas fa-calendar-alt text-blue-600 mr-3 {{ isset($fullWidth) && $fullWidth ? 'text-2xl' : 'text-xl' }}"></i>
            {{ isset($fullWidth) && $fullWidth ? 'Public Holidays for Your Department' : 'Fixed Public Holidays' }}
        </h2>
        <p class="text-gray-800 mt-2 font-medium">
            @if(isset($fullWidth) && $fullWidth)
                These are the public holidays assigned to your department. No flexible holidays are currently available for application.
            @else
                These are fixed public holidays assigned to your department. No application required.
            @endif
        </p>
    </div>

    @if($fixedHolidays->count() > 0)
        <div class="{{ isset($fullWidth) && $fullWidth ? 'p-8' : 'p-6' }}">
            <div class="{{ isset($fullWidth) && $fullWidth ? 'space-y-6' : 'space-y-4' }}">
                @foreach($fixedHolidays as $holiday)
                    <div class="group relative border border-gray-200 rounded-xl transition-all duration-300 bg-white hover:shadow-lg">
                        
                        <!-- Subtle color accent line -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-xl transition-all duration-300" 
                             style="background: {{ $holiday->color ?? '#3b82f6' }};"></div>
                        
                        <div class="p-6 pl-8">
                            <!-- Header with title and badge -->
                            <div class="flex items-center justify-between {{ isset($fullWidth) && $fullWidth ? 'mb-4' : 'mb-3' }}">
                                <div class="flex items-center {{ isset($fullWidth) && $fullWidth ? 'space-x-4' : 'space-x-3' }}">
                                    <!-- Minimal color dot -->
                                    <div class="{{ isset($fullWidth) && $fullWidth ? 'w-3 h-3' : 'w-2 h-2' }} rounded-full transition-all duration-300" 
                                         style="background: {{ $holiday->color ?? '#3b82f6' }};"></div>
                                    
                                    <!-- Title -->
                                    <h4 class="font-semibold {{ isset($fullWidth) && $fullWidth ? 'text-xl' : 'text-lg' }} text-gray-900">
                                        {{ $holiday->name }}
                                    </h4>
                                </div>
                                
                                <!-- Fixed badge -->
                                @if(isset($fullWidth) && $fullWidth)
                                    <span class="bg-gray-100 text-gray-600 px-4 py-2 rounded-full text-sm font-medium">
                                        <i class="fas fa-lock mr-1"></i>
                                        Fixed Holiday
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-medium">
                                        Fixed
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Description -->
                            <p class="text-gray-600 mb-4 {{ isset($fullWidth) && $fullWidth ? 'text-base' : 'text-sm' }} leading-relaxed">
                                {{ $holiday->description ?? 'Fixed public holiday - no application required' }}
                            </p>
                            
                            <!-- Date and day info -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center {{ isset($fullWidth) && $fullWidth ? 'space-x-4' : 'space-x-3' }}">
                                    <!-- Date display -->
                                    <span class="bg-gray-50 text-gray-700 {{ isset($fullWidth) && $fullWidth ? 'px-4 py-2 text-base' : 'px-3 py-1 text-sm' }} rounded-lg font-medium border">
                                        @if(isset($fullWidth) && $fullWidth)
                                            <i class="far fa-calendar mr-2"></i>
                                        @endif
                                        {{ $holiday->formatted_date }}
                                    </span>
                                    
                                    <!-- Day -->
                                    <span class="text-gray-500 {{ isset($fullWidth) && $fullWidth ? 'text-base' : 'text-sm' }} font-medium">
                                        {{ $holiday->day_of_week }}
                                    </span>
                                </div>
                                
                                <!-- Auto-approved indicator -->
                                @if(isset($fullWidth) && $fullWidth)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Auto-Approved
                                    </span>
                                @else
                                    <span class="text-xs font-medium text-green-600">Auto-Approved</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center {{ isset($fullWidth) && $fullWidth ? 'py-16' : 'py-12' }}">
            <div class="{{ isset($fullWidth) && $fullWidth ? 'w-20 h-20' : 'w-16 h-16' }} bg-gray-200 rounded-full flex items-center justify-center mx-auto {{ isset($fullWidth) && $fullWidth ? 'mb-6' : 'mb-4' }}">
                <i class="fas fa-calendar text-gray-500 {{ isset($fullWidth) && $fullWidth ? 'text-3xl' : 'text-2xl' }}"></i>
            </div>
            <h3 class="{{ isset($fullWidth) && $fullWidth ? 'text-2xl' : 'text-xl' }} font-bold text-gray-900 {{ isset($fullWidth) && $fullWidth ? 'mb-3' : 'mb-2' }}">
                {{ isset($fullWidth) && $fullWidth ? 'No Public Holidays' : 'No Fixed Holidays' }}
            </h3>
            <p class="text-gray-700 font-medium {{ isset($fullWidth) && $fullWidth ? 'text-lg' : '' }}">
                {{ isset($fullWidth) && $fullWidth ? 'No public holidays are currently assigned to your department.' : 'No fixed public holidays are assigned to your department.' }}
            </p>
        </div>
    @endif
</div>
