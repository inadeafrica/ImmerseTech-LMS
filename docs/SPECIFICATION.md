# ImmerseTech LMS — Functional & Technical Specification

Core: Management Training | Add-on Track: Mechanical & Process Engineering

Version 1.0 | Draft for Review | July 2026

## Table of Contents

1. [Document Overview](#1-document-overview)
2. [Institute Context](#2-institute-context)
3. [User Roles](#3-user-roles)
4. [Curriculum Structure](#4-curriculum-structure)
5. [Feature Specifications](#5-feature-specifications)
6. [User Stories](#6-user-stories)
7. [Non-Functional Requirements](#7-non-functional-requirements)
8. [High-Level Technical Architecture](#8-high-level-technical-architecture)
9. [VR Partner Integration](#9-vr-partner-integration)
10. [Glossary](#10-glossary)

---

## 1. Document Overview

This document defines the functional and high-level technical requirements for the ImmerseTech Learning Management System (LMS) — a core platform for management and professional training, built with a modular add-on architecture so that specialised, industry-specific training (starting with Mechanical & Process Engineering, including VR-based practicals) can be layered on per course or subscription tier without being hard-wired into the base system. It is intended for internal product and training-design teams, and for prospective technology or VR partners scoping integration.

### 1.1 Purpose

To specify the features, workflows, and user roles required for a management-training-first LMS — covering live instruction, assignments, group/cohort management, and assessment integrity — while defining how industry-specific add-on modules (competency-based practicals, VR tracking, safety gating) attach to individual courses rather than the whole platform.

### 1.2 Scope

- **In scope (core platform):** course/curriculum management, live-class delivery via Zoom, assignment and lesson submission with plagiarism/AI-content checks, group and cohort management, gradebook, certification, offline access, and reporting.
- **In scope (add-on modules, enabled per course content type or subscription tier):** competency-matrix tracking, VR simulation tracking and partner integration, practical/lab assessor sign-off, and safety & compliance gating — see Section 8.1 for how these attach to the core.
- **Out of scope (this version):** the VR simulation engine itself (treated as an integrated external partner), payroll/HR systems, and public marketing website.

## 2. Institute Context

ImmerseTech is a Lagos-based training institute launching with management and professional development training as its core program focus, with a technical/industry track — starting with Mechanical & Process Engineering — planned as a subsequent or parallel offering. The LMS is built so that the management-training experience is fully served by the core platform, while technical-track requirements (VR practicals, competency sign-off, safety gating) activate only for the courses that need them.

This shapes a few LMS design principles that differ from a single-purpose academic LMS:

- The default course experience (management training) is grade- and rubric-based, using case studies, written assignments, presentations, and role-play/peer assessment — no practical/lab machinery is assumed by default.
- Where a course is tagged as a technical/practical track, the same LMS activates competency-matrix tracking, VR completion tracking, and assessor sign-off for that course only, without affecting the standard management-training workflow.
- Assignment integrity (plagiarism and AI-generated-content checks) matters across both tracks, but especially for management training, where submissions are overwhelmingly written essays and case analyses rather than calculation sheets or drawings.
- Connectivity and power reliability in the Lagos operating environment make offline-capable content delivery a core, not optional, requirement across all course types.

## 3. User Roles

| Role | Description | Key Permissions |
|---|---|---|
| Admin | Platform owner; manages institute-wide configuration | User management, program setup, reporting, billing config |
| Instructor | Designs and delivers courses; runs live sessions | Create lessons/assignments, run Zoom classes, grade, mark competencies |
| Qualified Assessor | External subject-matter expert who signs off practical/applied competencies (e.g. an engineer for technical tracks, a senior manager for leadership assessments) | View submissions, approve competency sign-offs, cannot edit curriculum |
| Group Lead / Mentor | Senior trainee or junior instructor supporting a cohort | Moderate group discussion, view group progress, flag at-risk trainees |
| Learner / Trainee | Enrolled student | Consume content, submit work, join live/VR sessions, view own progress |

## 4. Curriculum Structure

Content is organised hierarchically, the same structure serving both management-training and technical-track courses:

- **Program** (e.g. "Management Development Programme" or, on the technical track, "Mechanical Engineering Technician Track")
  - **Course** (e.g. "Leading High-Performance Teams" or "Process Instrumentation & Control")
  - **Module** (e.g. "Giving Effective Feedback" or "Pressure & Flow Measurement")
  - **Lesson / Unit** — video, reading, case study, or live class
  - **Practical / Applied Exercise** — role-play, group project, or (technical track only) physical lab task/VR simulation, competency-tagged

Every course carries a content-type tag (e.g. "standard" or "technical/practical") that determines which add-on modules are active for it — see Section 8.1. A lesson or practical can also be tagged with one or more competencies from a program's competency map, feeding the gradebook and certification logic in Sections 5.5 and 5.6.

Core content types (available to every course) include video, PDF/slide decks, case studies, presentations, and quizzes. Add-on content types — enabled only for courses tagged as technical/practical — include CAD and technical drawing files, P&ID diagrams, calculation worksheets, and VR simulation modules.

## 5. Feature Specifications

### 5.1 Zoom Integration for Live Sessions

Every scheduled live class, tutorial, or practical debrief is powered by direct Zoom API integration — no manual link creation or sharing.

**Workflow**

- Instructor schedules a session from the course calendar; the LMS calls the Zoom API to create the meeting and auto-populates the join link on the lesson page.
- Trainees join via a single "Join Class" button; no separate Zoom credentials required (SSO passthrough).
- Attendance is marked automatically from Zoom's participant join/leave timestamps against a configurable presence threshold (e.g. present if in-session ≥70% of scheduled duration; otherwise flagged late/partial).
- Instructors can manually override auto-marked attendance with a reason code (e.g. connectivity issue).
- Cloud recordings sync back to the lesson automatically within a set window post-session, for trainees who missed or want to review.
- Breakout rooms are supported for small-group lab discussion and are logged as sub-sessions under the parent class for attendance purposes.

### 5.2 Assignments & Lesson Submissions

**Submission types**

- File upload — essays, case analyses, business plans, presentation decks (core); lab reports, calculation sheets, CAD/drawing files — technical track add-on
- Text entry — short answer, reflection, or procedure write-up
- Link submission — external repository or shared drive reference
- VR completion proof — technical track add-on; auto-logged when a linked VR simulation module is completed (see 5.4)

**Workflow**

- Instructor creates an assignment with due date, submission type(s), rubric or point scale, and optional competency tags.
- Late-submission policy is configurable per assignment: block, allow with penalty, or allow with a flag for review.
- Instructors grade with inline annotation (markup on text, PDFs, and drawings supported) and rubric scoring; feedback is visible to the trainee immediately on release.
- Resubmission is supported with full version history retained for audit and assessor review.

**Assignment integrity: plagiarism & AI-content checks**

Applies primarily to text-based submissions (essays, case analyses, reflections) and runs automatically on submission, before an instructor sees the work:

- Plagiarism/similarity check — integration with a similarity-detection service (e.g. Turnitin, Copyleaks); a similarity score and matched sources are shown to the instructor alongside the submission.
- AI-generated-content detection — a likelihood score flags submissions that may be substantially AI-written; this is advisory, not a hard block, since detection tools carry a false-positive risk.
- Configurable thresholds per assignment: auto-block above a set similarity/AI-likelihood threshold, or simply flag for instructor review at a lower threshold.
- For borderline cases, instructors can require a short oral defense or in-class follow-up rather than relying on the automated score alone.

### 5.3 Group & Cohort Management

- Cohorts are created per program intake, with defined enrollment windows and a shared schedule.
- Sub-groups (project/syndicate teams) can be formed manually or auto-balanced (e.g. by role, seniority, or a diagnostic assessment) for group assignments, case-study work, and — on the technical track — VR breakout exercises.
- Group dashboards show aggregate attendance, submission status, and at-risk indicators (missed deadlines, low participation) to instructors and group leads.
- Peer review workflows allow structured feedback within a team, configurable as anonymous or attributed.

### 5.4 VR Simulation Tracking (Add-on Module)

Enabled only for courses tagged as technical/practical. Where active, VR sessions are tracked as first-class LMS records rather than an external link the LMS can't see into.

- Each VR module reports completion status, time-on-task, and (where the simulation supports it) a performance score back to the LMS via API/webhook — see Section 9 for partner integration details.
- VR completions can satisfy specific competency requirements in the same way a physical lab sign-off does.
- Instructors can review a session replay or performance log where the VR platform provides one, to support assessment.

### 5.5 Gradebook & Competency Tracking

Every course uses traditional scoring by default. Courses tagged as competency-based (typically the technical track, but available to any course — e.g. a leadership-simulation module) additionally track a competency matrix:

- Traditional scores — points/percentages per assignment, quiz, and course. Applies to all courses.
- Competency matrix (add-on, per course) — a per-trainee checklist of required applied skills (e.g. "correctly size a centrifugal pump" on the technical track, or "conducts a structured performance review" on a management track), each marked not-started / in-progress / demonstrated / assessor-approved, sourced from tagged lessons, practicals, and VR modules.
- Where the competency matrix is active for a program, overall completion requires both a minimum score threshold and full matrix sign-off, not score alone; otherwise, score alone determines completion.

### 5.6 Practical & Applied Assessment Sign-off (Add-on Module)

Enabled for courses using the competency matrix. Practical or applied competencies — physical, VR, or role-play-based — require sign-off from a qualified Instructor or Qualified Assessor before being marked demonstrated.

- Assessor reviews submitted evidence (video of a physical/role-play task, VR session log, or in-person checklist) and approves or requests a re-attempt.
- All sign-offs are timestamped and attributed, forming an auditable competency record exportable for accreditation or employer verification.

### 5.7 Safety & Compliance Gating (Add-on Module)

Relevant primarily to the technical track, where safety induction must gate access to hazardous practicals. Not active for standard management-training courses.

- Where enabled for a course, trainees must complete a safety induction module (and any equipment-specific safety briefings) before their account is unlocked for the corresponding physical or VR lab.
- Safety certifications have expiry/renewal tracking, with automatic re-lock of related labs if a certification lapses.

### 5.8 Offline Mode

- Core lesson content (video, PDFs, drawings) is cacheable for offline viewing on the trainee's device.
- Assignment drafts can be composed offline and queued for submission on reconnect.
- Sync conflicts (e.g. edits made on two devices) are resolved with a last-write-wins default and a manual merge option for text submissions.

### 5.9 Notifications & Announcements

**System notifications**

- Email, SMS, and in-app notifications for upcoming live sessions, assignment deadlines, grades posted, and safety-certification expiry.
- Configurable per-user channel preferences, with SMS reserved for time-critical alerts given variable email reliability.

**Course announcements**

- Instructors and admins can broadcast a message to an entire course, cohort, or sub-group — e.g. a schedule change, a new resource, or a reminder — separate from the automated system alerts above.
- Announcements post to an in-app feed on the course page and optionally push via email/SMS depending on urgency, with delivery/read receipts visible to the sender.
- Pinned announcements remain at the top of the course feed until unpinned or expired.

### 5.10 Certification & Digital Credentials

- On program completion (score + competency matrix satisfied), the LMS auto-generates a verifiable certificate with a QR/ID code linking to a public verification page.
- Optional digital badges per competency for partial/in-progress recognition (e.g. for employer-sponsored trainees needing interim proof of progress).

### 5.11 Analytics & Reporting

- Trainee-level dashboards: attendance, submission status, competency progress, VR completion.
- Cohort-level dashboards: completion rates, average time-to-competency, at-risk trainee flags.
- Exportable reports for accreditation bodies and corporate sponsors of trainees.

### 5.12 Payments & Enrollment

- Enrollment workflow with cohort capacity limits and waitlisting.
- Payment gateway integration (e.g. Paystack/Flutterwave) for NGN-denominated fees, with support for corporate-sponsored batch enrollment and invoicing.

### 5.13 Data Governance & Compliance (NDPR)

- All trainee personal and performance data handled under Nigeria Data Protection Regulation (NDPR) principles: documented consent at enrollment, data-minimization, and role-based access control.
- Trainees can request export or deletion of their personal data; deletion requests are logged and honoured subject to any accreditation record-keeping requirements.
- Full audit trail on grading, attendance, and competency sign-off changes.

### 5.14 Discussion Forums & Social Learning

- Course-level discussion boards for open Q&A, separate from group/cohort chat (5.3) — persistent, threaded, and searchable across a course's lifetime.
- Instructor-moderated: pinning, locking, or flagging threads; optional requirement to post before viewing peers' responses (encourages original thinking on case discussions).
- Particularly important for management training, where peer debate on case studies is a core part of the pedagogy, not a supplementary feature.

### 5.15 Course Authoring & Content Library

- Instructor-facing course builder (WYSIWYG) for assembling modules, lessons, and practicals without needing developer support.
- Shared content/template library — reusable lesson blocks, rubrics, and case-study templates across courses and cohorts, reducing rebuild effort each intake.
- Version control on curriculum content: edits are tracked, previous versions retrievable, and changes to a live course are distinguished from changes to a draft.

### 5.16 Assessment Engine (Quizzes & Exams)

- Question types: multiple choice, true/false, matching, short answer, and file-based response.
- Auto-grading for objective question types; manual grading queue for short-answer/essay questions.
- Question banks with randomization and shuffling per attempt, to reduce copying within a cohort sitting a quiz simultaneously.
- Configurable attempt limits, time limits, and pass thresholds per quiz.

### 5.17 Prerequisites & Course Sequencing

- Courses and modules can require completion (and, where applicable, competency sign-off) of a prerequisite before unlocking.
- Program-level sequencing view lets admins define a required path through a curriculum, with optional electives outside the required sequence.

### 5.18 Bulk Enrollment & Roster Import

- CSV-based bulk enrollment for corporate-sponsored cohorts, with validation and error reporting before commit.
- Optional sync with a sponsoring organization's HR system for automated roster updates (joiners/leavers) on longer-running corporate contracts.
- Roster import also feeds VR partner integrations (Section 9.2), keeping trainee rosters consistent across the core LMS and connected add-on modules.

### 5.19 Localization & Multi-Language Support

Given ImmerseTech's reach across West and Central Africa, the platform interface and course content support multiple languages rather than English only.

- Interface localization: platform UI translatable into French (for Francophone West/Central African markets — e.g. Senegal, Côte d'Ivoire, Cameroon), with a framework that allows further languages (e.g. Portuguese for Lusophone markets) to be added without re-architecting.
- Content localization: course content (video subtitles/captions, PDFs, quizzes) can be authored or uploaded per language; a course can offer multiple language versions of the same curriculum rather than requiring a full course duplicate.
- Trainees select a language preference at enrollment; instructor/admin views can remain in a separate operating language from the trainee-facing content.
- Notifications and certificates respect the trainee's selected language where a translation exists, falling back to English otherwise.

### 5.20 Course Catalog & Discovery

- Public/browsable course catalog for prospective trainees or sponsoring organizations to review programs before enrollment, distinct from the payment/enrollment workflow itself (5.12).
- Filterable by track (management vs. technical), language, delivery mode, and start date.

### 5.21 Support & Help Desk

- In-app help/support ticketing for trainees and instructors, with category routing (technical issue, billing, academic query).
- Optional integration with an external help desk tool (e.g. Zendesk, Freshdesk) rather than building ticketing from scratch.

## 6. User Stories

| Role | User Story |
|---|---|
| Trainee | As a trainee, I want to join my scheduled Zoom class with one tap so that I don't lose time on manual links, especially on a mobile connection. |
| Trainee | As a trainee, I want to complete lesson videos offline on the bus so that my data costs and connectivity don't block my progress. |
| Instructor | As an instructor, I want attendance marked automatically from Zoom so that I don't spend class time on a roll call. |
| Instructor | As an instructor (management track), I want a similarity and AI-content score on every essay submission so that I can grade with confidence in originality before reading it in depth. |
| Instructor | As an instructor (technical track), I want to annotate a trainee's uploaded technical drawing directly so that feedback is precise and in context. |
| Qualified Assessor | As an assessor, I want to review VR session logs and physical task videos in one place so that I can sign off competencies without site visits for every case. |
| Group Lead | As a group lead, I want a dashboard of my project team's submission status so that I can flag at-risk trainees before deadlines pass. |
| Admin | As an admin, I want to enable the technical-track add-on modules for a specific course only, without affecting how management-training courses run. |
| Admin | As an admin, I want an exportable competency and attendance record per trainee so that I can respond to accreditation or employer verification requests. |
| Trainee | As a Francophone trainee, I want to take my course in French so that language isn't a barrier to a program originally built in English. |
| Instructor | As an instructor, I want to broadcast an announcement to my whole cohort so that everyone sees a schedule change without me emailing each trainee individually. |
| Admin | As an admin, I want to bulk-import a corporate sponsor's employee roster from a CSV so that I don't manually create accounts one by one. |
| Instructor | As an instructor, I want a randomized question bank for quizzes so that trainees sitting the same quiz simultaneously can't easily copy each other. |

## 7. Non-Functional Requirements

- **Availability:** platform designed for intermittent power/connectivity environments — offline-first for content, sync-tolerant for submissions. Target uptime of 99.5% for core services, excluding scheduled maintenance windows.
- **Scalability:** support multiple concurrent cohorts and simultaneous live Zoom sessions without degradation; target response times under 2 seconds for standard page/API operations at expected concurrent-user load.
- **Security:** role-based access control, encrypted data at rest and in transit, NDPR-aligned data handling, multi-factor authentication for admin/instructor accounts, configurable password policy, and session timeout on inactivity. Target alignment with ISO 27001 or SOC 2 control practices as the platform matures, since corporate sponsors will likely require this during procurement.
- **Auditability:** immutable audit log for grades, attendance, competency sign-offs, and curriculum content changes.
- **Accessibility:** mobile-responsive interface as the primary access assumption; target WCAG 2.1 AA conformance for core trainee-facing workflows (content playback, submissions, assessments).
- **Data resilience:** scheduled automated backups with a defined retention period, and a documented disaster-recovery process with a target recovery time objective (RTO) and recovery point objective (RPO) — to be finalized with the chosen hosting provider.

## 8. High-Level Technical Architecture

### 8.1 Core-Plus-Add-On Model

The platform is built as a single core LMS with optional modules that activate per course, based on that course's content-type tag (Section 4), rather than separate systems for management vs. technical training.

- **Core (always on):** course/curriculum management, Zoom integration, assignments with plagiarism/AI-content checks, group & cohort management, standard gradebook, certification, notifications, analytics, payments, NDPR governance.
- **Add-on modules (enabled per course or subscription tier):** competency-matrix tracking (5.5), VR simulation tracking (5.4), practical/applied assessor sign-off (5.6), safety & compliance gating (5.7).
- A course's content-type tag determines which add-ons are active for it; a management-training course runs on core-only, a technical-track course activates the relevant add-ons — no separate deployment or system required.
- This mirrors the pattern used by established AI-capable LMS platforms, where a shared core (enrollment, delivery, analytics) is paired with add-on modules gated by plan tier or module license, rather than a single monolithic feature set.

### 8.2 Core Components

- Web/mobile client (responsive front end) — primary trainee and instructor interface.
- Application/API layer — course, assignment, gradebook, and competency-matrix logic, exposed via a documented public/partner API for future third-party integrations (e.g. corporate sponsor HR systems, other institutes) beyond the pre-defined integrations below.
- Integration layer — Zoom API (meetings, attendance, recordings), VR platform webhook receiver, payment gateway, SMS/email provider, translation/localization service.
- Data layer — relational store for users, courses, submissions, competencies, and audit logs; object storage for content and submitted files.
- Offline sync service — manages local caching on client and reconciles queued submissions on reconnect.

### 8.3 Key Integrations

| Integration | Purpose | Direction |
|---|---|---|
| Zoom API | Meeting creation, attendance data, recording links | Bi-directional |
| VR Platform | Session completion, time-on-task, performance data | Inbound (webhook) |
| Payment Gateway | Fee collection, sponsor invoicing | Bi-directional |
| SMS/Email Provider | Notifications and announcements | Outbound |
| Public/Partner API | Third-party integrations (HR systems, sponsor platforms, future partners) | Bi-directional |
| Plagiarism/AI-Detection Service | Similarity and AI-content scoring on text submissions | Outbound request / inbound score |

### 8.4 Future Consideration: Multi-Tenancy

Not required for initial launch, but worth flagging now since retrofitting later is costly. If ImmerseTech intends to license the core platform to other training institutes or run fully separate corporate-sponsor instances, the data layer and admin model should be designed with tenant isolation in mind from the outset (e.g. tenant-scoped IDs throughout the schema), even if multi-tenant admin tooling itself is deferred to a later phase.

## 9. VR Partner Integration

ImmerseTech will not build a VR simulation engine in-house. VR practicals are delivered by a specialist third-party partner and integrated into the LMS as a content/data source — consistent with the add-on architecture in Section 8. This section defines how partners are evaluated, integrated, and rolled out.

### 9.1 Partner Selection Criteria

| Criterion | What to Check |
|---|---|
| Data export standard | Must export via xAPI or SCORM so completion, time-on-task, and score data flow directly into the LMS gradebook and competency matrix, rather than sitting in a separate system. |
| Hardware model | Standalone headsets (e.g. Meta Quest, Pico) are strongly preferred over PC-tethered rigs (HTC Vive, Oculus Rift) — no dedicated gaming PC per headset, easier to ship, deploy, and scale across sites. |
| Offline capability | Partner platform should support offline or low-connectivity delivery, consistent with the LMS's own offline-first requirement (Section 5.8). |
| Content fit | Off-the-shelf scenario library vs. custom-built scenarios matched to ImmerseTech's specific curriculum and equipment set. |
| Support model | On-site pilot support, cadence for scenario/content updates as procedures or equipment change, SLA for technical issues. |
| Commercial model | Per-seat, per-headset, or per-scenario licensing; hardware procurement and maintenance costs are typically separate from the software license. |

### 9.2 Integration Flow

- Trainee launches the assigned VR module from the LMS lesson/practical page (or directly on the headset, pre-assigned via roster sync).
- Partner's VR application runs the simulation locally on the headset or via their cloud service.
- On completion, the partner platform sends a webhook or SCORM/xAPI package back to the LMS containing: completion status, time-on-task, and performance score (where supported).
- The LMS's VR tracking module (Section 5.4) ingests this and updates the trainee's competency matrix entry for the linked skill.
- Where the partner platform provides a session replay or detailed performance log, instructors/assessors can access it from the LMS submission view for competency sign-off (Section 5.6).

### 9.3 Pilot Rollout Path

- **Phase 1 — Pilot:** 1–2 scenarios, one cohort, a small fixed set of headsets. Most vendors offer a structured 90-day pilot rather than requiring a full contract upfront.
- **Phase 2 — Validate:** confirm data lands cleanly in the LMS gradebook/competency matrix, and that trainees can complete modules without heavy support overhead.
- **Phase 3 — Expand:** scale scenario library and headset count only after the integration is proven; add further competencies/programs incrementally.

### 9.4 Commercial Considerations

- Budget separately for software licensing (per-seat/per-headset/per-scenario) and hardware procurement, shipping, and maintenance.
- Enterprise-tier partners may offer custom pricing tied to workforce/trainee volume — confirm this scales sensibly with ImmerseTech's cohort sizes rather than assuming a flat enterprise rate.
- Clarify content-ownership terms upfront: whether custom-built scenarios remain ImmerseTech's IP or are licensed on an ongoing basis from the partner.

## 10. Glossary

| Term | Definition |
|---|---|
| Add-on Module | An optional LMS capability (e.g. VR tracking, competency matrix, safety gating) that activates per course based on content-type tag or subscription tier, rather than running for every course. |
| Course Content Type | A tag on a course (e.g. "standard" or "technical/practical") that determines which add-on modules are active for that course. |
| Competency Matrix | Per-trainee checklist mapping required practical or applied skills to their demonstrated/approved status. |
| Cohort | A group of trainees enrolled together in the same program intake. |
| NDPR | Nigeria Data Protection Regulation — governs personal data handling in Nigeria. |
| Practical | A hands-on lab task, physical or VR-based, tied to specific competencies. |
| VR (Virtual Reality) | Fully immersive simulation via headset that replaces the trainee's view of the real world; used for practicing hazardous or hard-to-replicate scenarios. |
| AR (Augmented Reality) | Digital overlay on the real world via glasses, tablet, or phone; used for guided, real-time task support rather than pre-training simulation. |
| xAPI / SCORM | Industry-standard e-learning data formats used by VR/content partners to report completion, time-on-task, and performance data back to the LMS. |
| WCAG | Web Content Accessibility Guidelines — the standard used to assess whether a digital interface is usable by people with disabilities. |
| RTO / RPO | Recovery Time Objective / Recovery Point Objective — targets defining how quickly service is restored and how much data loss is acceptable after an outage. |
| Multi-Tenancy | An architecture where a single platform instance serves multiple separate organizations (tenants) with isolated data, as opposed to one deployment per organization. |
