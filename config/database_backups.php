<?php

return [
    'disk' => env('DB_BACKUP_DISK', 'local'),
    'directory' => env('DB_BACKUP_DIRECTORY', 'database-backups'),
    'max_files' => (int) env('DB_BACKUP_MAX_FILES', 20),
    'schedule_time' => env('DB_BACKUP_SCHEDULE_TIME', '04:00'),
    'schedule_timezone' => env('DB_BACKUP_SCHEDULE_TIMEZONE', 'Africa/Cairo'),
    'rows_per_insert' => (int) env('DB_BACKUP_ROWS_PER_INSERT', 50),
    'gzip' => (bool) env('DB_BACKUP_GZIP', true),
];
