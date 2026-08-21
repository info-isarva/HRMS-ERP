# HRMS Gap Analysis: ISARVA vs Karnataka HRMS 2.0

**Prepared for:** ISARVA HRMS (Payroll & Attendance)  
**Benchmark:** Karnataka State Government HRMS 2.0 (hrms.karnataka.gov.in / HRMS2 ESS)  
**Version:** 1.0  
**Date:** 25 June 2026

---

## Document Purpose

This document compares ISARVA HRMS (Payroll and Attendance modules) against Karnataka State Government HRMS 2.0. It is written in plain language so that HR managers, payroll administrators, and business stakeholders can understand what ISARVA already does well, where gaps exist, and what would need to be built.

## Status Legend

| Status | Meaning |
|--------|---------|
| **FULL** | Feature is available and functional in ISARVA |
| **PARTIAL** | Feature exists but is incomplete or simplified |
| **GAP** | Feature is not available in ISARVA |
| **N/A** | Not applicable — different target market |

## Summary Scorecard

| Status | Count |
|--------|-------|
| Full / Available | 28 |
| Partial | 28 |
| Gap (Not Available) | 25 |
| Not Applicable | 1 |

---

## Module 1: Executive Summary

Karnataka HRMS 2.0 is a state-government platform built for 6+ lakh government employees, aided institutions, and university staff. It is designed around government pay rules (pay scales, GPF, NPS, DA), DDO-led salary processing, and deep integration with 45+ departments.

ISARVA HRMS (Payroll + Attendance) is a private-sector, multi-tenant SaaS platform focused on CTC-based salary, EPF/ESI compliance, monthly payroll wizard, and attendance-to-payroll integration for companies.

This is not a like-for-like comparison of two identical products. Karnataka HRMS 2.0 serves government HR lifecycle; ISARVA serves corporate payroll operations. Many government-only features are intentionally absent. Where both overlap (payroll, leave, attendance, ESS), this document highlights what ISARVA already does well and what would need to be built to match Karnataka HRMS 2.0 depth.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Overall platform maturity | 20+ years evolution (HRMS 1.0 since 2005); HRMS 2.0 with Kellton; 200+ APIs; 6 lakh users | Modern Laravel 12 multi-app architecture; multi-tenant; Payroll + Attendance + Hub SSO | **PARTIAL** | ISARVA has modern tech stack but far fewer government integrations and smaller proven scale at state level. |
| Target user base | Government, aided schools, boards, universities — KGID-based identity | Private companies, SMEs, multi-location corporates — company code + email login | **N/A** | Different markets by design. |

### Recommendations

- **Overall platform maturity:** Position ISARVA for private sector; do not try to replicate full govt HRMS without dedicated govt product line.
- **Target user base:** No action unless entering government sector.

---

## Module 2: Authentication, Access & Security

Karnataka HRMS uses KGID (employee insurance ID) as the primary key, with separate login portals for Government, Aided, and Boards/Universities. The new ESS portal (hrmsess.karnataka.gov.in) uses OTP on registered mobile. DDOs (Drawing and Disbursing Officers) register employees — no self-registration.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Employee login (KGID / Employee Code) | KGID number + password on main portal; KGID + OTP on ESS mobile/web | Company code + email/employee ID + password via Workspace Hub; JWT SSO to Payroll/Attendance | **PARTIAL** | No KGID concept; no OTP-only ESS login; corporate SSO model instead. |
| Three-tier login categories (Govt / Aided / Boards) | Separate login tabs and workflows per employee category | Single tenant type per company; no aided-institution or university category split | **GAP** | No equivalent employee-category portals. |
| DDO (salary disbursing officer) role | 21,000+ DDOs process monthly salary accounts for their establishment | Payroll admin / HR admin with role-based permissions; no DDO establishment hierarchy | **PARTIAL** | No DDO code, establishment section, or govt salary bill workflow. |
| Self-registration | Not allowed — DDO registers employee and sends initial password to mobile | Admin creates users; optional Google OAuth; employee cannot self-register | **FULL** | Aligned — admin-controlled registration. |
| Password management | Change password with DDO code + role selection; ESS uses OTP | Forgot password, profile password change, cross-app password sync with Attendance | **FULL** | Corporate password flows are in place. |
| Aadhaar e-KYC integration | Kartavya-KAAMS attendance app integrates Aadhaar e-KYC for identity verification | Aadhaar number stored as employee personal field only — no e-KYC verification | **GAP** | No Aadhaar-based identity verification at login or attendance. |
| Multi-tenant / company isolation | Department-level segregation within single govt system | Full multi-tenant: central registry + per-tenant database shards | **FULL** | ISARVA exceeds Karnataka model for SaaS multi-company hosting. |

### Recommendations

- **Employee login (KGID / Employee Code):** Add optional OTP login for employee self-service if customers request it.
- **Three-tier login categories (Govt / Aided / Boards):** Only needed for government deployments.
- **DDO (salary disbursing officer) role:** Map 'Payroll Admin' as functional equivalent for private sector.
- **Aadhaar e-KYC integration:** Integrate Aadhaar e-KYC API if attendance fraud prevention is a priority.
- **Multi-tenant / company isolation:** Strength to highlight for private sector clients.

---

## Module 3: Employee Master Data & Service Book

Karnataka HRMS maintains an Electronic Service Book (e-SR) — a complete career record including every posting, promotion, transfer, training, and suspension. This is the heart of government HR. ISARVA maintains employee master data suitable for payroll but not a lifelong government service register.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Electronic Service Book (e-SR) | Full career history: postings, promotions, transfers, training, suspensions — viewable by employee via ESS | Employee profile with personal, bank, documents, salary history; increment records; exit details | **GAP** | No service book with posting history, cadre details, or govt order linkage. |
| KGID / Government ID linkage | KGID links employee to insurance, ESS login, and service records | Employee ID, UAN (EPF), PAN, Aadhaar fields on employee profile | **PARTIAL** | Private-sector IDs present; no government KGID or PRAN lifecycle management. |
| Pay Scale & Basic Pay (Government) | Pay scale, basic pay, DA%, HRA%, grade pay — revised per pay commission | CTC-based monthly/annual salary with configurable earning components (Basic, HRA, etc.) | **PARTIAL** | No pay commission matrix, pay level, or automatic DA revision per govt notification. |
| Cadre management | Cadre, category, class of post — drives promotion and transfer eligibility | Department, designation, location, employee status — no cadre hierarchy | **GAP** | No cadre/category/class-of-post master or rules engine. |
| Employee documents | Transfer orders, posting orders accessible via ESS | Document types, file upload on employee profile, resignation letter PDF | **PARTIAL** | Document storage exists but no govt order workflow or transfer order generation. |
| Suspension management | Suspension details with subsistence allowance calculation in salary | Employee status (active/left); held salary module; no subsistence allowance rules | **GAP** | No suspension workflow or subsistence pay calculation. |
| Profile update by employee | Limited self-update via ESS; major changes via DDO | Profile view in Attendance; data change request (DPDP compliance) sent to Payroll admin | **PARTIAL** | DPDP data change request exists — good for private sector; not full ESS profile edit. |

### Recommendations

- **Electronic Service Book (e-SR):** Build 'Employee Timeline' module if long-tenure career tracking is needed.
- **KGID / Government ID linkage:** Add PRAN/NPS fields if serving post-2004 govt-style pension clients.
- **Pay Scale & Basic Pay (Government):** For govt clients: build pay matrix module. For private: current CTC model is correct.
- **Cadre management:** Government-sector feature only.
- **Employee documents:** Extend document module with order templates if needed.
- **Suspension management:** Add only if serving government or PSU clients.
- **Profile update by employee:** Expand self-service profile fields (contact, emergency, bank) with approval workflow.

---

## Module 4: Recruitment, Transfer & Promotion

Karnataka HRMS 2.0 (Kellton scope) includes recruitment, cadre management, transfers, and promotion as first-class modules with API integration across 45+ departments. ISARVA handles increments and exit but not full recruitment or transfer lifecycle.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Recruitment module | End-to-end recruitment integrated with HRMS 2.0 (Kellton project scope) | Not in Payroll or Attendance; no job posting, application, or selection workflow | **GAP** | No recruitment / ATS module. |
| Transfer management | Transfer orders, posting changes, inter-department transfers; viewable on ESS | Location and department can be edited on employee; no transfer order workflow | **GAP** | No transfer request, approval chain, relieving/joining, or order document. |
| Promotion management | Promotion history in service book; linked to pay scale revision | Increment & Promotion module (IncrementController) — salary revision with history and revert | **PARTIAL** | Salary increment exists; no designation promotion workflow or pay scale upgrade. |
| Annual Performance Appraisal (APR) | Annual performance management module (Kellton HRMS 2.0 scope) | Not available in Payroll or Attendance | **GAP** | No performance review, rating, or APR-linked increment. |

### Recommendations

- **Recruitment module:** Separate product module or integrate with third-party ATS.
- **Transfer management:** Build transfer workflow if enterprise clients need internal mobility tracking.
- **Promotion management:** Link increment module to designation change and approval chain.
- **Annual Performance Appraisal (APR):** Separate PMS module; optional link to increment.

---

## Module 5: Payroll Processing & Salary Disbursement

Both systems process monthly salary, but the models differ fundamentally. Karnataka uses government pay components (Basic, DA, HRA, allowances, GPF deduction). ISARVA uses CTC-based components prorated by attendance days.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Monthly salary processing | DDO submits salary bill monthly; processed centrally; 6 lakh+ accounts | 5-step payroll wizard: select month → attendance → salary review → compare → finalize | **FULL** | Core monthly payroll exists with lock/finalize. |
| Attendance-based proration | Present days / LOP integrated with leave and attendance systems | worked_days / total_working_days factor applied to all earnings and deductions | **FULL** | Strong integration — attendance locked in Attendance app, consumed by Payroll. |
| Dearness Allowance (DA) auto-revision | DA revised by government notification; applied across all employees automatically | DA can be a salary component but no govt notification-driven mass revision | **PARTIAL** | No DA rate master linked to govt gazette notifications. |
| Salary slip (payslip) | PDF download via portal or ESS app; includes Basic, DA, HRA, GPF, GIS, PT, recoveries | PDF payslip generation, email, bulk send; employee views via Attendance portal API | **FULL** | Payslip delivery is implemented. |
| Annual salary statement | Full-year summary for income tax filing — available on ESS | Monthly analytics and year comparison reports; no dedicated Form 16 / annual tax statement | **PARTIAL** | No employee-facing annual tax summary document. |
| Bank salary transfer file | Integrated with treasury / government banking systems | Bank transfer Excel, CSV, ICICI XLSX formats with NEFT/RTGS/IMPS types | **FULL** | Private-sector bank file export is strong. |
| Multi-location payroll | Department and establishment level processing | Per-location or global payroll run with location_id on payout month status | **FULL** | Multi-location payroll supported. |
| Held salary / early salary | Recoveries and advance adjustments in salary bill | Held salary module, salary advances with auto-deduction at finalize, early salary flag | **FULL** | Advance and hold mechanisms exist. |
| OT, incentives, holiday payout | Allowances processed as part of govt salary components | Dedicated OT/Incentive module with separate finalize flags; holiday work payout | **FULL** | OT and incentives well covered for private sector. |
| Full & Final Settlement (FFS) on exit | Retirement benefits and final settlement via pension/GPF modules | Exit employee workflow (Pending → Approved → Completed); FFS calculation | **PARTIAL** | Exit exists; no gratuity/pension settlement automation. |

### Recommendations

- **Monthly salary processing:** None for private sector.
- **Attendance-based proration:** Highlight as competitive strength.
- **Dearness Allowance (DA) auto-revision:** Add bulk component revision tool for DA/rate changes.
- **Salary slip (payslip):** Add annual salary statement (see Tax module).
- **Annual salary statement:** Generate annual earnings/deductions PDF for IT filing.
- **Bank salary transfer file:** Add more bank format templates as needed.
- **Full & Final Settlement (FFS) on exit:** Add gratuity calculation on exit for private sector compliance.

---

## Module 6: Tax & Statutory Compliance — Deep Analysis

This is the most important area of difference. Karnataka HRMS 2.0 follows government statutory rules (GPF, NPS, GIS, KGID, DA-linked PT). ISARVA follows private-sector Indian payroll compliance (EPF, ESI, simplified PT, optional TDS slabs in settings).

Plain-language summary: ISARVA is built for companies under the Companies Act / Factories Act with EPF and ESI. Karnataka HRMS is built for government employees under GPF/NPS rules. They share the word 'payroll' but use different tax and deduction engines.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| EPF (Employees Provident Fund) — Private Sector | Not applicable — government uses GPF instead of EPF | Full EPF engine: 12% employee share, optional 24% if employer share deducted from CTC, wage cap ₹15,000 or full wages, manual override, UAN on employee, ECR Excel export | **FULL** | ISARVA is strong for private-sector EPF — Karnataka govt does not use this. |
| GPF (General Provident Fund) — Government | GPF account with balance, passbook, loan status, installment deduction in salary | Not available — no GPF account, passbook, or GPF loan management | **GAP** | Government provident fund entirely absent. |
| NPS / PRAN (National Pension System) | NPS account details for post-2004 employees; PRAN number on payslip | Not available — no PRAN field, NPS contribution calculation, or Tier-1/Tier-2 tracking | **GAP** | No NPS module for government-style pension. |
| GIS (Group Insurance Scheme) | GIS installment and coverage details on payslip and ESS | Not available | **GAP** | No group insurance scheme deduction or policy tracking. |
| KGID (Karnataka Govt Insurance Dept) | KGID policy details linked to employee identity and salary deductions | Not available | **GAP** | State-specific insurance scheme not supported. |
| ESI (Employees State Insurance) | Limited applicability in govt HRMS | Auto-calculated: 0.75% employee contribution when gross ≤ ₹21,000; ESI Excel export | **FULL** | Private-sector ESI is implemented. |
| Professional Tax (PT) — Karnataka Slabs | Karnataka govt PT deducted per state rules on govt salary (shown on payslip) | Simplified rule: ₹200/month if gross ≥ ₹25,000, else ₹0 — single flat threshold | **PARTIAL** | Does NOT implement full Karnataka PT slab structure (₹0–₹200 tiered by salary bands, half-yearly filing nuances, special category exemptions). |
| TDS (Tax Deducted at Source / Income Tax) | TDS deducted monthly; annual salary statement for IT return filing | TDS slab configuration UI exists in salary settings; TDS shown in analytics/comparison reports — BUT TDS is NOT auto-calculated in monthly payroll engine (recalculateEmployeePayroll only runs EPF, ESI, PT) | **PARTIAL** | TDS settings exist on paper but are not wired into live monthly salary calculation. No Form 16, Form 16A, or Form 24Q generation. |
| Form 16 / Form 24Q (Income Tax Returns) | Annual salary statement supports employee IT filing | Not available — no Form 16 PDF, no quarterly 24Q TDS return file | **GAP** | No statutory income tax return documents for employees or employer. |
| HRA exemption calculation | HRA as govt allowance component — exemption rules differ from private sector | HRA as configurable salary component; no automatic HRA tax exemption (metro/non-metro, rent declaration) | **GAP** | No rent declaration, metro flag, or HRA exemption in TDS computation. |
| Investment declarations (80C, 80D, etc.) | Managed through govt establishment / DDO for TDS | Not available — no employee tax declaration portal | **GAP** | Employees cannot submit proof of investment for TDS adjustment. |
| Labour Welfare Fund (LWF) | Applicable per state rules for certain establishments | Statutory component slot exists for LWF (generic); not Karnataka LWF-specific automation | **PARTIAL** | Can configure manually; no Karnataka LWF rate/period automation. |
| Gratuity calculation | Part of retirement settlement for eligible govt staff | Not auto-calculated on exit | **GAP** | No gratuity accrual or payment calculation (15 days salary × years of service). |
| Bonus (Payment of Bonus Act) | Festival advance / bonus as govt allowance | Can add as salary component; no statutory bonus calculation engine | **PARTIAL** | No auto 8.33% bonus calculation with eligibility rules. |
| EPF ECR / compliance filing export | N/A (uses GPF) | EPF Excel/CSV export with UAN, wages, contributions, NCP days | **FULL** | EPF filing export is a private-sector strength. |
| ESI contribution filing export | N/A | ESI Excel export for monthly contribution filing | **FULL** | ESI export available. |

### Recommendations

- **EPF (Employees Provident Fund) — Private Sector:** Core strength for corporate clients.
- **GPF (General Provident Fund) — Government:** Required only for government sector product.
- **NPS / PRAN (National Pension System):** Add if targeting PSU/government contractors.
- **GIS (Group Insurance Scheme):** Government sector only.
- **KGID (Karnataka Govt Insurance Dept):** Karnataka government only.
- **ESI (Employees State Insurance):** Core strength for eligible establishments.
- **Professional Tax (PT) — Karnataka Slabs:** Implement Karnataka PT slab table (and other state PT masters) with auto-selection by employee work state.
- **TDS (Tax Deducted at Source / Income Tax):** Priority fix: integrate TDS into payroll calculation using old/new tax regime, declarations (80C, HRA), and generate Form 16 annually.
- **Form 16 / Form 24Q (Income Tax Returns):** Build Form 16 generator and 24Q export — high value for private sector.
- **HRA exemption calculation:** Part of TDS module — capture rent paid, city type, compute exempt HRA.
- **Investment declarations (80C, 80D, etc.):** Add tax declaration module in Attendance self-service.
- **Labour Welfare Fund (LWF):** Add Karnataka LWF rules (employee + employer, annual/half-yearly).
- **Gratuity calculation:** Add gratuity module for Payment of Gratuity Act compliance.
- **Bonus (Payment of Bonus Act):** Add bonus calculation module if manufacturing clients need it.
- **EPF ECR / compliance filing export:** Keep updated with EPFO ECR format changes.

---

## Module 7: Leave Management

Karnataka government leave types include Earned Leave (EL), Half Pay Leave (HPL), and other special leaves governed by service rules. ISARVA uses configurable leave types with manager/HR approval workflow.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Leave types | EL, HPL, Casual Leave, Maternity, etc. per service rules; balances on ESS | Configurable leave types (LeaveTypeController); synced from Attendance to Payroll API | **PARTIAL** | Flexible leave types but no govt service-rule templates (EL/HPL accrual formulas). |
| Leave application workflow | Apply via ESS; approved by reporting officer / DDO | Employee applies → Manager approves → HR approves; notifications and activity log | **FULL** | Multi-level approval workflow exists. |
| Leave balance visibility (employee) | Real-time EL, HPL balances on ESS and mobile app | Leave balance fetched from Payroll API; visible in Attendance self-service | **FULL** | Employee can view balances. |
| Leave encashment | EL encashment on retirement or as per rules | Not automated — would need manual salary component adjustment | **GAP** | No leave encashment calculation or payout workflow. |
| On Other Duty (OOD) / field duty | OOD module in Kartavya-KAAMS for field staff not marking office attendance | GPS visit check-in type exists; no formal OOD approval workflow | **PARTIAL** | Field visit tracking via GPS but no OOD leave equivalent with approval. |
| Public holidays | State gazette holidays applied centrally | Public holiday master per financial year; department-wise holiday quota config; flexible/fixed types | **FULL** | Holiday management is comprehensive. |
| Compensatory off (comp-off) | Earned against holiday/weekend work per service rules | Comp-off support in leave application flow | **FULL** | Comp-off available. |
| Leave reports | Department-wise leave monitoring dashboards | Approved/rejected leaves, LOP, monthly leave chart, daily leave PDF reports | **FULL** | Good leave reporting for private sector. |

### Recommendations

- **Leave types:** Add leave accrual rule engine (monthly/yearly, carry-forward, encashment).
- **Leave encashment:** Add encashment module linked to leave balance and payroll.
- **On Other Duty (OOD) / field duty:** Add OOD/official tour request module.
- **Leave reports:** Add state/district hierarchy reports for large orgs.

---

## Module 8: Attendance Management

Karnataka has deployed Kartavya-KAAMS — an AI-powered mobile attendance system with facial recognition, liveness detection, Aadhaar e-KYC, and 100–150 metre geofencing. It integrates with HRMS and SATS. ISARVA uses biometric Excel upload, TimeStation API, manual punches, and GPS geofencing.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Mobile attendance app | Kartavya-KAAMS Android/iOS app; 3+ lakh registrations; state/district/taluk dashboards | GPS tracking API for mobile check-in/out; no dedicated employee attendance app in app stores | **PARTIAL** | GPS API exists but no polished public mobile app like KAAMS. |
| Facial recognition attendance | AI facial recognition with liveness detection to prevent photo spoofing | Not available — biometric via device Excel upload only, not face recognition | **GAP** | No face-based attendance capture. |
| Geofencing | 100–150 metre radius geofence; GIS-enabled; multiple approved locations (schools, offices, training centres) | Single office geofence (configurable lat/lng/radius ~250m); GPS visit mode for field staff | **PARTIAL** | One office geofence; no multi-location geofence per employee/branch. |
| Biometric device integration | Integrated with school/office biometric infrastructure via KAAMS | Biometric Excel import (multi-format), TimeStation API fetch, manual punch entry | **FULL** | Strong admin-side biometric ingestion for private sector. |
| Shift & duty roster | School/office duty schedules | Shift master + duty roster (per employee per day); bulk week copy/clear | **FULL** | Shift/roster management is solid. |
| Monthly attendance processing | Attendance flows to HRMS for salary; SATS integration for schools | Bulk attendance generate → preview → edit → lock → Payroll API consumes locked data | **FULL** | Lock-before-payroll is a strong control. |
| Late coming / early going / undertime | Monitored via KAAMS dashboards | AttendancePolicy rules; late/early/OT/undertime calculated in AttendanceService | **FULL** | Policy-based time deviation tracking exists. |
| Overtime management | Allowance-based in govt payroll | Dedicated OT module: monthly grid, approve, lock; feeds Payroll OT/incentive module | **FULL** | OT workflow is well implemented. |
| Real-time attendance dashboards (hierarchy) | State → District → Taluk → Division monitoring; 90%+ registration tracking | Admin dashboard and GPS tracking map; no govt-style geographic hierarchy | **PARTIAL** | No multi-level geographic drill-down dashboards. |
| Employee web punch clock | Mobile app primary; no web punch | No employee web punch; GPS mobile API or admin-processed biometric | **PARTIAL** | Both rely on mobile/admin — neither has simple web clock-in. |

### Recommendations

- **Mobile attendance app:** Launch branded ISARVA Attendance mobile app (Android/iOS).
- **Facial recognition attendance:** Evaluate face recognition SDK for mobile app if fraud is a concern.
- **Geofencing:** Support multiple geofence zones per location/employee.
- **Biometric device integration:** Add real-time biometric device API (not just Excel).
- **Monthly attendance processing:** Highlight as audit-friendly feature.
- **Real-time attendance dashboards (hierarchy):** Add org-tree attendance dashboard (company → region → branch).
- **Employee web punch clock:** Optional web punch for WFH employees.

---

## Module 9: Loans, Advances & Recoveries

Karnataka HRMS tracks GPF loans, KGID loans, festival advances, and salary recoveries visible on ESS. ISARVA handles salary advances and held salary for private sector.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| GPF loan management | GPF loan apply, approval, EMI deduction, balance on ESS | Not available | **GAP** | No provident fund loan module. |
| Salary advance | Festival advance and other govt advances with recovery schedule | EmployeeAdvance module with auto-deduction at payroll finalize | **FULL** | Salary advance with payroll recovery works. |
| Loan/advance visibility on ESS | HRMS2 ESS mobile app shows loans, advances, recoveries | Employee can view payslips in Attendance; advance balance not prominently in self-service | **PARTIAL** | Advance details not fully exposed in employee portal. |
| Recovery schedule / EMI tracking | Detailed recovery schedule per loan type | Advance with installment deduction; no full amortization schedule UI | **PARTIAL** | Basic recovery at payroll; no loan statement or EMI calendar. |

### Recommendations

- **GPF loan management:** Government sector only.
- **Loan/advance visibility on ESS:** Add 'My Advances' section in Attendance self-service.
- **Recovery schedule / EMI tracking:** Add advance/loan statement for employees.

---

## Module 10: Retirement & Pension

Karnataka HRMS includes retirement management (Kellton scope) and pension slip for retired employees. ISARVA handles employee exit but not pension lifecycle.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Retirement management | Retirement processing, pension commencement, service verification | Exit employee workflow with resignation date; login blocked after exit | **PARTIAL** | Exit exists; no retirement age trigger, superannuation workflow, or pension initiation. |
| Pension slip | Pension statement for retired employees on portal | Not available | **GAP** | No post-retirement pension payment module. |
| Gratuity / retiral benefits | Calculated as part of govt retirement settlement | Not auto-calculated | **GAP** | See Tax module — gratuity not implemented. |
| Leave encashment on retirement | EL encashment processed in final settlement | Not automated | **GAP** | No retirement leave encashment workflow. |

### Recommendations

- **Retirement management:** Add retirement due report and superannuation checklist.
- **Pension slip:** Government sector only.
- **Gratuity / retiral benefits:** Implement for private sector Payment of Gratuity Act.
- **Leave encashment on retirement:** Link leave balance to FFS on exit.

---

## Module 11: Employee Self-Service (ESS)

Karnataka HRMS2 ESS (mobile app + hrmsess.karnataka.gov.in) gives employees direct access to payslips, leave, service book, loans, and transfer documents without going through DDO. ISARVA routes employees to Attendance portal for self-service.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| ESS portal / mobile app | HRMS2 ESS app (Google Play); payslips, leave, loans, service book, transfer docs | Attendance module as self-service portal: leave, payslips, profile, data change request | **PARTIAL** | Self-service exists but split across Hub + Attendance; no unified ESS mobile app. |
| Payslip download | Current and past months via ESS | View and download PDF via Attendance /payroll API | **FULL** | Implemented. |
| Service book view | Full e-SR on ESS | Not available | **GAP** | No career history view for employees. |
| Transfer document access | Transfer orders on ESS | Not available | **GAP** | No transfer order repository for employees. |
| Tax declaration submission | Via establishment / integrated in salary processing | Not available | **GAP** | See TDS gaps. |

### Recommendations

- **ESS portal / mobile app:** Package Attendance as 'ISARVA ESS' with unified branding.
- **Service book view:** Add employee timeline (joining, promotions, transfers, trainings).
- **Transfer document access:** Depends on transfer module.
- **Tax declaration submission:** High priority for private sector ESS.

---

## Module 12: Reports, Analytics & Integrations

Karnataka HRMS 2.0 targets 200+ API integrations with 45+ departments and data analytics dashboards. ISARVA integrates Payroll ↔ Attendance via REST APIs and webhooks.

| Feature | Karnataka HRMS 2.0 | ISARVA HRMS | Status | Gap / Notes |
|---------|-------------------|-------------|--------|-------------|
| Operational reports | Salary bills, establishment reports, leave statements, attendance summaries | Payroll reports, OT/incentive reports, leave reports, analytical payroll comparison | **PARTIAL** | Good private-sector reports; no govt establishment/registrar reports. |
| Data analytics / BI dashboards | Analytical reports with data analytics (Kellton HRMS 2.0 scope) | Dashboard widgets, payroll comparison charts, leave analytics | **PARTIAL** | Basic analytics; no advanced BI or predictive workforce analytics. |
| Cross-department API integrations | 200+ APIs with 45+ Karnataka departments + central govt | Payroll ↔ Attendance APIs; Hub SSO; CRM/POSH user sync; webhook-based employee sync | **PARTIAL** | Strong internal integration; no external govt system APIs (treasury, IFMS, etc.). |
| Audit trail / activity log | Government audit requirements | Spatie Activity Log in Attendance; ActivityLogService in Payroll for payroll actions | **FULL** | Audit logging present. |
| Compliance (DPDP / data privacy) | Government data governance | DPDP consent flow, data change request module, POSH compliance (separate app) | **FULL** | Modern privacy compliance for private sector. |

### Recommendations

- **Operational reports:** Add custom report builder for enterprise clients.
- **Data analytics / BI dashboards:** Integrate Metabase/Power BI or build workforce cost dashboard.
- **Cross-department API integrations:** Document public API for third-party integrations.
- **Compliance (DPDP / data privacy):** Strength for corporate clients.

---

## Priority Roadmap

### P1 — Critical for private sector

1. Wire TDS into monthly payroll calculation (currently settings-only)
1. Implement Karnataka (and multi-state) Professional Tax slab engine
1. Generate Form 16 and annual salary statement for employees
1. Add employee tax declaration module (80C, HRA, regime selection)

### P2 — High value enhancements

1. Launch ISARVA Attendance mobile app (GPS + optional face recognition)
1. Multi-location geofence support for branch-based attendance
1. Gratuity calculation on employee exit
1. Leave encashment automation
1. Employee 'My Advances & Loans' in self-service portal

### P3 — Government sector only (if entering that market)

1. GPF / NPS / GIS / KGID modules
1. Electronic Service Book (e-SR)
1. Cadre management, transfer orders, DDO workflow
1. Recruitment and APR modules
1. Pension management post-retirement

## Important Disclaimer

Karnataka HRMS 2.0 is a government system designed for 6+ lakh state employees under government pay rules. ISARVA HRMS is designed for private companies under EPF/ESI/CTC payroll rules. A 'GAP' does not always mean ISARVA is deficient — it often means the feature is government-specific and not needed for corporate clients.

*Generated automatically on 25 June 2026 by generate_gap_analysis.py*