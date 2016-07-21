=== wpDataTables Lite ===
Contributors: wpDataTables
Author URI: http://tms-plugins.com/
Plugin URI: http://wpdatatables.com/
Tags: tables, wpdatatables, tables from excel, tables from CSV, datatables
Requires at least: 3.0.1
Tested up to: 4.5.2
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows you to create interactive sortable tables on your site based on a number of input sources - Excel, CSV, XML, JSON, PHP array.

== Description ==

wpDataTables Lite is a basic version of a well-known premium table creator plugin. While some of premium features are cut, wpDataTables is still quite a handy tool which would allow you to quickly create interactive tables from a number of sources:

* Excel - [Video tutorial](http://wpdatatables.com/video-course/creating-wpdatatables-from-existing-data-sources/creating-wpdatatables-from-excel-video/) - [Text documentation](http://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-excel/)
* CSV - [Video tutorial](http://wpdatatables.com/video-course/creating-wpdatatables-from-existing-data-sources/creating-wpdatatables-from-csv-video/) - [Text documentation](http://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-csv/)
* JSON - [Video tutorial](http://wpdatatables.com/video-course/creating-wpdatatables-from-existing-data-sources/creating-wpdatatables-from-json-input-video/) - [Text documentation](http://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-json-input/)
* XML - [Video tutorial](http://wpdatatables.com/video-course/creating-wpdatatables-from-existing-data-sources/creating-wpdatatables-from-xml/) - [Text documentation](http://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-xml/)
* Serialized PHP array - [Video tutorial](http://wpdatatables.com/video-course/creating-wpdatatables-from-existing-data-sources/creating-wpdatatables-from-serialized-php-arrays/) - [Text documentation](http://wpdatatables.com/documentation/creating-wpdatatables/creating-wpdatatables-from-serialized-php-array/)

Tables are created in several very basic steps:

1. You prepare the data source (for example the CSV file)
2. You create a new wpDataTable in the WP-admin panel and upload your file
3. You optionally configure the columns of the table (rename, reorder, hide, change colors)
4. Paste the generated shortcode in your post or page (you can also use the TinyMCE button that plugin adds)

All tables will become sortable and will have pagination by default.
Additionally, each table will have a search bar, and can have "Copy to Clipboard", "Export to CSV", "Export to PDF", "Export to XLS" functions.

Following column types are supported (most column types, except the images, have its own sorting rules):

* String - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/string-columns/)
* Integer - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/integer-columns/)
* Float - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/float-columns/)
* Date - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/date-columns/)
* Image - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/image-columns/)
* URL link - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/url-link-columns/)
* E-mail link - [Video tutorial](http://wpdatatables.com/video-course/column-types-and-features/column-types/) - [Text documentation](http://wpdatatables.com/documentation/column-features/e-mail-link-columns/)

Please note some limitations of the Lite version:

1. Plugin will allow only tables up to 150 rows.
2. MySQL-query based tables supprt is not included.
3. Server-side processing for large tables is not included.
4. Responsive mode for the tables is not included.
5. Front-end editing is not included.
6. Google Charts and HighCharts are not included.
7. Table Constructor Wizard (step-by-step table generator) is not included.
8. Access to our premium support system is not included.

You can get all of these features by purchasing the full version on plugin's site.

Please note that plugin requires PHP 5.4 or newer!

== Installation ==

Installation of the plugin is really simple.

1. Upload the extracted plugin folder to the `/wp-content/plugins/` directory of your WordPress installation, or upload it directly from your plugins section in the WordPress admin panel.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it!

== Frequently Asked Questions ==

= I added a table but see no sorting, filtering or pagination =

Usually this happens when PHP version older then 5.4 is installed. Please first check this, and upgrade to PHP 5.4 - 5.6 if that's the issue.

= How to hide “Showing X of X entries” in pagination? =

The easiest way to do this is to put this CSS block somewhere in your theme’s CSS file, or in inline CSS:
div.dataTables_info { display: none !important; }

If you wish to apply this code to all of your wpDataTables tables, you can put it in “Custom wpDataTables CSS” text area on wpDataTables Settings page,
or if you wish to apply it only on specific pages, you can put it in between style tags in for example html editor bellow tables short codes. E.g.

<style>
div.dataTables_info { display: none !important; }
</style>

= How to disable opening links in a popup? =

File that is responsible for rendering link columns is /source/class.link.wpdatacolumn.php
By opening that file you can see code for creating html anchor element with attribute “target=’_blank'”.
By changing ‘_blank’ value to ‘_self’ will cause link to open in same page instead of new window.

= How to hide “Show X entries” block from pagination? =

The easiest way to do this is to put this CSS block somewhere in your theme’s CSS file, or in inline CSS:
div.dataTables_length { display: none !important; }

If you wish to apply this code to all of your wpDataTables tables, you can put it in “Custom wpDataTables CSS” text area on wpDataTables Settings page,
or if you wish to apply it only on specific pages, you can put it in between style tags in for example html editor bellow tables short codes. E.g.

<style>
div.dataTables_length { display: none !important; }
</style>

= Adding text before/after column values without affecting sorting =

In wpDataTables table edit page in section ” Optional column setup”, for every table column there are text fields “Display text before” and “Display text after”.
Values from those text fields will be used for adding text before or/and after every cell content in a column.
This feature uses css for displaying entered text, therefore sorting of the columns will not be affected.

= How to change display date format of a date column? =

Display date format of a date column can be changed in wpDataTables Settings page from “Date format” drop-down menu.

= How to change thousand and decimal separators for number columns? =

This can be changed from “Number format” drop-down menu in the wpDataTables Settings page.

== Screenshots ==

1. Front-end table preview.
2. Back-end table editor preview.
3. Table preview from the back-end
4. Table browser example

== Changelog ==

= 1.1 =
* Migrated Table Tools to use HTML5 instead of Adobe Flash
* Advanced settings for configuring Table Tools (individually per button)
* Wide tables can be configured to be horizontally scrollable 
* Upgraded used libraries
* Compatibility with WP 4.5.2 approved
* Numerous bugfixes

= 1.0 =
* Launch of the Lite version

