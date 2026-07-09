=== Webxperthub Post Views & Reading Time ===
Contributors: iftiarhossain
Tags: post views, reading time, analytics, engagement, tracking, shortcodes
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track post views and accumulate total reading time from visitors. Display engagement metrics with shortcodes, admin dashboard, and post list columns.

== Description ==

Webxperthub Post Views & Reading Time is a lightweight WordPress plugin that automatically tracks how many times each post is viewed and measures the total reading time accumulated from all visitors. Perfect for bloggers, content creators, and site owners who want to understand their audience engagement at a glance.

**Key Features:**

* **View Tracking** - Automatically count the number of unique sessions viewing each post with intelligent bounce filtering
* **Reading Time Analytics** - Measure actual time visitors spend reading your posts
* **Frontend Shortcodes** - Display views and reading time anywhere using `[webxperthub_post_views]` and `[webxperthub_reading_time]` shortcodes
* **Admin Dashboard Widget** - Beautiful analytics widget showing total views, posts, and cumulative reading time
* **Post List Columns** - Display views and reading time directly in the post list for quick reference
* **Smart User Filtering** - Automatically excludes administrators, editors, authors, and contributors from view counts to track real visitor engagement
* **Session-based Counting** - Prevents artificially inflating counts by tracking views per session, not per page load
* **Security First** - Built with WordPress security standards including nonce verification, input sanitization, and proper output escaping
* **Lightweight & Efficient** - Minimal database footprint with optimized AJAX requests and prepared statements
* **Zero Configuration** - Works automatically after activation, no settings to configure
* **Responsive Design** - Dashboard widget and shortcodes look great on all screen sizes

== Installation ==

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for **"Webxperthub Post Views"**
4. Click **Install Now** and then **Activate**
5. Visit your **Dashboard** to see the new analytics widget
6. Go to **Posts** to see the new Views and Read Time columns

**Manual Installation:**

1. Download the plugin ZIP from WordPress.org
2. Upload the `webxperthub-post-views-reading-time` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. The plugin will start tracking immediately — no configuration required

== Shortcodes ==

Display post views and reading time on your pages and posts using these shortcodes:

**Display Post Views:**

`[webxperthub_post_views]` - Shows the total view count for the current post

`[webxperthub_post_views id="123"]` - Shows the view count for a specific post (replace 123 with post ID)

`[webxperthub_post_views label="yes"]` - Shows view count with "Views:" label

**Display Reading Time:**

`[webxperthub_reading_time]` - Shows the total reading time for the current post

`[webxperthub_reading_time id="123"]` - Shows the reading time for a specific post (replace 123 with post ID)

`[webxperthub_reading_time label="yes"]` - Shows reading time with "Read Time:" label

**Examples:**

* `[webxperthub_post_views label="yes"]` - Output: Views: 1,234
* `[webxperthub_reading_time label="yes"]` - Output: Read Time: 5m
* Display stats for a specific post: `[webxperthub_post_views id="42" label="yes"]`

These shortcodes can be used in page builders, widgets, or directly in post content to showcase your engagement metrics.

== Frequently Asked Questions ==

= Does this plugin track admin views? =

No. By design, administrators, editors, authors, and contributors are excluded from view counts. Only real visitor views are counted, giving you accurate engagement metrics.

= How accurate is the reading time? =

Reading time is measured from actual visitor activity on your posts. It begins after a 2-second initial delay (to filter out bounces) and stops when visitors leave the page or switch tabs. Times are capped at 30 minutes to exclude outliers.

= Will this slow down my site? =

No. The plugin uses efficient AJAX requests and minimal database queries. All tracking is done asynchronously with no noticeable impact on site performance.

= How is the data stored? =

All data is stored using WordPress standard post meta. No custom database tables are created, keeping your database clean and easy to manage.

= Can I use shortcodes with page builders? =

Yes! Both shortcodes work with any WordPress page builder. You can add them to any page, post, or custom post type where shortcodes are supported.

= Can I reset the view counts? =

Currently, view and reading time data cannot be reset through the plugin interface. You can manually remove the data through phpMyAdmin by deleting the post meta keys `_webxperthub_pvrt_view_count` and `_webxperthub_pvrt_reading_time`.

= What browsers are supported? =

The plugin works on all modern browsers including Chrome, Firefox, Safari, Edge, and Opera. JavaScript must be enabled for tracking to work.

= Does this plugin use cookies? =

The plugin uses browser sessionStorage (not cookies) to prevent double-counting views within the same session. SessionStorage is cleared automatically when the browser closes.

= Is my visitor data private? =

Yes. All data is stored locally in your WordPress database. No information is sent to external servers or third parties.

== How It Works ==

**View Tracking Process:**
1. When a visitor loads a post, JavaScript waits 2 seconds (bounce filter) before sending a tracking request
2. An AJAX request is sent to your WordPress backend
3. The view count for that post is incremented in the post meta
4. SessionStorage records that this post was counted in the current session to prevent duplicates

**Reading Time Tracking Process:**
1. JavaScript monitors how long a visitor spends on the page
2. Timing begins after the initial 2-second bounce delay
3. Timing ends when the visitor leaves the page or switches browser tabs
4. Only intervals of 3+ seconds are counted (minimum threshold)
5. A maximum of 30-minute intervals are recorded (filters outliers like tabs left open)
6. The accumulated time is added to the post's total reading time meta

**Dashboard Widget:**
The admin dashboard displays:
- Total number of published posts
- Sum of all views across all posts
- Cumulative reading time from all visitors (formatted as "X hours Y minutes")

== Changelog ==

= 1.1.0 =
* **NEW:** Added shortcodes for displaying post views and reading time
  - `[webxperthub_post_views]` — Display total view count for posts
  - `[webxperthub_reading_time]` — Display accumulated reading time for posts
  - Support for custom post IDs and optional labels
* Enhanced frontend display options with inline styling
* Improved shortcode flexibility for page builders and widgets
* Better documentation and usage examples

= 1.0.0 =
* Initial release
* View tracking with bounce filtering
* Reading time analytics with min/max thresholds
* Admin dashboard widget with engagement metrics
* Post list columns for views and reading time
* Smart user role filtering
* Security-first implementation with nonce verification

== Support ==

For support, feature requests, or bug reports, please visit the plugin's WordPress.org support forum. When posting, please include:

1. A clear description of your issue
2. Your WordPress version and PHP version
3. A list of your currently active plugins
4. Any error messages from your browser console (press F12)

== Credits ==

Developed by Iftiar Hossain — Webxperthub

== License ==

This plugin is licensed under the GPL-2.0-or-later license. You are free to use, modify, and distribute this plugin under the terms of the GNU General Public License.

For more information about the GPL, visit: https://www.gnu.org/licenses/gpl-2.0.html
