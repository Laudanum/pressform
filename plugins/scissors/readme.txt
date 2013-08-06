=== Plugin Name ===
Contributors: stephanreiter, samuelaguilera
Tags: admin, media, images, gallery, library, upload, resize, crop, watermark, rotate
Requires at least: 2.7
Tested up to: 2.8.6
Stable tag: 1.3.7

Scissors enhances WordPress' handling of images by introducing cropping, resizing, rotating, and watermarking functionality.

== Description ==

This plugin adds cropping, resizing, and rotating functionality to Wordpress' image upload and management dialogs. Scissors also allows automatic resizing of images when they are uploaded and supports automatic and manual watermarking of images. Additionally, images that are resized in the post editor are automatically resampled to the requested size using bilinear filtering when a post is saved, which improves the perceived image quality while reducing the amount of data transferred at the same time.

Please note that WordPress versions 2.9 and newer are not supported!

When the first version of Scissors was published in October 2008 it was available only in English and German. Since then translations into new languages have been contributed by the following kind individuals. Thank you for your time and initiative!

* Chinese (zh\_CN) - [tolingo translations](http://tolingo.com/)
* Danish (da\_DK) - [Georg S. Adamsen](http://wordpress.blogos.dk/2009/03/14/scissors/)
* Dutch (nl\_NL) - [Jens Swelson](http://www.spenk.nl/)
* Estonian (et\_ET) - [Fred Elkind](http://www.abhaldus.ee/am/)
* Finnish (fi\_FI) - [Taneli Selin](http://www.taneliselin.info/)
* French (fr\_FR) - [Jean-Michel Meyer](http://www.li-an.fr/blog/)
* Greek (el\_GR) - [Eleftherios Kosmas](http://elkosmas.gr/)
* Hungarian (hu\_HU) - [Lakatos Zsolt](http://djz.hu/)
* Italian (it\_IT) - [Gianni Diurno](http://gidibao.net/)
* Latvian (lv\_LV) - Eduards Marhelis
* Norwegian (nb\_NO, no\_NO) - John Myrstad
* Polish (pl\_PL) - Kamil Cichocki
* Portuguese (pt\_BR) - [Vitor Damiani](http://luzrefletida.com/)
* Romanian (ro\_RO) - [Marius Butuc](http://mariusbutuc.com/)
* Russian (ru\_RU) - [Sergey Shkoda](http://videodesigner.ru/)
* Spanish (es\_ES) - [Samuel Aguilera](http://www.samuelaguilera.com/)
* Swedish (sv\_SE) - Daniel Nyström
* Turkish (tr\_TR) - [Ömer Faruk](http://ramerta.com/)

== Installation ==

Scissors is available in the official plugin repository and can be installed directly from the WordPress administration interface. If you wish, however, to make a manual installation, follow these steps:

1. Extract the contents of the Scissors zip-archive you downloaded.
1. Upload the extracted `scissors` folder to your `/wp-content/plugins/` directory.
1. Activate the plugin in the 'Plugins' menu in WordPress.
1. Configure the plugin in WordPress' media settings.

Automatic resizing of the full-size image at upload-time can be configured in WordPress' media settings. You can specify a maximum width and a maximum height in pixels for this to take effect (a width/height of 0 disables image resizing in that particular dimension).

Watermarking can also be configured and enabled in WordPress' media settings: Supply an image that you want to be embedded into newly uploaded images, specify its alignment, size, and margin, and you're all set!

== Screenshots ==

1. Cropping a picture from within WordPress
2. Extended media settings for automatic resizing, cropping, and watermarking

== Changelog ==

**Version 1.3.7**

* Another attempt to make watermarking work across all WordPress platforms.

**Version 1.3.6**

* Fixed watermarking in WordPress MU. Make sure to re-select the watermark from the media library or alternatively remove the /files prefix of the watermark image path ...

**Version 1.3.5**

* Introduced check to disable the plugin on WordPress 2.9 and newer due to incompatibilities with the new integrated image editor I was invited to contribute to. Feel free to call this version the "farewell" edition of Scissors.

**Version 1.3.3**

* Better error messages, suggested by Nicholas Ward.
* Proper handling of predefined aspect ratios for medium and large images in the cropper.

**Version 1.3.2**

* Latvian translation by Eduards Marhelis added.

**Version 1.3.1**

* An error message is now displayed for images that have an unsupported type.

**Version 1.3**

* Polish translation by Kamil Cichocki added.
* Romanian translation by Marius Butuc added.
* Several other translations updated.

**Version 1.2.6**

* Added new configuration options: Cropping of medium and large images to exact dimensions.

**Version 1.2.5**

* Estonian translation by Fred Elkind added.
* Added new configuration options: Default aspect ratio and default relevance-enhanced image reduction state for cropping of pictures.

**Version 1.2.4**

* Updated Swedish translation by Daniel Nyström added.

**Version 1.2.3**

* Finnish translation by Taneli Selin added.
* Hungarian translation by Lakatos Zsolt added.

**Version 1.2.2**

* It's now possible to skip watermarking and/or resizing of full-size images at upload time for individual images.
* Added a link to the WordPress media settings page to Scissors' plugin list entry.

**Version 1.2.1**

* Chinese translation by tolingo translations added.
* Improved code for management of the Scissors configuration.
* Made image loading and saving more robust with regard to MIME types.

**Version 1.2**

* Turkish translation by Ömer Faruk added.
* Portuguese translation by Vitor Damiani added.
* Added checks to hide the Scissors panel for images of unsupported type.
* Added functionality to update the GUI when an image without a separate thumbnail is modified, e.g. because it's too small.
* From now on watermarking metadata is generated only if the full size image is watermarked (optimization).

**Version 1.1.9**

* Italian translation by Gianni Diurno added.
* Fixed 'imagerotate' related check.

**Version 1.1.8**

* Made Scissors more robust against corrupted image uploads that could cause division-by-zero errors in previous versions.
* Added a check to enable image rotation only on systems that have support for the correspondung GD library function 'imagerotate'.

**Version 1.1.5**

* Watermarking bug fix: Adding watermarks manually for a given image didn't work.
* Added checks to avoid problems with media for which no intermediate images were created at upload-time.

**Version 1.1.1**

* Watermarking bug fix: Sometimes enabling watermarking wouldn't work as expected.
* Dutch translation by Jens Swelson added.

**Version 1.1**

* Image resize overhauled: Image width and height are displayed and can be edited directly.
* Automatic image resize enhanced: Adaptive mode limits the maximal width of landscape images and the maximal height of portrait images.
* Selective watermarking introduced: Select the image sizes you wish to watermark in the configuration dialog and on a per-image basis, e.g. large image only.
* JCrop updated to 0.98: Fixes problems with IE8.
* Greek translation by Eleftherios Kosmas added.

**Version 1.0.2**

* Code improvements by Samuel Aguilera.
* Russian translation by Sergey Shkoda added.

**Version 1.0**

* Stable release with support for resizing, cropping, rotating, watermarking and automatic image resampling.
