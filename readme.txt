=== Plugin Name ===
Contributors: yourpay
Tags: woocommerce, payment, gateway, yourpay gateway, credit card, payment request, transaction, betaling, udbetaling, kredit kort, kortbetaling, ecommerce, e-store, e-commerce
Requires at least: 4.0  & WooCommerce 2.5+
Tested up to: 5.6.2
Stable tag: 4.0.13
Requires PHP: 5.6
Version: 4.0.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add Yourpay payment options to your WordPress / WooCommerce website.

== Description ==

With the Yourpay plugin for WooCommerce, you can safely receive card payments from all Visa and MasterCard credit cards from all over the world. 
Our engineers have developed and thoroughly tested this plugin on our functional platform, to ensure that making payments on your webshop is safe and easy for both you and your customers.

= Features =

* Get overview of status of payment directly in WooCommerce
* Capture and refund payments directly in WooCommerce
* Possibility to install MobilePay and ViaBill directly in the payment window
* Quick and easy installation
* Free fraud prevention included 
* Can handle subscription payments

= Why choose Yourpay? =
* You get gateway and acquiring agreement in one package deal
* Free sign up
* No monthly fee
* No minimum contract period
* You pay the same transaction fee on all cards
* Transaction fee as low as 1.25% per transaction
* Down to one day payout
* Payouts made directly to your bank account 
* The best service on the market

== DANSK ==

Med Yourpays plugin til WooCommerce kan du sikkert tage imod kortbetaling på alle typer Visa og MasterCard kort fra hele verden. 
Vores udviklere og teknikere har udviklet og grundigt testet pluginnet i vores funktionelle platform, for at sikre at betaling på din webshop er sikkert og nemt for både dig og dine kunder.
 
= Features =

* Få overblik over status på dine betalinger direkte i WooCommerce 
* Opkræv og refundér betalinger direkte i WooCommerce 
* Mulighed for installation af MobilePay direkte i betalingsvinduet
* Hurtig og nem installation 
* Gratis fraud prevention mod snyd
* Mulighed for at oprette abonnementer på dine kunder 

= Hvorfor vælge Yourpay? =
* Du får gateway og indløsningsaftale i én samlet pakke 
* Gratis oprettelse 
* Gratis abonnement
* Ingen bindingsperiode
* Du betaler det samme transaktionsgebyr på alle betalinger og kort 
* Gebyr ned til 1,25% pr. transaktion
* Ned til én dags udbetaling
* Udbetaling direkte til din bankkonto
* Markedets bedste service

= Easy setup video =
https://www.youtube.com/watch?v=5IiZtSAkFw4&feature=youtu.be

= Data Collection =

With the Yourpay plugin active we will gather following information when you update information in: “Woocommerce -> Settings -> Payments -> Yourpay”:

* Plugin name
* Plugin Version
* CMS systems name
* CMS systems version
* Your domain
* Administrative email associated with the Website

It is further gathered  when updating or on activation of payment methods available through the plugin. 
This is done in order provide better support and service of our product. The mail will also be used if we need to contact you.

== Screenshots ==
	
1. YourPay betalingsvindue
2. Yourpay gennemført betaling

== Installation ==

Minimum Requirements
WooCommerce 2.1 or higher

= Installation =

1.  Log in to your **backend in WooCommerce**
2.  Click on **Plugins** -> **Add New** in the left side menu 
3.  Search for **“Yourpay”**
4.  Click on **“Install Now”** and then on **“Activate”**
5.  Go to **WooCommerce** -> **Settings** -> **Payments**
6.  **Activate Yourpay** and click on **“Setup”**
7.  Make sure **Yourpay is Enabled**
8.  Insert your **Yourpay token**, which you will find on your **Yourpay admin** page
9.  Adjust the settings 
10. Click on **“Save Changes”** (this step should be repeated following an update of the plugin)

<a href="https://www.yourpay.eu/support/how-to-use-yourpay-in-woocommerce/">Read our guide to setting up Yourpay in WooCommerce **here**</a>

= DANSK =

= Installation =

1.  Log in i din **WooCommerce backend**
2.  Klik på **Plugins** -> **Tilføj Nyt** i menuen i venstre side
3.  Søg på **”Yourpay”**
4.  Klik på **”Installér nu”** og herefter på **”Aktivér”**
5.  Gå til **WooCommerce** -> **Instillinger** -> **Betalinger**
6.  **Aktivér Yourpay** og klik på **”Opsæt”**
7.  Sikre dig at der er sat hak i **”Enable Yourpay”**
8.  Sæt dit **Yourpay token** ind. Dette finder du i din **Yourpay admin**
9.  Tilpas indstillingerne
10. Klik på **”Gem Ændringer”**. (Dette step skal gentages efter en opdatering af plugginet)

<a href="https://www.yourpay.io/support/saadan-bruger-du-yourpay-i-woocommerce-2/">Læs vores guide til opsætning af Yourpay i WooCommerce **her**</a>

== Frequently Asked Questions ==

How do I create sandbox accounts for testing?
Apply at http://www.yourpay.io
You will receive an Test-MerchantID which is needed for sandbox accounts

What is the monthly fee?
Our packages have no monthly fee. You can find all prices at https://www.yourpay.io
== Changelog ==

= 4.0.13 =
 * Fixed a number of minor issues

= 4.0.11 =
 * Fixed translation issue when requesting renewal of subscription.

= 4.0.8 =
 * Added experimental support for Woocommerce-blocks.

= 4.0.7 =
 * Optimized performance when using Viabill

= 4.0.6 =
 * PRoduct Sync Optimizations

= 4.0.5 =
 * Tested against newest version of WooCommerce

= 4.0.3 =
 * Hotfix of Internal Server Error with Objects and Array

= 4.0.2 =
 * Minor update on defined constants

= 4.0.1 =
 * Implementation of WP Cron sync & Autocomplete registration on callback

= 4.0.0 =
 * Major update.

= 3.2.21 =
 * Autocapture fix on MobilePay

= 3.2.18 =
 * CSS fix

= 3.2.17 =
 * Mobilepay issue & Wordpress version update

= 3.2.16 =
 * Updated Saving issue on yp_token

= 3.2.15 =
 * Updated to yp_token instead of yourpay_token

= 3.2.14 =
 * Updated usage of get_id and get_order_number
 * Updated response on Callbacks and confirmation messages

= 3.2.13 =
 * Deprecated Paybygroup
 * Fixed Uncaught Error
 * Fixed lack of response on Callbacks

= 3.2.12 =
 * Fix for: Callback, Notices

= 3.2.11 =
 * Hotfix, Notice message

= 3.2.10 =
 * Hotfix, Callback

= 3.2.9 =
 * Filepath Fix

= 3.2.8 =
 * Added current_user_can on every POST call, to ensure they can only be performed from expected users.

= 3.2.7 =
 * Added security on every POST call, to ensure they can only be performed from expected pages.

= 3.2.6 =
 * Added check of order data, to ensure data remains unchanged during payment.

= 3.2.5 =
 * Removal of Resurs
 * Eliminated option to activate non installed addons
 * Removed auto activation of Yourpay on plugin activation
 * Changed old curl calls
 * Removed hardcodet file paths

= 3.2.4 =
 * Changerequest by the Wordpress Team

= 3.2.3 =
 * Verified and updated the tested version of Wordpress and WooCommerce.

= 3.2.2 =
 * Bugfix for MobilePay order ID when using sequential number

= 3.2.1 =
 * Minor change in non-staic and static function

= 3.2.0 =
 * Major improvements in checkout flow, initial integration of Yourpay Express Checkout
 * Implementation of Yourpay Supporter feature

= 3.1.98 =
 * Minor fix for WooCommerce Subscriptions

= 3.1.97 =
 * Hotfix of an Viabill issue that caused Composite Products of not working correctly

= 3.1.96 =
 * SCA compliance

= 3.1.95 =
 * Compateble with WooCommerce Sequential Order Numbers

= 3.1.94 =
 * added missing yp_capture_stage value

= 3.1.93 =
 * Iserted is-numeric into viabill 

= 3.1.92 =
 * Partial capture

= 3.1.90 =
 * Minor bug fix for refunding not yet captured transactions.

= 3.1.89 =
 * Implementation of Point-Of-Sales PAX A920 from Yourpay

= 3.1.83 =
 * Optimized 3D Secure integration

= 3.1.82 =
 * Optimized Viabill integration

= 3.1.77 =
 * Added Pay By Group

= 3.1.71 =
 * Minor changes in MobilePay

= 3.0.71 =
 * Updated the Bulk capture functionality

= 3.0.70 =
 * Appended check for is_lugin_active in line 747

= 3.0.68 =
 * Minor bugs have been resolved, and if PHP is in an show_error deprecation issues have been resolved


= 3.0.67 =
 * Fixed bug in Automated Capture, due to users who have installed WooCommerce Order Status Manager is changing the Order-slug.


= 3.0.65 =
 * Fixed bug in Automated Capture on specific stages

= 3.0.63 =
 * Multilingual currencies in WooCommerce

= 3.0.56 = 
 * Minor Javascript fix


= 3.0.40 = 
 * Finally it happend! Viabill and Resurs Bank is now available for all WooCommerce merchants!

= 3.0.31 = 
 * Minor fix resolving some issues with Refund-options

= 3.0.31 = 
 * Added Refund-options in WooCommerce

= 3.0.29 = 
 * Added Yourpay Widget to Plugin

= 3.0.28 = 
 * Made the use of Verified by VISA and MasterCard SecureCode

= 3.0.27 = 
 * Minor Callback fix

= 3.0.26 = 
 * Implemented Yourpay Subscriptions

= 3.0.24 = 
 * Corrected small bugfix in 3.0.23

= 3.0.23 =
* Added templates, simplified the administration information, always possible to charge transactions through the Woocommerce backend - no userpassword needed.

= 3.0.22 =
* Added Auto Capture functionality


= 3.0.21 =
* Bugfix in poush card fee towards customers. Some merchants did always push it to the consumer, even they had disabled the function.


= 3.0.16 =
* Added possiblity to push card fee towards consumer

= 3.0.10 =
* Fixed a problem in inpage payments

= 3.0.10 =
* Fixed a couple of bugs happening due to change in Plugin name.

= 3.0.08 =
* Implementation of Saved Card, where all card details is placed in an PCI compliant enviroment

= 3.0.07 =
* Implementation of direct card entries.

== Upgrade Notice ==

= 3.0.08 =
Minor upgrade
