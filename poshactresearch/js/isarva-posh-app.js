/* ISARVA POSH — Operational Case Management */
const OPERATE_STEPS = [
  { key: 'review', label: '1. IC Review', status: 'Under IC/LC Review' },
  { key: 'conciliation', label: '2. Conciliation', status: 'Conciliation In Progress' },
  { key: 'interim', label: '3. Interim Relief', status: 'Interim Relief Applied' },
  { key: 'notice', label: '4. Notice to Respondent', status: 'Notice Issued to Respondent' },
  { key: 'inquiry', label: '5. Inquiry & Hearing', status: 'Inquiry Started' },
  { key: 'hearing', label: '6. Hearing Done', status: 'Hearing Completed' },
  { key: 'recommendation', label: '7. IC Recommendation', status: 'Recommendation Pending' },
  { key: 'action', label: '8. Management Action', status: 'Management Action Pending (60 days)' },
  { key: 'appeal', label: '9. Appeal / Close', status: 'Closed' }
];

const App = {
  data: null,
  activeCaseId: null,
  operateStepIndex: 0,

  init() {
    this.load();
    this.bindNav();
    this.bindGlobal();
    this.bindForms();
    this.loadICForm();
    this.injectAllHelp();
    this.showSection('dashboard');
    this.refreshAll();
  },

  injectAllHelp() {
    if (typeof HELP === 'undefined') return;
    const set = (id, html) => { const e = document.getElementById(id); if (e) e.innerHTML = html; };
    const h = HELP;
    set('help-dashboard', helpBox(h.dashboard.title, h.dashboard.body, h.dashboard.example));
    set('help-new-case', helpBox(h.newCase.title, h.newCase.body, h.newCase.example));
    set('help-lc-note', helpBox(h.lc.title, h.lc.body, h.lc.example));
    set('help-cases', helpBox('All Cases', 'List of every POSH complaint in your company. Click <strong>Operate</strong> to process a case step-by-step. Each case has unique Case ID and timeline.', 'Example: HR sees POSH-2026-0001 (Priya) and POSH-2026-0002 (another employee) — opens Priya case to continue.'));
    set('help-operate-top', helpBox(h.operate.title, h.operate.body, h.operate.example));
    set('help-ic-setup', helpBox(h.icSetup.title, h.icSetup.body + '<br><br>' + h.icSetup.why, h.icSetup.example));
    const icDet = document.querySelector('#help-ic-members-detail .card-body');
    if (icDet) icDet.innerHTML = `<div class="help-panel" style="margin:0;box-shadow:none;border:none;background:transparent;padding:0;">
      <div class="help-panel-title">IC Members — Who sits on committee?</div>
      <div class="help-panel-body">${h.icSetup.members}</div></div>`;
    set('help-compliance', helpBox(h.compliance.title, h.compliance.body, h.compliance.example));
    set('help-audit', helpBox(h.audit.title, h.audit.body, h.audit.example));
    set('help-settings', helpBox(h.settings.title, h.settings.body, h.settings.example));
    const fh = h.fields;
    const setFh = (id, key) => { const e = document.getElementById(id); if (e && fh[key]) e.innerHTML = fh[key].help; };
    setFh('fh-name', 'complainantName');
    setFh('fh-empid', 'empId');
    setFh('fh-dept', 'dept');
    setFh('fh-anon', 'anonymous');
    setFh('fh-respondent', 'respondent');
    setFh('fh-restype', 'respondentType');
    setFh('fh-date', 'incidentDate');
    setFh('fh-desc', 'description');
    setFh('fh-loc', 'locations');
    setFh('fh-vs-employer', 'vsEmployer');
    const icf = h.icFields;
    ['fh-ic-po', 'fh-ic-ext', 'fh-ic-members'].forEach((id, i) => {
      const key = ['po', 'external', 'members'][i];
      const e = document.getElementById(id);
      if (e && icf[key]) e.innerHTML = icf[key].help;
    });
  },

  renderStepHelp(stepKey) {
    const el = document.getElementById('op-step-help');
    if (!el || typeof HELP === 'undefined') return;
    const s = HELP.steps[stepKey];
    if (!s) { el.innerHTML = ''; return; }
    let extra = '';
    if (s.outcomes) extra += `<div class="step-help-block step-help-block--options"><strong>Options</strong>${s.outcomes}</div>`;
    if (s.rules) extra += `<div class="step-help-block step-help-block--rules"><strong>Important rules</strong>${s.rules}</div>`;
    if (s.options) extra += `<div class="step-help-block step-help-block--options"><strong>What you can select</strong>${s.options}</div>`;
    el.innerHTML = `<div class="help-panel-title">${s.title}</div>
      <div class="help-panel-body">${s.body}</div>
      ${extra}
      ${s.law ? `<div class="help-panel-law">${s.law}</div>` : ''}
      ${s.example ? `<div class="help-panel-example"><strong>Example —</strong> ${String(s.example).replace(/^Example:\s*/i, '')}</div>` : ''}`;
  },

  defaultData() {
    return {
      ic: { presidingOfficer: '', externalMember: '', members: '', constituted: false },
      employerDuties: EMPLOYER_DUTIES_S19.map(d => ({ duty: d, done: false, date: '' })),
      workshops: [],
      cases: [],
      auditLog: [],
      activeCaseId: null
    };
  },

  load() {
    try {
      const raw = localStorage.getItem(ISARVA_STORAGE_KEY);
      this.data = raw ? JSON.parse(raw) : this.defaultData();
      if (!this.data.cases) this.data.cases = [];
      if (!this.data.auditLog) this.data.auditLog = [];
      if (!this.data.employerDuties || this.data.employerDuties.length !== EMPLOYER_DUTIES_S19.length) {
        this.data.employerDuties = EMPLOYER_DUTIES_S19.map(d => ({ duty: d, done: false, date: '' }));
      }
      this.activeCaseId = this.data.activeCaseId || (this.data.cases[0] && this.data.cases[0].id) || null;
    } catch (e) {
      this.data = this.defaultData();
    }
  },

  save() {
    this.data.activeCaseId = this.activeCaseId;
    localStorage.setItem(ISARVA_STORAGE_KEY, JSON.stringify(this.data));
    this.showToast('Saved');
  },

  log(action, caseId, details) {
    this.data.auditLog.unshift({
      time: new Date().toISOString(),
      action,
      caseId: caseId || '-',
      details: details || ''
    });
    if (this.data.auditLog.length > 500) this.data.auditLog = this.data.auditLog.slice(0, 500);
    this.save();
  },

  showToast(msg) {
    const t = document.getElementById('toast');
    if (!t) return;
    t.textContent = msg;
    t.classList.remove('hidden');
    clearTimeout(this._toastTimer);
    this._toastTimer = setTimeout(() => t.classList.add('hidden'), 2200);
  },

  bindNav() {
    document.querySelectorAll('.nav-item[data-section]').forEach(el => {
      el.addEventListener('click', () => this.showSection(el.dataset.section));
    });
    document.querySelectorAll('[data-goto]').forEach(el => {
      el.addEventListener('click', () => this.showSection(el.dataset.goto));
    });
  },

  bindGlobal() {
    document.getElementById('btn-new-quick')?.addEventListener('click', () => this.showSection('new-case'));
    document.getElementById('btn-goto-new')?.addEventListener('click', () => this.showSection('new-case'));
    document.getElementById('btn-load-demo')?.addEventListener('click', () => { this.seedDemoCase(); this.showToast('Demo case loaded'); });
    document.getElementById('export-data')?.addEventListener('click', () => this.exportJSON());
    document.getElementById('import-data')?.addEventListener('change', e => this.importJSON(e));
    document.getElementById('clear-data')?.addEventListener('click', () => {
      if (confirm('Delete ALL data?')) {
        localStorage.removeItem(ISARVA_STORAGE_KEY);
        this.data = this.defaultData();
        this.activeCaseId = null;
        this.init();
      }
    });
    document.getElementById('op-prev')?.addEventListener('click', () => this.changeOperateStep(-1));
    document.getElementById('op-next')?.addEventListener('click', () => this.saveOperateStep(true));
    document.getElementById('op-save')?.addEventListener('click', () => this.saveOperateStep(false));
    document.getElementById('case-picker-btn')?.addEventListener('click', e => {
      e.stopPropagation();
      this.toggleCasePicker();
    });
    document.addEventListener('click', e => {
      if (!document.getElementById('case-picker')?.contains(e.target)) {
        this.closeCasePicker();
      }
    });
    document.getElementById('btn-workshop')?.addEventListener('click', () => this.saveWorkshop());
    document.getElementById('menu-toggle')?.addEventListener('click', () => {
      document.getElementById('sidebar')?.classList.toggle('open');
    });
  },

  bindForms() {
    document.getElementById('complaint-form')?.addEventListener('submit', e => {
      e.preventDefault();
      this.fileComplaint();
    });
    document.getElementById('ic-form')?.addEventListener('submit', e => {
      e.preventDefault();
      this.saveIC();
    });
  },

  showSection(id) {
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('#sidebar .nav-item').forEach(n => {
      n.classList.remove('active');
      n.removeAttribute('aria-current');
    });
    const panel = document.getElementById('sec-' + id);
    const nav = document.querySelector(`#sidebar .nav-item[data-section="${id}"]`);
    if (panel) panel.classList.add('active');
    if (nav) {
      nav.classList.add('active');
      nav.setAttribute('aria-current', 'page');
    }
    const titles = {
      dashboard: ['Dashboard', 'Click a case to operate it'],
      'new-case': ['New Complaint', 'File and start workflow'],
      cases: ['All Cases', 'Open any case to operate'],
      operate: ['Operate Case', 'Step through POSH process'],
      'ic-setup': ['IC Setup', 'Internal Committee details'],
      compliance: ['Compliance', 'Employer duties & workshops'],
      audit: ['Audit Log', 'Every action recorded'],
      settings: ['Data Backup', 'Export / import JSON']
    };
    const t = titles[id] || ['ISARVA POSH', ''];
    const pt = document.getElementById('page-title');
    const ps = document.getElementById('page-subtitle');
    if (pt) pt.textContent = t[0];
    if (ps) ps.textContent = t[1];
    if (id === 'operate') this.renderOperate();
    if (id === 'dashboard' || id === 'cases') this.renderCasesLists();
    if (id === 'audit') this.renderAuditLog();
    if (id === 'compliance') this.renderEmployerDuties();
    this.injectAllHelp();
  },

  refreshAll() {
    this.refreshStats();
    this.renderCasesLists();
    this.renderCasePicker();
    this.renderEmployerDuties();
    this.renderWorkshopList();
    this.renderAuditLog();
    if (document.getElementById('sec-operate')?.classList.contains('active')) this.renderOperate();
  },

  refreshStats() {
    const cases = this.data.cases || [];
    const open = cases.filter(c => !['Closed', 'Archived'].includes(c.status)).length;
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    set('stat-total', cases.length);
    set('stat-open', open);
    set('stat-closed', cases.filter(c => c.status === 'Closed').length);
    set('stat-audit', (this.data.auditLog || []).length);
  },

  seedDemoCase() {
    const exists = this.data.cases.find(c => c.id === 'POSH-2026-DEMO');
    if (exists) {
      this.openCase('POSH-2026-DEMO');
      return;
    }
    const c = {
      id: 'POSH-2026-DEMO',
      complainant: 'Priya Sharma',
      empId: 'EMP-2041',
      dept: 'Engineering',
      anonymous: false,
      respondent: 'Rajesh Kumar',
      respondentType: 'supervisor',
      incidentDate: new Date(Date.now() - 30 * 86400000).toISOString().slice(0, 10),
      description: 'Repeated inappropriate late-night messages, comments on appearance, and pressure for private meetings outside office.',
      locations: 'WhatsApp, Office',
      status: 'Submitted',
      routedTo: 'IC',
      stepIndex: 0,
      reviewNotes: '',
      conciliationRequested: false,
      conciliationOutcome: '',
      interimRelief: [],
      noticeDate: '',
      hearingDate: '',
      witnesses: '',
      momContent: '',
      finding: '',
      recommendation: '',
      actionTaken: '',
      appealFiled: false,
      closureNotes: '',
      timeline: [{ date: new Date().toISOString(), status: 'Submitted', note: 'Demo case created' }],
      createdAt: new Date().toISOString()
    };
    this.data.cases.unshift(c);
    this.activeCaseId = c.id;
    this.save();
    this.log('Demo case created', c.id, 'Priya vs Rajesh');
    this.refreshAll();
    this.showSection('operate');
  },

  fileComplaint() {
    const anon = document.getElementById('f-anon').checked;
    const vsEmp = document.getElementById('f-vs-employer').checked;
    const id = 'POSH-' + new Date().getFullYear() + '-' + String(this.data.cases.length + 1).padStart(4, '0');
    const c = {
      id,
      complainant: anon ? 'Anonymous' : document.getElementById('f-name').value || 'Unknown',
      empId: document.getElementById('f-empid').value,
      dept: document.getElementById('f-dept').value,
      anonymous: anon,
      respondent: document.getElementById('f-respondent').value,
      respondentType: document.getElementById('f-restype').value,
      incidentDate: document.getElementById('f-date').value,
      description: document.getElementById('f-desc').value,
      locations: document.getElementById('f-loc').value,
      status: 'Submitted',
      routedTo: vsEmp || document.getElementById('f-restype').value === 'employer' ? 'LC' : 'IC',
      stepIndex: 0,
      reviewNotes: '', conciliationRequested: false, conciliationOutcome: '',
      interimRelief: [], noticeDate: '', hearingDate: '', witnesses: '', momContent: '',
      finding: '', recommendation: '', actionTaken: '', appealFiled: false, closureNotes: '',
      timeline: [{ date: new Date().toISOString(), status: 'Submitted', note: 'Complaint filed' }],
      createdAt: new Date().toISOString()
    };
    this.data.cases.unshift(c);
    this.activeCaseId = c.id;
    this.save();
    this.log('Complaint filed', id, c.complainant + ' vs ' + c.respondent);
    document.getElementById('complaint-result').innerHTML =
      `<div class="card" style="border-left:4px solid var(--isarva-success);">
        <div class="card-body">
          <strong style="color:var(--isarva-success);font-size:1.05rem;">Case ${id} created</strong>
          <p style="margin:8px 0 0;font-size:0.875rem;color:var(--isarva-text-secondary);">Routed to: <strong>${c.routedTo}</strong></p>
          <button type="button" class="btn-isarva" style="margin-top:16px;" id="btn-open-new-case"><i class="fas fa-play"></i> Open & Operate Case</button>
        </div>
      </div>`;
    document.getElementById('btn-open-new-case')?.addEventListener('click', () => {
      this.openCase(id);
      this.showSection('operate');
    });
    document.getElementById('complaint-form').reset();
    this.refreshAll();
    this.showToast('Case ' + id + ' created');
  },

  openCase(id) {
    if (!id) return;
    this.activeCaseId = id;
    const c = this.data.cases.find(x => x.id === id);
    if (c && c.stepIndex == null) c.stepIndex = 0;
    this.operateStepIndex = c ? (c.stepIndex || 0) : 0;
    this.save();
    this.showSection('operate');
  },

  renderCasePicker() {
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    const idEl = document.getElementById('picker-display-id');
    const stEl = document.getElementById('picker-display-status');
    const menu = document.getElementById('case-picker-menu');
    if (idEl) idEl.textContent = c ? c.id : 'No case';
    if (stEl) stEl.textContent = c ? c.status : 'Create or load a case';
    if (!menu) return;
    const cases = this.data.cases || [];
    if (!cases.length) {
      menu.innerHTML = '<div style="padding:14px;color:var(--isarva-muted);font-size:0.8125rem;">No cases available</div>';
      return;
    }
    menu.innerHTML = cases.map(item => `
      <button type="button" class="case-picker-item${item.id === this.activeCaseId ? ' active' : ''}" data-id="${item.id}" role="option">
        <span class="case-picker-item-id">${item.id}</span>
        <span class="case-picker-item-status">${item.status}</span>
        <span class="case-picker-item-meta">${item.complainant} vs ${item.respondent} · ${item.routedTo || 'IC'}</span>
      </button>`).join('');
    menu.querySelectorAll('.case-picker-item').forEach(btn => {
      btn.onclick = e => {
        e.stopPropagation();
        this.openCase(btn.dataset.id);
        this.closeCasePicker();
      };
    });
  },

  toggleCasePicker() {
    const picker = document.getElementById('case-picker');
    const menu = document.getElementById('case-picker-menu');
    const btn = document.getElementById('case-picker-btn');
    if (!picker || !menu) return;
    const open = menu.classList.contains('hidden');
    if (open) {
      menu.classList.remove('hidden');
      picker.classList.add('open');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    } else {
      this.closeCasePicker();
    }
  },

  closeCasePicker() {
    const picker = document.getElementById('case-picker');
    const menu = document.getElementById('case-picker-menu');
    const btn = document.getElementById('case-picker-btn');
    if (menu) menu.classList.add('hidden');
    if (picker) picker.classList.remove('open');
    if (btn) btn.setAttribute('aria-expanded', 'false');
  },

  renderCasesLists() {
    const html = this.renderCasesTableHTML();
    const list = document.getElementById('cases-list');
    const dash = document.getElementById('dashboard-cases');
    if (list) list.innerHTML = html;
    if (dash) dash.innerHTML = html;
    this.bindCaseButtons();
  },

  renderCasesTableHTML() {
    const cases = this.data.cases || [];
    if (!cases.length) {
      return `<div class="empty-state" style="padding:48px 24px;">
        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
        <p style="font-weight:600;color:var(--isarva-text);">No cases yet</p>
        <p>Click <strong>Demo Case</strong> or <strong>New Complaint</strong> to start.</p>
      </div>`;
    }
    const rows = cases.map(c => {
      const desc = (c.description || '—').slice(0, 80) + ((c.description || '').length > 80 ? '…' : '');
      const statusClass = (c.status || '').toLowerCase().includes('closed') ? 'status-closed' : 'status-submitted';
      return `<tr>
        <td class="col-id">${c.id}</td>
        <td class="col-parties">${c.complainant} <span>vs</span> ${c.respondent}</td>
        <td class="col-desc">${desc}</td>
        <td class="col-status"><span class="status-pill ${statusClass}">${c.status}</span></td>
        <td class="col-route">${c.routedTo || 'IC'}</td>
        <td class="col-action">
          <button type="button" class="btn-isarva btn-sm btn-operate-case" data-id="${c.id}">
            <i class="fas fa-play"></i> Operate
          </button>
        </td>
      </tr>`;
    }).join('');
    return `<div class="cases-table-wrap">
      <table class="cases-table">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Parties</th>
            <th>Summary</th>
            <th>Status</th>
            <th>Route</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>`;
  },

  bindCaseButtons() {
    document.querySelectorAll('.btn-operate-case').forEach(btn => {
      btn.onclick = () => this.openCase(btn.dataset.id);
    });
  },

  renderOperate() {
    const empty = document.getElementById('operate-empty');
    const panel = document.getElementById('operate-panel');
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    if (!c) {
      if (empty) empty.classList.remove('hidden');
      if (panel) panel.classList.add('hidden');
      return;
    }
    if (empty) empty.classList.add('hidden');
    if (panel) panel.classList.remove('hidden');
    this.operateStepIndex = c.stepIndex != null ? c.stepIndex : 0;
    const opId = document.getElementById('op-case-id');
    const opSt = document.getElementById('op-status');
    if (opId) opId.textContent = c.id;
    if (opSt) opSt.textContent = c.status;
    this.renderCasePicker();
    this.renderOperateStepsBar();
    this.renderOperateStepForm();
    this.renderStepHelp(OPERATE_STEPS[this.operateStepIndex].key);
  },

  renderOperateStepsBar() {
    const bar = document.getElementById('op-steps-bar');
    if (!bar) return;
    bar.innerHTML = OPERATE_STEPS.map((s, i) =>
      `<button type="button" class="step-tab${i === this.operateStepIndex ? ' active' : ''}" data-step="${i}">${s.label}</button>`
    ).join('');
    bar.querySelectorAll('.step-tab').forEach(b => {
      b.onclick = () => {
        this.operateStepIndex = parseInt(b.dataset.step, 10);
        const c = this.data.cases.find(x => x.id === this.activeCaseId);
        if (c) c.stepIndex = this.operateStepIndex;
        this.renderOperateStepsBar();
        this.renderOperateStepForm();
        this.renderStepHelp(OPERATE_STEPS[this.operateStepIndex].key);
      };
    });
  },

  renderOperateStepForm() {
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    const el = document.getElementById('op-step-content');
    if (!c || !el) return;
    const step = OPERATE_STEPS[this.operateStepIndex];
    let body = '';

    switch (step.key) {
      case 'review':
        body = `
          <div class="form-field-block">
            <p class="field-help">IC members read complaint privately. Decide: valid harassment case or not? Enough proof? Within 3 months?</p>
            <label class="form-label" for="fld-review">IC Review Decision *</label>
            <select id="fld-review" class="select">
              <option value="accept">Accept — start formal POSH process</option>
              <option value="reject">Reject — must write reason (not SH / insufficient)</option>
              <option value="moreinfo">Request more information from complainant</option>
            </select>
            <p class="field-help">Accept = inquiry will start. Reject = case ends with written reasons only.</p>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-review-notes">Review notes (what IC discussed)</label>
            <textarea id="fld-review-notes" class="textarea" rows="3" placeholder="e.g. Valid workplace harassment via WhatsApp. Screenshots attached.">${c.reviewNotes || ''}</textarea>
          </div>`;
        break;
      case 'conciliation':
        body = `
          <div class="form-field-block">
            <p class="field-help"><strong>Conciliation</strong> = optional settlement BEFORE inquiry. Only if woman wants. <strong>No money</strong> allowed.</p>
            <label class="checkbox-row">
              <input type="checkbox" id="fld-conc-req" ${c.conciliationRequested ? 'checked' : ''}>
              <span><strong>Complainant wants conciliation</strong></span>
            </label>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-conc-out">Outcome</label>
            <select id="fld-conc-out" class="select">
              <option value="skipped" ${c.conciliationOutcome === 'skipped' ? 'selected' : ''}>Skipped / Refused — go to inquiry</option>
              <option value="in_progress" ${c.conciliationOutcome === 'in_progress' ? 'selected' : ''}>In Progress</option>
              <option value="settled" ${c.conciliationOutcome === 'settled' ? 'selected' : ''}>Settled (apology/transfer only — NO cash)</option>
              <option value="failed" ${c.conciliationOutcome === 'failed' ? 'selected' : ''}>Failed / Terms breached — restart inquiry</option>
            </select>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-conc-terms">Settlement terms (if settled)</label>
            <textarea id="fld-conc-terms" class="textarea" rows="3" placeholder="e.g. Written apology + transfer. NO monetary payment.">${c.conciliationTerms || ''}</textarea>
          </div>`;
        break;
      case 'interim':
        body = `
          <div class="form-field-block">
            <p class="field-help">Temporary safety measures <strong>while inquiry is running</strong> — not final punishment.</p>
            <span class="form-label">Select interim relief</span>
            <div class="interim-options">
              ${INTERIM_RELIEF_OPTIONS.map(o => `<label class="interim-option"><input type="checkbox" name="fld-interim" value="${o}" ${(c.interimRelief || []).includes(o) ? 'checked' : ''}> ${o}</label>`).join('')}
            </div>
          </div>`;
        break;
      case 'notice':
        body = `
          <p class="field-help step-form-intro">Accused <strong>${c.respondent}</strong> gets official notice with complaint summary and hearing date. Law: minimum <strong>7 working days</strong> between notice and hearing.</p>
          <div class="form-row-2">
            <div class="form-field-block">
              <label class="form-label" for="fld-notice">Notice sent date</label>
              <input type="date" id="fld-notice" class="input" value="${c.noticeDate || ''}">
            </div>
            <div class="form-field-block">
              <label class="form-label" for="fld-hearing">Hearing date</label>
              <input type="date" id="fld-hearing" class="input" value="${c.hearingDate || ''}">
            </div>
          </div>`;
        break;
      case 'inquiry':
        body = `
          <div class="form-field-block">
            <p class="field-help"><strong>Inquiry</strong> = full IC investigation. Must finish within <strong>90 days</strong>.</p>
            <label class="form-label" for="fld-mom">Inquiry / investigation notes</label>
            <textarea id="fld-mom" class="textarea" rows="4" placeholder="Summary of evidence reviewed, IC observations...">${c.inquiryNotes || ''}</textarea>
          </div>
          <div class="form-field-block">
            <label class="checkbox-row">
              <input type="checkbox" id="fld-fir" ${c.firFiled ? 'checked' : ''}>
              <span>Woman also filed FIR / police case (workplace inquiry still continues)</span>
            </label>
          </div>`;
        break;
      case 'hearing':
        body = `
          <div class="form-field-block">
            <p class="field-help"><strong>Hearing</strong> = formal meeting. <strong>MoM</strong> = Minutes of Meeting.</p>
            <label class="form-label" for="fld-witnesses">Witnesses (one name per line)</label>
            <textarea id="fld-witnesses" class="textarea" rows="2" placeholder="e.g. Sunita Verma">${c.witnesses || ''}</textarea>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-mom-hearing">Minutes of Meeting (MoM)</label>
            <textarea id="fld-mom-hearing" class="textarea" rows="5" placeholder="Meeting date, attendees, statements...">${c.momContent || ''}</textarea>
          </div>`;
        break;
      case 'recommendation':
        body = `
          <div class="form-field-block">
            <p class="field-help">IC report to employer within <strong>10 days</strong> after inquiry ends.</p>
            <label class="form-label" for="fld-finding">Finding</label>
            <select id="fld-finding" class="select">
              <option value="proved" ${c.finding === 'proved' ? 'selected' : ''}>Proved — harassment confirmed</option>
              <option value="not_proved" ${c.finding === 'not_proved' ? 'selected' : ''}>Not Proved</option>
              <option value="partial" ${c.finding === 'partial' ? 'selected' : ''}>Partially Proved</option>
            </select>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-recommendation">IC Recommendation to management</label>
            <textarea id="fld-recommendation" class="textarea" rows="4" placeholder="Warning, training, transfer...">${c.recommendation || ''}</textarea>
          </div>`;
        break;
      case 'action':
        body = `
          <div class="form-field-block">
            <p class="field-help">Management must act within <strong>60 days</strong>.</p>
            <label class="form-label" for="fld-action">Action actually taken by company</label>
            <textarea id="fld-action" class="textarea" rows="4" placeholder="Warning letter, training, etc.">${c.actionTaken || ''}</textarea>
          </div>`;
        break;
      case 'appeal':
        body = `
          <p class="field-help step-form-intro">Appeal within <strong>90 days</strong> if unhappy. Do not publish complainant name in media.</p>
          <div class="form-field-block">
            <label class="checkbox-row">
              <input type="checkbox" id="fld-appeal" ${c.appealFiled ? 'checked' : ''}>
              <span><strong>Appeal filed</strong> (within 90 days)</span>
            </label>
          </div>
          <div class="form-field-block">
            <label class="form-label" for="fld-closure">Closure notes</label>
            <textarea id="fld-closure" class="textarea" rows="3" placeholder="Final summary, archive reference...">${c.closureNotes || ''}</textarea>
          </div>
          <div class="form-field-block">
            <div class="form-actions-row">
              <button type="button" id="btn-close-case" class="btn-success">
                <i class="fas fa-check-circle"></i> Close Case Now
              </button>
            </div>
          </div>`;
        break;
    }

    el.innerHTML = `
      <h3 class="step-form-title">${step.label} — Fill form below</h3>
      <p class="step-form-meta">Case: <b>${c.complainant}</b> vs <b>${c.respondent}</b> · Routed to: <b>${c.routedTo || 'IC'}</b> · Status: ${c.status}</p>
      <div class="step-form-body">${body}</div>`;
    document.getElementById('btn-close-case')?.addEventListener('click', () => this.closeCase());
    this.renderStepHelp(step.key);
  },

  saveOperateStep(goNext) {
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    if (!c) return;
    const step = OPERATE_STEPS[this.operateStepIndex];
    const g = id => document.getElementById(id);

    switch (step.key) {
      case 'review':
        const rv = g('fld-review')?.value;
        c.reviewNotes = g('fld-review-notes')?.value || '';
        c.status = rv === 'reject' ? 'Rejected (with reasons)' : rv === 'moreinfo' ? 'Additional Info Requested' : 'Under IC/LC Review';
        break;
      case 'conciliation':
        c.conciliationRequested = g('fld-conc-req')?.checked || false;
        c.conciliationOutcome = g('fld-conc-out')?.value || 'skipped';
        c.conciliationTerms = g('fld-conc-terms')?.value || '';
        c.status = c.conciliationOutcome === 'settled' ? 'Conciliation Settled' : c.conciliationOutcome === 'skipped' ? c.status : 'Conciliation In Progress';
        break;
      case 'interim':
        c.interimRelief = Array.from(document.querySelectorAll('[name=fld-interim]:checked')).map(x => x.value);
        c.status = 'Interim Relief Applied';
        break;
      case 'notice':
        c.noticeDate = g('fld-notice')?.value || '';
        c.hearingDate = g('fld-hearing')?.value || '';
        c.status = 'Notice Issued to Respondent';
        break;
      case 'inquiry':
        c.inquiryNotes = g('fld-mom')?.value || '';
        c.firFiled = g('fld-fir')?.checked || false;
        c.status = 'Inquiry Started';
        break;
      case 'hearing':
        c.witnesses = g('fld-witnesses')?.value || '';
        c.momContent = g('fld-mom-hearing')?.value || '';
        c.status = 'Hearing Completed';
        break;
      case 'recommendation':
        c.finding = g('fld-finding')?.value || '';
        c.recommendation = g('fld-recommendation')?.value || '';
        c.status = 'Recommendation Pending';
        break;
      case 'action':
        c.actionTaken = g('fld-action')?.value || '';
        c.status = 'Action Implemented';
        break;
      case 'appeal':
        c.appealFiled = g('fld-appeal')?.checked || false;
        c.closureNotes = g('fld-closure')?.value || '';
        if (!goNext) c.status = c.appealFiled ? 'Appeal Filed (90 days)' : c.status;
        break;
    }

    if (goNext && step.status && step.key !== 'appeal') c.status = step.status;
    c.stepIndex = this.operateStepIndex;
    c.timeline = c.timeline || [];
    c.timeline.unshift({ date: new Date().toISOString(), status: c.status, note: step.label + ' saved' });
    this.log('Step saved', c.id, step.label);
    this.save();
    if (goNext && this.operateStepIndex < OPERATE_STEPS.length - 1) {
      this.operateStepIndex++;
      c.stepIndex = this.operateStepIndex;
      this.save();
    }
    this.showToast(goNext ? 'Saved — next step' : 'Step saved');
    this.refreshAll();
    this.renderOperateStepsBar();
    this.renderOperateStepForm();
    this.renderStepHelp(OPERATE_STEPS[this.operateStepIndex].key);
  },

  changeOperateStep(delta) {
    this.operateStepIndex = Math.max(0, Math.min(OPERATE_STEPS.length - 1, this.operateStepIndex + delta));
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    if (c) c.stepIndex = this.operateStepIndex;
    this.renderOperateStepsBar();
    this.renderOperateStepForm();
    this.renderStepHelp(OPERATE_STEPS[this.operateStepIndex].key);
  },

  closeCase() {
    const c = this.data.cases.find(x => x.id === this.activeCaseId);
    if (!c) return;
    c.status = 'Closed';
    c.closedDate = new Date().toISOString();
    c.timeline.unshift({ date: new Date().toISOString(), status: 'Closed', note: 'Case closed' });
    this.log('Case closed', c.id, c.closureNotes || '');
    this.save();
    this.showToast('Case closed');
    this.refreshAll();
    this.renderOperate();
  },

  saveIC() {
    this.data.ic = {
      presidingOfficer: document.getElementById('ic-po').value,
      externalMember: document.getElementById('ic-ext').value,
      members: document.getElementById('ic-members').value,
      constituted: true
    };
    this.save();
    this.log('IC saved', '-', this.data.ic.presidingOfficer);
    document.getElementById('ic-saved-msg').textContent = 'IC saved: ' + this.data.ic.presidingOfficer;
    this.showToast('IC details saved');
  },

  loadICForm() {
    const ic = this.data.ic || {};
    const set = (id, v) => { const e = document.getElementById(id); if (e) e.value = v || ''; };
    set('ic-po', ic.presidingOfficer);
    set('ic-ext', ic.externalMember);
    set('ic-members', ic.members);
  },

  renderEmployerDuties() {
    const el = document.getElementById('employer-duties-list');
    if (!el) return;
    el.innerHTML = `<p class="field-help" style="margin-bottom:16px;">Tick each duty when completed. Mandatory under <strong>Section 19</strong> for annual District Officer report.</p>` +
      this.data.employerDuties.map((d, i) => `
      <label class="checkbox-row" style="margin-bottom:8px;${d.done ? 'opacity:0.75;' : ''}">
        <input type="checkbox" ${d.done ? 'checked' : ''} data-duty="${i}">
        <span style="font-size:0.8125rem;${d.done ? 'text-decoration:line-through;color:var(--isarva-muted);' : ''}">${d.duty}</span>
      </label>`).join('');
    el.querySelectorAll('[data-duty]').forEach(cb => {
      cb.onchange = () => {
        const i = parseInt(cb.dataset.duty);
        this.data.employerDuties[i].done = cb.checked;
        this.data.employerDuties[i].date = cb.checked ? new Date().toLocaleDateString() : '';
        this.save();
        this.renderEmployerDuties();
      };
    });
  },

  saveWorkshop() {
    const name = document.getElementById('workshop-name').value;
    const date = document.getElementById('workshop-date').value;
    if (!name || !date) return alert('Enter name and date');
    this.data.workshops.push({ name, date });
    this.save();
    this.log('Workshop added', '-', name);
    document.getElementById('workshop-name').value = '';
    this.renderWorkshopList();
    this.showToast('Workshop added');
  },

  renderWorkshopList() {
    const el = document.getElementById('workshop-list');
    if (!el) return;
    el.innerHTML = (this.data.workshops || []).map(w => `<li class="py-1 border-b">${w.date} — ${w.name}</li>`).join('') || '<li class="text-slate-400">None yet</li>';
  },

  renderAuditLog() {
    const el = document.getElementById('audit-log-table');
    if (!el) return;
    const logs = this.data.auditLog || [];
    el.innerHTML = logs.length ? logs.map(l => `<tr class="border-t">
      <td class="p-2">${new Date(l.time).toLocaleString()}</td>
      <td class="p-2">${l.action}</td>
      <td class="p-2 font-mono text-xs">${l.caseId}</td>
      <td class="p-2 text-slate-600">${l.details}</td>
    </tr>`).join('') : '<tr><td colspan="4" class="p-4 text-slate-500">No actions yet</td></tr>';
  },

  exportJSON() {
    const blob = new Blob([JSON.stringify(this.data, null, 2)], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'isarva-posh-' + new Date().toISOString().slice(0, 10) + '.json';
    a.click();
    this.showToast('Exported');
  },

  importJSON(e) {
    const file = e.target.files[0];
    if (!file) return;
    const r = new FileReader();
    r.onload = ev => {
      try {
        this.data = JSON.parse(ev.target.result);
        this.activeCaseId = this.data.activeCaseId || null;
        this.save();
        this.init();
        this.showToast('Imported');
      } catch (err) { alert('Invalid file'); }
    };
    r.readAsText(file);
  }
};

document.addEventListener('DOMContentLoaded', () => App.init());
