# Deployment

## Runtime

- PHP 8.4 with OPcache
- MySQL 8 or PostgreSQL 16
- Redis for cache, sessions, and queues
- Object storage for documents and report exports
- Dedicated queue workers for default, accounting, inventory, pos, and integrations queues

## Release Flow

1. Build assets.
2. Install Composer dependencies with optimized autoloading.
3. Run tests and static analysis.
4. Put the app in maintenance mode.
5. Run migrations.
6. Restart PHP workers and queue workers.
7. Warm config, route, and view caches.
8. Bring the app out of maintenance mode.

## Operational Controls

- Daily database backups with restore drills.
- Audit log retention by company policy.
- Queue retry monitoring and failed job alerts.
- Integration webhook replay tools.
- Separate credentials per integration channel.
- Rate limits for public API and webhook endpoints.
