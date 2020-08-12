=== Order Receipt Print for WooCommerce Google Cloud Print ===	
	
Contributors: bizswoop
Tags: print, google cloud print, woocommerce, woocommerce print, order print, google cloud print order, automatic print, printing, automatic printing, cloud print, cloud printing, print locations, multiple printers, pos, restaurant print, restaurants print, restaurant printing, restaurants printing, take-out, take-out printing, take-out order printing, delivery, delivery printing, pickup order printing, pos, point of sale
Requires at least: 4.4
Requires PHP: 5.6
Tested up to: 5.4.2
Stable tag: 3.0.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily Add Support for Printing WooCommerce Orders with Google Cloud Print and Print to Anywhere in the World!
== Description ==

Easily Add Support for Printing WooCommerce Orders with Google Cloud Print. Connect your Google Cloud Print API Account and start printing WooCommerce orders manually or automatically after an order is placed. The process is simple even for non-technical people to setup. Setup unlimited number of printers on Google Cloud Print and print to multiple locations anywhere in the world the printer is physically located. Cloud printing is great for printing Customers orders at a Restaurant or Retail Store. Or for Warehouse printing to fulfillment stations. Enable printing for the entire WooCommerce store or assign WordPress user roles to specific Google Cloud printers and locations. You can customize the Customer and Order Print Templates to include your company logo and other company information. The Google Cloud Printing functionality is compatible with the Point of Sale POS (https://bizswoop.com/wp/pos)

Simple To set up WordPress to have access to Google Cloud Print, you will need to follow

= Main features: = [Learn More](https://www.bizswoop.com/wp/print)

*   Automatically or Manually Print WooCommerce Orders to Multiple Printer Locations Anywhere in the World!
*   Support Unlimitted Google Clould Printer Locations
*   Assign Printers to Select WordPress User Roles 
*   Supports Standard Printers Letter/A4 or Thermal Receipt Printers for Customer and Order Receipts
*   Supports a Customer Receipt Template & Order Receipt Template hiding Pricing and Customer Details 
*   [Compatible with Point of Sale POS WooCommerce](https://bizswoop.com/wp/pos)

= Custom Templates=

*   The Plugin supports Custom Print Templates [Learn More](https://www.bizswoop.com/product/templates/)

= Product Mapping Add-on=

*   The Plugin supports Product And Category Mapping to Print Locations [Learn More](https://www.bizswoop.com/product/product-mapping/)

= NEW: Compatibility Plugin Support for PRO Plugins=

*   Now the Plugin supports Pick-up|Take-Out|Curbside and Delivery fields for Location, Time and Date on Print Template [Learn More](https://www.bizswoop.com/wp/orderhours/delivery/)
*   Now the Plugin supports Cart and Checkout Add-ons fields on Print Template [Learn More](https://www.bizswoop.com/wp/productaddons/checkout/)


== Installation ==

1. Upload Print Google Cloud Print GCP WooCommerce to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Now select from the WooCommerce menu Printer Setting
4. Go to Setting Tab, Copy domain link information and Setup Google Cloud Print API Access
5. Simple Instructions to [Setup Google Cloud Print API] (https://bizswoop.com/wp/googlecloudprintapi)
6. Upload the Client Secret File from the Google Cloud Print API, Login to your Google Cloud Print account; it's now Setup & Ready 
7. Click the General Tab and Customize Printer Settings and Company Information
8. Click the Locations Tab and Select Printers from your Google Cloud Print Account. Orders will Start Printing
9. If you have any issues contact. https://bizswoop.com/support/


== Screenshots ==

1. General Settings Tab for Print Template & Automatic Printing

2. Settings Tab to Setup Google Cloud Print API Access

3. Google OAuth Page for the initial Login to Google Cloud Print Services

4. Google Cloud Print Active and Logged In

5. Sample of Customer Print Template, Customize Information and Reload to View Changes

6. Sample of Order Print Template, Customize Header and Reload to View Changes

7. Add a Printer, Configure Settings and Select Cloud Printer

8. Select Order Receipt Template Type

9. Location Tab Showing List of All Configured Printers


== Changelog ==

	= 3.0.13 =
	* WP 5.2 and WC 4.3.0 compatibility
	* Performance improvements
	* Bug fixes
	
	= 3.0.12 =
	* Print template modifications for Take-Out and Delivery
	* Performance improvements
	* Bug fixes
	
	= 3.0.11 =
	* Support for Checkout Add-ons plugin
        * Support for Take-Out and Delivery plugin
        * Performance improvements
	* Bug fixes
	
	= 3.0.5 =
	* Change default print templates to use general settings for Local Timezone for time ordered value
	* Bug fixes
	
	= 3.0.4 =
	* Bug fix to custom logo and headers displaying on default templates
	* Bug fix location saving for existing locations
	* Hide _reduced_stock on default templates for inventory management of products
	* Print API performance improvements
	
	
	= 3.0.3 =
	* Modifications to zPrint API for product & category mapping on locations
	* New template option "Include Order Cost" to hide or show order total amounts
	* Bug fixes and performance improvements
	* WP 5.1 and WC 3.5.5 compatibility
	
	= 3.0.2 =
	* Added wp hook to zPrint API for custom templates loading
	* Bug fixes to print order status
	
	= 3.0.1 =
	* Major release will not provide backwards compatibility to print templates
	* Customizations on header, footer & content for templates on each location
	* Add support for automatic print to all WooCommerce order status types
	* Add support for custom templates output type selection on templates
	* Bug fixes to print stability and scalability
	
	= 2.1.2 =
	* Tweak to Text Domain string to fix for localization support
	
	= 2.1.1 =
	* Bug fix to Domain path for localization support
	
	= 2.1.0 =
	* Added Location Mapping to Users based on User Roles
	* Added Localization Support for String Translations
	* Added New Layout Styling for Location Settings
	* Added New Shipping & Customer Settings for Default Templates 
	* Added Support for Custom Templates
	* Bug Fixes
	
	= 2.0.5 =
	* Added Shipping Method Section to Customer Order Receipt & Order Receipt Template
	* Added Support for Local Pickup Shipping Method
	* Bug Fixes
	
	= 2.0.4 =
	* Added Shipping Method to Customer Order Receipt Template
	* Bug Fixes
	
	= 2.0.3 =
	* Bug Fixes
	
	= 2.0.2 =
	* Major upgrade release 
	* Added Support for Custom Margins
	* Added Support for Page Orientation
	* Add New Template for Customer Order Receipt
	* Added Support for Custom Layout Size of Paper
	* Added Templates for Order Receipt, Customer Receipt & Customer Order Receipt with Letter, A4 & Thermal Printers
	* Fixes to Bugs
	
	= 1.0.13 =
	* Feature Support for Enabling & Disabling Automatic Printing
	* Added Plug-in Version & Automatic Printing Status Information to Log File
	
	= 1.0.12 =
	* Feature Support for Automatic Printing based on WooCommerce Statuses
	* Hide POS Label, if POS not Installed
	
	
	= 1.0.11 =
	* Bug Fixes for Deleting Locations
	
	= 1.0.10 =
	* Bug Fixes
	
	= 1.0.9 =
	* Optimizing for background print jobs
	* Bug Fixes
	
	= 1.0.7 =
	* Fixes for background print jobs
	* Template Bug Fixes
	
	= 1.0.5 =
	* Bug Fixes and WooCommerce Load Order
	
	= 1.0 =
	* First version released for Print Google Cloud Print WooCommerce.
