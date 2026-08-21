/* ISARVA POSH — Inline help text shown on every screen */
const HELP = {
  dashboard: {
    title: 'Dashboard — What is this?',
    body: 'This is your POSH control room. Here you see how many complaints are open, closed, and all recent cases. <strong>POSH</strong> = Prevention of Sexual Harassment of Women at Workplace Act, 2013. Every company with 10+ employees must handle sexual harassment complaints through a proper process — this software helps you run that process step by step.',
    example: 'Example: HR opens dashboard Monday morning — sees 2 open cases, clicks Operate on Priya\'s case to continue inquiry.'
  },
  newCase: {
    title: 'File New Complaint — What happens here?',
    body: 'Any <strong>aggrieved woman</strong> (woman who faced harassment) can file a complaint. She fills this form. System creates a <strong>Case ID</strong> (like POSH-2026-0001). Complaint must normally be filed within <strong>3 months</strong> of incident (can extend 3 more months). Complaint is kept <strong>confidential</strong> — only IC/LC members see it, not random managers.',
    example: 'Example: Priya feels uncomfortable because manager Rajesh sends late-night WhatsApp messages. She opens portal, fills form, uploads screenshots, submits.'
  },
  fields: {
    complainantName: { label: 'Complainant Name', help: 'Name of the woman who faced harassment. She is called the <strong>Aggrieved Woman</strong> under POSH Act. Only women are covered under this Act.' },
    empId: { label: 'Employee ID', help: 'Company employee code — links complaint to HRMS record.' },
    dept: { label: 'Department', help: 'Which department complainant works in — for routing and records.' },
    anonymous: { label: 'Anonymous Complaint', help: 'If checked, her name is hidden from most people. IC still investigates but identity is protected so she does not fear revenge or gossip.' },
    respondent: { label: 'Respondent (Accused)', help: 'Person against whom complaint is filed — can be colleague, manager, client, vendor, or even employer.' },
    respondentType: { label: 'Respondent Type', help: '<strong>Employee/Supervisor</strong> → IC handles. <strong>Client/Vendor</strong> → still workplace harassment, IC inquires. <strong>Employer</strong> → goes to <strong>Local Committee (LC)</strong>, not IC.' },
    incidentDate: { label: 'Incident Date', help: 'When harassment happened. Must file within 3 months (+ possible 3 month extension). Software should flag late complaints.' },
    description: { label: 'Incident Details', help: 'Write what happened clearly: dates, what was said/done, how many times. This becomes the basis of inquiry. Can include WhatsApp, email, meetings, office, work trip, etc.' },
    locations: { label: 'Location', help: '<strong>Workplace</strong> is not just office — includes Teams calls, WhatsApp for work, client site, cab, work trip anywhere employee goes for job.' },
    vsEmployer: { label: 'Against Employer', help: 'If complaint is against company owner/CEO/employer himself → <strong>Local Committee (LC)</strong> at district level handles it, not internal IC (conflict of interest).' }
  },
  icSetup: {
    title: 'IC Setup — What is Internal Committee (IC)?',
    body: '<strong>IC = Internal Committee</strong>. Indian law (POSH Act) says every workplace with <strong>10 or more employees</strong> MUST have an IC. IC receives complaints, conducts inquiry, and gives recommendation to management. IC is not HR alone — it is a separate committee with specific members.',
    why: 'Why IC exists: So complaints are handled fairly inside company before going to court. External independent member ensures company cannot hide cases.',
    members: '<strong>Presiding Officer</strong> — Senior level woman employee (she leads IC).<br><strong>2+ Employee members</strong> — Preferably people who understand women\'s issues or law.<br><strong>External Member</strong> — Outsider from NGO/law background (mandatory by law).<br><strong>At least 50% women</strong> on committee.<br><strong>Tenure:</strong> 3 years.',
    example: 'Example: ABC Tech forms IC — Ms. Anita (VP-HR, woman) = Presiding Officer; 2 staff members; Advocate Meera = External Member from NGO.'
  },
  icFields: {
    po: { help: 'Senior level <strong>woman employee</strong> who chairs all IC meetings and leads the inquiry process.' },
    external: { help: 'Mandatory outsider — from NGO or person familiar with sexual harassment. Ensures independence. Company cannot control this person.' },
    members: { help: 'Other IC members (employees). At least half of total IC must be women.' }
  },
  operate: {
    title: 'Operate Case — How to use this screen',
    body: 'Select a case, then move through <strong>9 steps</strong> in order. Each step has explanation + form. Click <strong>Save & Next Step</strong> after filling. Data auto-saves. This mirrors real POSH lifecycle from complaint to closure.',
    example: 'Example: Open POSH-2026-DEMO → Step 1 IC Review (accept) → Step 2 Conciliation (skip) → Step 3 Interim relief → ... until close.'
  },
  steps: {
    review: {
      title: 'Step 1: IC Review — What is this?',
      body: 'After complaint is filed, <strong>only IC members</strong> read it first. They check: Is it sexual harassment? Enough details? Within 3 month deadline? Correct routing (IC vs LC)?',
      outcomes: '<strong>Accept</strong> — Start formal process.<br><strong>Reject</strong> — Only with written reasons (e.g. not workplace harassment).<br><strong>Request more info</strong> — Ask complainant for dates, proof, clarity.',
      example: 'Example: IC reads Priya\'s complaint about Rajesh\'s messages → Accept → Status becomes "Under IC Review".',
      law: 'Section 9 — Complaint procedure'
    },
    conciliation: {
      title: 'Step 2: Conciliation — Meaning & rules',
      body: '<strong>Conciliation</strong> = Optional settlement BEFORE full inquiry. Only if <strong>complainant wants it</strong>. She cannot be forced. IC helps both parties agree on resolution.',
      rules: '<strong>IMPORTANT:</strong> No money can be settled through conciliation (law rule). Allowed: written apology, transfer, warning, training. If settled, case may close. If terms broken, inquiry restarts.',
      example: 'Example: Priya does NOT want conciliation — select "Skipped". Another case: woman wants apology + transfer — settled without full hearing.',
      law: 'Section 10'
    },
    interim: {
      title: 'Step 3: Interim Relief — Why during inquiry?',
      body: 'While inquiry is still going on (not finished), IC can ask company to give <strong>temporary protection</strong> to complainant so she can work safely.',
      options: 'Transfer complainant to another team · Transfer accused away · Give complainant leave up to 3 months · Order accused not to contact or supervise her',
      example: 'Example: During Rajesh inquiry, Priya is temporarily moved to Marketing team until case ends.',
      law: 'Section 12'
    },
    notice: {
      title: 'Step 4: Notice to Respondent — What is this?',
      body: 'Rajesh (accused) must get <strong>official written notice</strong> that complaint exists. Notice includes summary and <strong>hearing date</strong>. Law says minimum <strong>7 working days</strong> gap between notice and hearing so he can prepare reply.',
      example: 'Example: Notice sent May 20 → Hearing fixed May 30. Rajesh submits written denial before hearing.',
      law: 'Rule 6 — 7 working days notice'
    },
    inquiry: {
      title: 'Step 5: Inquiry — What is formal inquiry?',
      body: '<strong>Inquiry</strong> = Full investigation by IC. Both sides heard. Evidence reviewed. Must finish within <strong>90 days</strong>. Principles of natural justice: fair hearing, both parties present, written reasoned outcome.',
      example: 'Example: IC reviews WhatsApp screenshots, Rajesh\'s reply email, records summary notes.',
      law: 'Section 11 — Inquiry within 90 days'
    },
    hearing: {
      title: 'Step 6: Hearing — What happens in hearing?',
      body: '<strong>Hearing</strong> = Formal meeting. Complainant tells her story. Respondent gives defence. <strong>Witnesses</strong> (colleagues who saw/knew) may speak. IC records <strong>MoM (Minutes of Meeting)</strong> — everything discussed written down.',
      example: 'Example: Teams meeting — Priya explains messages, Rajesh says "no bad intent", colleague Sunita confirms she saw chats. MoM generated.',
      law: 'Section 11(3) — Both parties heard'
    },
    recommendation: {
      title: 'Step 7: IC Recommendation — What IC decides',
      body: 'After inquiry IC writes finding: <strong>Proved / Not proved / Partially proved</strong> + recommendation to management (warning, training, transfer, suspension, termination). IC sends report to employer within <strong>10 days</strong> of inquiry ending.',
      example: 'Example: Partially proved — IC recommends written warning to Rajesh + mandatory POSH training + change reporting manager.',
      law: 'Section 11(4)'
    },
    action: {
      title: 'Step 8: Management Action — Employer must act',
      body: 'IC only <strong>recommends</strong>. Company <strong>management/employer</strong> must implement action within <strong>60 days</strong> — warning letter, suspension, termination, apology, etc. as per service rules.',
      example: 'Example: HR issues warning letter to Rajesh, enrolls him in training, documents completion in case file.',
      law: 'Section 11(4) — 60 days for employer action'
    },
    appeal: {
      title: 'Step 9: Appeal & Closure — End of case',
      body: 'If complainant or accused not satisfied with outcome, either can <strong>appeal to Court/Tribunal within 90 days</strong>. Otherwise case <strong>Closed</strong>. All documents archived. Identity must NOT be published in media (confidentiality).',
      example: 'Example: Case closed June 30. All evidence stored. Annual report counts this as 1 disposed case (no names published).',
      law: 'Section 12 appeal · Section 16 confidentiality'
    }
  },
  compliance: {
    title: 'Compliance — Employer legal duties',
    body: 'POSH is not only complaints. Employer must also: display IC order at office, run awareness workshops, keep workplace safe, submit <strong>annual report to District Officer</strong> every year with case counts and workshops done.',
    example: 'Example: HR ticks checklist — posters displayed, quarterly POSH training done, annual report filed.'
  },
  audit: {
    title: 'Audit Log — Why track every action?',
    body: 'Every click and save is recorded with time and case ID. If case goes to court later, company can prove proper process was followed. Shows who did what and when.',
    example: 'Example: "10:30 AM — IC member viewed evidence" — proves confidentiality and process.'
  },
  settings: {
    title: 'Data Backup',
    body: 'Prototype saves in browser. Export JSON to backup or move to another PC on office LAN. Import restores data.',
    example: ''
  },
  lc: {
    title: 'What is LC (Local Committee)?',
    body: 'If company has <strong>fewer than 10 workers</strong>, OR complaint is <strong>against employer</strong>, case goes to <strong>Local Committee (LC)</strong> through District Officer — not IC. Software routes automatically when you select "Employer" as respondent.',
    example: 'Example: Small shop with 8 staff — no IC — woman complains to LC.'
  }
};

function helpBox(title, body, example, law) {
  return `<div class="help-panel">
    <div class="help-panel-title"><i class="fas fa-circle-info"></i> ${title}</div>
    <div class="help-panel-body">${body}</div>
    ${law ? `<div class="help-panel-law"><i class="fas fa-scale-balanced mr-1"></i>${law}</div>` : ''}
    ${example ? `<div class="help-panel-example"><i class="fas fa-lightbulb"></i><span><strong>Example —</strong> ${String(example).replace(/^Example:\s*/i, '')}</span></div>` : ''}
  </div>`;
}

function fieldHelp(helpObj) {
  if (!helpObj) return '';
  return `<p class="field-help">${helpObj.help}</p>`;
}
