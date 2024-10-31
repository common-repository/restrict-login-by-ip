<?php
/*
Plugin Name: Restrict Login By IP
Plugin URI: http://w-shadow.com/blog/2008/11/07/restrict-login-by-ip-a-wordpress-plugin/
Description: Lets you specify which IP addresses or hosts users are allowed to login from.    
Version: 1.0
Author: Janis Elsts
Author URI: http://w-shadow.com/blog/
*/

/*
Created by Janis Elsts (email : whiteshadow@w-shadow.com) 
It's GPL.
*/

//Load the "framework"
require 'shadow_plugin_framework.php';

class RestrictByIP extends RestrictIP_ShadowPluginFramework {
	
	var $htaccessTag = 'RestrictByIP';
	
	function __construct(){
		//Set some plugin-specific options
		$this->option_name = 'ws_restrict_by_ip';
		$this->defaults = array(
			'allowed_ips' => ''
		);
		$this->settings_link = 'admin.php?page=restrict_login_ips';
		
		$this->magic_hooks = true;
		//Call the default constructor
		parent::__construct(__FILE__);
	}
	
	function hook_admin_print_scripts(){
		if (strpos($_SERVER['REQUEST_URI'], 'page=restrict_login_ips') !== false ) {
			wp_enqueue_script('jquery');
		}
	}
	
	function hook_admin_footer(){
		//XXXXXXX debugging
		/*
		echo '<pre>', print_r($this->options, true);
		//print_r($_SERVER);
		echo '</pre>';
		//*/
	}
	
	function hook_admin_menu(){
		add_submenu_page('users.php', 'Allowed Login IPs', 'Allowed Login IPs', 'manage_options', 'restrict_login_ips', array(&$this, 'page_allowed_ips'));
	}
	
	function page_allowed_ips(){
		$current_ip = $_SERVER['REMOTE_ADDR'];
		$ip_parts = explode('.', $current_ip);
		
		//check if one of the required Apache modules is installed
		$module_error = '';
		/*
		if (function_exists('apache_get_modules')){
			$modules = apache_get_modules();
			if ( !in_array('mod_access', $modules) && !in_array('mod_authz_host', $modules) ){
				$module_error = "Your server doesn't have mod_access/mod_authz_host Apache module installed!
					This plugin will not work on your site.";
			}
		} else {
			$module_error = "It appears this is not an Apache webserver! Only Apache servers are supported.";
		}
		*/
		
		if (!empty($_POST['action']) && ($_POST['action']=='update') ){
			$this->options['allowed_ips'] = trim($_POST['allowed_ips']);
			$this->save_options();
			
			$helptext = 'Click "View .htaccess rules" below and add the appropriate lines to the file(s).';
			$show_helptext = false;
			//Update .htaccess file(s)
			if ($this->update_wp_htaccess()){
				$message = "<code>.htaccess</code> was modified successfuly.<br>";
			} else {
				$message = "Couldn't modify <code>.htaccess</code>!<br>";
				$show_helptext = true;
			}
			
			if ($this->update_wpadmin_htaccess()){
				$message .= "<code>/wp-admin/.htaccess</code> was modified successfuly.<br>";
			} else {
				$message .= "Couldn't modify <code>/wp-admin/.htaccess</code>!<br>";
				$show_helptext = true;
			}
			
			if ($show_helptext) $message .= $helptext;
			
			echo "<div id='message' class='updated fade'><p><strong>Settings saved.</strong><br>$message</p></div>";
		}
		
		if (!empty($module_error)){
			echo "<div id='error_message' class='error'><p><strong>$module_error</strong></p></div>";
		}
		
		$my_rules = $this->generate_htaccess_rules(); 
 		
		?>
<div class="wrap">
<h2>Allowed IPs</h2>
<p>You can specify an IP address, a range of IP addresses, or hosts that users are allowed to login from. 
Your current IP is <?php echo $_SERVER['REMOTE_ADDR']; ?>. It's probably a good idea to allow 
access at least from that address. Leave the box empty to allow login from any IP.
</p>

<p><a href='javascript:void(0)' onclick='jQuery("#ip_restriction_examples").toggle();'>View examples</a></p>
<div id='ip_restriction_examples' 
	style='display: none; border: 1px solid #e0e0e0; padding: 6px; margin: 2px; background-color: #ffffe0'> 

The syntax used here is basically equivalent to that of <em>Allow</em> directives for the Apache webserver. 
More details can be found <a href='http://httpd.apache.org/docs/2.0/mod/mod_access.html'>in the Apache 
documentation</a>.<br />

<ul style="list-style: disc; margin-left: 20px;">

<li>Allow login from a specific IP : 
<pre><?php echo $current_ip; ?> </pre></li>

 
<li>Allow login from a range of IPs that start with <?php echo $ip_parts[0],'.',$ip_parts[1],'.',$ip_parts[2]; ?> :
<pre><?php echo $ip_parts[0],'.',$ip_parts[1],'.',$ip_parts[2]; ?></pre>
</li>

<li>Allow login from a network/netmask pair :
<pre>10.1.0.0/255.255.0.0</pre></li>

<li>Allow login from a (partial) domain-name :
<pre>example.com
xyz.domain.com</pre></li> 

</ul>

</div>

<p><a href='javascript:void(0)' onclick='jQuery("#ip_restriction_htaccess").toggle();'>View .htaccess rules</a></p>
<div id='ip_restriction_htaccess' 
	style='display: none; border: 1px solid #e0e0e0; padding: 6px; margin: 2px; background-color: #ffffe0'> 

Make sure these rules are added to your <code>.htaccess</code> and <code>/wp-admin/.htaccess</code> files.
<br><br>

<strong>Root .htaccess file</strong><br><br>
<pre>
# BEGIN <?php echo $this->htaccessTag; ?>

&lt;Files wp-login.php&gt;
<?php echo implode("\n", $my_rules); ?>

&lt;/Files&gt;
# END <?php echo $this->htaccessTag; ?>
</pre>

<br><br>

<strong>/wp-admin/.htaccess file</strong><br><br>
<pre>
# BEGIN <?php echo $this->htaccessTag; ?>

<?php
echo implode("\n", $my_rules); 
?>

# END <?php echo $this->htaccessTag; ?>
</pre>    

</div>



<br />

<form name="cache_cleaner_options" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=restrict_login_ips">
    <input type='hidden' name='action' value='update' />
<textarea rows='20' cols='50' name='allowed_ips' id='allowed_ips'><?php
	echo htmlspecialchars($this->options['allowed_ips']); 
?></textarea>
<p class="submit">
	<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
</p>
</form>
		<?php
	}
	
	function is_wp27(){
		global $wp_version;
		//return version_compare($wp_version, '2.7', '>='); //doesn't work for beta versions. Gah.
		return function_exists('register_uninstall_hook');
	}
	
  /**
   * RestrictByIP::generate_htaccess_rules()
   *
   * @param string $allowed
   * @return array
   */
	function generate_htaccess_rules($allowed = ''){
		if (empty($allowed)) $allowed = $this->options['allowed_ips'];
		$allowed = trim($allowed);
		
		if (empty($allowed)) return array(); //Set no restrictions if the list is empty
		
		//Split by newline
		$allowed = preg_split('/[\r\n]+/', $allowed, -1, PREG_SPLIT_NO_EMPTY);
		
		$rules = array('Order Deny,Allow');
		//Add an Allow directive for every line in config
		foreach($allowed as $line){
			$rules[] = "Allow from ".$line;
		}
		$rules[] = "Deny from all";
		
		return $rules;
	}
	
	/*
	function hook_mod_rewrite_rules($rules){
		$my_rules = $this->generate_htaccess_rules();
		//Add nothing if no rules have been specified
		if (count($my_rules)<1) return $rules;
		
		$my_rules = implode("\n", $my_rules);
		//Make the rules apply only to wp-login.php
		$my_rules = "<Files wp-login.php>\n".$my_rules."\n</Files>"; 
		//Wrap the rules with out tag 
		$my_rules = "\n# BEGIN ".$this->htaccessTag."\n".$my_rules."\n# END ".$this->htaccessTag."\n";
		
		return $my_rules.$rules;
	}
	//*/
	
	function update_wp_htaccess(){
		global $wp_rewrite;
		//$wp_rewrite->flush_rules();
		
		$rules = $this->generate_htaccess_rules();
		//Make the rules apply only to .htaccess
		array_unshift($rules, '<Files wp-login.php>');
		$rules[] = '</Files>';
		//Use the internal WP function to modify .htaccess
		return insert_with_markers(ABSPATH.'/.htaccess', $this->htaccessTag, $rules);
	}
	
	function update_wpadmin_htaccess(){
		$rules = $this->generate_htaccess_rules();
		//Use the internal WP function to modify .htaccess
		return insert_with_markers(ABSPATH.'/wp-admin/.htaccess', $this->htaccessTag, $rules);
	}
	
	function activate(){
		//If any rules were previously defined they should be again put into effect
		$this->update_wp_htaccess();
		$this->update_wpadmin_htaccess();
	}
	
	function deactivate(){
		//Remove .htaccess rules when the plugin is deactivated
		$this->remove_htaccess_rules(ABSPATH.'/.htaccess');
		$this->remove_htaccess_rules(ABSPATH.'/wp-admin/.htaccess');
	}
	
  /**
   * RestrictByIP::remove_htaccess_rules()
   * Attempts to remove any .htaccess rules generated by this plugin
   *
   * @return void
   */
	function remove_htaccess_rules($filename){
		if (!file_exists( $filename ) || !is_writeable( $filename ) ) {
			return false;
		}
		//Get current contents
		$contents = file_get_contents($filename);
		//Replace the plugin's rules
		$regexp = '/[\r\n\s]*#+\s*BEGIN\s+'.$this->htaccessTag.'.*?#+\s*END\s+'.$this->htaccessTag.'.*?$[\r\n]*/s';
		$contents = preg_replace($regexp, "\n", $contents);
		
		if (trim($contents) == ''){
			//If the file ends up empty, delete it
			return unlink($filename);
		} else {
			//Write out the modified contents
			return file_put_contents($filename, $contents)!==false;
		}
	}
	
} //class

if ( is_admin() )
	$restric_by_ip = new RestrictByIP();

?>