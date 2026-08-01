# Rollback

The plugin captures an activation snapshot before defaults or migrations mutate shell-owned settings.

Rollback may restore only:

- shell settings;
- shell navigation and theme-visibility configuration;
- captured `show_on_front`, `page_on_front`, and `page_for_posts` values;
- the shell rewrite-flush marker.

Rollback must not delete or modify:

- posts, pages, users, media, or comments;
- messages or notification records;
- appointments, clinic records, or patient data;
- marketplace data;
- companion-plugin tables or options not owned by the shell.

## Admin Rollback

1. Open **Sabri Shell > Repair** on staging.
2. Select **Rollback Shell Settings**.
3. Run System Check.
4. Purge LiteSpeed/Hostinger caches.
5. verify front page, posts page, navigation, authentication, feed, Notifications, doctor, and clinic routes.

## Emergency Disable

Append:

```text
?sabri_shell_safe=1
```

or temporarily define:

```php
define( 'SABRI_SHELL_DISABLE', true );
```

Emergency disable suppresses shell rendering only. It does not constitute rollback or repair.
