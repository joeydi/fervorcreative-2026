=== ClearOpt - GDPR/CCPA Cookie Consent Banner  ===

Author: ClearOpt
Contributors: rm2773
Tags: cookie consent, cookie banner, consent management, gdpr, ccpa
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.17
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight cookie consent for WordPress with a customizable banner, cookie scanning, script blocking, Google Consent Mode v2, and GDPR/CCPA support.

== Description ==

Why ClearOpt?

ClearOpt was designed to provide a clean, lightweight approach to cookie consent without unnecessary complexity.

Instead of requiring extensive configuration, ClearOpt focuses on the features most WordPress websites need:

* Control when analytics technologies load based on configurable consent defaults.
* Control when marketing technologies load based on configurable consent defaults.
* Google Consent Mode v2 support synchronized with Analytics and Marketing consent preferences.
* Simple integration with Google Tag Manager, Microsoft Clarity, and Meta Pixel.
* Attractive, responsive consent banner and settings modal.
* Lightweight and easy to configure.
* Optional Global Privacy Control (GPC) support.
* Optional ClearOpt Cloud connection for manual homepage cookie scanning.

ClearOpt helps WordPress websites manage how analytics and marketing cookies are used.

Features include:

* Cookie consent banner
* Cookie preference modal
* Google Tag Manager integration
* Google Consent Mode v2 support
* Microsoft Clarity integration
* Meta Pixel integration
* Analytics and marketing consent categories
* Multiple banner themes
* Accent color customization
* Lightweight and easy to configure
* Global Privacy Control (GPC) support
* Optional ClearOpt Cloud connection
* Manual homepage cookie scanning

== Installation ==

1. Upload the ClearOpt plugin through the WordPress Plugins screen, or upload the plugin files to the `/wp-content/plugins/clearopt-cookie-consent/` directory.
2. Activate ClearOpt through the Plugins screen in WordPress.
3. Go to Settings > ClearOpt to configure the consent banner and cookie preferences.
4. Configure any supported integrations, such as Google Tag Manager, Microsoft Clarity, or Meta Pixel.
5. Save your settings and ClearOpt is ready to use.

== Frequently Asked Questions ==

= Does ClearOpt support Google Consent Mode v2? =

Yes. ClearOpt supports Google Consent Mode v2 and synchronizes visitor Analytics and Marketing preferences with Google's analytics_storage, ad_storage, ad_user_data, and ad_personalization consent signals. ClearOpt also respects Global Privacy Control (GPC) when configured to do so.

= Does ClearOpt support Google Tag Manager? =

Yes. ClearOpt includes a Google Tag Manager integration that can load your GTM container according to your configured analytics consent preferences.

= Does ClearOpt support Meta Pixel and Microsoft Clarity? =

Yes. ClearOpt includes built-in integrations for Meta Pixel and Microsoft Clarity. Enter the appropriate IDs in the ClearOpt settings to enable these integrations.

= Can visitors change their cookie preferences after making a choice? =

Yes. Add a link to `#privacy-settings` anywhere on your website to allow visitors to reopen the ClearOpt settings modal and update their preferences.

== External services ==

= ClearOpt Cloud =

ClearOpt can optionally connect to ClearOpt Cloud to provide manual website cookie scanning.

ClearOpt Cloud is not contacted unless a site administrator explicitly chooses to connect the WordPress site using the "Connect to ClearOpt Cloud" button.

When a site is connected, ClearOpt sends the site's name, homepage URL, and a randomly generated installation identifier to `api.clearopt.cloud`. ClearOpt Cloud returns a site-specific authentication credential that is stored in the WordPress database and used for authenticated communication with the service.

When a site administrator manually selects "Scan Site", ClearOpt Cloud uses the previously registered homepage URL to perform a browser-based scan of that public homepage. The scan may record cookies observed during the scan, including cookie names, domains, paths, security attributes, SameSite settings, expiration information, the total number of cookies found, and the time of the scan.

Scans are initiated manually by the site administrator. The free ClearOpt plugin does not automatically or periodically scan the website.

Disconnecting the site revokes the site's ClearOpt Cloud authentication credential and removes the locally stored connection and scan-status information from WordPress.

ClearOpt Cloud is provided by ClearOpt.

ClearOpt Privacy Policy:
https://clearopt.org/privacy-policy/

ClearOpt Terms:
https://clearopt.org/terms/

= Google Tag Manager =

ClearOpt can optionally load Google Tag Manager when a Google Tag Manager container ID is configured by the site administrator and the Analytics consent category is active according to the site's configured consent settings or visitor preferences.

When Google Tag Manager is loaded, the configured container ID is included in a request to Google's `googletagmanager.com` service. Google Tag Manager may then load and execute tags configured by the site administrator. The data collected or transmitted by those tags depends on the services and configuration used within that Google Tag Manager container.

Google Tag Manager is provided by Google LLC.

Google Tag Manager Terms of Service:
https://marketingplatform.google.com/about/analytics/tag-manager/use-policy/

Google Privacy Policy:
https://policies.google.com/privacy

= Microsoft Clarity =

ClearOpt can optionally load Microsoft Clarity when a Clarity project ID is configured by the site administrator and the Analytics consent category is active according to the site's configured consent settings or visitor preferences.

When Microsoft Clarity is loaded, the configured project ID is included in a request to Microsoft's `clarity.ms` service. Clarity may collect behavioral analytics data such as page interactions, session activity, clicks, scrolling, and related usage information according to the site's Microsoft Clarity configuration.

Microsoft Clarity is provided by Microsoft Corporation.

Microsoft Clarity Terms:
https://clarity.microsoft.com/terms

Microsoft Clarity Privacy Information:
https://clarity.microsoft.com/privacy

= Meta Pixel =

ClearOpt can optionally load Meta Pixel when a Meta Pixel ID is configured by the site administrator and the Marketing consent category is active according to the site's configured consent settings or visitor preferences.

When Meta Pixel is loaded, ClearOpt requests Meta's Pixel JavaScript from `connect.facebook.net`, initializes the Pixel using the configured Pixel ID, and sends a PageView event to Meta. Meta may receive event data and related browser, device, network, cookie, and website interaction information according to the site's Meta Pixel configuration and Meta's policies.

Meta Pixel is provided by Meta Platforms, Inc.

Meta Business Tools Terms:
https://www.facebook.com/legal/terms/businesstools

Meta Privacy Policy:
https://www.facebook.com/privacy/policy/

== Changelog ==

= 1.0.17 =
* Improved cookie settings links to reduce broken-link warnings in accessibility scans.
* Added support for existing `#privacy-settings` links.
* Added recognition for DoubleClick, TapAd, Magellan AI, and TruConversion.
* Improved service matching using cookie names and domains.
* Added broader consent-based blocking for supported Analytics and Marketing services.
* Added icons for Magellan AI and TapAd.
* Prevented unrelated WordPress admin notices from appearing on the ClearOpt screen.

= 1.0.16 =
* Added Smart Regional Consent to automatically adjust consent defaults based on visitor region.
* Added Global Privacy Control (GPC) support for disabling marketing consent when a GPC signal is detected.
* Added conservative consent defaults when visitor location cannot be determined.
* Updated Analytics and Marketing default settings with toggle controls.
* Improved the Default Settings interface with cleaner control alignment, spacing, descriptions, and dividers.
* Improved sanitization of visitor country headers used for regional consent detection.

= 1.0.15 =
* Added a dedicated top-level ClearOpt admin menu for easier access.
* Added a custom ClearOpt shield-and-cookie menu icon.
* Improved admin menu visibility while keeping ClearOpt positioned unobtrusively near the bottom of the WordPress dashboard.

= 1.0.14 =
* Added Google Consent Mode v2 support for Google Analytics and marketing consent.
* Synced Analytics and Marketing preferences with Google's consent signals.
* Added saved-consent restoration for returning visitors.
* Extended Global Privacy Control (GPC) handling to Google advertising consent.

= 1.0.13 =
* Added a branded Meta Pixel icon to Service Review.
* Replaced cookie preference checkboxes with themed toggle switches.
* Expanded scan recognition for additional Cloudflare, Google Ads/DoubleClick, Marketo, and TikTok cookies.

= 1.0.12 =
* Refined the Service Review layout with a new header and Scan Again button.
* Added service-specific icons for recognized services and improved styling for unidentified cookies.
* Updated Service Review summary counts and labels for clearer status reporting.
* Improved the Unknown cookie row so it matches the recognized service layout.
* Added a Get Support button linking directly to ClearOpt support.

= 1.0.11 =
* Improved consent blocking for Google Tag Manager and Meta Pixel so saved visitor preferences are respected on page load.

= 1.0.10 =
* Redesigned the Service Review dashboard with clearer status summaries for allowed, attention-needed, unknown, and total cookies.
* Improved service health messaging and status styling.
* Reorganized Cookie Banner settings into a cleaner sidebar and content layout.
* Added clearer controls for banner layout, position, theme, accent color, and banner status.
* Improved Cookie Banner and integration settings organization.
* Verified consent-based loading for Google Tag Manager, Meta Pixel, and Microsoft Clarity.
* General admin UI and usability improvements.

= 1.0.09 =
* Updated plugin title, description, tags, and author branding.
* Added dedicated Cookie Banner and Integrations tabs.
* Improved the Overview and Service Review experience.
* Redesigned ClearOpt Cloud status and scan controls.
* Improved admin navigation and interface styling.

= 1.0.08 =
* Re-skinned the ClearOpt dashboard and positioned the website scan as the main feature.
* Fixed the "Scan Again" button so it reconnects automatically when Cloud credentials are missing or expired.

= 1.0.07 =
* Added consent-aware script blocking for supported Analytics and Marketing services.
* Added layered browser-side script interception for dynamic and static third-party scripts.
* Added Google Analytics and ShareThis resource blocking rules.
* Updated blocking behavior to respect configured Analytics and Marketing defaults.
* Improved ClearOpt Cloud scanning to verify accepted-state behavior after consent.
* Updated service health results to compare observed cookie behavior against current ClearOpt settings.
* Improved first-scan experience with automatic Cloud connection and Scan My Site workflow.
* Added scan progress percentage feedback.

= 1.0.06 =
* Added service-level cookie review and classification.
* Grouped related cookies under their detected services.
* Added service-level Reclassify and Ignore actions.
* Added WordPress Consent API support for sharing consent state with compatible plugins.
* Improved scan summaries and before-consent service warnings.
* Added first-scan admin notice with persistent dismissal.
* Improved handling of unidentified cookies and legacy classification overrides.

= 1.0.05 =
* Added detailed cookie scan results grouped by category.
* Added baseline and accepted-state tracking for discovered cookies.
* Added red, yellow, and green technical health indicators for cookie behavior.
* Added scan health summaries to highlight items needing attention or review.
* Updated WordPress.org plugin tags to better reflect ClearOpt's scanning and consent features.

= 1.0.04 =
* Added cookie classification for Necessary, Analytics, Marketing, and Unknown categories.
* Added ClearOpt Cloud scanning before and after consent with merged cookie results.
* Added category totals to the ClearOpt Status panel.
* Added scan progress feedback while ClearOpt Cloud scans the site.

= 1.0.03 =
* Added optional ClearOpt Cloud connection and disconnection support.
* Added manual homepage cookie scanning for connected sites.
* Added cookie scan results to the ClearOpt Status panel.
* Added secure site-specific authentication for ClearOpt Cloud connections.

= 1.0.02 =
* Added optional Global Privacy Control (GPC) support.
* When enabled, ClearOpt honors the browser GPC signal by disabling Marketing consent for that visitor.

= 1.0.01 =
* Added a customizable title for the cookie preferences modal.

= 1.0.00 =
* Initial release of ClearOpt.
* Cookie consent banner and preference modal.
* Configurable default analytics and marketing preferences.
* Google Tag Manager integration.
* Microsoft Clarity integration.
* Meta Pixel integration.
* Multiple banner layouts, themes, and accent colors.

== Screenshots ==

1. ClearOpt settings dashboard with banner, consent, and integration configuration options.
2. Cookie consent banner displayed on the frontend.
3. Cookie preference modal allowing visitors to manage analytics and marketing preferences.
4. Third-party integration settings for Google Tag Manager, Microsoft Clarity, and Meta Pixel.
