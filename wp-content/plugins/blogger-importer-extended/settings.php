<?php if (!defined('ABSPATH')) die;

// Add menu item under Tools
add_action('admin_menu', function() {
	add_submenu_page('options-general.php', 'Blogger Importer', 'Blogger Importer', 'manage_options', 'bie-settings', 'bie_settings_page_render');
}, 999999);


// Highlight the permalink setting to choose
add_action('admin_footer-options-permalink.php', function() {
	if (get_option('permalink_structure')) {
		return;
	}
	if (!isset($_GET['bie-highlight-permalink'])) {
		return;
	}
	?>
	<script>
	jQuery('input[value="/%postname%/"]').closest('tr').addClass('highlight');
	</script>
	<?php
}, 999999);


function bie_settings_page_render() {
	?>
	<style>
	.notice {
		display: none !important;
	}
	#bieNoticeOk {
		display: block !important;
		/* max-width: 691px; */
	}
	.card {
		max-width: 720px;
	}
	.description {
		font-size: 90% !important;
	}
	
	.switch {
		position: relative;
		display: inline-block;
		width: 60px;
		height: 34px;
	}

	.switch input {display:none;}

	.slider_checkbox {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		-webkit-transition: .4s;
		transition: .4s;
		border-radius: 34px;
	}

	.slider_checkbox:before {
		position: absolute;
		content: "";
		height: 26px;
		width: 26px;
		left: 4px;
		bottom: 4px;
		background-color: white;
		-webkit-transition: .4s;
		transition: .4s;
		border-radius: 50%;
	}

	input:checked + .slider_checkbox {
		background-color: #0085BA;
	}

	input:focus + .slider_checkbox {
		box-shadow: 0 0 1px #0085BA;
	}

	input:checked + .slider_checkbox:before {
		-webkit-transform: translateX(26px);
		-ms-transform: translateX(26px);
		transform: translateX(26px);
	}
	
	#wpfooter {
		display: none;
	}
	</style>
	<div class="wrap">
		<h1 style="display:none"></h1>
		<div class="card">
		
			<h2>1. Import published posts</h2>
			
			<p>Ready to import content from Blogger? Click the button below to get started:</p>
			<?php
			$check_resp = wp_remote_get('https://api.bloggerimporter.com/check_connection.php', array('timeout' => 6));
			$check = wp_remote_retrieve_body($check_resp);
			if ($check != 1) {
			?>
				<p><span class="dashicons dashicons-warning"></span> Hmmmm we can't connect to the import system. This might just be temporary, so please try again later.</p>
				<p>This error usually occurs if your host has disabled "cURL" on the server. It can also be caused by an expired/invalid SSL certificate on your server. Please contact your host for assistance with this.</p>
				<p>If you are still experiencing the same issue and your host is unable to help, please contact support@pipdig.zendesk.com.</p>
				<p>Error details:</p>
				<?php
				echo '<pre>';
					print_r($check_resp);
				echo '<pre>';
				?>
			<?php } else { ?>
				<p><a href="<?php echo admin_url('tools.php?page=bie-importer'); ?>" class="button" target="_blank">Run importer</a></p>
				<p>After the import is finished, you can return to this page to complete any remaining steps.</p>
			<?php } ?>
		</div>
		
		<?php //if (current_user_can('upload_files') && get_option('bie_license') && get_option('bie_license') != 'free') { ?>
		<!--
		<div class="card">
		
			<h2>2. Import draft posts</h2>
			
			<p>If you would like to import any draft posts from Blogger, please follow this guide.</p>
			<p>If you don't need any of your old drafts from Blogger you can skip this step.</p>
			
			<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="bie_import_drafts_ajax" />
				<input type="hidden" name="sec" value="<?php echo wp_create_nonce('bie_ajax_nonce'); ?>" />
				<input type="file" name="bie_xml_file" id="bie_xml_file" accept=".xml" />
				<br /><br />
				<input type="submit" class="button" id="bieImportDrafts" value="Import Drafts" />
			</form>
			
			<script>
			jQuery(document).ready(function($) {
				$('#bieImportDrafts').on('click', function(e) {
					if (window.FileReader && window.File && window.FileList && window.Blob && $('#bie_xml_file').val()) {
						
						var file = $('#bie_xml_file')[0].files[0];
						
						if (file.type != 'text/xml') {
							e.preventDefault();
							alert('Please select an XML file');
						} else if (file.size > 33554432) {
							e.preventDefault();
							alert('File size too large. Must be under 32MB.');
						}
					
					}
				});
			});
			</script>
			
		</div>
		-->
		<?php //} ?>
		
		<div class="card" id="redirectsCard">
			
			<h2>2. Redirect old links</h2>
			
			<p>Blogger uses a different url/link structure compared to WordPress. The options below will make sure any old links redirect to the correct place.</p>
			
			<?php if (get_option('permalink_structure')) { ?>
				<p>We recommend enabling both options if you are not using any other redirection plugins/methods. If you're not sure what that means, it should be safe to enable both options anyway.</p>
			<?php } else { ?>
				<p><span class="dashicons dashicons-warning"></span> This site is not currently using "pretty permalinks". The redirection options <strong>will not work</strong>.<br />Please go to <a href="<?php echo admin_url('options-permalink.php?bie-highlight-permalink=1'); ?>">this page</a> and select the "Post name" option <a href="<?php echo BIE_PATH; ?>img/pretty_permalinks.png" target="_blank">shown here</a>.</p>
				<style>
				#blogger_redirects_settings {
					pointer-events: none;
					opacity: .25;
				}
				</style>
			<?php } ?>
			<form method="post" action="options.php" id="blogger_redirects_settings">
				<?php
				settings_fields('bie_redirects_section');
				do_settings_sections('bie_settings');
				submit_button();
				?>
			</form>
		
		</div>
		
		<div class="card">
			
			<h2>3. Redirect traffic from Blogger</h2>
			
			<p>After transferring to WordPress, the last step is to install a new template on your old Blogger blog. This will redirect all your old blogspot links to this new site. <a href="https://go.pipdig.co/open.php?id=bie-blogger-template" target="_blank" rel="noopener">Click here</a> for instructions on how to install the template.</p>
			
			<p style="margin-top: 25px;"><span class="button" id="downloadTemplateBtn">Download Template</span></p>
			
<p><textarea style="width: 100%; height: 100px; margin-top: 10px; display: none;" id="templateContent" class="code" readonly>
<?php echo htmlspecialchars('<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html expr:dir=\'data:blog.languageDirection\' lang=\'en\' xml:lang=\'en\' xmlns=\'http://www.w3.org/1999/xhtml\' xmlns:b=\'http://www.google.com/2005/gml/b\' xmlns:data=\'http://www.google.com/2005/gml/data\' xmlns:expr=\'http://www.google.com/2005/gml/expr\' xmlns:fb=\'http://ogp.me/ns/fb#\' xmlns:og=\'http://ogp.me/ns#\'>
<head>
<b:if cond=\'data:blog.pageType == "index"\'>
<link rel=\'canonical\' href=\''.home_url('/').'\' />
<meta content=\'0;url='.home_url('/').'\' http-equiv=\'refresh\'/>
<b:else/>
<link rel=\'canonical\' expr:href=\'"'.home_url('/').'" + data:blog.url\' />
<meta expr:content=\'"0;url='.home_url('/').'" + data:blog.url\' http-equiv=\'refresh\'/>		
</b:if>
<script>
//<![CDATA[
window.location.href = "'.home_url().'" + window.location.pathname;
//]]>
</script>
<b:skin><![CDATA[/*
-----------------------------------------------
Name: Blogger Redirect
Designer: pipdig
URL: https://www.pipdig.co
----------------------------------------------- */
/*
]]></b:skin>
</head>
<body>
<b:section id=\'header\' showaddelement=\'no\'>
<b:widget id=\'Header1\' locked=\'true\' title=\'techxt (Header)\' type=\'Header\'/>
</b:section>
</body>
</html>');?></textarea></p>
		</div>
		
		<div class="card">
			<p>Have you found this <?php if (!get_option('bie_license') || get_option('bie_license') == 'free') echo 'free '; ?>plugin useful? Please consider <a href="https://go.pipdig.co/open.php?id=bie-rev" target="_blank" rel="noopener" style="text-decoration:none">leaving a &#9733;&#9733;&#9733;&#9733;&#9733; rating</a>.</p>
			<p>Not happy with the plugin? Please contact support@pipdig.zendesk.com and we may be able to help.</p>
		</div>
		
		<?php if (!function_exists('is_cart')) { // don't want to risk ecommerce sites losing data from misunderstanding the reset function ?>
		<p id="resetBieStatus" style="margin-top: 30px;"><span style="font-size: 10px;">Need to reset this site's content and start a fresh import? <a href="#" id="resetBieBtn">Click here</a></span></p>
		<?php } ?>
		
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		
		$("#downloadTemplateBtn").click(function(e) {
			
			e.preventDefault();
			
			var textToWrite = $("#templateContent").val();
			var textFileAsBlob = new Blob([textToWrite], {type: 'Application/xml'});
			var fileNameToSaveAs = "blogger-redirect-template.xml";
			var downloadLink = document.createElement("a");
			downloadLink.download = fileNameToSaveAs;
			downloadLink.innerHTML = "link text";
			window.URL = window.URL || window.webkitURL;
			downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
			downloadLink.style.display = "none";
			document.body.appendChild(downloadLink);
			downloadLink.click();
			
		});
		
		$("#resetBieBtn").click(function(e) {
			
			e.preventDefault();
			
			if (!confirm("Please note that this will delete ALL posts from WordPress. This includes things like blog posts, pages and custom post types from other plugins. Are you sure?")) {
				return;
			}
			
			if (!confirm("Seriously, it will delete ALL the posts and pages from this WordPress site. Are you sure?")) {
				return;
			}
			
			var data = {
				'action': 'bie_reset',
				'sec': '<?php echo wp_create_nonce('bie_reset_nonce'); ?>',
			};
			
			$.post(ajaxurl, data, function(response) {
				if (response == 1) {
					$('#resetBieStatus').html('Reset complete! You can now <a href="<?php echo admin_url('tools.php?page=bie-importer'); ?>">start a new import</a>.');
				} else {
					$('#resetBieStatus').text('Reset failed! Please reload this page to try again.');
				}
			});
			
		});
		
	});
	</script>
	
	<?php
}


add_action('admin_init', function() {
	
	add_settings_section('bie_redirects_section', '', null, 'bie_settings');
	
	// Enable redirects
	add_settings_field('enabled_redirects', 'Redirect old Blogger links', 'bie_settings_field_enable_redirects', 'bie_settings', 'bie_redirects_section');
	
	// 404 to homepage
	add_settings_field('redirect_404s', 'Redirect 404s to homepage', 'bie_settings_field_redirect_404s', 'bie_settings', 'bie_redirects_section');
	
	register_setting('bie_redirects_section', 'bie_settings');
	
});


function bie_settings_field_enable_redirects() {
	$value = 0;
	$options = get_option('bie_settings');
	if (isset($options['enabled_redirects'])) {
		$value = absint($options['enabled_redirects']);
	}
	$postname = 'blog-post-title';
	$year = date('Y');
	$month = date('m');
	$permalink_structure = get_option('permalink_structure');
	if ($permalink_structure) {
		$permalink = str_replace('%postname%', $postname, $permalink_structure);
		$permalink = str_replace('%year%', $year, $permalink);
		$permalink = str_replace('%monthnum%', $month, $permalink);
	}
	$show_example = false;
	if ($permalink_structure && ($permalink_structure == '/%postname%/' || $permalink_structure == '/%year%/%monthnum%/%postname%/')) {
		$show_example = true;
	}
	?>
	<label class="switch">
		<input type="checkbox" id="enabled_redirects" name="bie_settings[enabled_redirects]" value="1" <?php checked(1, $value, true); ?>>
		<span class="slider_checkbox"></span>
	</label>
	<p class="description">Any old Blogger post, page, label or RSS feed links will be 301 redirected, keeping any SEO value.<?php if ($show_example) { ?> For example:</p>
	<p class="description"><?php echo home_url().'/'.$year.'/'.$month.'/'.$postname.'.html'; ?><br />
	will redirect to<br />
	<?php echo home_url().$permalink; ?></p><?php } ?>
	<?php
}


function bie_settings_field_redirect_404s() {
	$value = 0;
	$options = get_option('bie_settings');
	if (isset($options['redirect_404s'])) {
		$value = absint($options['redirect_404s']);
	}
	?>
	<label class="switch">
		<input type="checkbox" id="redirect_404s" name="bie_settings[redirect_404s]" value="1" <?php checked(1, $value, true); ?>>
		<span class="slider_checkbox"></span>
	</label>
	<p class="description">If a post/page can't be found, it will be 301 redirected to the homepage instead of showing the normal 404 error page.</p>
	<?php
}



add_action('wp_ajax_bie_reset', function() {

	check_ajax_referer('bie_reset_nonce', 'sec');
	
	if (!is_super_admin()) {
		die;
	}
	
	global $wpdb;
	
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."posts");
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."postmeta");
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."comments");
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."commentmeta");
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."term_relationships");
	$wpdb->query("TRUNCATE TABLE ".$wpdb->prefix."bie_redirects");
	
	echo 1;
	
	die;
});


/*
add_action('wp_ajax_bie_import_drafts_ajax', function() {

	check_ajax_referer('bie_ajax_nonce', 'sec');
	
	if (!current_user_can('upload_files')) {
		echo 5;
		die;
	}
	
	if (empty($_FILES['bie_xml_file'])) {
		echo 2;
		die;
	}
	
	if (empty($_FILES['bie_xml_file']['tmp_name'])) {
		echo 2;
		die;
	}
	
	// xml file check
	$file_info = wp_check_filetype(basename($_FILES['bie_xml_file']['name']), array('xml' => 'text/xml'));
	if (empty($file_info['ext'])) {
		echo 4;
		die;
	}
	
	$data = file_get_contents($_FILES['bie_xml_file']['tmp_name']);
	unlink($_FILES['bie_xml_file']['tmp_name']);
	
	if (!$data) {
		echo 2;
		die;
	}
	
	if (!class_exists('SimplePie', false)) {
		require_once ABSPATH.WPINC.'/class-simplepie.php';
	}
	
	$feed = new SimplePie();
	$feed->enable_cache(false);
	$feed->set_raw_data($data);
	$feed->init();
	
	if (is_wp_error($feed)) {
		echo 2;
		die;
	}
	
	if (!is_array($feed->get_items()) || count($feed->get_items()) < 1) {
		echo 3;
		die;
	}
	
	$imported = $failed = array();
	$imported_counter = $failed_counter = 0;
	
	foreach ($feed->get_items() as $item) {
		
		$is_draft = false;
		if (($control = $item->get_item_tags('http://purl.org/atom/app#', 'control')) && !empty($control[0]['child']['http://purl.org/atom/app#']['draft'][0]['data'])) {
			$is_draft = true;
		}
		
		if (!$is_draft) {
			continue;
		}
		
		// deal with categories first as this is the part which says if it is a blog post or not
		$post_cats = array();
		foreach ((array) $item->get_categories() as $category) {
			
			$category_name = $category->get_label();
			
			if (empty($category_name)) {
				continue;
			}
			
			if (strpos($category_name, 'kind#post') === false) {
				continue 2; // skip this post by continuing from the parent loop. It isn't a blog post.
			}
			
			// only add labels that aren't schema thingies
			if (strpos($category_name, 'http') === false) {
				$post_cats[] = $category_name;
			}
			
		}
		
		$post_title = $item->get_title();
		$post_content = $item->get_content();
		
		wp_suspend_cache_invalidation(true);
		wp_defer_term_counting(true);
		wp_defer_comment_counting(true);
		
		$post_id = wp_insert_post(array(
			'post_type' => 'post',
			'post_content' => $post_content,
			'post_title' => $post_title,
			'post_status' => 'draft',
			'ping_status' => 'closed',
			'tags_input' => $post_cats,
		));
		
		if (is_wp_error($post_id)) {
			$failed[] = $post_title;
			$failed_counter++;
		} else {
			$imported[] = $post_title;
			$imported_counter++;
		}
		
		wp_suspend_cache_invalidation(false);
		wp_defer_term_counting(false);
		wp_defer_comment_counting(false);
		
	}
	
	if ($imported_counter > 0) {
		$redirect_url = admin_url('options-general.php?page=bie-settings&bie_drafts_imported='.$imported_counter);
	} elseif ($failed_counter > 0) {
		$redirect_url = admin_url('options-general.php?page=bie-settings&bie_drafts_failed='.$failed_counter);
	} else {
		$redirect_url = admin_url('options-general.php?page=bie-settings&bie_msg=1');
	}
	
	echo '<scr'.'ipt>window.location.replace("'.$redirect_url.'");</scr'.'ipt>';
		
	die;
});


add_action('admin_notices', function() {
	
	global $pagenow;
	if ($pagenow != 'options-general.php') {
		return;
	}
	
	if (!isset($_GET['page']) || $_GET['page'] != 'bie-settings') {
		return;
	}
	
	$imported = $failed = $notifcation_text = 0;
	
	if (!empty($_GET['bie_drafts_imported']) && is_numeric($_GET['bie_drafts_imported'])) {
		$imported = absint($_GET['bie_drafts_imported']);
	} elseif (!empty($_GET['bie_drafts_failed']) && is_numeric($_GET['bie_drafts_failed'])) {
		$failed = absint($_GET['bie_drafts_failed']);
	} elseif (!empty($_GET['bie_msg']) && is_numeric($_GET['bie_msg'])) {
		$bie_msg = absint($_GET['bie_msg']);
		if ($bie_msg === 1) {
			$notifcation_text = '<p>There were no blog posts in the export file.</p><p>Please try downloading a new export file from Blogger.</p>';
		} elseif ($bie_msg === 2) {
			$notifcation_text = 'There was an error during upload. Please reload the page and try again.';
		} elseif ($bie_msg === 3) {
			$notifcation_text = 'There are no blog posts in this file. Please try downloading a new export from Blogger.';
		} elseif ($bie_msg === 4) {
			$notifcation_text = 'Please make sure the file is an XML file. You can download this from your Blogger settings page.';
		} elseif ($bie_msg === 5) {
			$notifcation_text = 'You do not have permission to upload files to this website.';
		} else {
			return;
		}
	} else {
		return;
	}
	
	?>
	<?php if ($imported && $failed) { ?>
		<div class="notice notice-success" id="bieNoticeOk">
			<p><?php echo $imported; ?> draft posts were successfully imported. You can view them by <a href="<?php echo admin_url('edit.php?post_status=draft&post_type=post'); ?>">clicking here</a>.</p>
			<p><?php echo $failed; ?> draft posts were skipped. This is usually because the post did not have any content.</p>
		</div>
	<?php } elseif ($imported) { ?>
		<div class="notice notice-success" id="bieNoticeOk">
			<h2>Success!</h2>
			<p><?php echo $imported; ?> draft posts were successfully imported. You can view them by <a href="<?php echo admin_url('edit.php?post_status=draft&post_type=post'); ?>">clicking here</a>.</p>
		</div>
	<?php } elseif ($failed) { ?>
		<div class="notice notice-error" id="bieNoticeOk">
			<h2>Drafts not imported</h2>
			<p><?php echo $failed; ?> draft posts were not imported. Maybe the posts had no content? If so, they are skipped. Another reason this can happen is if the export file is very large or there are some issues with your web host. If you continue to see this message, you're welcome to email support@pipdig.zendesk.com and we can help import the drafts.</p>
		</div>
	<?php } elseif ($notifcation_text) { ?>
		<div class="notice notice-error" id="bieNoticeOk">
			<?php echo $notifcation_text; ?>
		</div>
	<?php } ?>
	<?php
});
*/
