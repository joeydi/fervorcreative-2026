document.addEventListener('DOMContentLoaded', function () {

  const config = window.clearOptSettings || {};

  const COOKIE_KEY = 'clearopt_consent';
  const COOKIE_MAX_AGE = 60 * 60 * 24 * 365;
  const CONSENT_VERSION = '1.0';

  let clearOptPreviouslyFocusedElement = null;

  function isGlobalPrivacyControlActive() {
    return (
      config.honorGpc === '1' &&
      navigator.globalPrivacyControl === true
    );
  }

  function enforceGlobalPrivacyControl(preferences) {
    const enforcedPreferences = Object.assign({}, preferences);

    if (isGlobalPrivacyControlActive()) {
      enforcedPreferences.marketing = false;
    }

    return enforcedPreferences;
  }

  function getCookie(name) {
    const nameEQ = encodeURIComponent(name) + '=';
    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();

      if (cookie.indexOf(nameEQ) === 0) {
        return decodeURIComponent(cookie.substring(nameEQ.length));
      }
    }

    return null;
  }

  function setCookie(name, value) {
    document.cookie =
      encodeURIComponent(name) + '=' + encodeURIComponent(value) +
      '; Max-Age=' + COOKIE_MAX_AGE +
      '; Path=/' +
      '; SameSite=Lax' +
      (window.location.protocol === 'https:' ? '; Secure' : '');
  }

  function getPreferences() {
    try {
      const storedCookie = getCookie(COOKIE_KEY);

      if (!storedCookie) {
        return null;
      }

      const preferences = JSON.parse(storedCookie);

      if (
        !preferences ||
        typeof preferences !== 'object' ||
        preferences.version !== CONSENT_VERSION
      ) {
        return null;
      }

      return enforceGlobalPrivacyControl(preferences);
    } catch (error) {
      return null;
    }
  }

  function savePreferences(preferences) {
    setCookie(COOKIE_KEY, JSON.stringify(preferences));
  }

  function defaultPreferences() {
    return {
      necessary: true,
      analytics: config.analyticsDefault === '1',
      marketing: isGlobalPrivacyControlActive()
        ? false
        : config.marketingDefault === '1',
      version: CONSENT_VERSION,
      timestamp: new Date().toISOString()
    };
  }

  function buildPreferences(analytics, marketing) {
    return {
      necessary: true,
      analytics: !!analytics,
      marketing: isGlobalPrivacyControlActive()
        ? false
        : !!marketing,
      version: CONSENT_VERSION,
      timestamp: new Date().toISOString()
    };
  }

  function defineWpConsentType() {

    if (typeof window.wp_set_consent !== 'function') {
      return;
    }

    window.wp_consent_type = 'optin';

    document.dispatchEvent(
      new CustomEvent('wp_consent_type_defined')
    );
  }

  function updateWpConsentApi(preferences) {

    defineWpConsentType();

    if (typeof window.wp_set_consent !== 'function') {
      return;
    }

    window.wp_set_consent(
      'statistics',
      preferences.analytics ? 'allow' : 'deny'
    );

    window.wp_set_consent(
      'marketing',
      preferences.marketing ? 'allow' : 'deny'
    );
  }

  function applyPreferences(preferences) {
    preferences = enforceGlobalPrivacyControl(preferences);

    window.clearOptCookiePreferences = preferences;

    updateWpConsentApi(preferences);

    document.documentElement.setAttribute(
      'data-analytics-consent',
      preferences.analytics ? 'granted' : 'denied'
    );

    document.documentElement.setAttribute(
      'data-marketing-consent',
      preferences.marketing ? 'granted' : 'denied'
    );

    updateGoogleConsentMode(preferences);

    if (preferences.analytics) {
      loadAnalyticsScripts();
    } else {
      clearAnalyticsCookies();
    }

    if (preferences.marketing) {
      loadMarketingScripts();
    } else {
      clearMarketingCookies();
    }

    document.dispatchEvent(
      new CustomEvent('clearopt:consent-updated', {
        detail: preferences
      })
    );
  }

  function updateGoogleConsentMode(preferences) {
    window.dataLayer = window.dataLayer || [];

    function clearOptGtag() {
      window.dataLayer.push(arguments);
    }

    clearOptGtag('consent', 'update', {
      analytics_storage: preferences.analytics ? 'granted' : 'denied',
      ad_storage: preferences.marketing ? 'granted' : 'denied',
      ad_user_data: preferences.marketing ? 'granted' : 'denied',
      ad_personalization: preferences.marketing ? 'granted' : 'denied'
    });
  }

  function loadAnalyticsScripts() {
    if (window.clearOptAnalyticsLoaded) {
      return;
    }

    window.clearOptAnalyticsLoaded = true;

    if (config.gtmContainerId) {
      loadGoogleTagManager(config.gtmContainerId);
    }

    if (config.clarityId) {
      loadMicrosoftClarity(config.clarityId);
    }

    activateConsentScripts('analytics');
  }

  function loadMarketingScripts() {
    if (window.clearOptMarketingLoaded) {
      return;
    }

    window.clearOptMarketingLoaded = true;

    if (config.metaPixelId) {
      loadMetaPixel(config.metaPixelId);
    }
  }

  function loadGoogleTagManager(containerId) {
    if (!containerId || document.getElementById('clearopt-gtm-script')) {
      return;
    }

    window.dataLayer = window.dataLayer || [];

    window.dataLayer.push({
      'gtm.start': new Date().getTime(),
      event: 'gtm.js'
    });

    const firstScript = document.getElementsByTagName('script')[0];
    const script = document.createElement('script');

    script.id = 'clearopt-gtm-script';
    script.async = true;
    script.src =
      'https://www.googletagmanager.com/gtm.js?id=' +
      encodeURIComponent(containerId);

    firstScript.parentNode.insertBefore(script, firstScript);
  }

  function loadMetaPixel(pixelId) {
    if (
      !pixelId ||
      document.getElementById('clearopt-meta-pixel-script')
    ) {
      return;
    }

    if (!window.fbq) {
      const fbq = window.fbq = function () {
        fbq.callMethod
          ? fbq.callMethod.apply(fbq, arguments)
          : fbq.queue.push(arguments);
      };

      if (!window._fbq) {
        window._fbq = fbq;
      }

      fbq.push = fbq;
      fbq.loaded = true;
      fbq.version = '2.0';
      fbq.queue = [];
    }

    const firstScript = document.getElementsByTagName('script')[0];
    const script = document.createElement('script');

    script.id = 'clearopt-meta-pixel-script';
    script.async = true;
    script.src = 'https://connect.facebook.net/en_US/fbevents.js';

    firstScript.parentNode.insertBefore(script, firstScript);

    window.fbq('init', pixelId);
    window.fbq('track', 'PageView');
  }

  function loadMicrosoftClarity(projectId) {
    if (!projectId || document.getElementById('clearopt-clarity-script')) {
      return;
    }

    (function(c,l,a,r,i,t,y){
      c[a] = c[a] || function() {
        (c[a].q = c[a].q || []).push(arguments);
      };

      t = l.createElement(r);
      t.id = 'clearopt-clarity-script';
      t.async = 1;
      t.src = 'https://www.clarity.ms/tag/' + encodeURIComponent(projectId);

      y = l.getElementsByTagName(r)[0];
      y.parentNode.insertBefore(t, y);

    })(window, document, 'clarity', 'script');
  }

  function activateConsentScripts(category) {
    const selector =
      'script[type="text/plain"][data-' + category + '-consent]';

    document.querySelectorAll(selector).forEach(function (blockedScript) {
      if (blockedScript.dataset.clearoptActivated === 'true') {
        return;
      }

      const activeScript = document.createElement('script');

      Array.prototype.slice
        .call(blockedScript.attributes)
        .forEach(function (attribute) {
          if (
            attribute.name === 'type' ||
            attribute.name === 'data-analytics-consent' ||
            attribute.name === 'data-marketing-consent' ||
            attribute.name === 'data-clearopt-activated'
          ) {
            return;
          }

          activeScript.setAttribute(attribute.name, attribute.value);
        });

      activeScript.textContent = blockedScript.textContent;

      blockedScript.dataset.clearoptActivated = 'true';
      blockedScript.parentNode.insertBefore(
        activeScript,
        blockedScript.nextSibling
      );
    });
  }

  function deleteCookie(name) {
    const hostname = window.location.hostname;
    const rootDomain = hostname.replace(/^www\./, '');

    const domains = [
      '',
      hostname,
      '.' + hostname,
      rootDomain,
      '.' + rootDomain
    ];

    const paths = ['/', window.location.pathname];

    domains.forEach(function (domain) {
      paths.forEach(function (path) {
        let cookie =
          encodeURIComponent(name) +
          '=; Expires=Thu, 01 Jan 1970 00:00:00 GMT;' +
          ' Max-Age=0;' +
          ' Path=' + path + ';' +
          ' SameSite=Lax';

        if (domain) {
          cookie += '; Domain=' + domain;
        }

        document.cookie = cookie;
      });
    });
  }

  // function deleteCookie(name) {
  //   document.cookie = name + '=; Max-Age=0; path=/';
  //   document.cookie = name + '=; Max-Age=0; path=/; domain=' + window.location.hostname;
  //   document.cookie = name + '=; Max-Age=0; path=/; domain=.' + window.location.hostname;
  // }

  function clearCookiesByPrefix(prefixes) {
    document.cookie.split(';').forEach(function (cookie) {
      const name = decodeURIComponent(cookie.split('=')[0].trim());

      prefixes.forEach(function (prefix) {
        if (name.indexOf(prefix) === 0) {
          deleteCookie(name);
        }
      });
    });
  }

  function clearAnalyticsCookies() {
    [
      '_ga',
      '_gid',
      '_gat'

    ].forEach(deleteCookie);

    clearCookiesByPrefix([
      '_ga_',
      '_gat_',
      '_gac_',
      '_clck',
      '_clsk'
    ]);
  }

  function clearMarketingCookies() {
    [
      '_fbp',
      '_fbc',
      'fr',
      'IDE',
      'test_cookie'
    ].forEach(deleteCookie);

    clearCookiesByPrefix([
      '_fbp',
      '_fbc'
    ]);
  }

  function showBanner() {
    const banner = document.getElementById(
      'clearopt-cookie-dialog'
    );

    if (banner) {
      banner.removeAttribute('hidden');
      banner.classList.add('active');
    }
  }

  function hideBanner() {
    const banner = document.getElementById(
      'clearopt-cookie-dialog'
    );

    if (banner) {
      banner.setAttribute('hidden', 'hidden');
      banner.classList.remove('active');
    }
  }

  function getFocusableElements(container) {
    if (!container) {
      return [];
    }

    return Array.prototype.slice.call(
      container.querySelectorAll(
        'a[href], ' +
        'button:not([disabled]), ' +
        'input:not([disabled]):not([type="hidden"]), ' +
        'select:not([disabled]), ' +
        'textarea:not([disabled]), ' +
        '[tabindex]:not([tabindex="-1"])'
      )
    ).filter(function (element) {
      return (
        !element.hasAttribute('hidden') &&
        element.getClientRects().length > 0
      );
    });
  }

  function openModal() {
    const modal = document.getElementById(
      'clearopt-cookie-modal'
    );

    const analyticsInput = document.getElementById(
      'clearopt-cookie-analytics'
    );

    const marketingInput = document.getElementById(
      'clearopt-cookie-marketing'
    );

    const preferences = getPreferences() || defaultPreferences();

    if (analyticsInput) {
      analyticsInput.checked = !!preferences.analytics;
    }

    if (marketingInput) {
      marketingInput.checked = !!preferences.marketing;
      marketingInput.disabled = isGlobalPrivacyControlActive();
    }

    if (modal) {
      clearOptPreviouslyFocusedElement = document.activeElement;

      modal.removeAttribute('hidden');

      document.body.classList.add(
        'clearopt-cookie-modal-open'
      );

      const closeButton = document.getElementById(
        'clearopt-close-cookie-settings'
      );

      const dialogPanel = document.getElementById(
        'cookie-preferences'
      );

      if (closeButton) {
        closeButton.focus();
      } else if (dialogPanel) {
        dialogPanel.focus();
      }
    }
  }

  function closeModal() {
    const modal = document.getElementById(
      'clearopt-cookie-modal'
    );

    if (modal) {
      modal.setAttribute('hidden', 'hidden');

      document.body.classList.remove(
        'clearopt-cookie-modal-open'
      );

      if (
        clearOptPreviouslyFocusedElement &&
        typeof clearOptPreviouslyFocusedElement.focus === 'function'
      ) {
        clearOptPreviouslyFocusedElement.focus();
      }

      clearOptPreviouslyFocusedElement = null;
    }
  }

  function acceptAllPreferences() {
    const preferences = buildPreferences(true, true);

    savePreferences(preferences);
    applyPreferences(preferences);
    hideBanner();
    closeModal();
  }

  function rejectNonEssentialPreferences() {
    const preferences = buildPreferences(false, false);

    savePreferences(preferences);
    applyPreferences(preferences);
    hideBanner();
    closeModal();
  }

  function saveCustomPreferences() {
    const analyticsInput = document.getElementById(
      'clearopt-cookie-analytics'
    );

    const marketingInput = document.getElementById(
      'clearopt-cookie-marketing'
    );

    const preferences = buildPreferences(
      analyticsInput ? analyticsInput.checked : false,
      marketingInput ? marketingInput.checked : false
    );

    savePreferences(preferences);
    applyPreferences(preferences);
    hideBanner();
    closeModal();
  }

  const banner = document.getElementById(
    'clearopt-cookie-dialog'
  );

  if (!banner) {
    return;
  }

  const storedPreferences = getPreferences();

  if (!storedPreferences) {
    const preferences = defaultPreferences();

    applyPreferences(preferences);
    showBanner();
  } else {
    applyPreferences(storedPreferences);
  }

  const agreeButton = document.getElementById(
    'clearopt-cookie-agree'
  );

  const acceptAllButton = document.getElementById(
    'clearopt-cookie-accept-all'
  );

  const rejectButton = document.getElementById(
    'clearopt-cookie-reject'
  );

  const saveButton = document.getElementById(
    'clearopt-cookie-save'
  );

  const closeButton = document.getElementById(
    'clearopt-close-cookie-settings'
  );

  const overlay = document.getElementById(
    'clearopt-cookie-modal-overlay'
  );

  const settingsLinks = document.querySelectorAll(
    'a[href="#cookie-preferences"], ' +
    'a[href="#privacy-settings"]'
  );

  settingsLinks.forEach(function (link) {
    if (link.getAttribute('href') === '#privacy-settings') {
      link.setAttribute('href', '#cookie-preferences');
    }

    link.setAttribute('aria-haspopup', 'dialog');
    link.setAttribute('aria-controls', 'cookie-preferences');

    link.addEventListener('click', function (event) {
      event.preventDefault();
      openModal();
    });
  });

  if (agreeButton) {
    agreeButton.addEventListener('click', acceptAllPreferences);
  }

  if (acceptAllButton) {
    acceptAllButton.addEventListener(
      'click',
      acceptAllPreferences
    );
  }

  if (rejectButton) {
    rejectButton.addEventListener(
      'click',
      rejectNonEssentialPreferences
    );
  }

  if (saveButton) {
    saveButton.addEventListener(
      'click',
      saveCustomPreferences
    );
  }

  if (closeButton) {
    closeButton.addEventListener('click', closeModal);
  }

  if (overlay) {
    overlay.addEventListener('click', closeModal);
  }

  document.addEventListener('keydown', function (event) {
    const modal = document.getElementById(
      'clearopt-cookie-modal'
    );

    if (!modal || modal.hasAttribute('hidden')) {
      return;
    }

    if (event.key === 'Escape') {
      event.preventDefault();
      closeModal();
      return;
    }

    if (event.key !== 'Tab') {
      return;
    }

    const dialogPanel = document.getElementById(
      'cookie-preferences'
    ) || modal;

    const focusableElements = getFocusableElements(dialogPanel);

    if (!focusableElements.length) {
      event.preventDefault();
      dialogPanel.focus();
      return;
    }

    const firstFocusable = focusableElements[0];
    const lastFocusable =
      focusableElements[focusableElements.length - 1];

    if (
      event.shiftKey &&
      document.activeElement === firstFocusable
    ) {
      event.preventDefault();
      lastFocusable.focus();
      return;
    }

    if (
      !event.shiftKey &&
      document.activeElement === lastFocusable
    ) {
      event.preventDefault();
      firstFocusable.focus();
    }
  });
});