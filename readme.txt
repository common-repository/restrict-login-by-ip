=== Restrict Login By IP ===
Contributors: whiteshadow
Donate link: http://w-shadow.com/
Tags: security, ip, htaccess, login, restrict, deny, access
Requires at least: 2.6
Tested up to: 2.7
Stable tag: 1.0

Lets you specify IP addresses or hosts that users are allowed to login from.

== Description ==

"Restrict By IP" lets you specify IP addresses or hosts that users are allowed to login from. Only users that have the exact IP will be able to access the dashboard. Everyone else will get a "Forbidden" error when trying to log in or access an admin page directly. However, normal visitors won't be affected - everyone will still be able to read your posts browse the site.

When setting the allowed addresses, you can use full IPs (e.g. "12.34.56.7") or a range of IPs (e.g. "12.34"). If necessary, you can also use more advanced settings - define allowed subnet(s) via network/netmask, enter IPv6 addresses, etc. All the configuration is done via a very simple and intuitive* interface.

*\* Maybe.*

On the technical side, the plugin is basically a very simple frontend for setting mod_access/mod_authz_host directives in the .htaccess file. 

**Requirements :** PHP 5 or later, Apache, mod_access or mod_authz_host.

== Installation ==

1. Download and extract the ZIP file.
2. Upload `restrict-login-by-ip` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to *Users -> Allowed Login IPs* configure the plugin.

Note : If you accidentally lock yourself out of your site, edit your .htaccess file to remove everything that refers to "RestrictByIP". Also edit or delete the .htaccess file the plugin has created in `wp-admin`.