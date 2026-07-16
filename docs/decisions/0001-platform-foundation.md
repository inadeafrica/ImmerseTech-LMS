# ADR 0001: Build on Moodle Instead of a Greenfield LMS

- **Status:** Proposed
- **Date:** 2026-07-16
- **Related:** [Functional & Technical Specification](../SPECIFICATION.md)

## Context

The [ImmerseTech LMS Specification](../SPECIFICATION.md) defines a large surface area — 21 feature areas spanning live delivery, assignments with integrity checks, cohort management, a competency matrix, VR partner integration, certification, payments, and NDPR compliance (Sections 5, 8, 9). Before implementation starts, we need to decide whether to build the platform from scratch, adopt an existing open-source LMS as the core and extend it, or integrate a set of SaaS products.

## Options Considered

| Option | Fit for this spec | Notes |
|---|---|---|
| **Moodle** (self-hosted, open-source) | Best overall fit | Native **competency frameworks + learning plans** (maps directly to Section 5.5/5.6), gradebook, cohorts/groups, forums, quiz engine with question banks, course authoring, Open Badges/certificates, multi-language, RBAC, and a mobile app with offline content caching. Official `mod_zoom` plugin handles meeting creation and attendance sync from Zoom reports. Backed by a 2,000+ plugin ecosystem and 20 years of maturity. |
| **Open edX** | Good for MOOC-scale delivery | Strong for large concurrent cohorts and xBlock-based interactive content, but competency-matrix and practical-sign-off workflows aren't native — more custom Python/XBlock work than Moodle's plugin model requires. |
| **ILIAS** | Notable alternative | Has an arguably more mature *competency-based learning* model out of the box than Moodle, but a much smaller plugin ecosystem and community — more custom integration work for Zoom, payments, etc. |
| **Canvas** (open-source core) | Weaker fit | Strong LTI/Caliper analytics, but xAPI needs external integration, and self-hosting the open-source edition is less battle-tested at this scale than Moodle. |
| **Greenfield build** | Rejected | Would require re-implementing solved problems (gradebook, cohorts, quiz engine, forums, course authoring, Zoom/attendance sync, badges) that mature open-source platforms already provide, for no differentiation benefit. |
| **SaaS integration** (e.g. commercial LMS API + bolt-on VR layer) | Rejected for core | Faster initial setup, but risks vendor lock-in on exactly the parts of the spec that are most custom and differentiated (competency matrix, safety gating, VR sign-off — Sections 5.4–5.7). |

## Decision

Self-host **Moodle** as the core platform and build ImmerseTech-specific plugins for the parts of the spec that have no off-the-shelf equivalent. This covers roughly 70–80% of the spec natively and concentrates custom engineering on the sections that make this platform differentiated, consistent with the core-plus-add-on model in [Section 8.1](../SPECIFICATION.md#81-core-plus-add-on-model).

### Covered natively by Moodle

- Course/curriculum management, gradebook, cohorts & groups (5.3, 5.5)
- Zoom live sessions and attendance sync (5.1) — official `mod_zoom` plugin
- Plagiarism checking (5.2) — mature Turnitin/similar integrations
- Discussion forums (5.14), course authoring (5.15), assessment engine (5.16)
- Competency frameworks & learning plans (5.5, 5.6) — Moodle's built-in Competencies feature
- Certificates & Open Badges (5.10), multi-language (5.19), offline mobile caching (5.8)
- RBAC and audit logging foundations (7)

### Requires custom development

| Gap | Spec section | Why |
|---|---|---|
| VR partner webhook/xAPI ingestion tied to the competency matrix | 5.4, 9.2 | The specific "VR completion → competency entry" logic is ImmerseTech-specific; Moodle supports xAPI (via `logstore_xapi`/H5P) but not this workflow. |
| Safety & compliance gating with expiry/re-lock | 5.7 | No off-the-shelf Moodle plugin implements induction-gated lab access with automatic re-lock on certification lapse. |
| Paystack/Flutterwave payment gateway | 5.12 | Community demand exists but no mature, maintained Moodle plugin for either; Moodle's payment API is pluggable, so this is a moderate lift. |
| AI-generated-content detection | 5.2 | Distinct from plagiarism/Turnitin (which Moodle already integrates well); likely a custom integration with a third-party detection API. |

## Consequences

- Faster time-to-market and lower cost than a greenfield build, at the cost of working within Moodle's plugin architecture and data model rather than a clean-slate design.
- Custom plugins should be scoped and versioned independently so they can be upstreamed or swapped without forking Moodle core.
- Self-hosting gives us control over data residency for NDPR compliance (Section 5.13).

## Alternatives Revisited

If the technical/VR track grows to dominate the roadmap, or if ImmerseTech pursues multi-tenant licensing to other institutes (Section 8.4), this decision should be revisited — ILIAS's native competency model or a greenfield build may become more attractive at that scale.
