/* ISARVA POSH - Complete workflow definitions - nothing omitted */
const ISARVA_STORAGE_KEY = 'isarva_posh_v1';

const ROLES = [
  { id: 'employee', name: 'Employee / Aggrieved Woman', view: 'Own complaints, policy, training', edit: 'File complaint', close: '-', export: 'Own receipts' },
  { id: 'ic_member', name: 'IC Member', view: 'Assigned cases (confidential)', edit: 'Inquiry steps, evidence, hearings', close: '-', export: 'Case reports (authorized)' },
  { id: 'presiding', name: 'Presiding Officer', view: 'All IC cases', edit: 'Lead inquiry, schedule hearings', close: 'Recommend closure', export: 'IC reports' },
  { id: 'external', name: 'External Member', view: 'Investigation records', edit: 'Statements, recommendations', close: '-', export: '-' },
  { id: 'hr', name: 'HR Admin', view: 'Aggregated reports (identity masked where required)', edit: 'Training, employer duties', close: '-', export: 'Compliance dashboards' },
  { id: 'legal', name: 'Legal Team', view: 'Cases referred for criminal/civil', edit: 'Legal notices', close: '-', export: 'Legal packs' },
  { id: 'management', name: 'Management / Employer', view: 'Recommendations, action items', edit: 'Implement actions within 60 days', close: 'Confirm action taken', export: 'Annual reports' },
  { id: 'district', name: 'District Officer (Report)', view: 'Annual statutory report only', edit: '-', close: '-', export: 'Submitted annual form' },
  { id: 'superadmin', name: 'Super Admin', view: 'System config', edit: 'All settings', close: 'Archive', export: 'Full audit' }
];

const STATUS_LIFECYCLE = [
  'Draft', 'Submitted', 'Acknowledged', 'Under IC/LC Review', 'Additional Info Requested',
  'Rejected (with reasons)', 'Routed to LC', 'Conciliation Requested', 'Conciliation In Progress',
  'Conciliation Settled', 'Conciliation Failed', 'Interim Relief Applied', 'Inquiry Started',
  'Notice Issued to Respondent', 'Evidence Collection', 'Hearing Scheduled', 'Hearing Completed',
  'Witness Statements Recorded', 'Inquiry Documentation', 'Recommendation Pending',
  'IC Report Submitted (10 days)', 'Management Action Pending (60 days)', 'Action Implemented',
  'Appeal Filed (90 days)', 'Appeal Under Review', 'Closed', 'Archived',
  'False/Malicious Review', 'Criminal Parallel Track', 'Medical Support Provided'
];

const STATUTORY_TIMELINES = [
  { id: 'file', label: 'Complaint filing window', days: '90 (3 months + 3 extension)', law: 'Section 9' },
  { id: 'notice', label: 'Notice to respondent before hearing', days: '7 working days', law: 'Rule 6' },
  { id: 'inquiry', label: 'Inquiry completion', days: '90 days', law: 'Section 11(4)' },
  { id: 'report', label: 'IC report to employer after inquiry', days: '10 days', law: 'Section 11(4)' },
  { id: 'action', label: 'Employer action on recommendation', days: '60 days', law: 'Section 11(4)' },
  { id: 'appeal', label: 'Appeal to Court/Tribunal', days: '90 days', law: 'Section 12' },
  { id: 'interim_leave', label: 'Interim leave to complainant', days: 'Up to 3 months', law: 'Section 12' },
  { id: 'annual', label: 'Annual report to District Officer', days: 'Every calendar year', law: 'Section 22' }
];

const EMPLOYER_DUTIES_S19 = [
  'Provide safe working environment',
  'Display penal consequences of sexual harassment at conspicuous place',
  'Display order constituting IC at conspicuous place',
  'Organize awareness workshops at regular intervals for employees',
  'Organize orientation programmes for IC members',
  'Facilitate IC/LC to conduct proceedings',
  'Assist securing attendance of respondent and witnesses',
  'Provide information to IC/LC as required',
  'Assist aggrieved woman if she cannot submit written complaint',
  'Treat sexual harassment as misconduct under service rules',
  'Initiate action under IPC or other law if criminal conduct',
  'Provide medical assistance / treatment for illness from harassment',
  'Submit annual report with prescribed particulars to District Officer',
  'Include sexual harassment in service rules / standing orders'
];

const IC_CONSTITUTION = [
  'Presiding Officer: senior level woman employee',
  'Minimum 2 employee members (preferably committed to women cause or legal/social knowledge)',
  'One External Member from NGO/association or familiar with sexual harassment issues',
  'At least half of total members must be women',
  'Tenure: 3 years (as per Rules)',
  'IC at every administrative unit with 10+ employees OR district-level grouping for smaller units',
  'Vacancies filled within prescribed time',
  'Quorum as per Rules for valid meetings'
];

const COMPLETE_WORKFLOW = [
  {
    phase: 'A', phaseName: 'Prevention & Foundation', steps: [
      { id: 'A1', title: 'Employer registers workplace under POSH', desc: 'Organization with workplace (10+ employees needs IC). Establishments with fewer workers use Local Committee (LC).', legal: 'Section 1, 4', example: 'ABC Technologies (500 employees) registers and constitutes IC at HQ and each branch.', fields: ['orgName', 'employeeCount', 'units'] },
      { id: 'A2', title: 'Constitute Internal Committee (IC)', desc: 'Form IC with Presiding Officer (senior woman), 2+ employee members, External Member. At least 50% women. Tenure 3 years.', legal: 'Section 4, Rule 4', example: 'Ms. Anita (VP-HR) = Presiding Officer; 2 staff members; Advocate Meera = External Member.', fields: ['poName', 'members', 'externalMember', 'womenCount'] },
      { id: 'A3', title: 'Constitute / identify Local Committee (LC) path', desc: 'For <10 workers OR complaint against employer — complaint goes to LC via District Officer, not IC.', legal: 'Section 6, 7', example: 'Small branch with 8 staff — no IC; LC at district handles complaints.', fields: ['hasIC', 'lcRequired'] },
      { id: 'A4', title: 'Display IC order & penal consequences', desc: 'Employer must display conspicuously at workplace: penal consequences of SH and order constituting IC.', legal: 'Section 19(b)', example: 'Posters in lift, canteen, intranet homepage — tracked in compliance checklist.', fields: ['displayLocations', 'displayDate'] },
      { id: 'A5', title: 'Publish POSH Policy & service rules', desc: 'Written policy: definition of SH, rights, complaint process, misconduct, confidentiality. SH must be misconduct in service rules.', legal: 'Section 19(a)(i)', example: 'PDF on portal: what counts as harassment, timelines, punishments.', fields: ['policyVersion', 'policyFile'] },
      { id: 'A6', title: 'Prevention workshops & IC orientation', desc: 'Regular awareness for all employees; orientation for IC members on Act, Rules, natural justice.', legal: 'Section 19(a)(ii)-(iii)', example: 'Quarterly POSH session; annual IC legal briefing.', fields: ['workshopDate', 'attendees', 'icOrientationDate'] }
    ]
  },
  {
    phase: 'B', phaseName: 'Complaint Intake', steps: [
      { id: 'B1', title: 'Aggrieved woman accesses portal', desc: 'Only woman aggrieved by SH at workplace is protected under POSH Act. Login via SSO/HRMS.', legal: 'Section 2(a)', example: 'Priya logs in via company Microsoft account — no separate password.', fields: [] },
      { id: 'B2', title: 'Portal options shown', desc: 'Raise Complaint, Anonymous Complaint, View Policy, Contact IC, Training, WhatsApp/QR intake, multilingual.', legal: 'Best practice + Rules', example: 'Priya sees Hindi/English toggle and "Raise Complaint" button.', fields: [] },
      { id: 'B3', title: 'Who may file complaint', desc: 'Aggrieved woman; or relative/friend/co-worker with written consent; or legal heir if incapacitated/deceased.', legal: 'Section 9(1), Rule 6', example: 'Priya files herself. If injured, colleague with her written consent could file.', fields: ['filedBy', 'consentDoc'] },
      { id: 'B4', title: 'Complaint form — basic details', desc: 'Name, Employee ID, Department OR anonymous (identity hidden from general staff, IC can investigate).', legal: 'Section 9, Rule 6', example: 'Priya enters ID EMP-2041, Engineering. Or checks Anonymous.', fields: ['complainantName', 'empId', 'dept', 'anonymous'] },
      { id: 'B5', title: 'Against whom (Respondent)', desc: 'Employee, supervisor, or third party (client/customer/vendor) at workplace.', legal: 'Section 2(o), 3', example: 'Rajesh, Engineering Manager — OR external client if applicable.', fields: ['respondentName', 'respondentType', 'designation'] },
      { id: 'B6', title: 'Incident details & workplace', desc: 'Date, description. Workplace = any place employee goes for work: office, Teams, WhatsApp, client site, transport, work trip.', legal: 'Section 2(o)', example: 'May 10: late-night messages, comments on looks, insistence on private meetings.', fields: ['incidentDate', 'description', 'locations'] },
      { id: 'B7', title: 'Filing deadline check (3 + 3 months)', desc: 'Complaint within 3 months of incident; IC may extend 3 months for valid reasons (e.g. trauma, absence).', legal: 'Section 9(1)', example: 'Incident May 10, filed June 1 — OK. System flags if >3 months without extension approval.', fields: ['withinDeadline', 'extensionReason'] },
      { id: 'B8', title: 'Evidence upload', desc: 'Screenshots, emails, chats, recordings — stored securely with access control.', legal: 'Rule 7', example: 'Priya uploads WhatsApp screenshots and Teams chat export.', fields: ['evidenceFiles'] },
      { id: 'B9', title: 'Written complaint assistance', desc: 'If woman cannot write, employer/IC must assist or person with her knowledge can write with her oral account.', legal: 'Section 9(2)', example: 'Illiterate worker — HR scribes verbatim, she signs thumb impression.', fields: ['assistanceProvided'] },
      { id: 'B10', title: 'Complaint submitted — Case ID generated', desc: 'Status: Submitted. Acknowledgement email/SMS/receipt. Audit log entry.', legal: 'Rule 6', example: 'POSH-2026-0012 created. Priya gets acknowledgement within 24 hours.', fields: ['caseId', 'submittedAt'] },
      { id: 'B11', title: 'Routing: IC vs LC vs against employer', desc: '10+ employees + not against employer → IC. <10 workers OR against employer → LC. Domestic worker: 6 copies to IC with assistance.', legal: 'Section 6, 7, 9', example: 'Priya vs Rajesh at ABC Tech → IC. Complaint vs CEO → LC.', fields: ['routedTo', 'routingReason'] }
    ]
  },
  {
    phase: 'C', phaseName: 'IC/LC Initial Review', steps: [
      { id: 'C1', title: 'Confidential access — IC/LC only', desc: 'Not visible to unrelated managers/employees. Role-based confidentiality control.', legal: 'Section 16', example: 'Only Presiding Officer, 2 IC members, External Member see Priya case.', fields: [] },
      { id: 'C2', title: 'Initial review', desc: 'Valid complaint? Sufficient info? Within timeline? Jurisdiction correct?', legal: 'Section 9', example: 'IC reviews: valid workplace SH allegation — Accept.', fields: ['reviewOutcome'] },
      { id: 'C3', title: 'Outcomes: Accept / Reject / Request more info', desc: 'Reject only with written reasons. Request info — status Additional Info Requested.', legal: 'Natural justice', example: 'IC accepts; if vague, asks Priya for dates of each message.', fields: ['rejectReason', 'infoRequested'] },
      { id: 'C4', title: 'False or malicious complaint flag (later)', desc: 'If proven false/malicious — action under service rules. NOT same as "not proved".', legal: 'Section 14', example: 'Separate track if evidence shows deliberate false accusation.', fields: [] }
    ]
  },
  {
    phase: 'D', phaseName: 'Conciliation (Optional Branch)', steps: [
      { id: 'D1', title: 'Complainant requests conciliation?', desc: 'ONLY if woman wants — before inquiry. Not mandatory.', legal: 'Section 10(1)', example: 'Priya does NOT want conciliation — skip to inquiry.', fields: ['conciliationRequested'] },
      { id: 'D2', title: 'Conciliation process', desc: 'IC facilitates settlement. No monetary settlement allowed under conciliation.', legal: 'Section 10(2)', example: 'If chosen: apology + transfer — no cash settlement via conciliation.', fields: ['settlementTerms'] },
      { id: 'D3', title: 'Conciliation settled', desc: 'Record settlement. Case may close. Complainant not compel conciliation.', legal: 'Section 10(1)', example: 'Written settlement signed; status Conciliation Settled → Closed.', fields: ['settlementDoc'] },
      { id: 'D4', title: 'Conciliation failed / terms breached', desc: 'Resume formal inquiry. Complainant can refuse conciliation at any stage before settlement.', legal: 'Section 10', example: 'Rajesh breaches apology terms — inquiry restarted.', fields: ['breachDetails'] }
    ]
  },
  {
    phase: 'E', phaseName: 'Interim Relief (During Pending Inquiry)', steps: [
      { id: 'E1', title: 'Interim relief request', desc: 'During inquiry, IC may recommend: transfer (complainant or accused), leave up to 3 months, restrain contact.', legal: 'Section 12', example: 'Priya requests transfer to another team until inquiry ends.', fields: ['interimType'] },
      { id: 'E2', title: 'Employer implements interim relief', desc: 'Employer may grant per IC recommendation. Document in case file.', legal: 'Section 12', example: 'HR transfers Priya to Marketing temporarily; Rajesh no-contact order.', fields: ['interimImplemented', 'interimDate'] }
    ]
  },
  {
    phase: 'F', phaseName: 'Formal Inquiry', steps: [
      { id: 'F1', title: 'Inquiry started', desc: 'Status: Inquiry Started. SLA clock: 90 days for completion.', legal: 'Section 11', example: 'Day 1: IC formally opens inquiry POSH-2026-0012.', fields: ['inquiryStartDate'] },
      { id: 'F2', title: 'Notice to respondent (7 working days)', desc: 'Copy of complaint summary, hearing date — minimum 7 working days before hearing. Auto-generate PDF notice.', legal: 'Rule 6', example: 'Rajesh receives notice May 20; hearing scheduled May 30.', fields: ['noticeDate', 'hearingDate'] },
      { id: 'F3', title: 'Principles of natural justice', desc: 'Both parties heard. Fair opportunity. Written reasoned findings. Woman need not repeat trauma unnecessarily.', legal: 'Section 11(3)', example: 'Rajesh submits written reply; both attend hearing.', fields: ['naturalJusticeChecklist'] },
      { id: 'F4', title: 'Evidence management', desc: 'Secure storage, download audit, chain of custody. Complainant & respondent may present evidence.', legal: 'Rule 7', example: 'IC reviews screenshots; Rajesh submits his chat logs.', fields: ['evidenceLog'] },
      { id: 'F5', title: 'Hearing scheduling', desc: 'Calendar, reminders, online meeting links, attendance tracking.', legal: 'Rule 6', example: 'Teams meeting May 30, 11 AM — both parties + IC + External Member.', fields: ['hearingId', 'meetingLink'] },
      { id: 'F6', title: 'Witness management', desc: 'Add witnesses, statements, attendance, documents.', legal: 'Section 11', example: 'Colleague Sunita confirms she saw inappropriate messages.', fields: ['witnesses'] },
      { id: 'F7', title: 'Hearing conducted — statements recorded', desc: 'Complainant speaks, respondent responds, witnesses examined. Cross-examination as per rules.', legal: 'Section 11(3)', example: 'Priya narrates incidents; Rajesh denies intent; Sunita testifies.', fields: ['hearingNotes'] },
      { id: 'F8', title: 'Inquiry documentation & MoM', desc: 'Minutes of Meeting — manual or AI-assisted summary. All Q&A logged.', legal: 'Rule 6', example: 'MoM: Meeting May 30. Priya explained messages. Rajesh denied. Witness recorded.', fields: ['momContent'] },
      { id: 'F9', title: 'Third-party harassment track', desc: 'If respondent is client/vendor — employer still must assist, IC inquiry, may also take action against third party contractually.', legal: 'Section 3', example: 'Client harassed employee at site — IC inquires; management bans client.', fields: ['thirdPartyAction'] },
      { id: 'F10', title: 'Criminal complaint parallel track', desc: 'If criminal offence, woman may go to police. Employer initiates action under IPC if needed. Limited inquiry may still proceed.', legal: 'Section 19(e)', example: 'Priya also files FIR — workplace inquiry continues on service rules aspect.', fields: ['firFiled', 'firNumber'] },
      { id: 'F11', title: 'Medical support', desc: 'Employer arrange medical if harassment caused illness/injury.', legal: 'Section 19(f)', example: 'Counselling/EAP referral for Priya documented.', fields: ['medicalSupport'] },
      { id: 'F12', title: 'SLA monitoring & escalation', desc: '90-day inquiry deadline. Reminders, escalate to senior HR if delay.', legal: 'Section 11(4)', example: 'Day 75 alert: 15 days left to complete inquiry.', fields: ['slaStatus'] }
    ]
  },
  {
    phase: 'G', phaseName: 'Recommendation & Employer Action', steps: [
      { id: 'G1', title: 'IC findings & recommendation', desc: 'Proved / Not proved / Partially proved. Written reasoned report with recommendations.', legal: 'Section 11(3)-(4)', example: 'Partially proved: warning + mandatory training + hierarchy change for Rajesh.', fields: ['finding', 'recommendation'] },
      { id: 'G2', title: 'IC report to employer (within 10 days of inquiry end)', desc: 'IC submits report to employer/District Officer as applicable.', legal: 'Section 11(4)', example: 'June 28 inquiry ends — report submitted July 5.', fields: ['reportSubmittedDate'] },
      { id: 'G3', title: 'Management action (within 60 days)', desc: 'Warning, suspension, termination, apology, training, transfer per service rules. Same action if proved for employee or third party policy.', legal: 'Section 11(4), 19(d)', example: 'HR issues warning letter; Rajesh attends POSH training.', fields: ['actionTaken', 'actionDate'] },
      { id: 'G4', title: 'Compensation (if applicable via inquiry not conciliation)', desc: 'Deduction from respondent salary for payment to complainant only as per Act inquiry outcome — not via conciliation money.', legal: 'Section 15', example: 'If IC recommends compensation — employer deducts per rules.', fields: ['compensation'] }
    ]
  },
  {
    phase: 'H', phaseName: 'Appeal, Closure & Compliance', steps: [
      { id: 'H1', title: 'Appeal (90 days)', desc: 'Aggrieved woman or respondent may appeal to Court/Tribunal within 90 days if dissatisfied.', legal: 'Section 12', example: 'Rajesh appeals — tracked separately; case status Appeal Filed.', fields: ['appealFiled', 'appealDate'] },
      { id: 'H2', title: 'Case closure', desc: 'Status Closed. Secure archive: evidence, reports, hearings, recommendations, closure letter.', legal: 'Section 11', example: 'All documents sealed; retention per company policy.', fields: ['closedDate', 'closureNotes'] },
      { id: 'H3', title: 'Confidentiality post-closure', desc: 'Identity not published in media. No publicity of case details.', legal: 'Section 16', example: 'Only aggregated stats in annual report — no names.', fields: [] },
      { id: 'H4', title: 'Annual compliance report', desc: 'Cases filed, disposed, pending, workshops, nature of action, units without IC — to District Officer.', legal: 'Section 22', example: '2026 report: 5 filed, 4 closed, 12 workshops, 0 pending >90 days.', fields: ['annualStats'] },
      { id: 'H5', title: 'Penalty tracking (employer non-compliance)', desc: '₹50,000 fine for non-compliance; repeat offences — higher penalty / licence cancellation for licensed establishments.', legal: 'Section 26', example: 'Compliance dashboard flags: display notice overdue 30 days.', fields: ['complianceGaps'] },
      { id: 'H6', title: 'Archive', desc: 'Long-term secure storage, audit retention, access only Super Admin/Legal.', legal: 'Best practice', example: 'Case moved to Archived after 7 years per policy.', fields: ['archivedDate'] }
    ]
  }
];

const HARASSMENT_TYPES = [
  'Physical contact or advances',
  'Demand or request for sexual favours',
  'Sexually coloured remarks',
  'Showing pornography',
  'Any other unwelcome physical, verbal or non-verbal conduct of sexual nature',
  'Cyber / digital harassment (messages, calls, social media)',
  'Quid pro quo (benefit/threat linked to sexual conduct)',
  'Hostile work environment'
];

const INTERIM_RELIEF_OPTIONS = [
  'Transfer of aggrieved woman',
  'Transfer of respondent',
  'Leave to aggrieved woman (up to 3 months)',
  'Restrain respondent from reporting to complainant',
  'Restrain contact between parties',
  'Any other appropriate relief per IC recommendation'
];

const RECOMMENDATION_ACTIONS = [
  'Written warning', 'Written apology', 'Mandatory POSH training',
  'Counselling / EAP', 'Transfer', 'Demotion', 'Suspension',
  'Termination', 'Withholding promotion', 'Monitoring period',
  'Ban third-party from premises', 'Report to police / FIR support'
];
