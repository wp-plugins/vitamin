<?php

function_exists ('is_admin') or exit;
is_admin() or exit;

defined('SP_PLUGIN_ROOT') or exit;
defined('SP_ABSPATH') or exit;

class spDataMenu{
	function __construct(){
		$this->menuData = array(
			'sp_dashboard' => array(
				'title' => 'Vitamin',
				'ch' => array(
					'sp_dashboard' => array(
						'title' => 'Dashboard',
						'icon' => 'icon-index',
						'ch' => array(
							'sp_dashboard' => array(
								'title' => 'Dashboard',
								'class' => 'DashboardAdmin',
								'method' => 'printAdminPage',
							),
							'stats' => array(
								'title' => 'Statistics',
								'class' => 'StatsAdmin',
								'method' => 'printAdminPage',
							),
							'install' => array(
								'title' => 'Installation status',
								'class' => 'InstallAdmin',
								'method' => 'printAdminPage',
							),
							'phpinfo' => array(
								'title' => 'PHPInfo',
								'class' => 'PHPInfoAdmin',
								'method' => 'printAdminPage',
							),
						),
					), // dashboard

					'sp_seo' => array(
						'title' => 'SEO',
						'icon' => 'icon-link-manager',
						'ch' => array(
							'files' => array(
								'title' => 'Main SEO Files',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', '.htaccess' ),
									array('file', 'sp_file_edit_htaccess' ),
									array('title', 'Sitemaps and robots.txt' ),
									array('option', 'sp_sitemaps_enabled' ),
									array('file', 'sp_file_robots_txt' ),
									array('file', 'sp_file_root_sitemap_xml' ),
									array('title', 'Feeds to index sitemap.xml and to robots.txt' ),
									array('option', 'sp_add_rss_to_robots_sitemap' ),
									array('option', 'sp_add_atom_to_robots_sitemap' ),
								),
							),
							'sitemaps' => array(
								'title' => 'Sitemaps',
								'class' => 'SitemapsAdmin',
								'method' => 'printAdminPage',
							),
							'redir' => array(
								'title' => 'Redirections',
								'class' => 'RedirsAdmin',
								'method' => 'printAdminPage',
							),
							'404' => array(
								'title' => '404 Pages',
								'class' => '404sAdmin',
								'method' => 'printAdminPage',
							),
						),
					), // seo

					'sp_speed' => array(
						'title' => 'Speed',
						'icon' => 'icon-plugins',
						'ch' => array(
							'html_cache' => array(
								'title' => 'Server Cache',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', 'Server Cache Settings' ),
									array('option', 'sp_cache_level' ),
									array('option', 'sp_cache_reaction_on_comments' ),
									array('option', 'sp_cache_reaction_on_session' ),
									array('title', 'Server Cache Disabling' ),
									array('strings', 'sp_cache_disabled_substrings' ),
								),
							),
							'compression' => array(
								'title' => 'Compression',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', 'Files gzip compression' ),
									array('checkboxes', 'sp_mod_gzip_ext' ),
									array('title', 'Orginal files minifying / reducing' ),
									array('checkboxes', 'sp_mod_minify' ),
								),
							),
							'browser_cache' => array(
								'title' => 'Browser Cache',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', 'Images, scripts and CSS files to web browser cache' ),
									array('checkboxes', 'sp_mod_expires_ext' ),
									array('option', 'sp_mod_expires_time' ),
								),
							),
							'fast404' => array(
								'title' => 'Fast 404',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', '404 without Wordpress' ),
									array('checkboxes', 'sp_fast404' ),
								),
							),
						),
					), // speed

					'sp_security' => array(
						'title' => 'Security',
						'icon' => 'icon-users',
						'ch' => array(
								'main' => array(
										'title' => 'WP Login',
										'class' => 'SettingsAdmin',
										'method' => 'printAdminPage',
										'data' => array(
													array('title', 'Logging in to WP admin' ),
													array('option', 'sp_WP_admin_allowed_IPs_on' ),
													array('strings', 'sp_WP_admin_allowed_IPs_list' ),
										),
								),
								'blocks' => array(
										'title' => 'Hacker Blocks',
										'class' => 'BlocksAdmin',
										'method' => 'printAdminPage',
								),
								'blocked' => array(
										'title' => 'Hacker Attempts',
										'class' => '403sAdmin',
										'method' => 'printAdminPage',
								),
								'antispam' => array(
										'title' => 'Antispam',
										'class' => 'SettingsAdmin',
										'method' => 'printAdminPage',
										'data' => array(
													array('title', 'Antispam' ),
													array('option', 'sp_enable_miniantispam' ),
													array('option', 'sp_enable_miniantispam_referer' ),
										),
								),
								'censorship' => array(
										'title' => 'Comment Censorship',
										'class' => 'SettingsAdmin',
										'method' => 'printAdminPage',
										'data' => array(
													array('title', 'Bad words blocking' ),
													array('option', 'sp_disable_bad_words' ),
													array('title', 'Anti - troll comment rules' ),
													array('option', 'sp_disable_very_long_words' ),
													array('option', 'sp_disable_very_long_comments' ),
													array('option', 'sp_disable_very_newline_comments' ),
										),
								),
						),
					), // security

					'sp_sweep' => array(
						'title' => 'Sweep',
						'icon' => 'icon-options-general',
						'ch' => array(
							'head' => array(
								'title' => 'Code',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', 'WP Head Tag Metas' ),
									array('checkboxes', 'sp_wp_remove_metas' ),
									array('title', 'WP Head Code' ),
									array('text', 'sp_into_head_insert_code' ),
									array('title', 'Code before &lt;/BODY&gt;' ),
									array('text', 'sp_before_body_end_insert_code' ),
								),
							),
							'forbiden' => array(
								'title' => 'Forbiden Page Types',
								'class' => 'SettingsAdmin',
								'method' => 'printAdminPage',
								'data' => array(
									array('title', 'Forbidden kinds of pages' ),
									array('option', 'sp_forbidden_attachment' ),
									array('option', 'sp_forbidden_day' ),
									array('option', 'sp_forbidden_author' ),
									array('title', 'Forbidden feeds' ),
									array('option', 'sp_forbidden_special_feeds' ),
								),
							),
						),
					), // sweep
				),
			),
		);
	}
}