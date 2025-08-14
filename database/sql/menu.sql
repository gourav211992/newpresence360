INSERT INTO `menus` (`name`, `uri`, `route_name`, `icon`, `parent_id`, `sequence`, `status`, `app_type`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('Master Management', '/admin/master-management', 'admin.master-management', 'iconly-boldCategory', NULL, 2, 'active', 'web', 1, 1, NULL, NULL, '2023-09-13 00:34:05', NULL),
('Menus', '/admin/menus', 'admin.menus.index', 'iconly-boldCategory', 1, 1, 'active', 'web', 1, 1, NULL, NULL, '2023-08-28 01:52:48', NULL),
('Roles', '/admin/roles', 'admin.roles.index', 'iconly-boldCategory', 1, 2, 'active', 'web', 1, 1, NULL, NULL, '2023-08-28 01:53:03', NULL),
('Roles & Permissions', '/admin/role-permissions', 'admin.role-permissions.index', 'iconly-boldCategory', 1, 3, 'active', 'web', 1, 1, NULL, NULL, '2023-08-28 01:53:17', NULL),
('Dashboard', 'admin/dashboard', 'admin.dashboard', 'iconly-boldCategory', NULL, 1, 'active', 'web', 1, 1, NULL, '2023-09-13 00:32:55', '2023-09-13 00:33:46', NULL),
('Setting', 'admin/settings', 'settings.index', 'iconly-boldCategory', NULL, 11, 'active', 'web', 1, 1, NULL, '2023-10-17 06:15:01', '2023-10-17 06:42:21', NULL),
('Blog', '/admin/blogs', 'admin.blogs.index', 'iconly-boldCategory', 1, 4, 'active', 'web', 1, 1, NULL, '2023-08-28 18:36:00', '2023-08-27 20:00:15', NULL),
('Banner', '/admin/banners', 'admin.banners.index', 'iconly-boldCategory', 1, 5, 'active', 'web', 1, 1, NULL, '2023-08-28 18:36:00', '2023-08-27 20:00:50', NULL),
('Category', '/admin/categories', 'admin.categories.index', 'iconly-boldCategory', 1, 6, 'active', 'web', 1, 1, NULL, '2023-08-28 18:58:55', '2023-08-28 18:59:09', NULL),
('FAQ', '/admin/faqs', 'admin.faqs.index', 'iconly-boldActivity', 1, 7, 'active', 'web', 1, 1, NULL, '2023-09-07 18:21:27', '2023-09-07 18:21:27', NULL),
('Notifications', 'notifications', 'notifications', '#', NULL, 2, 'active', 'app', 1, 1, NULL, '2023-10-09 10:57:52', '2023-10-09 11:35:53', NULL),
('Announcement', 'admin/announcements', 'admin.announcements.index', '#', NULL, 12, 'active', 'web', 1, 1, NULL, '2023-10-19 10:32:09', '2023-10-19 10:32:09', NULL);
