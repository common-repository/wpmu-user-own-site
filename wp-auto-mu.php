<?php
/*
Plugin Name: WPMU User own site
Description: Plugin allows to create site on user registration in WPMU environment.
Version: 1.0.0
Author: gavrilov.web
Author URI: https://ua.linkedin.com/in/evgengavrilov
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
/*  Copyright 2015  gavrilov.web (email: gavrilov.web@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class MyGeweb_WPMU {
	
	var $option_page_slug;
	
    function MyGeweb_WPMU(){
		add_action('init', array( $this, 'add_site' ) );
		
		if(!is_admin()):
            add_action( 'wp_enqueue_scripts', array($this, 'mygewebmu_enqueue_media') );
		else:
			add_action('admin_menu', array($this, 'mygewebmu_add_page') );
		endif;
		
		add_shortcode('display_register_form', array($this, 'display_register_form_shortcode'));
    }
	
	function mygewebmu_enqueue_media(){
		wp_register_script('recaptcha-js', 'https://www.google.com/recaptcha/api.js');
        wp_enqueue_script('recaptcha-js');
	}
	
	function mygewebmu_add_page() {
		$this->option_page_slug = add_options_page('MyGewebMU', 'MyGewebMU', 'manage_network', 'mygewebmu', array($this, 'mygewebmu_option_page'));
		add_action('load-'.$this->option_page_slug, array($this, 'mygewebmu_add_help_tab') );
	}
	
	function mygewebmu_add_help_tab(){
		$screen = get_current_screen();
		if( $screen->id != $this->option_page_slug ) return;
		$admin_widget_url = admin_url('widgets.php');
		$screen->add_help_tab( array(
										'id'	=> 'mygewebmu_php_code',
										'title'	=> __('How to use'),
										'content'	=> '<pre><strong>PHP Code: </strong>&lt;?php if(function_exists(\'geweb_display_register_form\')) geweb_display_register_form(); ?&gt;<hr /><strong>Shortcode: </strong>[display_register_form]<hr /><strong>Widget: </strong><a href="' . $admin_widget_url . '">visit widget page</a></pre>',
									)
							  );
		$screen->add_help_tab( array(
										'id'	=> 'mygewebmu_recaptcha',
										'title'	=> __('Recaptcha'),
										'content'	=> '<p><a href="https://www.google.com/recaptcha/" target="_blank">Generate recaptcha</a></p>',
									)
							  );
		
	}
	
	function mygewebmu_option_page(){
		$re_sitekey = get_option('re_sitekey');
		$re_secret = get_option('re_secret');
		$re_response = get_option('re_response');
		if(!$re_response) $re_response = wp_generate_password( 12, false );
		?>
		<div class="wrap">
			<h1>Settings</h1>
			<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<fieldset>
					<h3 class="title">Recaptcha:</h3>
					<table class="form-table">
						<tr>
							<th><label for="re_sitekey">Recaptcha sitekey</label></th>
							<td><input type="text" name="re_sitekey" id="re_sitekey" value="<?php echo $re_sitekey; ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th><label for="re_sitekey">Recaptcha secret</label></th>
							<td><input type="text" name="re_secret" id="re_secret" value="<?php echo $re_secret; ?>" class="regular-text" /></td>
						</tr>
						<tr>
							<th><label for="re_sitekey">Recaptcha response</label></th>
							<td><input type="text" name="re_response" id="re_response" value="<?php echo $re_response; ?>" class="regular-text" /></td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save') ?>" />
					</p>
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="re_sitekey,re_secret,re_response" />
				</fieldset>
			</form>
		</div>
		<div class="clear"></div>
		<?php
	}
	
	public function display_register_form($type = 'default'){
		if ( is_main_site($blog_id) ) {
			require apply_filters('geweb-mu-register-path', dirname( __FILE__ ) . '/geweb-mu/register.php', $type);
		}
	}
	
	function display_register_form_shortcode(){
		ob_start();
		$this->display_register_form('shortcode');
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	
	function add_site() {
		if ( is_multisite() ) {
			
			if ( isset($_POST['action']) && 'gewev_add_site' == $_POST['action'] ) {
				check_admin_referer( 'geweb-add-blog', '_wpnonce_add-blog' );
			
				if ( ! is_array( $_POST['blog'] ) ) wp_die( __( 'Can&#8217;t create an empty site.' ) );
				
				if( $curl = curl_init() ) {
					curl_setopt($curl, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, 'secret=' . get_option('re_secret') . '&response=' . $_POST['g-recaptcha-response']);
					$response = json_decode(curl_exec($curl), true);
					if(isset($response['error-codes']) and count($response['error-codes'])) wp_die( __( 'Use recaptcha' ) );
					curl_close($curl);
				}
				
				$blog = $_POST['blog'];
				$domain = '';
				if ( preg_match( '|^([a-zA-Z0-9-])+$|', $blog['domain'] ) ) $domain = strtolower( $blog['domain'] );
			
				if ( ! is_subdomain_install() ) {
					$subdirectory_reserved_names = apply_filters( 'subdirectory_reserved_names', array( 'page', 'comments', 'blog', 'files', 'feed' ) );
					if ( in_array( $domain, $subdirectory_reserved_names ) ) wp_die( sprintf( __('The following words are reserved for use by WordPress functions and cannot be used as blog names: <code>%s</code>' ), implode( '</code>, <code>', $subdirectory_reserved_names ) ) );
				}
			
				$title = $blog['title'];
			
				if ( empty( $domain ) ) wp_die( __( 'Missing or invalid site address.' ) );
				if ( isset( $blog['email'] ) && '' === trim( $blog['email'] ) ) wp_die( __( 'Missing email address.' ) );
			
				$email = sanitize_email( $blog['email'] );
				if ( ! is_email( $email ) ) wp_die( __( 'Invalid email address.' ) );
				
				if ( !isset( $current_site ) ) {
					$current_site = get_current_site();
				}
				
				if ( is_subdomain_install() ) {
					$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
					$path      = $current_site->path;
				} else {
					$newdomain = $current_site->domain;
					$path      = $current_site->path . $domain . '/';
				}
			
				$password = 'N/A';
				$user_id = email_exists($email);
				if ( !$user_id ) {
					$password = wp_generate_password( 12, false );
					$user_id = wpmu_create_user( $domain, $password, $email );
					if ( false === $user_id ) wp_die( __( 'There was an error creating the user.' ) );
					else wp_new_user_notification( $user_id, null, 'both' );
				}
				
				global $wpdb;
				$wpdb->hide_errors();
				$id = wpmu_create_blog( $newdomain, $path, $title, $user_id , array( 'public' => 1 ), $current_site->id );
				$wpdb->show_errors();
				if ( ! is_wp_error( $id ) ) {
					if ( ! is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) ) {
						update_user_option( $user_id, 'primary_blog', $id, true );
					}
			
					$content_mail = sprintf(
						/* translators: 1: user login, 2: site url, 3: site name/title */
						__( 'New site created by %1$s
							Address: %2$s
							Name: %3$s' ),
						$current_user->user_login,
						get_site_url( $id ),
						wp_unslash( $title )
					);
					wp_mail( get_site_option('admin_email'), sprintf( __( '[%s] New Site Created' ), $current_site->site_name ), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
					wp_redirect( get_site_url( $id ) );
					exit;
				} else {
					wp_die( $id->get_error_message() );
				}
			}
		}
	}
}

if( is_multisite() ):
	$mygeweb_wpmu = new MyGeweb_WPMU();
	
	function geweb_display_register_form(){
		MyGeweb_WPMU::display_register_form();
	}
	
	/*WIDGET CLASS*/
	class Widget_MyGeweb_WPMU extends WP_Widget {

		function __construct() {
			$widget_ops = array( 'classname' => 'widget-mygeweb-wpmu', 'description' => __( 'Display new site registration form' ) );
			parent::__construct( 'widget-mygeweb-wpmu', __( 'MyGeweb WPMU' ), $widget_ops );
			$this->alt_option_name = 'widget-mygeweb-wpmu';
		}
	
		function widget( $args, $instance ) {
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			MyGeweb_WPMU::display_register_form('widget');
			echo $after_widget;
		}
	
		function update( $new_instance, $old_instance ) {
			$instance           = $old_instance;
			$instance['title']  = strip_tags( $new_instance['title'] );
			return $instance;
		}
	
		function form( $instance ) {
			$title	= isset( $instance['title'] )  ? esc_attr( $instance['title'] ) : '';
			?><p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php __( 'Title:' ); ?></label><input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p><?php
		}
	}
	add_action( 'widgets_init', create_function( '', 'return register_widget( "Widget_MyGeweb_WPMU" );' ) );
	/*WIDGET CLASS*/
endif;