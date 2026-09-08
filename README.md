# ImmerseTech LMS

Learning Management System for ImmerseTech — a Lagos-based training institute. Core: management & professional training. Add-on track: Mechanical & Process Engineering, including VR-based practicals.

See [`docs/SPECIFICATION.md`](docs/SPECIFICATION.md) for the full functional & technical specification, and [`docs/decisions/0001-platform-foundation.md`](docs/decisions/0001-platform-foundation.md) for why this is built on Moodle rather than a greenfield LMS.

## Architecture

Stock **Moodle** as the core platform, extended with a small set of ImmerseTech-specific plugins for the parts of the spec Moodle doesn't cover natively — see [`plugins/README.md`](plugins/README.md) for what those are and why each is built as the Moodle plugin type it is.

Moodle core lives in [`moodle/`](moodle) as a **git submodule** pinned to the `MOODLE_502_STABLE` branch, rather than baked into a pre-built image — the real source tree is what runs, so the exact Moodle version is explicit and pluggable via the standard Moodle plugin-directory layout (`moodle/local/vrtracking`, etc.), the same one Moodle core itself uses.

## Local development

Requires Docker, Docker Compose, and Git.

```bash
git clone --recurse-submodules <this-repo-url>
# or, if already cloned without --recurse-submodules:
git submodule update --init

cp .env.example .env    # adjust values as needed
docker compose up -d
```

Wait for the `db` service to report healthy (`docker compose ps`), then run the one-time database install (only needed the first time, or after a `docker compose down -v`):

```bash
docker compose exec webserver php admin/cli/install_database.php \
  --agree-license \
  --fullname="ImmerseTech LMS" \
  --shortname="ImmerseTech" \
  --adminuser=admin \
  --adminpass=changeme \
  --adminemail=admin@immersetech.example
```

Use the same values as `MOODLE_ADMIN_USER` / `MOODLE_ADMIN_PASSWORD` / `MOODLE_ADMIN_EMAIL` in your `.env`. Moodle will then be reachable at `http://localhost:8080` (or whatever `MOODLE_HTTP_PORT` you set).

If the install fails with a "dataroot is not writable" error, the `moodledata` volume needs its permissions fixed once:

```bash
docker compose exec -u root webserver chown -R www-data:www-data /var/www/moodledata
```

To stop:

```bash
docker compose down        # keep data
docker compose down -v     # also wipe the database/site data volumes
```

## Status

Early scaffolding stage — base Moodle deployment is in place; custom add-on plugins (VR tracking, safety gating, payment gateways, AI-content detection) are being built incrementally. See `plugins/README.md` for the current status of each.
