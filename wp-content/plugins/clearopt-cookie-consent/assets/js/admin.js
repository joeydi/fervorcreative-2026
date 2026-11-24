jQuery(document).ready(function ($) {

  $('#clearopt_save_settings').on('click', function () {
      var $button = $(this);
      var $status = $('#clearopt_save_status');

      $button.prop('disabled', true);
      $status.text('Saving...');

      $.post(ClearOptAdmin.ajax_url, {
          action: 'clearopt_save_settings',
          nonce: ClearOptAdmin.nonce,
          enable_banner: $('#clearopt_enable_banner').is(':checked') ? '1' : '0',
          banner_position: $('#clearopt_banner_position').val(),
          banner_layout_style: $('#clearopt_banner_layout_style').val(),
          floating_banner_position: $('#clearopt_floating_banner_position').val(),
          privacy_policy_url: $('#clearopt_privacy_policy_url').val(),
          banner_theme: $('#clearopt_banner_theme').val(),
          modal_title: $('#clearopt_modal_title').val(),
          accent_color: $('#clearopt_accent_color').val(),
          banner_message: $('#clearopt_banner_message').val(),
          gtm_container_id: $('#clearopt_gtm_container_id').val(),
          meta_pixel_id: $('#clearopt-meta-pixel-id').val(),
          clarity_project_id: $('#clearopt-clarity-project-id').val(),
          analytics_default: $('#clearopt-analytics-default').is(':checked') ? '1' : '0',
          marketing_default: $('#clearopt-marketing-default').is(':checked') ? '1' : '0',
          honor_gpc: $('#clearopt-honor-gpc').is(':checked') ? '1' : '0',
          regional_consent_mode: $('#clearopt-regional-consent-mode').val(),
      })
      .done(function (response) {
          if (response.success) {
              $status.text(response.data?.message || 'Settings saved.');
          } else {
              $status.text(response.data?.message || 'Save failed.');
          }
      })
      .fail(function (xhr) {
          $status.text(xhr.responseJSON?.data?.message || 'Save failed.');
      })
      .always(function () {
          $button.prop('disabled', false);
      });
  });

  function clearOptResetScanButton($button) {
    $('#clearopt-scan-progress').hide();
    $('#clearopt-scan-percent').text('0%');
    $button.prop('disabled', false).html($button.data('originalHtml'));
  }

  function clearOptConnectAndScan($button) {
    $('#clearopt-scan-progress').hide();
    $('#clearopt-scan-percent').text('0%');
    $button.prop('disabled', true).text('Connecting...');

    $.post(
        ClearOptAdmin.ajax_url,
        {
            action: 'clearopt_cloud_connect',
            nonce: ClearOptAdmin.cloudConnectNonce,
        }
    )
    .done(function (response) {
  
      if (response.success) {
          $button.text('Scanning My Site...');
          clearOptRunCloudScan($button, true);
          return;
      }
  
      alert(response.data?.message || 'Unable to connect to ClearOpt Cloud.');
      clearOptResetScanButton($button);
    })
    .fail(function (xhr) {
        alert(xhr.responseJSON?.data?.message || 'Unable to connect to ClearOpt Cloud.');
        clearOptResetScanButton($button);
    });
  }

  $('#clearopt-connect-cloud').on('click', function () {
    const $button = $(this);
    $button.data('originalHtml', $button.html());
    clearOptConnectAndScan($button);
  });

  $('#clearopt-disconnect-cloud').on('click', function () {
    const $button = $(this);

    $button.prop('disabled', true).text('Disconnecting...');

    $.post(
        ClearOptAdmin.ajax_url,
        {
            action: 'clearopt_cloud_disconnect',
            nonce: ClearOptAdmin.cloudDisconnectNonce,
        }
    )
    .done(function (response) {
        if (response.success) {
            window.location.reload();
            return;
        }

        alert(response.data?.message || 'Unable to disconnect from ClearOpt Cloud.');
        $button.prop('disabled', false).text('Disconnect');
    })
    .fail(function () {
        alert('Unable to disconnect from ClearOpt Cloud.');
        $button.prop('disabled', false).text('Disconnect');
    });
  });

  function clearOptRunCloudScan($button, justConnected = false) {
    const $progress = $('#clearopt-scan-progress');
    const $percent = $('#clearopt-scan-percent');

    let percent = 0;

    $button.prop('disabled', true);
    $percent.text('0%');
    $progress.show();

    const scanningProgress = setInterval(function () {
        if (percent >= 92) {
            return;
        }

        if (percent < 30) {
            percent += 4;
        } else if (percent < 70) {
            percent += 3;
        } else {
            percent += 1;
        }

        percent = Math.min(percent, 92);

        $percent.text(percent + '%');
    }, 500);

    $.post(
        ClearOptAdmin.ajax_url,
        {
            action: 'clearopt_cloud_scan',
            nonce: ClearOptAdmin.cloudScanNonce,
        }
    )
    .done(function (response) {
        clearInterval(scanningProgress);

        if (response.success) {
            $percent.text('100%');

            setTimeout(function () {
                window.location.reload();
            }, 350);

            return;
        }

        if (!justConnected && response.data?.code === 'connection_required') {
            clearOptConnectAndScan($button);
            return;
        }

        clearOptResetScanButton($button);
        alert(response.data?.message || 'Unable to scan this site.');
    })
    .fail(function (xhr) {
        clearInterval(scanningProgress);

        if (!justConnected && xhr.responseJSON?.data?.code === 'connection_required') {
            clearOptConnectAndScan($button);
            return;
        }

        clearOptResetScanButton($button);
        const message =
            xhr.responseJSON?.data?.message ||
            'Unable to scan this site.';

        alert(message);
    });
  }

  $('.clearopt-scan-cloud').on('click', function () {
      const $button = $(this);
      $button.data('originalHtml', $button.html());
      clearOptRunCloudScan($button);
  });

  $('.clearopt-cookie-review').on('click', function () {
      const $button = $(this);
      const $item = $button.closest('.clearopt-cookie-details__item');
      const $panel = $item.find('.clearopt-cookie-review-panel').first();

      $panel.prop('hidden', !$panel.prop('hidden'));
  });

  $('.clearopt-dismiss-cookie').on('click', function () {
    const $button = $(this);

    const cookieName = $button.attr('data-cookie-name');
    const cookieDomain = $button.attr('data-cookie-domain');

    $button.prop('disabled', true).text('Ignoring...');

    $.post(
        ClearOptAdmin.ajax_url,
        {
            action: 'clearopt_dismiss_cookie',
            nonce: ClearOptAdmin.dismissCookieNonce,
            cookie_name: cookieName,
            cookie_domain: cookieDomain,
        }
    )
    .done(function (response) {
        if (response.success) {
            window.location.reload();
            return;
        }

        alert(response.data?.message || 'Unable to ignore this cookie.');
        $button.prop('disabled', false).text('Ignore');
    })
    .fail(function (xhr) {
        const message =
            xhr.responseJSON?.data?.message ||
            'Unable to ignore this cookie.';

        alert(message);
        $button.prop('disabled', false).text('Ignore');
    });
  });

  $('.clearopt-service-ignore').on('click', function () {
    const $button = $(this);
  
    const service = $button.attr('data-service');
  
    if (!service) {
      return;
    }
  
    $button.prop('disabled', true).text('Ignoring...');
  
    $.post(
      ClearOptAdmin.ajax_url,
      {
        action: 'clearopt_ignore_service',
        nonce: ClearOptAdmin.ignoreServiceNonce,
        service: service,
      }
    )
    .done(function (response) {
      if (response.success) {
        window.location.reload();
        return;
      }
  
      alert(
        response.data?.message ||
        'Unable to ignore this service.'
      );
  
      $button.prop('disabled', false).text('Ignore');
    })
    .fail(function (xhr) {
      const message =
        xhr.responseJSON?.data?.message ||
        'Unable to ignore this service.';
  
      alert(message);
  
      $button.prop('disabled', false).text('Ignore');
    });
  });

  $('.clearopt-change-classification').on('click', function () {
    const $button = $(this);

    const cookieName = $button.attr('data-cookie-name');
    const cookieDomain = $button.attr('data-cookie-domain');
    const currentCategory = $button.attr('data-cookie-category');

    const category = window.prompt(
        'Choose a category: necessary, analytics, marketing, or unknown',
        currentCategory
    );

    if (!category) {
        return;
    }

    $.post(
        ClearOptAdmin.ajax_url,
        {
            action: 'clearopt_reclassify_cookie',
            nonce: ClearOptAdmin.reclassifyCookieNonce,
            cookie_name: cookieName,
            cookie_domain: cookieDomain,
            category: category.toLowerCase(),
        }
    )
    .done(function (response) {
        if (response.success) {
            window.location.reload();
            return;
        }

        alert(
            response.data?.message ||
            'Unable to reclassify this cookie.'
        );
    })
    .fail(function (xhr) {
        const message =
            xhr.responseJSON?.data?.message ||
            'Unable to reclassify this cookie.';

        alert(message);
    });
  });

  $('.clearopt-service-reclassify').on('click', function () {
    const $button = $(this);
  
    const service = $button.attr('data-service');
    const currentCategory = $button.attr('data-category');
  
    const category = window.prompt(
      'Choose a category: necessary, analytics, marketing, or unknown',
      currentCategory
    );
  
    if (!category) {
      return;
    }
  
    $.post(
      ClearOptAdmin.ajax_url,
      {
        action: 'clearopt_reclassify_service',
        nonce: ClearOptAdmin.reclassifyServiceNonce,
        service: service,
        category: category.toLowerCase(),
      }
    )
    .done(function (response) {
      if (response.success) {
        window.location.reload();
        return;
      }
  
      alert(
        response.data?.message ||
        'Unable to reclassify this service.'
      );
    })
    .fail(function (xhr) {
      const message =
        xhr.responseJSON?.data?.message ||
        'Unable to reclassify this service.';
  
      alert(message);
    });
  });

  $('.clearopt-block-cookie').on('click', function () {
    const $button = $(this);
  
    const service = $button.attr('data-cookie-service');
  
    if (!service) {
      return;
    }
  
    $button.prop('disabled', true).text('Blocking...');
  
    $.post(
      ClearOptAdmin.ajax_url,
      {
        action: 'clearopt_block_service',
        nonce: ClearOptAdmin.blockServiceNonce,
        service: service,
      }
    )
    .done(function (response) {
      if (response.success) {
        window.location.reload();
        return;
      }
  
      alert(
        response.data?.message ||
        'Unable to block this service.'
      );
  
      $button.prop('disabled', false).text('Block');
    })
    .fail(function (xhr) {
      const message =
        xhr.responseJSON?.data?.message ||
        'Unable to block this service.';
  
      alert(message);
  
      $button.prop('disabled', false).text('Block');
    });
  });

  function clearOptTogglePositionRows() {
    var layoutStyle = $('#clearopt_banner_layout_style').val();

    if (layoutStyle === 'floating_box') {
        $('.clearopt-banner-position-row').hide();
        $('.clearopt-floating-banner-position-row').show();
    } else {
        $('.clearopt-banner-position-row').show();
        $('.clearopt-floating-banner-position-row').hide();
    }
  }

  clearOptTogglePositionRows();

  $('#clearopt_banner_layout_style').on('change', function() {
    clearOptTogglePositionRows();
  });

});
