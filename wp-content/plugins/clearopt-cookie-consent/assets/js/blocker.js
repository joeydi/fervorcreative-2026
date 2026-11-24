(function () {
  'use strict';

  var blockerSettings = window.ClearOptBlockerSettings || {};
  var googleConsentDefault =
    blockerSettings.googleConsentDefault || {};

  window.dataLayer = window.dataLayer || [];

  function clearOptGtag() {
    window.dataLayer.push(arguments);
  }

  var gpcBlocksMarketing =
    blockerSettings.honorGpc === '1' &&
    navigator.globalPrivacyControl === true;

  clearOptGtag('consent', 'default', {
    analytics_storage:
      googleConsentDefault.analytics_storage || 'denied',
    ad_storage:
      gpcBlocksMarketing
        ? 'denied'
        : (googleConsentDefault.ad_storage || 'denied'),
    ad_user_data:
      gpcBlocksMarketing
        ? 'denied'
        : (googleConsentDefault.ad_user_data || 'denied'),
    ad_personalization:
      gpcBlocksMarketing
        ? 'denied'
        : (googleConsentDefault.ad_personalization || 'denied'),
  });

  var savedConsent = getConsentPreferences();

  if (savedConsent) {
    var savedAnalyticsConsent =
      savedConsent.analytics === true ? 'granted' : 'denied';

    var savedMarketingConsent =
      gpcBlocksMarketing
        ? 'denied'
        : (savedConsent.marketing === true ? 'granted' : 'denied');

    clearOptGtag('consent', 'update', {
      analytics_storage: savedAnalyticsConsent,
      ad_storage: savedMarketingConsent,
      ad_user_data: savedMarketingConsent,
      ad_personalization: savedMarketingConsent,
    });
  }

  var fallbackResourceRules = [
    {
      service: 'google-analytics',
      category: 'analytics',
      patterns: [
        'googletagmanager.com/gtag/js',
      ],
    },
    {
      service: 'sharethis',
      category: 'marketing',
      patterns: [
        'platform-api.sharethis.com/js/sharethis.js',
      ],
    },
    {
      service: 'google-tag-manager',
      category: 'analytics',
      patterns: [
        'googletagmanager.com/gtm.js',
      ],
    },
    {
      service: 'meta-pixel',
      category: 'marketing',
      patterns: [
        'connect.facebook.net/en_US/fbevents.js',
      ],
    },
  ];

  var cloudResourceRules = Array.isArray(blockerSettings.resourceRules)
    ? blockerSettings.resourceRules
    : [];

  var resourceRules = cloudResourceRules.concat(
    fallbackResourceRules
  );

  function getConsentPreferences() {
    var cookieName = 'clearopt_consent=';
    var cookies = document.cookie ? document.cookie.split('; ') : [];

    for (var i = 0; i < cookies.length; i++) {
      if (cookies[i].indexOf(cookieName) !== 0) {
        continue;
      }

      try {
        return JSON.parse(
          decodeURIComponent(
            cookies[i].substring(cookieName.length)
          )
        );
      } catch (error) {
        return null;
      }
    }

    return null;
  }

  function hasConsent(category) {
    var defaults = window.ClearOptBlockerSettings || {};

    if (
      category === 'marketing' &&
      defaults.honorGpc === '1' &&
      navigator.globalPrivacyControl === true
    ) {
      return false;
    }

    var preferences = getConsentPreferences();

    if (preferences) {
      return preferences[category] === true;
    }

    if (category === 'analytics') {
      return defaults.analyticsDefault === '1';
    }

    if (category === 'marketing') {
      return defaults.marketingDefault === '1';
    }

    return false;
  }

  function shouldBlockScript(value) {
    var url = String(value || '');

    for (var i = 0; i < resourceRules.length; i++) {
      var rule = resourceRules[i];

      var matchesRule = rule.patterns.some(function (pattern) {
        return url.indexOf(pattern) !== -1;
      });

      if (
        matchesRule &&
        !hasConsent(rule.category)
      ) {
        return rule;
      }
    }

    return null;
  }

  function markScriptBlocked(originalSetAttribute, value, rule) {
    originalSetAttribute('type', 'javascript/blocked');
    originalSetAttribute('data-clearopt-src', String(value));
    originalSetAttribute(
      'data-clearopt-category',
      rule.category
    );
    originalSetAttribute(
      'data-clearopt-service',
      rule.service
    );
  }

  var originalCreateElement = document.createElement.bind(document);

  document.createElement = function (tagName) {
    var element = originalCreateElement(tagName);

    if (
      typeof tagName !== 'string' ||
      tagName.toLowerCase() !== 'script'
    ) {
      return element;
    }

    var originalSetAttribute = element.setAttribute.bind(element);
    var srcDescriptor = Object.getOwnPropertyDescriptor(
      HTMLScriptElement.prototype,
      'src'
    );

    if (
      srcDescriptor &&
      srcDescriptor.get &&
      srcDescriptor.set
    ) {
      Object.defineProperty(element, 'src', {
        configurable: true,

        get: function () {
          return srcDescriptor.get.call(element);
        },

        set: function (value) {
          var blockedRule = shouldBlockScript(value);

          if (blockedRule) {
            markScriptBlocked(
              originalSetAttribute,
              value,
              blockedRule
            );

            console.log('ClearOpt BLOCKED:', String(value));

            return;
          }

          srcDescriptor.set.call(element, value);
        },
      });
    }

    element.setAttribute = function (name, value) {
      if (
        typeof name === 'string' &&
        name.toLowerCase() === 'src'
      ) {
        var blockedRule = shouldBlockScript(value);

        if (blockedRule) {
          markScriptBlocked(
            originalSetAttribute,
            value,
            blockedRule
          );

          console.log('ClearOpt BLOCKED:', String(value));

          return;
        }
      }

      return originalSetAttribute(name, value);
    };

    return element;
  };

  var originalAppendChild = Node.prototype.appendChild;

  Node.prototype.appendChild = function (node) {
    if (
      node &&
      node.tagName &&
      node.tagName.toLowerCase() === 'script'
    ) {
      var src = node.getAttribute('src');

      if (src) {
        var blockedRule = shouldBlockScript(src);

        if (blockedRule) {
          var originalSetAttribute =
            Element.prototype.setAttribute.bind(node);

          markScriptBlocked(
            originalSetAttribute,
            src,
            blockedRule
          );

          node.removeAttribute('src');

          console.log(
            'ClearOpt BLOCKED before append:',
            src
          );
        }
      }
    }

    return originalAppendChild.call(this, node);
  };

  var staticScriptObserver = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      mutation.addedNodes.forEach(function (node) {
        if (
          !node.tagName ||
          node.tagName.toLowerCase() !== 'script'
        ) {
          return;
        }

        var src = node.getAttribute('src');

        if (!src) {
          return;
        }

        var blockedRule = shouldBlockScript(src);

        if (!blockedRule) {
          return;
        }

        node.setAttribute('type', 'javascript/blocked');
        node.setAttribute('data-clearopt-src', src);
        node.setAttribute(
          'data-clearopt-category',
          blockedRule.category
        );
        node.setAttribute(
          'data-clearopt-service',
          blockedRule.service
        );

        node.removeAttribute('src');

        console.log(
          'ClearOpt observer caught static script:',
          src
        );
      });
    });
  });

  staticScriptObserver.observe(document.documentElement, {
    childList: true,
    subtree: true,
  });

  window.ClearOptBlocker = {
    ready: true,
  };

  console.log('ClearOpt blocker loaded');

})();