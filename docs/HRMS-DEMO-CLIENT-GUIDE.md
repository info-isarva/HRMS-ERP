# ISARVA HRMS — Demo Client Guide (15 Days)

**Valid till:** 24 June 2026  
**For:** New client evaluation  
**Modules in demo:** Payroll + Attendance only  

---

## 1. What is this demo?

This is a **15-day trial HRMS setup** for you to explore the system. It is **not for live/production use**. All data you enter here is for testing and evaluation only.

**Available in demo:**
- HRMS Workspace (main login page)
- Payroll module
- Attendance module

**Not available in demo:**
- CRM
- POSH

When you log in, you will see a **yellow demo message** on top showing how many days are left.

---

## 2. Login details

| Item | Detail |
|------|--------|
| Main login URL | https://hrmsdev.isarva.in |
| Payroll URL | https://payrolldev.isarva.in |
| Attendance URL | https://attendancedev.isarva.in |
| Login email | sup_admin@gmail.com |
| Password | *(Will be shared separately by our team)* |
| Role | Super Admin |

**Note:** Right now only **one user** (Super Admin) is there. You need to create employees and users yourself as per steps below.

---

## 3. How to start (first day)

### Step 1 — Login
1. Open **https://hrmsdev.isarva.in**
2. Enter Super Admin email and password
3. You will reach the **Workspace dashboard**
4. From there you can open **Payroll** and **Attendance**

### Step 2 — Company setup (in Payroll)
1. Open **Payroll** from workspace
2. Go to **System Settings → Company Settings**
3. Fill company name, logo, address, contact details, etc.
4. Save the settings

### Step 3 — Create departments
1. In Payroll, go to department / organisation setup
2. Create departments first (example: HR, Accounts, IT, Operations)
3. Employees will be linked to these departments

---

## 4. How to add employees and users

**Important:** In this system, **Payroll is the main module**. Users are created from Payroll and then synced to Attendance automatically.

### Step 1 — Add employee
1. In Payroll → **Employees → Add Employee**
2. Fill **Basic Details** tab:
   - Employee name
   - Employee ID
   - Email *(must be valid — needed for login)*
   - Department
   - Designation
   - Date of joining
3. Fill other tabs if needed (Personal, Bank, Salary, etc.)

### Step 2 — Set permissions (very important)
Go to **Permissions** tab for each employee and enable as needed:

| Option | What it does |
|--------|----------------|
| **Enable Self Portal** | Creates login for employee and syncs to **Attendance** |
| **Enable Payroll** | Allows employee to access **Payroll** from workspace |

**For demo testing:**
- Enable **both** for employees who should test payroll and attendance
- Employee **must have email** if Self Portal is enabled

### Step 3 — Save employee
- Click Save
- System will create user account automatically when Self Portal is enabled

---

## 5. User management and Attendance sync

### Check users in Payroll
1. Go to **User Management** in Payroll menu
2. You can see all users created from employees

### Sync users to Attendance
Normally sync happens **automatically** when you save employee with Self Portal enabled.

If any user is missing in Attendance:
1. In Payroll, go to **User Sync** section
2. Click **Sync All Users**
3. Wait for success message

### Verify in Attendance
1. Open **Attendance** from workspace
2. Check that employees/users are visible
3. Try logging in with one test employee (after sync)

---

## 6. Attendance setup (after users are ready)

1. Open Attendance module
2. Configure as per your need:
   - **Shifts**
   - **Attendance policies**
   - **Leave types**
   - **Public holidays** (if required)
3. Map employees to shifts/policies
4. Test:
   - Attendance punch / marking
   - Leave apply
   - Leave approval flow

---

## 7. Suggested demo checklist

Use this list to make sure you have tested main features:

- [ ] Super Admin login working
- [ ] Company settings saved
- [ ] At least 2–3 departments created
- [ ] At least 2–3 sample employees added
- [ ] Self Portal + Payroll enabled on permissions
- [ ] Users visible in User Management
- [ ] Users synced to Attendance
- [ ] One employee login tested
- [ ] Leave apply and approve tested
- [ ] Basic payroll flow tested (if needed)

---

## 8. Points to remember

1. **Demo ends on 24 June 2026** — please plan your evaluation before that date.
2. **Only Payroll + Attendance** are enabled. CRM and POSH are not part of this demo.
3. **Do not use real confidential production data** if not required — sample data is enough for demo.
4. **Payroll is master** — always add/edit employees in Payroll first; Attendance will get data from there.
5. If sync fails, contact our support team with employee email and screenshot.

---

## 9. Common issues and quick fixes

| Problem | What to do |
|---------|------------|
| Employee not showing in Attendance | Check Self Portal is enabled, email is filled, then run **Sync All Users** in Payroll |
| Employee cannot login | Confirm user exists in User Management and permissions are enabled |
| Only Payroll or only Attendance opening | Check Permissions tab — enable both if needed |
| Demo message not showing | No action needed — it shows remaining days automatically |

---

## 10. Support contact

For demo support, onboarding call, or extension request, please contact:

**ISARVA Team**  
Email: *(add your support email)*  
Phone: *(add your support phone)*  

---

*Document prepared for ISARVA HRMS client demo — June 2026*
