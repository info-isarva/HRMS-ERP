@extends('layouts.app')

@section('title', 'Activity Log Details - HRMS')

@push('styles')
<style>
    .header-gradient {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #6366f1 100%);
    }
    .detail-card {
        transition: all 0.3s ease;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        border-color: #3b82f6;
    }
    .pulse-dot {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .json-viewer {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
    }
    .property-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .property-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
    }
    .ip-badge {
        background: linear-gradient(45deg, #3b82f6 0%, #6366f1 100%);
        color: white;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
    .server-ip-badge {
        background: linear-gradient(45deg, #10b981 0%, #059669 100%);
        color: white;
        font-family: 'Courier New', monospace;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }
    .info-badge {
        background: linear-gradient(45deg, #8b5cf6 0%, #a855f7 100%);
        box-shadow: 0 4px 6px -1px rgba(139, 92, 246, 0.3);
    }
    .activity-title {
        color: #1f2937;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-full">
        <!-- Back Navigation -->
        <div class="mb-6">
            <a href="{{ route('activity-logs.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-white text-gray-700 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 border border-gray-300 hover:border-blue-500 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Activity Logs
            </a>
        </div>

        <!-- Main Header -->
        <div class="header-gradient rounded-2xl shadow-xl p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/30">
                        <i class="fas {{ $activity->event == 'created' ? 'fa-plus' : 
                                       ($activity->event == 'updated' ? 'fa-edit' : 
                                       ($activity->event == 'deleted' ? 'fa-trash' : 'fa-eye')) }} text-3xl text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">{{ $activity->description }}</h1>
                        <p class="text-white/90 text-lg">
                            <i class="fas fa-fingerprint mr-2"></i>
                            Activity ID: #{{ $activity->id }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full pulse-dot"></div>
                        <span class="text-sm text-white/90">{{ $activity->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ $activity->created_at->format('H:i:s') }}</div>
                    <div class="text-sm text-white/80">{{ $activity->created_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Basic Information -->
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-info-circle text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Basic Information</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Event Type</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $activity->event == 'created' ? 'bg-green-100 text-green-800' : 
                                   ($activity->event == 'updated' ? 'bg-blue-100 text-blue-800' : 
                                   ($activity->event == 'deleted' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($activity->event) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">Log Category</span>
                            <span class="text-sm font-semibold text-gray-900 bg-white px-3 py-1 rounded-full">
                                {{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}
                            </span>
                        </div>
                    </div>
                    
                    @if($activity->subject_type)
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Subject Type</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $activity->subject_type }}</span>
                            </div>
                        </div>
                    @endif
                    
                    @if($activity->subject_id)
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Subject ID</span>
                                <span class="text-sm font-semibold text-gray-900">#{{ $activity->subject_id }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- User Information -->
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">User Information</h3>
                </div>
                
                @if($activity->causer)
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($activity->causer->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-gray-900">{{ $activity->causer->name }}</div>
                                <div class="text-sm text-gray-600">{{ $activity->causer->email }}</div>
                                <div class="text-xs text-gray-500 bg-white px-2 py-1 rounded-full inline-block mt-1">
                                    {{ ucfirst($activity->causer->role) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">User ID</span>
                                <span class="text-sm font-semibold text-gray-900">#{{ $activity->causer->id }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg p-6 text-center">
                        <div class="w-16 h-16 bg-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-robot text-white text-2xl"></i>
                        </div>
                        <div class="text-lg font-semibold text-gray-900">System Generated</div>
                        <div class="text-sm text-gray-600">Automated Activity</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Access Information -->
        @php
            $hasAccessInfo = false;
            $clientIp = $activity->properties['ip'] ?? $activity->properties['client_ip'] ?? null;
            $serverIp = $activity->properties['server_ip'] ?? null;
            $userAgent = $activity->properties['user_agent'] ?? null;
            $method = $activity->properties['method'] ?? null;
            $url = $activity->properties['url'] ?? null;
            
            $hasValidClientIp = $clientIp && $clientIp !== 'N/A' && $clientIp !== 'unknown';
            $hasValidServerIp = $serverIp && $serverIp !== 'unknown' && $serverIp !== 'N/A';
            $hasValidUserAgent = $userAgent && !empty($userAgent);
            $hasValidMethod = $method && !empty($method);
            $hasValidUrl = $url && !empty($url);
            
            $hasAccessInfo = $hasValidClientIp || $hasValidServerIp || $hasValidUserAgent || $hasValidMethod || $hasValidUrl;
        @endphp

        @if($hasAccessInfo)
            <div class="mt-8">
                <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-network-wired text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Access Information</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        @if($hasValidClientIp)
                            <div class="property-card rounded-lg p-4">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-user-circle text-blue-600 text-lg"></i>
                                    <span class="text-sm font-medium text-gray-600">Client IP Address</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="ip-badge px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $clientIp }}
                                    </span>
                                    <button onclick="copyToClipboard('{{ $clientIp }}')" class="text-gray-400 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                        
                        @if($hasValidServerIp)
                            <div class="property-card rounded-lg p-4">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-server text-green-600 text-lg"></i>
                                    <span class="text-sm font-medium text-gray-600">Server IP Address</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="server-ip-badge px-3 py-1 rounded-full text-sm font-medium">
                                        <i class="fas fa-server mr-1"></i>
                                        {{ $serverIp }}
                                    </span>
                                    <button onclick="copyToClipboard('{{ $serverIp }}')" class="text-gray-400 hover:text-green-600 transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                        
                        @if($hasValidMethod)
                            <div class="property-card rounded-lg p-4">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-exchange-alt text-purple-600 text-lg"></i>
                                    <span class="text-sm font-medium text-gray-600">Request Method</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="info-badge px-3 py-1 rounded-full text-sm font-medium text-white">
                                        {{ $method }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        
                        @if($hasValidUrl)
                            <div class="property-card rounded-lg p-4 md:col-span-2 lg:col-span-3">
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-link text-purple-600 text-lg"></i>
                                    <span class="text-sm font-medium text-gray-600">Access URL</span>
                                </div>
                                <div class="text-sm font-mono text-gray-900 break-all bg-gray-50 p-3 rounded-lg border-l-4 border-purple-500">
                                    {{ $url }}
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($hasValidUserAgent)
                        <div class="mb-6">
                            <div class="flex items-center space-x-2 mb-3">
                                <i class="fas fa-desktop text-indigo-600"></i>
                                <span class="text-sm font-medium text-gray-600">Browser/Device Information</span>
                            </div>
                            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4">
                                <div class="text-sm text-gray-700 font-mono break-all bg-white p-3 rounded">
                                    {{ $userAgent }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Request Data -->
        @if(isset($activity->properties['request_data']) && is_array($activity->properties['request_data']) && count($activity->properties['request_data']) > 0)
            <div class="mt-8">
                <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-code text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Request Data</h3>
                    </div>
                    
                    <div class="json-viewer rounded-lg p-4">
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap overflow-x-auto">{{ json_encode($activity->properties['request_data'], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        @endif

        <!-- Model Changes -->
        @if(isset($activity->properties['attributes']) || isset($activity->properties['old']))
            <div class="mt-8">
                <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-database text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Model Changes</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        @if(isset($activity->properties['old']) && count($activity->properties['old']) > 0)
                            <div>
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-history text-red-600"></i>
                                    <h4 class="text-lg font-medium text-gray-700">Previous Values</h4>
                                </div>
                                <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-lg p-4">
                                    <pre class="text-sm text-red-700 whitespace-pre-wrap overflow-x-auto bg-white p-3 rounded">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif

                        @if(isset($activity->properties['attributes']) && count($activity->properties['attributes']) > 0)
                            <div>
                                <div class="flex items-center space-x-2 mb-3">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                    <h4 class="text-lg font-medium text-gray-700">New Values</h4>
                                </div>
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4">
                                    <pre class="text-sm text-green-700 whitespace-pre-wrap overflow-x-auto bg-white p-3 rounded">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Timestamps -->
        <div class="mt-8">
            <div class="detail-card bg-white rounded-2xl shadow-lg p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-clock text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Timeline</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-blue-700">Created At</span>
                            <i class="fas fa-calendar-plus text-blue-600"></i>
                        </div>
                        <div class="text-2xl font-bold text-blue-900 mb-1">
                            {{ $activity->created_at->format('M d, Y H:i:s') }}
                        </div>
                        <div class="text-sm text-blue-600">
                            {{ $activity->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-purple-700">Updated At</span>
                            <i class="fas fa-sync text-purple-600"></i>
                        </div>
                        <div class="text-2xl font-bold text-purple-900 mb-1">
                            {{ $activity->updated_at->format('M d, Y H:i:s') }}
                        </div>
                        <div class="text-sm text-purple-600">
                            {{ $activity->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center';
        notification.innerHTML = '<i class="fas fa-check mr-2"></i>Copied to clipboard!';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    }).catch(function(error) {
        console.error('Failed to copy text: ', error);
        
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show success notification
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 flex items-center';
        notification.innerHTML = '<i class="fas fa-check mr-2"></i>Copied to clipboard!';
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 2000);
    });
}

// Auto-refresh activity status every 60 seconds
setInterval(function() {
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Update timestamp section only
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTimestamps = doc.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-6');
        
        if (newTimestamps) {
            const currentTimestamps = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-6');
            if (currentTimestamps) {
                currentTimestamps.innerHTML = newTimestamps.innerHTML;
            }
        }
    })
    .catch(error => console.error('Error refreshing activity:', error));
}, 60000);
</script>
@endpush
@endsection
