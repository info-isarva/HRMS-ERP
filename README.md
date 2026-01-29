# HRMS General Sector V1

**Human Resource Management System with Payroll and Self-Service Portal**

This is a comprehensive HRMS solution built with Laravel, featuring attendance management, payroll processing, and employee self-service portal for leave management.

## Features

### 🎯 Core Modules
- **Attendance Management**: Track employee attendance with bulk import/export capabilities
- **Payroll Processing**: Complete payroll system with salary calculations and reports
- **Leave Management**: Employee self-service portal for leave applications and approvals
- **Employee Management**: Comprehensive employee database with profile management
- **Reporting**: Advanced reporting and analytics dashboard

### 🚀 Recent Enhancements
- ✅ **Floating Navigation Buttons**: Smooth scrolling navigation for large employee lists
- ✅ **API Integration**: Attendance system integration with token-based authentication
- ✅ **Dynamic Permissions**: Role-based permission management with real-time updates
- ✅ **Modern UI/UX**: Bootstrap 4 compatible responsive design

### 🔧 Technical Stack
- **Framework**: Laravel 12.19.3
- **PHP Version**: 8.2+
- **Frontend**: Bootstrap 4, jQuery, Font Awesome
- **Database**: MySQL
- **Authentication**: JWT with API token support

## Installation

1. Clone the repository
2. Install dependencies: `composer install && npm install`
3. Configure environment variables in `.env`
4. Run migrations: `php artisan migrate`
5. Start the application: `php artisan serve`

## API Documentation

The system includes RESTful APIs for:
- Employee synchronization between attendance and payroll systems
- Bulk attendance data processing
- Permission management
- Salary day calculations

## Contributing

Please read our contribution guidelines before submitting pull requests.

## License

This project is licensed under the MIT License.

---

Built with ❤️ using Laravel Framework
