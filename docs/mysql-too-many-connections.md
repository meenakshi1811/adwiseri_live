# Fixing `SQLSTATE[HY000] [1040] Too many connections` in Adwiseri

## What this error means

MySQL refused a new connection because the server had already reached its connection limit (`max_connections` or user-level limits like `MAX_USER_CONNECTIONS`).

In your stack trace, Laravel failed while loading the authenticated user record:

- query: `select * from users where id = 91 limit 1`
- URL: `/userprofile`

That query is small and normal. The real problem is **connection saturation**, not this specific SQL.

## Why this happens

Most common causes in Laravel/PHP deployments:

1. **Traffic spikes**
   - Many PHP-FPM workers/processes hit MySQL simultaneously.
2. **Long-running queries / locks**
   - Existing connections stay busy too long, so new requests queue and fail.
3. **Leaking or stuck workers**
   - Queue workers, cron jobs, or custom scripts keep idle/sleeping DB sessions open.
4. **Low MySQL limits for current load**
   - `max_connections` or per-user connection caps are too small.
5. **Slow external dependencies inside request lifecycle**
   - Requests stay open longer, indirectly holding DB resources longer.

## Immediate recovery steps

Run these on the MySQL server:

```sql
SHOW VARIABLES LIKE 'max_connections';
SHOW VARIABLES LIKE 'max_user_connections';
SHOW STATUS LIKE 'Threads_connected';
SHOW FULL PROCESSLIST;
```

Then:

1. Kill clearly stuck/sleeping sessions if needed.
2. Restart overloaded PHP-FPM pools or queue workers.
3. Temporarily raise MySQL connection limits (short-term mitigation).

## Permanent fixes checklist

- Tune PHP-FPM worker counts so total concurrent PHP workers are realistic for DB capacity.
- Add slow query logging and optimize slow endpoints.
- Put frequently-read user/profile data behind cache where safe.
- Ensure queue workers are supervised and recycled (`--max-jobs`, `--max-time`).
- Add monitoring/alerts for:
  - `Threads_connected`
  - `Threads_running`
  - query latency
  - PHP-FPM active processes

## Laravel-specific notes

- This project uses standard Laravel DB config via `config/database.php` with MySQL over PDO.
- If `.env` changes are made for DB capacity or host, clear cached config:

```bash
php artisan config:clear
php artisan cache:clear
```

## Why `/userprofile` is failing specifically

`/userprofile` likely requires authentication, which triggers Laravel to fetch the current user from `users` table. When MySQL cannot accept any new connection, even this trivial query fails first and surfaces to users on that page.
