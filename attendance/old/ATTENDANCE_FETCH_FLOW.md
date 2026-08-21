# Attendance Automation Flow & Life Example

This document explains the logic behind the **TimeStation Fetch** and **Attendance Rules** module, specifically designed to handle long shifts and automated recovery days (Comp-Offs).

---

## 📅 Real-Life Scenario: The "Long Night" Shift

Consider an employee named **Alex** who works in a critical support role.

### 1. The Work Event
*   **Standard Shift**: 09:30 AM to 06:30 PM (9 hours).
*   **Actual Work (Feb 1st)**: Alex punches in at **09:30 AM on Feb 1st**.
*   **Critical Issue**: A server goes down. Alex stays all night.
*   **Actual Punch Out**: Alex finally finishes and punches out at **06:30 AM on Feb 2nd**.
*   **Duration**: 21 Hours.

### 2. The Problem with Standard Systems
Most attendance systems look at each day individually:
*   **Feb 1st**: Only has an "IN" punch. (Marked as Missed Punch/Absent).
*   **Feb 2nd**: Only has an "OUT" punch. (Marked as Missed Punch/Absent).
*   **Feb 3rd**: Alex is exhausted and takes a rest day. (Marked as Absent).
*   **Result**: Alex worked 21 hours but is marked **Absent for 3 days**.

### 3. Our New Intelligent Flow
With the new module, the system processes this as follows:

1.  **Punch Pairing**: The system fetches all data for Feb 1-2. It sees an "IN" at 09:30 AM (Feb 1) and matches it with the *next* sequential "OUT" at 06:30 AM (Feb 2), regardless of the date boundary.
2.  **Shift Calculation**: It calculates **21 Hours** for the Feb 1st record.
3.  **Rule Trigger**: It checks the **Attendance Rules**. It finds a rule: *"If Shift > 18 Hours, grant Comp-Off after 2 days"*.
4.  **Proposed Records**:
    *   **Feb 1st**: Marked as **Present** (21 Hours).
    *   **Feb 3rd**: System automatically generates a record as **Comp-Off (Present)**.
5.  **Admin Review**: HR sees these records in the "Proposed" staging area, verifies the "21 hour" note, and clicks **Finalize**.

---

## 🛠 Technical Process Flow

### Phase 1: Data Acquisition
*   **Service**: `TimeStationService.php`
*   Calls the TimeStation API and parses the CSV into a PHP array.
*   Uses `stream_context` to bypass SSL/Certificate errors common in older server environments.

### Phase 2: Sequential Processing
*   **Controller**: `TimeStationFetchController.php` method `processProposed()`
*   All punches for a specific employee are sorted chronologically.
*   The system iterates through the list. When it finds an **IN**, it searches for the **exact next OUT**, even if it's on the next day.
*   This ensures the "21-hour" shift is captured as one single event.

### Phase 3: Rule Application
*   The `attendance_rules` table stores thresholds.
*   If `total_hours >= shift_threshold_hours`:
    *   The `date` of the shift is recorded.
    *   The `recovery_days_offset` is added to that date.
    *   A second entry is created for that "Recovery Date" with the status defined in the rule (usually `compoff`).

### Phase 4: Staging (Proposed Attendance)
*   Data is saved to the `proposed_attendance` table first.
*   **Admin Override**: The UI allows HR to change any status if the system's rule-based decision needs manual correction.
*   This acts as a "Buffer Zone" before touching the final payroll data.

### Phase 5: Finalization
*   When the admin clicks **Finalize Month**, the records are moved to the `attendances` table.
*   The `attendances` table is what the **Payroll Module** reads to calculate Salary, LOP (Loss of Pay), and Present days.

---

## 📂 Key Files to Reference

1.  **`app/Http/Controllers/TimeStationFetchController.php`**: The "Brain" of the operation. Contains the logic for pairing punches and applying recovery rules.
2.  **`app/Models/AttendanceRule.php`**: Defines the "Why" (Thresholds, Offsets).
3.  **`resources/views/timestation/fetch.blade.php`**: The "Command Center" where HR manages the sync.
4.  **`database/migrations/..._create_proposed_attendance_table.php`**: The schema for the staging area.

---

## 🚀 Benefits
- **Zero Manual Calculation**: No need for HR to manually count hours for cross-day shifts.
- **Fairness**: Employees are automatically rewarded with rest days for extra-long shifts.
- **Audit Trail**: Every automated status shows a note explaining *which* rule was triggered.
