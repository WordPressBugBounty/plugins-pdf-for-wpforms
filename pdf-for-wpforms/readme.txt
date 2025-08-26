=== PDF for WPForms + Drag and Drop Template Builder ===
Contributors: addonsorg
Tags: form pdf, WPForms pdf,contact form pdf, pdf contact form, pdf WPForms
Requires at least: 2.0
Tested up to: 6.8
Stable tag: 6.3.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The plugin helps you create PDF for WPForms you can builder PDF template

== Description ==

[youtube https://youtu.be/56EqI6qa_eA]

**DEMO**: <https://pdf.add-ons.org/wpforms/>
**Download Pro Version**: <https://add-ons.org/plugin/pdf-for-wp-forms-pro/>
**Documents**: <https://pdf.add-ons.org/document/>

The PDF For WPForms add-on is an additional plugin that extends the functionality of WPForms by providing features for generating PDF files from form submissions and attaching PDF files to email notifications.

The plugin provides sufficient base elements and WPForms elements as well as developmental tools for users to build a completed PDF: Text/HTML, Table, QRCode, Barcode, Page Break, Divider, Image, Spacer,...and other elements.

== Drag and Drop Templates ==
The plugin offers a web-based drag & drop PDF template editor that lets you design templates and generate PDF documents from reusable templates without coding.
== Unlimited Use, No Restrictions ==
There are zero hard limits to the number of PDFs you can configure across all your forms or generate on your entries. 
== Additional Fonts ==
Match your branding and enhance the overall look and feel of PDFs by install your own fonts.
== Custom PDF Paper Size ==
Allows you to add custom different paper sizes for your PDF templates. 
== Shortcode and Merge Tags ==
You can generate a link or URL to PDFs using both shortcodes and merge tags so your users can download the PDF right after form submission.
== Conditional Logic ==
You can hide or show PDF content or create or uncreate pdf
== The Second PDF For a Form ==
It is possible to make a generation of multiple PDFs for the same form.
== Download PDF Files ==
You can view and download PDFs from the WPForms Entries page.
== Smart Real-Time Preview ==
View the instant preview of the customization for documents as you make them. See real-time previews of the last placed order with your desired custom appearance.
== Privacy and Security ==
Your form data is never sent to a third party to generate the PDFs and the documents are generated directly on your web server.
== Page Break  ==
We can use Page Break and new content will be move on next pages in PDF.
== Multilingual ==
A multitude of languages from across the globe can be displayed in PDFs, include complex scripts like Hebrew, Arabic, Thai, Japanese, Chinese, and Korean.


== PRO VERSION ==
* Table Customization
* QRcode Supports
* Barcode Supports
* Watermarks Supports
* Header Supports
* Footer Supports
* Conditional logics Supports
* 30-day money-back guarantee
* 1-year support

== External services ==

This plugin connects to the Dropbox API to store PDF files. Data is only sent to Dropbox when the user has configured and enabled the integration in the plugin settings. This service is provided by Dropbox, Inc.
terms of use <https://www.dropbox.com/terms>, privacy policy <https://www.dropbox.com/privacy>

	
== Frequently Asked Questions ==

= Why is the pdf file not attached to the email? =
You choose which PDF files are attached to which notifications in the PDF settings

= My fields are not getting filled, what is wrong? =
Make sure the mapping exists in the list of mappings and the field names match.

If you attached an updated PDF file and your mappings were associated with the old attachment ID then those mappings will be deleted and you will need to recreate them.

= Can I create 2,3 pdf files? =
You can create unlimited PDF files for 1-time submission form, depending on the power of your server.


== Installation ==
**Normal installation**

1. Download the pdf-for--wpforms.zip file to your computer.
1. Unzip the file.
1. Upload the `pdf-for-wpforms.zip` directory to your `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
Document include in plugin

== Changelog ==
= 6.3.0 =
- Fixed: Security update

= 6.2.1 =
- Added:  Compatible with WPForms 1.9
- Fixed:  Save settings with WPForms 1.9

= 6.2.0 =
- Added:  Installation wizard template

= 6.0.0 =
- Added: Font awesome ( [yeepdf_fontawesome unicode='f2b4'] use Unicode ) https://fontawesome.com/search?o=r&ic=free&s=regular&ip=classi

= 5.9.1 =
- Fixed: Edit table

= 5.9.0 =
- Fixed: Format current_time 

= 5.3.6 =
- Fixed: Show shortcode []
= 5.3.1 =
- Fixed: Broken Access Control

= 5.3.0 =
- Added: Rotate text

= 5.2.0 =
- Added: Save PDF to Dropbox
- Fixed: Background transparent
- Fixed: Custom size

= 5.1.0 =
- Added: Preview entry in editor

= 4.9.1 =
- Added: Hook yeepdf_format_br

= 4.9.0 =
- Fixed: Check class_exists('QRcode')

= 4.8.1 =
- Added: Use [yeepdf_download_wpforms] in email

= 4.6.0 =
- Fixed: Font-size 

= 4.5.0 =
- Add: Add hook save PDF

= 4.4.1 =
- Fixed: Shorcode [pdf_download]


= 4.4.0 =
- Fixed: Do not save PDFs on the server
- Added: Secure the folder for downloading PDFs

= 4.3.0 =
- Added: Random number shortcode

= 4.2.0 =
- Fixed: Default Font
- Added: Do not save PDFs on the server 

= 4.1.0 =
- Fixed: Save Barcode, QRCode

= 4.0.0 =
- Fixed: Save settings show name key number
- Fixed: Conditional logic

= 3.9.1 =
- Added: Button Re-generate PDF in entry

= 3.9.0 =
- Added: Width Height 100% or Auto or px

= 3.8.0 =
- Fixed: Conditional logic
- Fixed: Option Header Footer

= 3.7.0 =
- Added: Active Form

= 3.6.8 =
- Fixed: Style {order_summary}
- Added: hook

= 3.6.7 =
- Fixed: Current date languages

= 3.6.6 =
- Change: Style Break page

= 3.6.5 =
- Fixed: Signature field

= 3.5.2 =
- Fixed: UI
- Fixed: MacOS

= 3.5.0 =
- Added: Update performance
- Added: Element table

= 3.2.0 =
- Fixed: Compatible with WPForms Free Version

= 3.1.0=
- Fixed: Small error

= 3.0.0=
- Important: Big update do not update old the plugin to this version!

= 2.3.5 =
- Added: Resend PDF and create new pdf when resend Notifications

= 2.3.3 =
- Fixed: Compatible with WPForms Free Version

= 2.3.2 =
- Fixed: Do not send attachments

= 2.3.1 =
- Fixed: RTL

= 2.3.0 =
- Fixed: Shortcode in Header and Footer

= 2.2.9 =
- Added: 7 columns
- Added: Not contains Conditional logic

= 2.2.8 =
- Added: Check the PDF library requirements

= 2.2.7.2 =
- Fixed: Not send notifications

= 2.2.7.1 =
- Fixed: Not submitted successfully

= 2.2.7 =
- Added: Show mask conditional logic in backend
- Fixed: conditional logic preview

= 2.2.6 =
- Added: 5 + 6 columns
- Fixed: Backend Icon

= 2.2.2 =
- Big Update