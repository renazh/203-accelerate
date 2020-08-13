=== Blogger Importer Extended ===
Contributors: pipdig
Tags: blogger, blogspot, importer
Requires at least: 4.9
Tested up to: 5.5
Requires PHP: 7.0
Stable tag: trunk
Donate link: https://wordpress.org/support/plugin/blogger-importer-extended/reviews/#new-post
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The only plugin you need to move from Blogger to WordPress. Import all your content and setup 301 redirects automatically.

== Description ==

**The only plugin you need to move from Blogger to WordPress**

Blogger Importer Extended is the easiest way to import your posts, pages, tags, comments, images and authors from **Blogger to WordPress**. After the import is complete, you can also enable 301 redirects from old Blogger links.

Import up to 20 blog posts/pages, along with all comments, featured images and post labels for free. If you have more than 30 blog posts, you may wish to purchase an [unlimited license](https://www.pipdig.co/shop/blogger-importer-plugin/). This allows you to import an unlimited amount of posts, pages, comments, images, labels and authors. You can also choose from a range of options to help with things like SEO and content formatting.

Blogger Importer Extended can:

* Import published posts
* Import pages
* Import tags/labels
* Import comments
* Import images (high res when available)
* Import authors
* Preserve all post/page links
* Automatically remove "?m=1" from old Blogger links
* Filter links from spam comments (good for SEO)
* Setup all 301 redirects from Blogger to WordPress
* Fix mixed-content (convert http to https)
* Convert post content formatting to match WP standards

"I have a huge blog with over 3,800 posts and I was skeptical that it would be imported, let alone be imported well. My skepticism was unfounded. Not only did it move all the posts to my new WordPress Blog but with all the images, all the type definitions, and all the links that I am aware of. I am way beyond pleased. I am thrilled. I will be using this plugin for many of our company’s blog transitions. Thank you for building this outstanding tool. You have my full appreciation." - [Review from terryminion](https://wordpress.org/support/topic/wow-im-blown-away/)

== Why is the free version limited to 20 posts? ==

The Google Blogger API has a limited quota per day. Offering 20 free imports should be enough for most personal blogs without hitting the Google API quota limit.  If you have more than 20 posts, or simply want to support the plugin, you may wish to purchase an [unlimited license](https://www.pipdig.co/shop/blogger-importer-plugin/).

== What is the difference between Free and Unlimited? ==

With the free version, you can import up to 20 blog posts/pages, along with all comments, featured images and post labels. If a post has more than one image, the first image will be downloaded to the media library and set as the featured image. The remaining images will still show in the post content, however they will be hosted on Google rather than WordPress.

With the unlimited version, all content is imported into WordPress (unless you select the option to exlude them). There are no limits on the number of posts, pages, comments, images or labels. The only thing which is not imported is draft or sheduled posts.

With both versions, you can setup all the required 301 redirects from Blogger to WordPress.

== What if I have 1,000,000 blog posts? ==

Wow, that's a lot of posts, nice work! If you want to import more than 20 posts, please consider purchasing an [unlimited license](https://www.pipdig.co/shop/blogger-importer-plugin/). This allows you to import as much as you like. The importer can handle any number of posts you have.

If you have a large blog, you may wish to consider using our full [Blogger to WordPress migration service](https://www.pipdig.co/shop/blogger-to-wordpress-migration/). This covers all aspects of the migration from start to finish.

== Can I use it on more than one site? ==

You can use the both free or unlimited version on all your sites. If you purchase an [unlimited license](https://www.pipdig.co/shop/blogger-importer-plugin/), you can use it for all your own projects.

== Privacy and GDPR ==

This plugin connects to your Blogger/Blogspot blog via the Google Blogger API. We do not store any personal information in this plugin or connected services.

== Installation ==

1. Install the plugin by going to the 'Plugins > Add New' section of your WordPress dashboard.
2. When the plugin is installed and active, go to the 'Settings > Blogger Importer' page.
3. Ths page will show you the 3 steps for migrating your site.

== Screenshots ==

1. Settings page
2. Importing
3. Success!

== Changelog ==

= 2.2.2 =
* Release date: 24 July 2020.
* Reduce delay time between import runs to 3 seconds.
* Bump min PHP version to 7.

= 2.2.1 =
* Release date: 13 June 2020.
* Fix issue which could cause a blank screen during import.

= 2.2.0 =
* Release date: 13 May 2020.
* Update 301 redirect logic.

= 2.1.1 =
* Release date: 14 April 2020.
* New settings page at  'Settings > Blogger Importer'.
* New option automatically redirect old links from Blogger.
* New option to generate a Blogger redirection XML template file.

= 2.0.0 =
* Release date: 20 March 2020.
* New simplified importing screen.
* Use new system to avoid chance of going over the Google Blogger API quota limit.
* Greatly improve efficiency when downloading images to the media library.
* Imported internal-links are converted to the new site/domain. I.e. links will be changed from old blogspot.com urls to the new site.
* Automatically set the Featured Image for all imported posts.
* New options to purchase a license for Pro features.
* Require PHP 5.6+

= 1.3.3 =
* Update GDPR section in plugin description.

= 1.3.2 =
* Don't strip span tags.

= 1.3.1 =
* Improvements on formatting conversion

= 1.2.2 =
* Fix for page comments

= 1.2.1 =
* Fix for unexpected timeout

= 1.2 =
* Fix for alert loop
* Workaround for imprecision in denormalized counters

= 1.1 =
* Fix for posts without slug