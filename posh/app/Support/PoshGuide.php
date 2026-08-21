<?php

namespace App\Support;

use App\Models\User;

class PoshGuide
{
    public static function primaryRoleKey(User $user): string
    {
        if ($user->canManageIc()) {
            return 'admin';
        }
        if ($user->hasIcAccess()) {
            return 'ic';
        }

        return 'employee';
    }

    /** @return array<string, string> */
    public static function roleTabs(User $user): array
    {
        $tabs = [
            'employee' => 'Employee guide',
        ];

        if ($user->hasIcAccess()) {
            $tabs['ic'] = 'IC / HR guide';
        }
        if ($user->canManageIc()) {
            $tabs['admin'] = 'Administrator guide';
        }

        return $tabs;
    }

    /** @return list<array<string, mixed>> */
    public static function sectionsForTab(string $tab, User $user): array
    {
        $allowed = array_keys(self::roleTabs($user));
        if (! in_array($tab, $allowed, true)) {
            $tab = self::primaryRoleKey($user);
        }

        return array_values(array_filter(
            self::allSections(),
            fn (array $s) => in_array('all', $s['tabs'], true) || in_array($tab, $s['tabs'], true)
        ));
    }

    /** @return list<array{term: string, definition: string}> */
    public static function glossary(): array
    {
        return [
            ['term' => 'POSH Act', 'definition' => 'The Sexual Harassment of Women at Workplace (Prevention, Prohibition and Redressal) Act, 2013 — Indian law requiring safe workplaces and a formal complaint process.'],
            ['term' => 'Aggrieved woman', 'definition' => 'A woman who alleges she has been subjected to sexual harassment at the workplace. The Act uses this legal term for the complainant.'],
            ['term' => 'Respondent', 'definition' => 'The person against whom a harassment complaint is filed (colleague, manager, client, vendor, or employer).'],
            ['term' => 'IC (Internal Committee)', 'definition' => 'Mandatory committee in workplaces with 10+ employees to receive complaints, conduct inquiry, and recommend action. At least half the members must be women.'],
            ['term' => 'LC (Local Committee)', 'definition' => 'District-level committee that handles complaints when the workplace has fewer than 10 workers, or when the respondent is the employer.'],
            ['term' => 'Presiding Officer', 'definition' => 'Senior woman employee who chairs IC meetings and leads the inquiry process.'],
            ['term' => 'External member', 'definition' => 'Independent person from an NGO or legal background — mandatory on the IC to ensure fairness.'],
            ['term' => 'Conciliation', 'definition' => 'Optional settlement before full inquiry — only if the complainant agrees. Monetary settlement is not permitted under the Act.'],
            ['term' => 'Interim relief', 'definition' => 'Temporary measures during inquiry (transfer, leave, no contact orders) to keep the complainant safe.'],
            ['term' => 'Natural justice', 'definition' => 'Fair process: both sides heard, evidence considered, written reasoned decisions.'],
            ['term' => 'Case number', 'definition' => 'Unique ID such as POSH-2026-0001 assigned when a complaint is filed — used in all records and audit logs.'],
            ['term' => 'Workplace', 'definition' => 'Not only the office — includes work-related travel, client sites, video calls, and work WhatsApp/email.'],
            ['term' => 'Annual report (S.22)', 'definition' => 'Yearly report to the District Officer with case statistics and prevention activities — no complainant names published.'],
            ['term' => 'Section 19 duties', 'definition' => 'Employer obligations: safe environment, posters, IC display, workshops, service rules, annual report, and more.'],
        ];
    }

    /** @return list<array{label: string, days: string, law: string}> */
    public static function statutoryTimelines(): array
    {
        $s = config('posh.statutory_sla_days');

        return [
            ['label' => 'File complaint after incident', 'days' => $s['filing_window_months'] . ' months (+ ' . $s['filing_extension_months'] . ' month extension possible)', 'law' => 'Section 9'],
            ['label' => 'Complete inquiry', 'days' => $s['inquiry_days'] . ' days', 'law' => 'Section 11'],
            ['label' => 'IC report to employer after inquiry', 'days' => $s['report_after_inquiry_days'] . ' days', 'law' => 'Section 11(4)'],
            ['label' => 'Management implements IC recommendation', 'days' => $s['management_action_days'] . ' days', 'law' => 'Section 11(4)'],
            ['label' => 'Appeal to court/tribunal', 'days' => $s['appeal_days'] . ' days', 'law' => 'Section 12'],
            ['label' => 'Notice before hearing (working days)', 'days' => '7 working days minimum', 'law' => 'POSH Rules'],
        ];
    }

    /** @return list<array<string, mixed>> */
    protected static function allSections(): array
    {
        return array_merge(
            self::fundamentalsSections(),
            self::employeeSections(),
            self::icSections(),
            self::adminSections(),
        );
    }

    /** @return list<array<string, mixed>> */
    protected static function fundamentalsSections(): array
    {
        return [
            [
                'id' => 'what-is-posh',
                'title' => 'What is POSH & why this software?',
                'icon' => 'fa-shield-halved',
                'tabs' => ['all'],
                'summary' => 'Understand the law, your organisation\'s duties, and how this product helps.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => '<strong>POSH</strong> (Prevention of Sexual Harassment) refers to the 2013 Act that protects women from sexual harassment at work. Employers must provide a safe environment, constitute an <strong>Internal Committee (IC)</strong>, display policies, run awareness programmes, and handle every complaint through a fair, confidential process.'],
                    ['type' => 'paragraph', 'text' => 'This application is your organisation\'s <strong>compliance workspace</strong>: employees can read policy and file complaints; IC/HR can investigate using a step-by-step wizard; management tracks 60-day actions; HR admins configure IC, policy, and public QR intake.'],
                    ['type' => 'callout', 'style' => 'law', 'title' => 'Who is covered?', 'text' => 'Women employees and women in workplace relationships (including contract workers in many cases). Complaints against the <strong>employer</strong> go to the <strong>Local Committee (LC)</strong>, not the internal IC.'],
                    ['type' => 'visual', 'screen' => 'dashboard'],
                    ['type' => 'bullets', 'title' => 'Core principles', 'items' => [
                        '<strong>Confidentiality</strong> — identities are not shared casually; only IC/authorised roles see full details.',
                        '<strong>Timelines</strong> — filing windows, 90-day inquiry, 60-day management action are tracked.',
                        '<strong>Documentation</strong> — every step is recorded in the audit log for defensibility.',
                        '<strong>Prevention</strong> — not only complaints: workshops, posters, and annual reporting matter too.',
                    ]],
                ],
            ],
            [
                'id' => 'roles-explained',
                'title' => 'Roles in this system',
                'icon' => 'fa-users',
                'tabs' => ['all'],
                'summary' => 'What Employee, IC/HR, and Administrator can each do.',
                'blocks' => [
                    ['type' => 'table', 'headers' => ['Role', 'Typical user', 'Can do'], 'rows' => [
                        ['Employee', 'Any staff member', 'Read policy, acknowledge, file complaint, view own cases, use employee portal'],
                        ['IC / HR', 'Presiding officer, IC members, external member, HR with IC access', 'All cases, operate 9-step workflow, compliance, annual report, audit log, download evidence'],
                        ['Administrator', 'HR Admin / Super Admin', 'Everything IC can do + IC setup, policy versions, organisation settings & QR link'],
                    ]],
                    ['type' => 'callout', 'style' => 'info', 'title' => 'How you get your role', 'text' => 'You sign in from the HRMS Workspace via SSO. Admins are set by bootstrap email; IC members are added in <strong>IC Setup</strong>; everyone else is an Employee until mapped to the IC list.'],
                ],
            ],
            [
                'id' => 'case-lifecycle',
                'title' => 'Complaint lifecycle (big picture)',
                'icon' => 'fa-diagram-project',
                'tabs' => ['all'],
                'summary' => 'From filing to closure — statuses and who acts when.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        ['title' => 'File', 'text' => 'Employee or public QR intake submits complaint → case number created.'],
                        ['title' => 'IC review', 'text' => 'IC accepts, rejects with reasons, or requests more information.'],
                        ['title' => 'Optional conciliation', 'text' => 'Only if complainant wants settlement (no monetary settlement under law).'],
                        ['title' => 'Inquiry & hearing', 'text' => 'Notice to respondent, evidence, hearing, MoM — within 90 days.'],
                        ['title' => 'Recommendation', 'text' => 'IC finding + recommendation to management within 10 days of inquiry end.'],
                        ['title' => 'Management action', 'text' => 'Employer implements action within 60 days (warning, transfer, termination, etc.).'],
                        ['title' => 'Close or appeal', 'text' => 'Case closed; parties may appeal within 90 days if unsatisfied.'],
                    ]],
                    ['type' => 'callout', 'style' => 'warning', 'title' => 'Statuses you may see', 'text' => implode(' · ', config('posh.statuses'))],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    protected static function employeeSections(): array
    {
        return [
            [
                'id' => 'employee-portal',
                'title' => 'Employee portal',
                'icon' => 'fa-user-shield',
                'tabs' => ['employee'],
                'summary' => 'Your home for policy, IC contacts, and quick actions.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Open <strong>Employee Portal</strong> from the sidebar. You will see whether you have acknowledged the active POSH policy, contacts of IC members (names and roles — for reporting, not for gossip), and shortcuts to file a complaint or read policy.'],
                    ['type' => 'visual', 'screen' => 'employee-portal'],
                    ['type' => 'link', 'route' => 'employee.portal', 'label' => 'Open Employee Portal'],
                    ['type' => 'example', 'text' => 'On your first login, HR asks you to open the portal, read the policy, and click <strong>I acknowledge</strong> so the organisation has proof of awareness training.'],
                ],
            ],
            [
                'id' => 'policy-ack',
                'title' => 'Read & acknowledge policy',
                'icon' => 'fa-file-contract',
                'tabs' => ['employee'],
                'summary' => 'Section 19 requires a written policy — you must confirm you have read it.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Go to <strong>Employee Portal → View policy</strong> or the policy page directly. Read the full text (and download PDF if attached). Scroll to the bottom and submit acknowledgement once you understand it.'],
                    ['type' => 'visual', 'screen' => 'policy-employee'],
                    ['type' => 'link', 'route' => 'employee.policy', 'label' => 'Open policy page'],
                    ['type' => 'callout', 'style' => 'info', 'title' => 'What acknowledgement means', 'text' => 'It records the date and your user account — evidence for audits and annual reports that employees were informed of workplace rules and the complaint process.'],
                ],
            ],
            [
                'id' => 'file-complaint',
                'title' => 'How to file a complaint',
                'icon' => 'fa-file-circle-plus',
                'tabs' => ['employee'],
                'summary' => 'Step-by-step: New Complaint form, fields, evidence, anonymity.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Click <strong>New Complaint</strong> in the sidebar. Fill every required field honestly and clearly. Upload screenshots, emails, or documents as <strong>evidence</strong>. Submit — you receive a <strong>case number</strong> immediately.'],
                    ['type' => 'visual', 'screen' => 'new-complaint'],
                    ['type' => 'link', 'route' => 'complaints.create', 'label' => 'File a complaint'],
                    ['type' => 'table', 'headers' => ['Field', 'Meaning'], 'rows' => [
                        ['Complainant name', 'Your name as aggrieved woman (hidden if anonymous complaint selected).'],
                        ['Employee ID / Department', 'Links complaint to HR records for routing.'],
                        ['Respondent', 'Person accused — name and type (employee, supervisor, third party, employer).'],
                        ['Incident date & description', 'When it happened and what occurred — be factual: dates, words, channel (WhatsApp, meeting, etc.).'],
                        ['Location', 'Office, remote call, client site, travel — all can be workplace.'],
                        ['Evidence', 'Files IC will review confidentially.'],
                        ['Anonymous', 'If checked, identity protected from wider workplace; IC still investigates.'],
                    ]],
                    ['type' => 'callout', 'style' => 'law', 'title' => 'Filing deadline', 'text' => 'Normally within <strong>3 months</strong> of the incident; IC may allow up to <strong>3 more months</strong> with written reasons. Late complaints may still be reviewed but document why they are late.'],
                    ['type' => 'example', 'text' => 'Priya receives inappropriate late-night messages from her manager. She saves screenshots, opens New Complaint, selects respondent type Supervisor, describes dates and messages, uploads images, submits → case <strong>POSH-2026-0004</strong> is created.'],
                ],
            ],
            [
                'id' => 'my-cases',
                'title' => 'My cases — track your complaint',
                'icon' => 'fa-folder',
                'tabs' => ['employee'],
                'summary' => 'View status and timeline for complaints you filed.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => '<strong>My Cases</strong> lists only complaints where you are the complainant. Click a case to see current <strong>status</strong>, key dates, and a timeline of IC actions (without exposing confidential IC notes meant for committee only).'],
                    ['type' => 'visual', 'screen' => 'my-cases'],
                    ['type' => 'link', 'route' => 'complaints.my', 'label' => 'View My Cases'],
                    ['type' => 'callout', 'style' => 'info', 'title' => 'Do not discuss openly', 'text' => 'POSH requires confidentiality. Avoid discussing case details in common areas or chat groups — it can harm the process and re-traumatise parties.'],
                ],
            ],
            [
                'id' => 'employee-management',
                'title' => 'Management portal (employees)',
                'icon' => 'fa-briefcase',
                'tabs' => ['employee'],
                'summary' => 'If you are in management, what this view shows.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Managers may access <strong>Management</strong> to see cases awaiting <strong>employer action within 60 days</strong> after IC recommendation — not to bypass IC. Implementation of warnings, transfers, or termination is documented here.'],
                    ['type' => 'visual', 'screen' => 'management'],
                    ['type' => 'link', 'route' => 'management.index', 'label' => 'Open Management portal'],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    protected static function icSections(): array
    {
        $steps = config('posh.operate_steps');

        $stepBlocks = [
            ['type' => 'paragraph', 'text' => 'Open <strong>All Cases</strong> → choose a case → <strong>Operate</strong>. Move through nine steps in order. Each step updates case status and saves to the audit log. Use <strong>Save & Next Step</strong> after completing forms.'],
            ['type' => 'visual', 'screen' => 'operate'],
            ['type' => 'link', 'route' => 'cases.index', 'label' => 'Open All Cases'],
        ];

        foreach ($steps as $i => $step) {
            $help = self::stepHelp($step['key']);
            $stepBlocks[] = [
                'type' => 'callout',
                'style' => 'info',
                'title' => $step['label'],
                'text' => ($help['body'] ?? '') . ($help['law'] ? ' <em>(' . $help['law'] . ')</em>' : ''),
            ];
            if (! empty($help['example'])) {
                $stepBlocks[] = ['type' => 'example', 'text' => $help['example']];
            }
        }

        return [
            [
                'id' => 'ic-dashboard',
                'title' => 'IC dashboard & SLA alerts',
                'icon' => 'fa-gauge-high',
                'tabs' => ['ic'],
                'summary' => 'Command center for open cases, compliance %, and deadline warnings.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'The <strong>Dashboard</strong> shows counts of open/closed cases, whether policy is active, acknowledgement totals, IC member count, employer duty completion %, and <strong>SLA alerts</strong> (inquiry overdue, management action due, etc.).'],
                    ['type' => 'visual', 'screen' => 'dashboard'],
                    ['type' => 'link', 'route' => 'dashboard', 'label' => 'Open Dashboard'],
                ],
            ],
            [
                'id' => 'all-cases',
                'title' => 'All cases — search & filter',
                'icon' => 'fa-folder-open',
                'tabs' => ['ic'],
                'summary' => 'Find any organisation case and open Operate or detail view.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'IC sees every complaint for the organisation. Filter by status, search by case number or name. Click <strong>Operate</strong> for the wizard or the case row for read-only detail and evidence download.'],
                    ['type' => 'visual', 'screen' => 'all-cases'],
                    ['type' => 'link', 'route' => 'cases.index', 'label' => 'Open All Cases'],
                ],
            ],
            [
                'id' => 'operate-wizard',
                'title' => 'Operate case — 9-step wizard',
                'icon' => 'fa-list-check',
                'tabs' => ['ic'],
                'summary' => 'Complete guide to each operate step with law references.',
                'blocks' => $stepBlocks,
            ],
            [
                'id' => 'respondent-notice',
                'title' => 'Respondent notice (print)',
                'icon' => 'fa-print',
                'tabs' => ['ic'],
                'summary' => 'Generate printable notice after step 4.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'From Operate step 4 or case tools, open <strong>Respondent Notice</strong>. It formats a print-ready document with complaint summary and hearing date. Respondent must receive at least <strong>7 working days</strong> before hearing.'],
                    ['type' => 'callout', 'style' => 'law', 'title' => 'Natural justice', 'text' => 'Accused must know allegations and have opportunity to respond in writing and at hearing.'],
                ],
            ],
            [
                'id' => 'compliance-module',
                'title' => 'Compliance — Section 19 duties',
                'icon' => 'fa-clipboard-check',
                'tabs' => ['ic'],
                'summary' => 'Employer checklist and prevention events.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'POSH is not only reactive. <strong>Compliance</strong> tracks 14 employer duties (posters, IC order display, workshops, service rules, annual report, etc.). Tick items when done and log <strong>prevention events</strong> (orientation, workshop, poster campaign).'],
                    ['type' => 'visual', 'screen' => 'compliance'],
                    ['type' => 'link', 'route' => 'compliance.index', 'label' => 'Open Compliance'],
                    ['type' => 'bullets', 'title' => 'Prevention event types', 'items' => [
                        'Awareness workshop for all employees',
                        'IC member orientation',
                        'Display of policy / IC order on notice boards',
                    ]],
                ],
            ],
            [
                'id' => 'annual-report',
                'title' => 'Annual report (Section 22)',
                'icon' => 'fa-file-lines',
                'tabs' => ['ic'],
                'summary' => 'Generate, review, export, mark submitted to District Officer.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Each year, generate a report with case statistics (no names), workshops held, policy acknowledgements, and duty completion. Review, export PDF-style view, then mark <strong>Submitted</strong> when filed with the District Officer.'],
                    ['type' => 'visual', 'screen' => 'annual-report'],
                    ['type' => 'link', 'route' => 'reports.annual.index', 'label' => 'Open Annual Reports'],
                    ['type' => 'callout', 'style' => 'warning', 'title' => 'Confidentiality', 'text' => 'Annual reports contain aggregate data only — never publish complainant or respondent identities.'],
                ],
            ],
            [
                'id' => 'audit-log',
                'title' => 'Audit log',
                'icon' => 'fa-clock-rotate-left',
                'tabs' => ['ic'],
                'summary' => 'Who did what, when — defensibility in disputes.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Every significant action (login, complaint filed, step saved, evidence downloaded, policy published) is logged with timestamp and user. Search by keyword or case number.'],
                    ['type' => 'visual', 'screen' => 'audit'],
                    ['type' => 'link', 'route' => 'audit.index', 'label' => 'Open Audit Log'],
                    ['type' => 'example', 'text' => 'If a party claims IC never reviewed evidence, audit shows "Evidence downloaded — POSH-2026-0004 — 14 Jun 2026 10:32".'],
                ],
            ],
            [
                'id' => 'evidence-download',
                'title' => 'Evidence handling',
                'icon' => 'fa-paperclip',
                'tabs' => ['ic'],
                'summary' => 'Only IC roles can download uploaded files.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'From case detail or operate flow, IC members download evidence attachments. Access is denied to regular employees to protect confidentiality. Store downloads securely; do not forward to unauthorised persons.'],
                    ['type' => 'callout', 'style' => 'warning', 'title' => 'Data protection', 'text' => 'Treat evidence as sensitive personal data. Use official devices and approved storage only.'],
                ],
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    protected static function adminSections(): array
    {
        return [
            [
                'id' => 'ic-setup-guide',
                'title' => 'IC Setup — constitute the committee',
                'icon' => 'fa-people-group',
                'tabs' => ['admin'],
                'summary' => 'Add presiding officer, members, external member; 50% women rule.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Before handling complaints, add all IC members in <strong>IC Setup</strong>. Law requires: Presiding Officer (senior woman employee), minimum 2 employee members, one <strong>external member</strong>, and at least <strong>50% women</strong> on the committee.'],
                    ['type' => 'visual', 'screen' => 'ic-setup'],
                    ['type' => 'link', 'route' => 'ic-members.index', 'label' => 'Open IC Setup'],
                    ['type' => 'table', 'headers' => ['IC position', 'Requirement'], 'rows' => [
                        ['Presiding Officer', 'Senior woman employee — chairs inquiries'],
                        ['Internal members', 'Employees of the organisation'],
                        ['External member', 'From NGO / association — not employed by company'],
                        ['Member Secretary', 'Often HR — maintains records and coordinates'],
                    ]],
                    ['type' => 'callout', 'style' => 'info', 'title' => 'SSO mapping', 'text' => 'When a member\'s email is added here, their next login assigns IC role automatically (presiding officer / IC member / external).'],
                ],
            ],
            [
                'id' => 'policy-admin',
                'title' => 'Policy management',
                'icon' => 'fa-file-contract',
                'tabs' => ['admin'],
                'summary' => 'Create versions, publish, one active policy for employees.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'Under <strong>Policy</strong>, create a version (e.g. v2026.1), write HTML content or attach PDF, save as draft or <strong>Publish</strong>. Only one policy is <strong>active</strong> at a time — publishing replaces the previous live version.'],
                    ['type' => 'visual', 'screen' => 'policy-admin'],
                    ['type' => 'link', 'route' => 'policies.index', 'label' => 'Open Policy admin'],
                    ['type' => 'bullets', 'items' => [
                        '<strong>Draft</strong> — work in progress, not visible to employees.',
                        '<strong>Active</strong> — shown on employee portal for reading and acknowledgement.',
                        '<strong>Preview</strong> — use "Preview as employee" before publishing major changes.',
                    ]],
                ],
            ],
            [
                'id' => 'org-settings',
                'title' => 'Organisation settings & QR intake',
                'icon' => 'fa-building',
                'tabs' => ['admin'],
                'summary' => 'Name, size, helpline, public complaint link.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => '<strong>Settings</strong> stores organisation display name, employee count, optional WhatsApp helpline (shown on employee portal), and the <strong>public intake URL</strong> for QR posters.'],
                    ['type' => 'visual', 'screen' => 'settings'],
                    ['type' => 'link', 'route' => 'settings.edit', 'label' => 'Open Settings'],
                    ['type' => 'steps', 'items' => [
                        ['title' => 'Copy intake link', 'text' => 'Use on workplace posters so anyone can file without login.'],
                        ['title' => 'Generate QR', 'text' => 'Use any QR generator with the URL — place at reception and notice boards.'],
                        ['title' => 'Regenerate', 'text' => 'Only if link was leaked — old QR codes will stop working.'],
                    ]],
                ],
            ],
            [
                'id' => 'public-intake',
                'title' => 'Public QR / anonymous intake',
                'icon' => 'fa-qrcode',
                'tabs' => ['admin', 'ic'],
                'summary' => 'How public complaints enter the system.',
                'blocks' => [
                    ['type' => 'paragraph', 'text' => 'The intake page does not require login. Submissions create a normal case in your organisation queue with source marked as public intake. IC processes them the same as internal complaints.'],
                    ['type' => 'visual', 'screen' => 'intake'],
                    ['type' => 'callout', 'style' => 'law', 'title' => 'Section 19 display', 'text' => 'Employer must display details of IC and the complaint mechanism prominently at the workplace.'],
                ],
            ],
            [
                'id' => 'admin-checklist',
                'title' => 'Administrator go-live checklist',
                'icon' => 'fa-clipboard-list',
                'tabs' => ['admin'],
                'summary' => 'Recommended order when rolling out POSH software.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        ['title' => '1. Organisation settings', 'text' => 'Set name, employee count, helpline.'],
                        ['title' => '2. IC Setup', 'text' => 'Add all members; verify 50% women indicator.'],
                        ['title' => '3. Publish policy', 'text' => 'Create and activate workplace policy.'],
                        ['title' => '4. Employee communication', 'text' => 'Ask all staff to acknowledge policy via Employee Portal.'],
                        ['title' => '5. Posters & QR', 'text' => 'Print IC order + intake QR at conspicuous places.'],
                        ['title' => '6. Compliance duties', 'text' => 'Start ticking Section 19 checklist and log first workshop.'],
                        ['title' => '7. Train IC', 'text' => 'Walk through Operate wizard with this User Guide.'],
                    ]],
                ],
            ],
        ];
    }

    /** @return array{body?: string, law?: string, example?: string} */
    protected static function stepHelp(string $key): array
    {
        $map = [
            'review' => [
                'body' => 'IC reads complaint first: Is it sexual harassment at workplace? Enough detail? Within filing window? If against employer → route to LC. Outcomes: Accept, Reject with written reasons, or Request more info.',
                'law' => 'Section 9',
                'example' => 'IC accepts Priya\'s complaint → status becomes Under IC/LC Review.',
            ],
            'conciliation' => [
                'body' => 'Optional settlement only if complainant wants it — cannot be forced. No monetary settlement through conciliation. Allowed: apology, transfer, warning. Can skip if not wanted.',
                'law' => 'Section 10',
                'example' => 'Complainant declines conciliation → mark Skipped and proceed to inquiry.',
            ],
            'interim' => [
                'body' => 'Temporary protection during inquiry: transfer complainant or accused, paid leave up to 3 months, no-contact orders.',
                'law' => 'Section 12',
                'example' => 'Priya moved to another department until inquiry ends.',
            ],
            'notice' => [
                'body' => 'Formal written notice to respondent with summary and hearing date. Minimum 7 working days before hearing.',
                'law' => 'Rule 6',
                'example' => 'Notice dated 20 May, hearing 30 May — respondent submits written reply.',
            ],
            'inquiry' => [
                'body' => 'Full investigation within 90 days. Review evidence, both parties heard, principles of natural justice.',
                'law' => 'Section 11',
                'example' => 'IC reviews WhatsApp screenshots and email chain.',
            ],
            'hearing' => [
                'body' => 'Formal meeting: complainant, respondent, witnesses. Record Minutes of Meeting (MoM).',
                'law' => 'Section 11(3)',
                'example' => 'Video hearing with colleague witness — MoM uploaded to case.',
            ],
            'recommendation' => [
                'body' => 'Finding: proved / not proved / partially proved. Written recommendation to management within 10 days of inquiry completion.',
                'law' => 'Section 11(4)',
                'example' => 'Partially proved → warning + mandatory training recommended.',
            ],
            'action' => [
                'body' => 'Management must implement recommendation within 60 days — warning, suspension, termination per service rules.',
                'law' => 'Section 11(4)',
                'example' => 'HR issues warning letter and documents in Management portal.',
            ],
            'appeal' => [
                'body' => 'Either party may appeal to Court/Tribunal within 90 days. Otherwise mark case Closed and archive. Maintain confidentiality — no media disclosure of identities.',
                'law' => 'Section 12 & 16',
                'example' => 'Case closed 30 June; counted in annual report without names.',
            ],
        ];

        return $map[$key] ?? ['body' => 'Complete this step per POSH Rules and document outcomes in the case file.'];
    }

    /** Plain-text blob for client-side guide search (title, summary, all block copy). */
    public static function sectionSearchText(array $section): string
    {
        $parts = [
            $section['title'] ?? '',
            $section['summary'] ?? '',
            $section['id'] ?? '',
        ];

        foreach ($section['blocks'] ?? [] as $block) {
            $parts = array_merge($parts, self::blockSearchParts($block));
        }

        $text = implode(' ', array_filter($parts, fn ($p) => is_string($p) && $p !== ''));

        return mb_strtolower(strip_tags(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** @return list<string> */
    protected static function blockSearchParts(array $block): array
    {
        $parts = [];

        foreach (['text', 'title'] as $key) {
            if (! empty($block[$key]) && is_string($block[$key])) {
                $parts[] = $block[$key];
            }
        }

        foreach ($block['items'] ?? [] as $item) {
            if (is_string($item)) {
                $parts[] = $item;
            } elseif (is_array($item)) {
                foreach (['title', 'text'] as $key) {
                    if (! empty($item[$key])) {
                        $parts[] = (string) $item[$key];
                    }
                }
            }
        }

        foreach ($block['headers'] ?? [] as $h) {
            $parts[] = (string) $h;
        }
        foreach ($block['rows'] ?? [] as $row) {
            foreach ($row as $cell) {
                $parts[] = (string) $cell;
            }
        }

        return $parts;
    }
}
