<?php
/*
Plugin Name: Blogger Importer Extended
Plugin URI: https://wordpress.org/plugins/blogger-importer-extended/
Description: The only plugin you need to move from Blogger to WordPress. Import all your content and setup 301 redirects automatically.
Author: pipdig
Version: 2.2.2
Author URI: https://www.pipdig.co/
License: GPLv2 or later
Text Domain: blogger-importer-extended
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) die;

define('BIE_VER', '2.2.2');
define('BIE_DIR', plugin_dir_path(__FILE__));
define('BIE_PATH', plugin_dir_url(__FILE__));

if (!defined('BIE_WAIT_TIME')) {
	define('BIE_WAIT_TIME', 3000); // default 3 seconds wait time between import batches
}

include(BIE_DIR.'settings.php');
include(BIE_DIR.'redirects.php');

// create entry in 'Tools > Import'
add_action('admin_init', function() {
	register_importer('bie-importer', 'Blogger Importer Extended', __('Import posts, pages, comments and labels from Blogger to WordPress.', 'bie-importer'), 'bie_page_render');
});


// Add menu item under Tools
add_action('admin_menu', function() {
	add_submenu_page('tools.php', __('Blogger Importer'), __('Blogger Importer'), 'edit_theme_options', 'bie-importer', 'bie_page_render');
}, 999999);

// Plugin page links
add_filter('plugin_action_links_'.plugin_basename(__FILE__), function($links) {
	$links[] = '<a href="'.admin_url('options-general.php?page=bie-settings').'">'.__('Run Importer').'</a>';
	$links[] = '<a href="'.admin_url('options-general.php?page=bie-settings#redirectsCard').'">301 Redirects</a>';
	return $links;
});

function bie_activate_plugin() {
	
	bie_create_database_tables();
	
	if (!get_option('bie_installed_date')) {
		add_option('bie_installed_date', date('Y-m-d'));
	}
	
}
register_activation_hook(__FILE__, 'bie_activate_plugin');

function bie_deactivate_plugin() {
	delete_option('bie_license');
}
register_deactivation_hook(__FILE__, 'bie_deactivate_plugin');

// Create table used to store redirects from Blogger traffic
function bie_create_database_tables() {
	
	global $wpdb;
	
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix.'bie_redirects';
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		post_id bigint(20) PRIMARY KEY,
		blogger_permalink tinytext NOT NULL,
		blogger_post_id tinytext NOT NULL
	) $charset_collate;";
	
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Remove a redirect from the database when a post is deleted
function pipdig_blogger_delete_redirect_entry($post_id) {
	global $wpdb;
	$table_name = $wpdb->prefix.'bie_redirects';
	$wpdb->delete($table_name, array('post_id' => $post_id));
}
add_action('before_delete_post', 'pipdig_blogger_delete_redirect_entry');

add_action('admin_notices', function() {
	
	global $pagenow;
	if ($pagenow != 'plugins.php') {
		return;
	}
	
	if (current_user_can('manage_options')) {
		if (!empty($_POST['bie_hide_setup_notice']) && wp_verify_nonce($_POST['bie_hide_setup_notice_nonce'], 'sec')) {
			update_option('bie_hide_setup_notice', 1);
			return;
		}
	} else {
		return;
	}

	if (get_option('bie_hide_setup_notice')) {
		return;
	}
	
	?>
	<div class="notice notice-success">
		<h2>Blogger Importer</h2>
		<p>Thank you for installing Blogger Importer Extended! Please go to <a href="<?php echo admin_url('options-general.php?page=bie-settings'); ?>">this page</a> to get started.</p>
		<form action="" method="post">
			<input type="hidden" value="1" name="bie_hide_setup_notice" />
			<?php wp_nonce_field('sec', 'bie_hide_setup_notice_nonce'); ?>
			<p class="submit" style="margin-top: 5px; padding-top: 5px;">
				<input name="submit" class="button" value="Remove this notice" type="submit" />
			</p>
		</form>
	</div>
	<?php
});


function bie_page_render() {
	?>
	<style>
	html.wp-toolbar {
		padding-top: 0;
	}
	#wpwrap {
		display: none;
	}

	body {
		margin: 65px auto 24px;
		box-shadow: none;
		background: #f1f1f1;
		padding: 0;
		max-width: 600px;
	}
	
	#pipdigBloggerImporter {
		text-align: center;
		position: relative;
	}
	
	#pipdigBloggerClose {
		position: absolute;
		top: 0;
		right: 0;
		text-decoration: none;
	}
	#pipdigBloggerClose .dashicons {
		font-size: 40px;
		width: 40px;
		height: 40px;
	}
	
	#pipdigBloggerImporterContent {
		box-shadow: 0 1px 3px rgba(0, 0, 0, .13);
		padding: 2em;
		margin: 0 0 20px;
		background: #fff;
		overflow: hidden;
		zoom: 1;
		text-align: center;
	}

	#pipdigBloggerImpoterMsg1 {
		margin-top: 35px;
	}

	input.fade_out, .fade_out {
		-moz-transition: opacity 0.25s ease-out; -webkit-transition: opacity 0.25s ease-out; transition: opacity 0.25s ease-out;
	}
	.pipdig_hide {
		opacity: 0.25;
		pointer-events: none;
	}

	.dashicons.spin {
		animation: dashicons-spin 1.3s infinite;
		animation-timing-function: linear;
	}
	@keyframes dashicons-spin {
		0% {
			transform: rotate( 0deg );
		}
		100% {
			transform: rotate( 360deg );
		}
	}
	
	.pipdig_progress_circle {
		position: relative;
	}
	.pipdig_progress_circle strong {
		font-size: 25px;
		position: absolute;
		top: 39px;
		left: 0;
		width: 100%;
		text-align: center;
	}
	
	.bie_info_icon {
		text-decoration: none;
	}
	</style>
	
	<script>
	/**
	 * jquery-circle-progress - jQuery Plugin to draw animated circular progress bars:
	 * {@link http://kottenator.github.io/jquery-circle-progress/}
	 *
	 * @author Rostyslav Bryzgunov <kottenator@gmail.com>
	 * @version 1.2.2
	 * @license MIT
	 * @preserve
	 */
	!function(i){if("function"==typeof define&&define.amd)define(["jquery"],i);else if("object"==typeof module&&module.exports){var t=require("jquery");i(t),module.exports=t}else i(jQuery)}(function(i){function t(i){this.init(i)}t.prototype={value:0,size:100,startAngle:-Math.PI,thickness:"auto",fill:{gradient:["#3aeabb","#fdd250"]},emptyFill:"rgba(0, 0, 0, .1)",animation:{duration:1200,easing:"circleProgressEasing"},animationStartValue:0,reverse:!1,lineCap:"butt",insertMode:"prepend",constructor:t,el:null,canvas:null,ctx:null,radius:0,arcFill:null,lastFrameValue:0,init:function(t){i.extend(this,t),this.radius=this.size/2,this.initWidget(),this.initFill(),this.draw(),this.el.trigger("circle-inited")},initWidget:function(){this.canvas||(this.canvas=i("<canvas>")["prepend"==this.insertMode?"prependTo":"appendTo"](this.el)[0]);var t=this.canvas;if(t.width=this.size,t.height=this.size,this.ctx=t.getContext("2d"),window.devicePixelRatio>1){var e=window.devicePixelRatio;t.style.width=t.style.height=this.size+"px",t.width=t.height=this.size*e,this.ctx.scale(e,e)}},initFill:function(){function t(){var t=i("<canvas>")[0];t.width=e.size,t.height=e.size,t.getContext("2d").drawImage(g,0,0,r,r),e.arcFill=e.ctx.createPattern(t,"no-repeat"),e.drawFrame(e.lastFrameValue)}var e=this,a=this.fill,n=this.ctx,r=this.size;if(!a)throw Error("The fill is not specified!");if("string"==typeof a&&(a={color:a}),a.color&&(this.arcFill=a.color),a.gradient){var s=a.gradient;if(1==s.length)this.arcFill=s[0];else if(s.length>1){for(var l=a.gradientAngle||0,o=a.gradientDirection||[r/2*(1-Math.cos(l)),r/2*(1+Math.sin(l)),r/2*(1+Math.cos(l)),r/2*(1-Math.sin(l))],h=n.createLinearGradient.apply(n,o),c=0;c<s.length;c++){var d=s[c],u=c/(s.length-1);i.isArray(d)&&(u=d[1],d=d[0]),h.addColorStop(u,d)}this.arcFill=h}}if(a.image){var g;a.image instanceof Image?g=a.image:(g=new Image,g.src=a.image),g.complete?t():g.onload=t}},draw:function(){this.animation?this.drawAnimated(this.value):this.drawFrame(this.value)},drawFrame:function(i){this.lastFrameValue=i,this.ctx.clearRect(0,0,this.size,this.size),this.drawEmptyArc(i),this.drawArc(i)},drawArc:function(i){if(0!==i){var t=this.ctx,e=this.radius,a=this.getThickness(),n=this.startAngle;t.save(),t.beginPath(),this.reverse?t.arc(e,e,e-a/2,n-2*Math.PI*i,n):t.arc(e,e,e-a/2,n,n+2*Math.PI*i),t.lineWidth=a,t.lineCap=this.lineCap,t.strokeStyle=this.arcFill,t.stroke(),t.restore()}},drawEmptyArc:function(i){var t=this.ctx,e=this.radius,a=this.getThickness(),n=this.startAngle;i<1&&(t.save(),t.beginPath(),i<=0?t.arc(e,e,e-a/2,0,2*Math.PI):this.reverse?t.arc(e,e,e-a/2,n,n-2*Math.PI*i):t.arc(e,e,e-a/2,n+2*Math.PI*i,n),t.lineWidth=a,t.strokeStyle=this.emptyFill,t.stroke(),t.restore())},drawAnimated:function(t){var e=this,a=this.el,n=i(this.canvas);n.stop(!0,!1),a.trigger("circle-animation-start"),n.css({animationProgress:0}).animate({animationProgress:1},i.extend({},this.animation,{step:function(i){var n=e.animationStartValue*(1-i)+t*i;e.drawFrame(n),a.trigger("circle-animation-progress",[i,n])}})).promise().always(function(){a.trigger("circle-animation-end")})},getThickness:function(){return i.isNumeric(this.thickness)?this.thickness:this.size/14},getValue:function(){return this.value},setValue:function(i){this.animation&&(this.animationStartValue=this.lastFrameValue),this.value=i,this.draw()}},i.circleProgress={defaults:t.prototype},i.easing.circleProgressEasing=function(i){return i<.5?(i=2*i,.5*i*i*i):(i=2-2*i,1-.5*i*i*i)},i.fn.circleProgress=function(e,a){var n="circle-progress",r=this.data(n);if("widget"==e){if(!r)throw Error('Calling "widget" method on not initialized instance is forbidden');return r.canvas}if("value"==e){if(!r)throw Error('Calling "value" method on not initialized instance is forbidden');if("undefined"==typeof a)return r.getValue();var s=arguments[1];return this.each(function(){i(this).data(n).setValue(s)})}return this.each(function(){var a=i(this),r=a.data(n),s=i.isPlainObject(e)?e:{};if(r)r.init(s);else{var l=i.extend({},a.data());"string"==typeof l.fill&&(l.fill=JSON.parse(l.fill)),"string"==typeof l.animation&&(l.animation=JSON.parse(l.animation)),s=i.extend(l,s),s.el=a,r=new t(s),a.data(n,r)}})}});
	
	jQuery(document).ready(function($) {
		
		$('#wpwrap').before('<div id="pipdigBloggerImporter"><a id="pipdigBloggerClose" href="<?php echo admin_url('options-general.php?page=bie-settings'); ?>" title="Return to dashboard"><span class="dashicons dashicons-no-alt"></span></a><div id="pipdigBloggerImporterContent"><div id="postImportProgress" class="pipdig_progress_circle"><strong></strong></div><img src="<?php echo BIE_PATH; ?>img/boxes.svg" alt="" class="fade_out" style="width:150px" /><h2 class="fade_out">Welcome to the Blogger Importer!</h2><div id="bieLicenseChoices"><p>The free version of this plugin can import up to 20 blog posts and pages.</p><p>Alternatively you can purchase an <a href="https://go.pipdig.co/open.php?id=bie-pro" target="_blank" rel="noopener">unlimited license</a> for unlimited posts, pages, comments and images.</p><p>Read more about the differences <a href="https://go.pipdig.co/open.php?id=bie-pro" target="_blank" rel="noopener">here</a>.</p><div style="margin-top:20px"><div class="button" id="bieFreeBtn">20 posts for free</div> <div class="button button-primary" id="bieProBtn">Unlimited license</div></div></div><div id="blogLicenseStep" class="fade_out" style="display:none"><p class="fade_out" style="margin-bottom: 20px;">What is your license key? License keys an be purchased <a href="https://go.pipdig.co/open.php?id=bie-pro" target="_blank" rel="noopener">here</a>.</p><input type="text" value="" class="wide-fat fade_out" style="width:320px;max-width:100%;" id="bieLicenseField"> <input type="button" value="<?php echo esc_attr(__('Submit')); ?>" class="button button-primary fade_out" id="bieLicenseSubmit"><div id="bieCheckingLicense" style="display: none; margin-top: 10px;"><span class="dashicons dashicons-update spin"></span> Checking License...</div><div id="bieCheckingLicenseResult" style="margin-top: 10px;"></div></div><div id="blogIdStep" class="fade_out" style="display:none"><p><span id="bieLicenseSuccessMsg"></span>Please enter your Blog\'s ID in the option below. You can find your Blog ID like <a href="<?php echo BIE_PATH; ?>img/find_blog_id.png" target="_blank" rel="noopener">this example</a>.</p><p style="margin-bottom: 20px;"><span class="dashicons dashicons-warning"></span> Please note that Blogger settings must be <a href="<?php echo BIE_PATH; ?>img/blogger_public.png" target="_blank" rel="noopener">Public</a> during the import.</p><input type="text" value="" class="wide-fat fade_out" style="width:320px;max-width:100%;" id="BlogggerBlogIdField" placeholder="Blog ID should be a number"> <input type="button" value="<?php echo esc_attr(__('Submit')); ?>" class="button button-primary fade_out" id="submitBlogId"></div><div id="pipdigBloggerImpoterMsg1"></div></div></div><div id="totalPostCount" style="display:none"></div><div id="lastUpdateCountdown" style="display:none"></div>');
		
		var bieLicenseField = $('#bieLicenseField');
		
		<?php
		if (get_option('bie_license') && get_option('bie_license') != 'free') {
			echo 'bieLicenseField.val("'.sanitize_text_field(get_option('bie_license')).'");';
		}
		?>
		
		// Select Free button
		$('#pipdigBloggerImporter').on('click', '#bieFreeBtn', function() {
			
			$('#bieLicenseChoices').slideUp(300);
			$('#blogIdStep').slideDown(300);
			
			var data = {
				'action': 'bie_free_license_ajax',
				'sec': '<?php echo wp_create_nonce('bie_ajax_nonce'); ?>',
			};
			
			$.post(ajaxurl, data, function(response) {
				
			});
			
		});
		
		// Select Pro button
		$('#pipdigBloggerImporter').on('click', '#bieProBtn', function() {
			$('#bieLicenseChoices').slideUp(300);
			$('#blogLicenseStep').slideDown(300);
		});
		
		// Submit license button
		$('#pipdigBloggerImporter').on('click', '#bieLicenseSubmit', function() {
			checkLicenseStep();
		});
		// or on Enter key
		bieLicenseField.bind("enterKey", function() {
			checkLicenseStep();
		});
		bieLicenseField.keyup(function(e) {
		    if (e.keyCode == 13) {
		        $(this).trigger("enterKey");
		    }
		});
		
		function checkLicenseStep() {
			
			if (!bieLicenseField.val()) {
				return;
			}
			
			$('#bieCheckingLicenseResult').text('');
			$('#bieCheckingLicense').show();
			
			var data = {
				'action': 'bie_check_license_ajax',
				'sec': '<?php echo wp_create_nonce('bie_ajax_nonce'); ?>',
				'bie_license': bieLicenseField.val()
			};
			
			$.post(ajaxurl, data, function(response) {
				
				//console.log(response);
				
				$('#bieCheckingLicense').hide();
				
				/*
				1 = success
				2 = license key has expired
				3 = license key does not exist
				*/
				
				if (response == 1) {
					//$('#bieLicenseSuccessMsg').text('License is valid, thanks! ');
					$('#blogLicenseStep').slideUp(300);
					$('#blogIdStep').slideDown(300);
				} else if (response == 2) {
					$('#bieCheckingLicenseResult').html('This license has expired. Would you like to <a href="https://go.pipdig.co/open.php?id=bie-pro" target="_blank" rel="noopener">purchase a new one</a>?');
				} else if (response == 3) {
					$('#bieCheckingLicenseResult').html('This license does not exist. Please check your email receipt for the license key or <a href="<?php echo admin_url('tools.php?page=bie-importer'); ?>">click here</a> to restart the import process.');
				} else {
					
				}
				
			});
			
		}
		
		var BlogggerBlogIdField = $('#BlogggerBlogIdField');
		var button = $('#submitBlogId');
		var message = $('#pipdigBloggerImpoterMsg1');
		
		<?php
		// if we're restarting importer, copy old ID from GET
		if (!empty($_GET['bid'])) {
			echo '$("#bieLicenseChoices").hide();';
			echo 'BlogggerBlogIdField.val("'.sanitize_text_field($_GET['bid']).'"); checkBlogggerBlogId()';
		}
		?>
		
		function checkBlogggerBlogId() {
			
			if (BlogggerBlogIdField.val()) {
				
				button.prop('disabled', true);
				button.removeClass('button-primary');
				$('.fade_out').addClass('pipdig_hide');
				$('#postImportProgress').html('<strong></strong>');
				message.html('<h2><span class="dashicons dashicons-update spin"></span> Loading...</h2>');
				
				var data = {
					'action': 'bie_get_blog_ajax',
					'sec': '<?php echo wp_create_nonce('bie_ajax_nonce'); ?>',
					'blogger_blog_id': BlogggerBlogIdField.val()
				};
				
				$.post(ajaxurl, data, function(response) {
					message.html(response);
					//console.log(response);
					button.prop('disabled', false);
					$('.fade_out').removeClass('pipdig_hide');
				});
				
			} else {
				message.html('Please enter your Blogger blog ID in the field above. You can get your Blog ID from <a href="<?php echo BIE_PATH; ?>img/find_blog_id.png" rel="nofollow" target="_blank">here</a> when logged in to Blogger.');
			}
		}

		// Search blog ID on submit button
		button.on('click', function() {
			checkBlogggerBlogId();		
		});
		// or on Enter key
		BlogggerBlogIdField.bind("enterKey", function() {
			checkLicenseStep();
		});
		BlogggerBlogIdField.keyup(function(e) {
			// numbers only
			if (/\D/g.test(this.value)) {
				this.value = this.value.replace(/\D/g, '');
			}
			// submit on enter key
		    if (e.keyCode == 13) {
		        $(this).trigger("enterKey");
		    }
		});
		
		$('#pipdigBloggerImporter').on('click', '#startImport', function() {
			
			importPostsBtn = $(this);
			
			if (confirm("Are you sure you want to import this blog?")) {
				
				// Set "browse away" navigation prompt
				window.onbeforeunload = function() {
					return true;
				};
				
				$('#pipdigBloggerClose').hide();
				
				$('.fade_out').slideUp(550);
				message.css('margin-top', 0);
				
				var skipComments = 0;
				if ($('#skipComments').prop("checked") == true) {
					skipComments = 1;
				}
				
				var skipPages = 0;
				if ($('#skipPages').prop("checked") == true) {
					skipPages = 1;
				}
				
				var skipImages = 0;
				if ($('#skipImages').prop("checked") == true) {
					skipImages = 1;
				}
				
				var skipAuthors = 0;
				if ($('#skipAuthors').prop("checked") == true) {
					skipAuthors = 1;
				}
				
				var convertFormatting = 0;
				if ($('#convertFormatting').prop("checked") == true) {
					convertFormatting = 1;
				}
				
				var totalPosts = parseInt(importPostsBtn.data('total-posts'));
				$('#totalPostCount').text(totalPosts);
				
				var importingTimeNotice = 'The import process can take a long time for large blogs.';
				if (totalPosts > 1500) {
					importingTimeNotice = 'Importing thousands of blog posts will take some time. Please note that the importer may need to restart, but you will not lose any progress.';
				}
				
				button.prop('disabled', true);
				$('.fade_out').addClass('pipdig_hide');
				importPostsBtn.addClass('pipdig_hide');
				message.html('<h2><span class="dashicons dashicons-update spin"></span> Processing, Please wait...</h2><p>Do not browse away from this screen.</p><p>'+importingTimeNotice+'</p><div style="margin-top: 25px"><a href="<?php echo admin_url('tools.php?page=bie-importer'); ?>&bid='+BlogggerBlogIdField.val()+'" class="button fade_out" id="stopImport"><span class="dashicons dashicons-no" style="margin-top: 4px;"></span> Stop the import!</a></div>');
				
				$('#postImportProgress').circleProgress({
					value: 0,
					size: 100
				}).on('circle-animation-progress', function(event, progress, stepValue) {
					$(this).find('strong').text(Math.round(100 * stepValue) + '%');
				});
				
				importPosts('', 0, skipComments, skipImages, skipPages, skipAuthors, convertFormatting);
				
			}
			
		});
		
		$('#pipdigBloggerImporter').on('change', '#includeDrafts', function() {
			if (this.checked) {
				$('#draftsFile').slideDown();
			} else {
				$('#draftsFile').slideUp();
			}
		});
		
		$('#pipdigBloggerImporter').on('change', '#skipPages', function() {
			if (this.checked) {
				$('#andPages').fadeOut();
			} else {
				$('#andPages').fadeIn();
			}
		});
		
		// check the response is a json object
		function checkIsJsonString(str) {
			try {
				JSON.parse(str);
			} catch (e) {
				return false;
			}
			return true;
		}
		
		function importPosts(pageToken, postsImported, skipComments, skipImages, skipPages, skipAuthors, convertFormatting) {
			
			var data = {
				'action': 'bie_progress_ajax',
				'sec': '<?php echo wp_create_nonce('bie_ajax_nonce'); ?>',
				'page_token': pageToken,
				'posts_imported': postsImported,
				'blogger_blog_id': BlogggerBlogIdField.val(),
				'skip_comments': skipComments,
				'skip_pages': skipPages,
				'skip_images': skipImages,
				'skip_authors': skipAuthors,
				'convert_formatting': convertFormatting,
			};
			
			$.post(ajaxurl, data, function(response) {
				
				//console.log(response);
				
				if (!checkIsJsonString(response)) {
					console.log(response);
					if (response.includes("https://go.pipdig.co/open.php?id=2")) {
						message.html('<img src="<?php echo BIE_PATH; ?>img/battery_low.svg" alt="" style="width: 150px;" />'+response+'<p style="margin-top: 20px"><a class="button" href="<?php echo admin_url('options-general.php?page=bie-settings'); ?>">Return to dashboard</a></p>');
					} else {
						message.html('<img src="<?php echo BIE_PATH; ?>img/broken.svg" alt="" style="width: 150px;" /><h2>Connection lost</h2><p>It looks like the importer has stopped working. Don\'t worry though, any progress was not lost! Click the button below to continue.</p><p>Are you seeing this message a lot? <a href="https://go.pipdig.co/open.php?id=bie-tips" target="_blank" rel="noopener">Click here</a> for some tips for easier migrations.</p><p style="margin-top: 20px"><a class="button-primary" href="<?php echo admin_url('tools.php?page=bie-importer'); ?>&bid='+BlogggerBlogIdField.val()+'">Restart Importer</a></p>');
					}
					window.onbeforeunload = null; // Remove navigation prompt
					$('#postImportProgress').text('');
					$('#lastUpdateCountdown').text('');
					return;
				}
				
				var resp = JSON.parse(response);
				
				var postsImported = parseInt(resp.posts_imported);
				//console.log(postsImported);
				var totalPostCount = parseInt($('#totalPostCount').text());
				
				if (resp.next_page !== null && resp.next_page !== '') {
					var value = (postsImported / totalPostCount).toFixed(2);
					$('#postImportProgress').circleProgress('value', value);
					$('#lastUpdateCountdown').text('180');
					
					setTimeout(function(){
						importPosts(resp.next_page, postsImported, skipComments, skipImages, skipPages, skipAuthors, convertFormatting);
					}, <?php echo absint(BIE_WAIT_TIME); ?>);
				} else {
					// complete!
					window.onbeforeunload = null; // Remove navigation prompt
					$('#lastUpdateCountdown').text('');
					$('#postImportProgress').html('<strong></strong>');
					message.html('<img src="<?php echo BIE_PATH; ?>img/success.svg" alt="" style="width: 150px;" /><h2>Success!</h2><p>All content was imported successfully.</p><p>What now? Don\'t forget to setup the <a href="<?php echo admin_url('options-general.php?page=bie-settings'); ?>">remaining steps</a>.</p><p style="margin-top: 20px"><a class="button" href="<?php echo admin_url('options-general.php?page=bie-settings'); ?>">Return to dashboard</a></p>');
					$('.fade_out').slideUp(550);
				}
				
			});
				
		}
		
		
		// Every 1s, decrement our 60s counter. The counter is set back to 180 when a post import has completed via $('#lastUpdateCountdown').text('180'); above.
		setInterval(function() {
			
			var counter = parseInt($('#lastUpdateCountdown').text());
			
			if (!isNaN(counter)) {
				--counter; // decrement by 1
				
				$('#lastUpdateCountdown').text(counter);
				
				if (counter === 0) {
					window.onbeforeunload = null; // Remove navigation prompt
					$('#postImportProgress').html('<strong></strong>');
					message.html('<img src="<?php echo BIE_PATH; ?>img/broken.svg" alt="" style="width: 150px;" /><h2>Connection lost</h2><p>It looks like the importer has stopped unexpectedly. Don\'t worry though, any progress was not lost! Click the button below to continue the current import.</p><p>Are you seeing this message a lot? <a href="https://go.pipdig.co/open.php?id=bie-tips" target="_blank" rel="noopener">Click here</a> for some tips for easier migrations.</p><p style="margin-top: 20px"><a class="button-primary" href="<?php echo admin_url('tools.php?page=bie-importer'); ?>&bid='+BlogggerBlogIdField.val()+'">Continue Importer</a></p>');
					$('#lastUpdateCountdown').text('');
					return;
				}
				
			}
			
		}, 1000);
		
	});
	</script>
	
	<?php
}


add_action('wp_ajax_bie_free_license_ajax', function() {
	check_ajax_referer('bie_ajax_nonce', 'sec');
	update_option('bie_license', 'free');
	wp_die();
});


add_action('wp_ajax_bie_check_license_ajax', function() {

	check_ajax_referer('bie_ajax_nonce', 'sec');
	
	if (!isset($_POST['bie_license'])) {
		echo 0;
		wp_die();
	}
	
	$license = sanitize_text_field($_POST['bie_license']);
	
	$body = wp_remote_retrieve_body(wp_remote_get('https://api.bloggerimporter.com/check_license.php?license='.$license.'&refresh='.rand(0, 999999), array('timeout' => 8)));
	
	if ($body == 1) {
		update_option('bie_license', $license);
	}
	
	echo absint($body);
	
	wp_die();
});


add_action('wp_ajax_bie_get_blog_ajax', function() {

	check_ajax_referer('bie_ajax_nonce', 'sec');
	
	if (!isset($_POST['blogger_blog_id'])) {
		echo '<p>Error: Please enter your Blogger blog ID.</p>';
		wp_die();
	}
		
	$blogger_blog_id = sanitize_text_field($_POST['blogger_blog_id']);

	if (!is_numeric($blogger_blog_id)) {
		echo '<p>Error: Your blog ID should be a number.</p>';
		wp_die();
	}
	
	$free_license = true;
	if (get_option('bie_license') && get_option('bie_license') != 'free') {
		$free_license = false;
	}
	
	$url = 'https://api.bloggerimporter.com/?blog_id='.$blogger_blog_id.'&query=blog&refresh='.rand(0, 999999);
	$response = pipdig_blogger_get_response($url);
	
	if (isset($response->name)) {
		$site_url = rtrim($response->url, '/');
		$site_url = parse_url($site_url, PHP_URL_HOST);
		echo '<h2 class="fade_out">'.esc_html($response->name).'</h2>';
		
		if (!$free_license) {
			echo '<p class="fade_out">Import '.absint($response->posts).' posts<span id="andPages"> and '.absint($response->pages).' pages</span> from <a href="'.esc_url($response->url).'" target="_blank" rel="noopener">'.esc_html($site_url).'</a>.</p>';
			echo '<p style="margin-bottom: 10px;"><em>Please note that Draft & Scheduled posts are not imported.</em></p>';
			//echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="includeDrafts" name="includeDrafts" value="1"> Include Draft &amp; Schedule posts</label> <a href="https://go.pipdig.co/open.php?id=bie-faq" target="_blank" rel="noopener" class="bie_info_icon"><span class="dashicons dashicons-editor-help"></span></a></div>';
			//echo '<div style="margin-bottom: 10px;" id="draftsFile">You can download your Blogger XML file <a href="https://www.blogger.com/feeds/'.$blogger_blog_id.'/archive?authuser=0" target="_blank" rel="noopener">here</a><br /><input type="file" id="draftsFileField" name="draftsFileField"></div>';
			echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="skipPages" name="skipPages" value="1"> Don\'t import pages.</label></div>';
			echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="skipComments" name="skipComments" value="1"> Don\'t import post comments</label> <a href="https://go.pipdig.co/open.php?id=bie-faq" target="_blank" rel="noopener" class="bie_info_icon"><span class="dashicons dashicons-editor-help"></span></a></div>';
			echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="skipImages" name="skipImages" value="1"> Don\'t import images</label> <a href="https://go.pipdig.co/open.php?id=bie-faq" target="_blank" rel="noopener" class="bie_info_icon"><span class="dashicons dashicons-editor-help"></span></a></div>';
			echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="skipAuthors" name="skipAuthors" value="1"> Don\'t import authors</label> <a href="https://go.pipdig.co/open.php?id=bie-faq" target="_blank" rel="noopener" class="bie_info_icon"><span class="dashicons dashicons-editor-help"></span></a></div>';
			echo '<div style="margin-bottom: 10px;"><label><input type="checkbox" id="convertFormatting" name="convertFormatting" value="1"> Convert to Gutenberg formatting</label> <a href="https://go.pipdig.co/open.php?id=bie-faq" target="_blank" rel="noopener" class="bie_info_icon"><span class="dashicons dashicons-editor-help"></span></a></div>';
		} else {
			echo '<p class="fade_out">Import up to 20 posts and pages</span> from <a href="'.esc_url($response->url).'" target="_blank" rel="noopener">'.esc_html($site_url).'</a>.</p>';
			echo '<p style="margin-bottom: 10px;">Want to import more content? Purchase an <a href="https://go.pipdig.co/open.php?id=bie-pro" target="_blank" rel="noopener">unlimited license</a> for unlimited inports.</p>';
			echo '<p style="margin-bottom: 10px;"><em>Please note that Draft & Scheduled posts are not imported.</em></p>';
		}
		echo '<div class="button button-primary fade_out" id="startImport" style="margin-top: 10px" data-total-posts="'.absint($response->posts).'"><span class="dashicons dashicons-controls-play" style="margin-top: 4px;"></span> Start import!</div> &nbsp;<a href="'.admin_url('tools.php?page=bie-importer').'" class="button fade_out" style="margin-top: 10px">Cancel</a>';
		wp_die();
	}
	
	wp_die();
});


add_action('wp_ajax_bie_progress_ajax', function() {

	check_ajax_referer('bie_ajax_nonce', 'sec');
	
	$page_query = '';
	if (!empty($_POST['page_token'])) {
		$page_query = '&page_token='.sanitize_text_field($_POST['page_token']);
	//} elseif (get_option('bie_page_token'.$blogger_blog_id)) {
		//$page_query = '&page_token='.get_option('bie_page_token'.$blogger_blog_id);
	} else {
		$page_query = '';
	}
	
	if (empty($_POST['blogger_blog_id'])) {
		echo '<p>Error: Please enter your Blogger blog ID.</p>';
		wp_die();
	}
	
	$skip_comments = '';
	if (isset($_POST['skip_comments']) && absint($_POST['skip_comments']) === 1) {
		$skip_comments = '&skip_comments';
	}
	
	$skip_images = '';
	if (isset($_POST['skip_images']) && absint($_POST['skip_images']) === 1) {
		$skip_images = '&skip_images';
	}
	
	$skip_authors = false;
	if (isset($_POST['skip_authors']) && absint($_POST['skip_authors']) === 1) {
		$skip_authors = true;
	}
	
	$convert_formatting = '';
	if (isset($_POST['convert_formatting']) && absint($_POST['convert_formatting']) === 1) {
		$convert_formatting = '&convert_formatting';
	}
	
	$blogger_blog_id = sanitize_text_field($_POST['blogger_blog_id']);
	
	$url = 'https://api.bloggerimporter.com/?blog_id='.$blogger_blog_id.'&query=posts'.$skip_comments.$skip_images.$convert_formatting.$page_query;
	$response = pipdig_blogger_get_response($url);
	
	if (!empty($_POST['posts_imported'])) {
		$x = absint($_POST['posts_imported']);
	} else {
		$x = 0;
	}
	
	if (isset($response->items) && is_array($response->items)) {
		
		global $wpdb;
		wp_suspend_cache_invalidation(true);
		wp_defer_term_counting(true);
		wp_defer_comment_counting(true);
		
		foreach ($response->items as $item) {
			
			// check if post was already imported
			/*
			$exists = (int) $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta m inner join $wpdb->posts p on p.ID = m.post_id where meta_key = 'blogger_id' and meta_value = '%s' and p.post_type = 'post' LIMIT 0, 1", $item->id) );
			
			if ($exists !== 0) {
				$x++;
				continue;
			}
			*/
			
			$exists = (int) $wpdb->get_var( 
				$wpdb->prepare('SELECT blogger_permalink FROM '.$wpdb->prefix.'bie_redirects WHERE blogger_permalink = %s', $item->permalink) 
			);
			
			if ($exists !== 0) {
				$x++;
				continue;
			}
			
			$post_id = wp_insert_post(array(
				'post_type' => 'post',
				'post_date' => $item->published,
				'post_content' => '',
				'post_title' => $item->title,
				'post_status' => 'publish',
				'ping_status' => 'closed',
				'post_name' => $item->slug,
				'tags_input' => property_exists($item, 'labels') ? $item->labels : '',
				/*
				'meta_input' => array(
					'blogger_id' => $item->id,
					'blogger_permalink' => $item->permalink,
				),
				*/
			));
			
			if (!is_wp_error($post_id) && $post_id) {
				
				// returns post content and also featured image ID
				$content = pipdig_blogger_process_content($post_id, $item->content, $item->published, $skip_images);
				
				$update_post = array(
					'ID' => $post_id,
					'post_content' => $content['content'],
				);
				
				if (!$skip_authors) {
					if ($item->author != 'Unknown') {
						$update_post['post_author'] = pipdig_blogger_process_author(sanitize_user($item->author_id), $item->author);
					}
				}
 
				$post_id = wp_update_post($update_post);
				
				if (!is_wp_error($post_id) && $post_id) {
					$x++;
					if (!$skip_comments && isset($item->comments)) {
						pipdig_bloggger_process_comments($post_id, $blogger_blog_id, $item->id, $item->comments);
					}
					// set the featured image
					if (!empty($content['featured_image_id'])) {
						update_post_meta($post_id, '_thumbnail_id', $content['featured_image_id']);
					}
					
					// store redirect
					$row = array(
						'post_id' => $post_id,
						'blogger_permalink' => $item->permalink,
						'blogger_post_id' => sanitize_text_field($item->id),
					);
					$formats = array(
						'%d',
						'%s',
						'%s',
					);
					$wpdb->insert($wpdb->prefix.'bie_redirects', $row, $formats);
					
				} else {
					wp_delete_post($post_id, true);
				}
				
			}
			
		}
		
		wp_suspend_cache_invalidation(false);
		wp_defer_term_counting(false);
		wp_defer_comment_counting(false);
		
	}
	
	
	if (!empty($response->nextPageToken)) {
		$next_page_token = $response->nextPageToken;
		//update_option('bie_page_token'.$blogger_blog_id, $next_page_token);
	} else {
		$next_page_token = '';
		
		if (absint($_POST['skip_pages']) !== 1) {
			
			$url = 'https://api.bloggerimporter.com/?blog_id='.$blogger_blog_id.'&query=pages'.$skip_images.$convert_formatting;
			$response = pipdig_blogger_get_response($url);
			
			if (isset($response->items) && is_array($response->items)) {
				
				global $wpdb;
				wp_suspend_cache_invalidation(true);
				
				foreach ($response->items as $item) {
					
					// check if page was already imported
					$exists = (int) $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta m inner join $wpdb->posts p on p.ID = m.post_id where meta_key = 'blogger_id' and meta_value = '%s' and p.post_type = 'page' LIMIT 0, 1", $item->id) );
					
					if ($exists !== 0) {
						continue;
					}
					
					$page_id = wp_insert_post(array(
						'post_type' => 'page',
						'post_date' => $item->published,
						'post_content' => '',
						'post_title' => $item->title,
						'post_status' => 'publish',
						'ping_status' => 'closed',
						'post_name' => $item->slug,
						/*
						'meta_input' => array(
							'blogger_id' => $item->id,
						),
						*/
					));
					
					if ($page_id) {
						
						// returns post content and also featured image ID
						$content = pipdig_blogger_process_content($page_id, $item->content, $item->published, $skip_images);
						
						$update_page = array(
							'ID' => $page_id,
							'post_content' => $content['content'],
						);
						
						if (!$skip_authors) {
							if ($item->author != 'Unknown') {
								$update_page['post_author'] = pipdig_blogger_process_author(sanitize_user($item->author_id), $item->author);
							}
						}
		 
						$page_id = wp_update_post($update_page);
						
					}
					
				}
				
				wp_suspend_cache_invalidation(false);
				
				//delete_option('bie_page_token'.$blogger_blog_id);
				
			}
			
		}
		
	}
	
	$output = array(
		'next_page' => $next_page_token,
		'posts_imported' => $x,
	);
	
	echo json_encode($output);
	
	wp_die();

});


function pipdig_bloggger_process_comments($post_id, $blogger_blog_id, $blogger_post_id, $total_comments) {
	
	$url = 'https://api.bloggerimporter.com/?blog_id='.$blogger_blog_id.'&query=comments&post_id='.$blogger_post_id;
	$response = pipdig_blogger_get_response($url);
	
	$comments_with_parent = array();
	
	if (isset($response->items) && is_array($response->items)) {
		
		foreach ($response->items as $item) {
			
			$content = htmlspecialchars_decode($item->content);
			
			$comment_id = wp_insert_comment(array(
				'comment_post_ID' => $post_id,
				'comment_author' => !empty($item->author) ? esc_html($item->author) : 'Anonymous',
				'comment_date' => $item->published,
				'comment_content' => strip_tags($content, '<abbr><acronym><b><blockquote><br><cite><code><del><em><q><strike><strong><ul>'),
				'comment_meta' => array(
					'blogger_id' => $item->id
				),
			));
			
			// check if comment is a reply, then assign to parent comment
			if (isset($item->inReplyTo->id)) {
				$comments_with_parent[] = array(
					'wp_id' => $comment_id,
					'blogger_id' => $item->id,
					'parent_blogger_id' => $item->inReplyTo->id,
				);
			}
			
		}
		
	}
	
	// next page if supplied. Surely 2 sweeps of 500 comments is enough?
	if (!empty($response->nextPageToken)) {
		
		$url = 'https://api.bloggerimporter.com/?blog_id='.$blogger_blog_id.'&query=comments&post_id='.$blogger_post_id.'&page_query='.$response->nextPageToken;
		$response = pipdig_blogger_get_response($url);
		
		if (isset($response->items) && is_array($response->items)) {
			
			$comments = array_reverse($response->items);
			
			foreach ($comments as $item) {
				
				$content = htmlspecialchars_decode($item->content);
				
				$comment_id = wp_insert_comment(array(
					'comment_post_ID' => $post_id,
					'comment_author' => !empty($item->author) ? esc_html($item->author) : 'Anonymous',
					'comment_date' => $item->published,
					'comment_content' => strip_tags($content, '<abbr><acronym><b><blockquote><br><cite><code><del><em><q><strike><strong><ul>'),
					'comment_meta' => array(
						'blogger_id' => $item->id
					),
				));
				
				// check if comment is a reply, then assign to parent comment
				if (isset($item->inReplyTo->id)) {
					$comments_with_parent[] = array(
						'wp_id' => $comment_id,
						'parent_blogger_id' => $item->inReplyTo->id,
					);
				}
				
			}
			
		}
		
	}
	
	// Now that the comments are imported, let's assign any parent-children
	if ($comments_with_parent) {
		foreach ($comments_with_parent as $comment) {
			
			$comment_query = new WP_Comment_Query();
			
			$comments = $comment_query->query(array(
				'meta_key' => 'blogger_id',
				'meta_value' => $comment['parent_blogger_id'],
			));
			
			if (isset($comments[0]->comment_ID)) {
				$parent_id = $comments[0]->comment_ID;
			} else {
				continue;
			}
			
			wp_update_comment(array(
				'comment_ID' => $comment['wp_id'],
				'comment_parent' => $parent_id,
			));
			
		}
	}
	
}

function pipdig_blogger_skip_image_sizes($sizes) {
	//unset($sizes['large']);
	return array();
}

function pipdig_blogger_process_content($post_id, $content, $post_date, $skip_images) {
	
	$content = htmlspecialchars_decode($content);
	
	// download images to media library
	if (!$skip_images) {
		$featured_image_id = false;
		preg_match_all('/<img [^>]*src="([^"]+blogspot\.com\/[^"]+)"[^>]*>/', $content, $found_images);
		if (!empty($found_images)) {
			$x = 0;
			foreach ($found_images[1] as $found_image) {
				
				preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $found_image, $matches);
				
				$file = array(
					'name' => wp_basename($matches[0]),
					'tmp_name' => download_url($found_image),
				);
				
				if (is_wp_error($file['tmp_name'])) {
					@unlink($file['tmp_name']);
					continue;
				}
				
				add_filter('intermediate_image_sizes_advanced', 'pipdig_blogger_skip_image_sizes'); // disable image sizes from generating, temporarily whilst uploading
				$image_id = media_handle_sideload($file, $post_id, $file['name'], array('post_date' => $post_date));
				remove_filter('intermediate_image_sizes_advanced', 'pipdig_blogger_skip_image_sizes'); // return image sizes to normal after
				
				if (!is_wp_error($image_id)) {
					
					$attachment = wp_get_attachment_image_src($image_id, 'large');
					if (empty($attachment[0])) {
						@unlink($file['tmp_name']);
						break;
					}
					$content = str_replace($found_image, $attachment[0], $content);
					
					// if this is the first image, include it in the return as the featured_image_id
					if ($x === 0) {
						
						$featured_image_id = $image_id;
						
						if (!get_option('bie_license') || get_option('bie_license') == 'free') { // skip after first image
							@unlink($file['tmp_name']);
							break;
						}
						
					}
					
					$x++;
					
				}
				
				@unlink($file['tmp_name']);
				
			}
		}
	}
	
	return array(
		'content' => $content,
		'featured_image_id' => $featured_image_id,
	);
	
}

function pipdig_blogger_process_author($username, $name) {
	
	// does user already exist?
	$user = get_user_by('login', $username);
	if ($user) {
		return $user->ID;
	}
	
	// Create new user
	$user_id = wp_insert_user(array(
		'user_login' => $username,
		'display_name' => $name,
		'nickname' => $name,
		'role' => 'author',
	));
	
	if (!is_wp_error($user_id)) {
		return $user_id;
	} else {
		// default to current user if error
		return get_current_user_id();
	}
	
}


function pipdig_blogger_get_response($url) {
	
	$url = add_query_arg( array(
		'plugin_v' => BIE_VER,
		'home_url' => home_url(),
		'license' => !empty(get_option('bie_license')) ? get_option('bie_license') : 'free',
	), $url);
	
	$body = wp_remote_retrieve_body(wp_remote_get($url, array('timeout' => 20)));
	
	if (!$body) {
		echo '<p>Error: Could not connect to Blogger. Please try again later.</p>';
		wp_die();
	}
	
	$response = json_decode($body);
	
	if (isset($response->message)) {
		echo '<p>'.strip_tags($response->message, '<a>').'</p>';
		wp_die();
	}
	
	if (isset($response->error->message)) {
		echo '<h3>Error message from Blogger: '.esc_html($response->error->message).'</p>';
		wp_die();
	}
	
	return $response;
	
}
