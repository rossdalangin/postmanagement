jQuery(document).ready(function($) {
    let currentData = null;
    let currentPlatform = null;

    function loadStats() {
        const category_id = $('#scm-dashboard-category').val();
        $.ajax({
            url: scm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'scm_get_stats',
                category_id: category_id,
                nonce: scm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const s = response.data;
                    let html = '<ul>';
                    html += '<li>Unused FB Group Content: ' + s.fb_group_unused + '</li>';
                    html += '<li>Unused LI Group Content: ' + s.li_group_unused + '</li>';
                    html += '<li>Facebook Used: ' + s.facebook + '</li>';
                    html += '<li>LinkedIn Used: ' + s.linkedin + '</li>';
                    html += '<li>YouTube Used: ' + s.youtube + '</li>';
                    html += '<li>TikTok Used: ' + s.tiktok + '</li>';
                    html += '<li>Pinterest Used: ' + s.pinterest + '</li>';
                    html += '<li>Twitter Used: ' + s.twitter + '</li>';
                    html += '<li>Threads Used: ' + s.threads + '</li>';
                    html += '<li>Instagram Used: ' + s.ig + '</li>';
                    html += '<li>Total Posts: ' + s.total_posts + '</li>';
                    html += '</ul>';
                    $('#scm-stats-content').html(html);
                }
            }
        });
    }

    loadStats();

    $('#scm-refresh-stats').on('click', loadStats);
    $('#scm-dashboard-category').on('change', loadStats);

    $('#scm-reset-usage').on('click', function() {
        if (confirm('Are you sure you want to reset ALL usage tracking? This cannot be undone.')) {
            $.ajax({
                url: scm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'scm_reset_usage',
                    nonce: scm_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Usage tracking has been reset.');
                        loadStats();
                    }
                }
            });
        }
    });

    $('.scm-platform-btn').on('click', function() {
        const platform = $(this).data('platform');
        currentPlatform = platform;

        $('.scm-spinner').css('display', 'inline-block');
        $('.scm-error-msg').hide();
        $('.scm-content-display').hide();
        $('#scm-group-link-area').hide();

        const category_id = $('#scm-dashboard-category').val();
        $.ajax({
            url: scm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'scm_get_content',
                platform: platform,
                category_id: category_id,
                nonce: scm_ajax.nonce
            },
            success: function(response) {
                $('.scm-spinner').hide();
                if (response.success) {
                    currentData = response.data;
                    $('#scm-post-content').val(currentData.content);

                    if (currentData.group_url) {
                        $('#scm-group-info').html('Group URL: <a href="' + currentData.group_url + '" target="_blank" class="scm-target-link">' + currentData.group_url + '</a><br>Group Post ID: ' + currentData.group_post_id);
                        $('#scm-group-link').attr('href', currentData.group_url);
                        $('#scm-group-link-area').show();

                        // Attempt to open in new tab as requested
                        const newTab = window.open(currentData.group_url, '_blank');
                        if (!newTab || newTab.closed || typeof newTab.closed === 'undefined') {
                            console.warn('Popup blocked. Please allow popups for this site.');
                        }
                    } else {
                        $('#scm-group-info').empty();
                        $('#scm-group-link-area').hide();
                    }

                    $('.scm-content-display').fadeIn();
                } else {
                    $('.scm-error-msg').text(response.data).fadeIn();
                }
            },
            error: function() {
                $('.scm-spinner').hide();
                $('.scm-error-msg').text('An error occurred.').fadeIn();
            }
        });
    });

    $('#scm-copy-btn').on('click', function() {
        const content = $('#scm-post-content').val();
        navigator.clipboard.writeText(content).then(function() {
            $('.scm-success-msg').text('Content copied to clipboard!').fadeIn().delay(2000).fadeOut();
        });
    });

    $('#scm-used-btn').on('click', function() {
        if (!currentData) return;

        $(this).prop('disabled', true);
        $('.scm-spinner').css('display', 'inline-block');

        $.ajax({
            url: scm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'scm_mark_used',
                platform: currentPlatform,
                post_id: currentData.post_id,
                group_post_id: currentData.group_post_id,
                nonce: scm_ajax.nonce
            },
            success: function(response) {
                $('.scm-spinner').hide();
                $('#scm-used-btn').prop('disabled', false);
                if (response.success) {
                    $('.scm-success-msg').text('Marked as used!').fadeIn().delay(2000).fadeOut();
                    $('.scm-content-display').fadeOut();
                    loadStats();
                } else {
                    $('.scm-error-msg').text(response.data).fadeIn();
                }
            },
            error: function() {
                $('.scm-spinner').hide();
                $('#scm-used-btn').prop('disabled', false);
                $('.scm-error-msg').text('An error occurred.').fadeIn();
            }
        });
    });
});
