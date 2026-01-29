/**
 * Bulk Attendance Progress Tracker
 * Handles AJAX-based saving and locking with real-time progress updates
 */

class BulkAttendanceProgressTracker {
    constructor() {
        this.progressModal = null;
        this.progressBar = null;
        this.progressText = null;
        this.progressPercentage = null;
        this.isOperationRunning = false;
        this.currentSessionKey = null;
        this.progressCheckInterval = null;

        this.init();
    }

    init() {
        this.createProgressModal();
        this.attachEventListeners();
    }

    createProgressModal() {
        // Create progress modal HTML with improved design
        const progressModalHTML = `
            <div id="progressModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="display: none;">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-blue-600 px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-white" id="progressTitle">Processing...</h3>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-6">
                        <!-- Progress Bar Container -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Progress</span>
                                <span class="text-sm font-bold text-blue-600" id="progressPercentage">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 shadow-sm" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500" id="progressCount">0/0 records</span>
                                <span class="text-xs text-gray-500">Please wait...</span>
                            </div>
                        </div>
                        
                        <!-- Status Message -->
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500">
                            <p class="text-sm text-gray-700" id="progressText">Initializing operation...</p>
                        </div>
                        
                        <!-- Warning -->
                        <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xs text-yellow-800">Please don't close this window or navigate away</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add progress modal to body
        document.body.insertAdjacentHTML('beforeend', progressModalHTML);

        // Create confirmation modal
        this.createConfirmationModal();

        // Get references
        this.progressModal = document.getElementById('progressModal');
        this.progressBar = document.getElementById('progressBar');
        this.progressText = document.getElementById('progressText');
        this.progressPercentage = document.getElementById('progressPercentage');
        this.progressCount = document.getElementById('progressCount');
        this.progressTitle = document.getElementById('progressTitle');
    }

    createConfirmationModal() {
        const confirmModalHTML = `
            <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="display: none;">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 overflow-hidden transform transition-all">
                    <!-- Header -->
                    <div class="bg-red-600 px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-white" id="confirmTitle">Confirm Action</h3>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-6">
                        <p class="text-gray-700 mb-6 leading-relaxed" id="confirmMessage">Are you sure you want to proceed?</p>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <button id="confirmCancel" class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                            <button id="confirmProceed" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span id="confirmButtonText">Proceed</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add confirmation modal to body
        document.body.insertAdjacentHTML('beforeend', confirmModalHTML);

        // Get references to confirmation modal elements
        this.confirmModal = document.getElementById('confirmModal');
        this.confirmTitle = document.getElementById('confirmTitle');
        this.confirmMessage = document.getElementById('confirmMessage');
        this.confirmButtonText = document.getElementById('confirmButtonText');
        this.confirmCancel = document.getElementById('confirmCancel');
        this.confirmProceed = document.getElementById('confirmProceed');

        // Setup confirmation modal event listeners
        this.confirmCancel.addEventListener('click', () => {
            this.hideConfirmModal();
        });

        // Close on backdrop click
        this.confirmModal.addEventListener('click', (e) => {
            if (e.target === this.confirmModal) {
                this.hideConfirmModal();
            }
        });

        // Handle ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.confirmModal.style.display === 'flex') {
                this.hideConfirmModal();
            }
        });
    }

    attachEventListeners() {
        // Attach to save button
        const saveBtn = document.querySelector('button[onclick*="save"]');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSave();
            });
        }

        // Attach to lock button
        const lockBtn = document.querySelector('button[onclick*="lock"]');
        if (lockBtn) {
            lockBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLock();
            });
        }

        // Also check for form submissions
        const saveForm = document.querySelector('form[action*="save"]');
        if (saveForm) {
            saveForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSave();
            });
        }

        const lockForm = document.querySelector('form[action*="lock"]');
        if (lockForm) {
            lockForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLock();
            });
        }
    }

    async handleSave() {
        if (this.isOperationRunning) return;

        const month = this.getMonthFromPage();
        const year = this.getYearFromPage();

        if (!month || !year) {
            this.showError('Unable to determine month and year from page. Please refresh and try again.');
            return;
        }

        this.startOperation('Saving Attendance Records');
        
        try {
            const response = await fetch(`/attendance/bulk/save-with-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ month, year })
            });

            const data = await response.json();

            if (data.success) {
                this.currentSessionKey = data.session_key;
                this.startProgressTracking();
            } else {
                this.showError(data.message || 'Failed to start save operation');
            }
        } catch (error) {
            this.showError('Network error: ' + error.message);
        }
    }

    async handleLock() {
        if (this.isOperationRunning) return;

        const month = this.getMonthFromPage();
        const year = this.getYearFromPage();

        if (!month || !year) {
            this.showError('Unable to determine month and year from page. Please refresh and try again.');
            return;
        }

        // Show beautiful confirmation modal before locking
        this.showConfirmModal(
            '🔒 Lock Attendance Records',
            'Are you sure you want to lock attendance records? This action cannot be undone and will make the records available for payroll processing.',
            'Lock Records',
            () => {
                this.proceedWithLock(month, year);
            }
        );
    }

    async proceedWithLock(month, year) {
        this.startOperation('Locking Attendance Records');
        
        try {
            const response = await fetch(`/attendance/bulk/lock-with-progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ month, year })
            });

            const data = await response.json();

            if (data.success) {
                this.currentSessionKey = data.session_key;
                this.startProgressTracking();
            } else {
                this.showError(data.message || 'Failed to start lock operation');
            }
        } catch (error) {
            this.showError('Network error: ' + error.message);
        }
    }

    startOperation(title) {
        this.isOperationRunning = true;
        this.progressTitle.textContent = title;
        this.resetProgress();
        this.showModal();
    }

    startProgressTracking() {
        if (!this.currentSessionKey) return;

        this.progressCheckInterval = setInterval(async () => {
            try {
                const response = await fetch(`/attendance/bulk/progress?session_key=${this.currentSessionKey}`, {
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success && data.progress) {
                    this.updateProgress(data.progress);

                    if (data.progress.status === 'completed') {
                        this.completeOperation(data.progress.message);
                    } else if (data.progress.status === 'error') {
                        this.showError(data.progress.message);
                    }
                } else {
                    this.showError('Failed to get progress information');
                }
            } catch (error) {
                this.showError('Failed to check progress: ' + error.message);
            }
        }, 1000); // Check every second
    }

    updateProgress(progress) {
        const percentage = progress.percentage || 0;
        const message = progress.message || 'Processing...';
        const processed = progress.processed || 0;
        const total = progress.total || 0;

        this.progressBar.style.width = `${percentage}%`;
        this.progressPercentage.textContent = `${percentage}%`;
        this.progressText.textContent = message;
        
        if (total > 0) {
            this.progressCount.textContent = `${processed}/${total} records`;
        } else {
            this.progressCount.textContent = `${percentage}% complete`;
        }

        // Add visual feedback for different stages
        if (percentage >= 100) {
            this.progressBar.className = 'bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500 shadow-sm';
        } else if (percentage >= 75) {
            this.progressBar.className = 'bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-500 shadow-sm';
        }
    }

    completeOperation(message) {
        this.stopProgressTracking();
        this.progressText.textContent = message;
        this.progressPercentage.textContent = '100%';
        this.progressBar.style.width = '100%';
        this.progressBar.className = 'bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500 shadow-sm';
        this.progressTitle.textContent = '✅ Completed Successfully!';
        this.progressCount.textContent = 'Operation finished';
        
        // Auto close after 2 seconds and refresh page
        setTimeout(() => {
            this.hideModal();
            window.location.reload();
        }, 2500);
    }

    showError(message) {
        this.stopProgressTracking();
        this.progressText.textContent = message;
        this.progressBar.className = 'bg-gradient-to-r from-red-500 to-red-600 h-3 rounded-full transition-all duration-500 shadow-sm';
        this.progressTitle.textContent = '❌ Operation Failed';
        this.progressCount.textContent = 'Please try again';
        
        // Auto close after 5 seconds
        setTimeout(() => {
            this.hideModal();
        }, 5000);
    }

    stopProgressTracking() {
        if (this.progressCheckInterval) {
            clearInterval(this.progressCheckInterval);
            this.progressCheckInterval = null;
        }
        this.isOperationRunning = false;
        this.currentSessionKey = null;
    }

    resetProgress() {
        this.progressBar.style.width = '0%';
        this.progressPercentage.textContent = '0%';
        this.progressText.textContent = 'Initializing operation...';
        this.progressCount.textContent = '0/0 records';
        this.progressBar.className = 'bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 shadow-sm';
    }

    showModal() {
        this.progressModal.style.display = 'flex';
    }

    hideModal() {
        this.progressModal.style.display = 'none';
        this.stopProgressTracking();
    }

    showConfirmModal(title, message, buttonText, onConfirm) {
        this.confirmTitle.textContent = title;
        this.confirmMessage.textContent = message;
        this.confirmButtonText.textContent = buttonText;
        
        // Remove any existing event listeners
        const newProceedBtn = this.confirmProceed.cloneNode(true);
        this.confirmProceed.parentNode.replaceChild(newProceedBtn, this.confirmProceed);
        this.confirmProceed = newProceedBtn;
        
        // Add new event listener
        this.confirmProceed.addEventListener('click', () => {
            this.hideConfirmModal();
            if (onConfirm) onConfirm();
        });
        
        this.confirmModal.style.display = 'flex';
    }

    hideConfirmModal() {
        this.confirmModal.style.display = 'none';
    }

    getMonthFromPage() {
        // Try to extract month from URL or page elements
        const urlParams = new URLSearchParams(window.location.search);
        return parseInt(urlParams.get('month')) || null;
    }

    getYearFromPage() {
        // Try to extract year from URL or page elements
        const urlParams = new URLSearchParams(window.location.search);
        return parseInt(urlParams.get('year')) || null;
    }

    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new BulkAttendanceProgressTracker();
});

// Export for use in other scripts
window.BulkAttendanceProgressTracker = BulkAttendanceProgressTracker;