# ImmerseTech LMS

Learning Management System for ImmerseTech — a Lagos-based training institute. Core: management & professional training. Add-on track: Mechanical & Process Engineering, including VR-based practicals.

See [`docs/SPECIFICATION.md`](docs/SPECIFICATION.md) for the full functional & technical specification, and [`docs/decisions/0001-platform-foundation.md`](docs/decisions/0001-platform-foundation.md) for why this is built on Moodle rather than a greenfield LMS.

## Architecture

Stock **Moodle** (self-hosted) as the core platform, extended with a small set of ImmerseTech-specific plugins for the parts of the spec Moodle doesn't cover natively — see [`plugins/README.md`](plugins/README.md) for what those are and why each is built as the Moodle plugin type it is.

## Local development

Requires Docker and Docker Compose.

```bash
cp .env.example .env    # adjust values as needed
docker compose up -d
```

Moodle will be reachable at `http://localhost:8080` (or whatever `MOODLE_HTTP_PORT` you set in `.env`) once the first-run install finishes — this can take a few minutes on first boot while the database initializes and Moodle installs itself. Log in with the `MOODLE_ADMIN_USER` / `MOODLE_ADMIN_PASSWORD` from your `.env`.

To stop:

```bash
docker compose down        # keep data
docker compose down -v     # also wipe the database/site data volumes
```

## Status

Early scaffolding stage — base Moodle deployment is in place; custom add-on plugins (VR tracking, safety gating, payment gateways, AI-content detection) are being built incrementally. See `plugins/README.md` for the current status of each.
