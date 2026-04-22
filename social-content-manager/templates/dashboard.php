<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap">
    <h1>Posting Dashboard</h1>
    <div class="scm-dashboard-buttons">
        <button type="button" class="button scm-platform-btn" data-platform="fb_group">FB Group</button>
        <button type="button" class="button scm-platform-btn" data-platform="li_group">LinkedIn Group</button>
        <button type="button" class="button scm-platform-btn" data-platform="facebook">Facebook</button>
        <button type="button" class="button scm-platform-btn" data-platform="linkedin">LinkedIn</button>
        <button type="button" class="button scm-platform-btn" data-platform="youtube">YouTube</button>
        <button type="button" class="button scm-platform-btn" data-platform="tiktok">TikTok</button>
        <button type="button" class="button scm-platform-btn" data-platform="pinterest">Pinterest</button>
        <button type="button" class="button scm-platform-btn" data-platform="twitter">Twitter</button>
        <button type="button" class="button scm-platform-btn" data-platform="threads">Threads</button>
        <button type="button" class="button scm-platform-btn" data-platform="ig">IG</button>
        <span class="spinner scm-spinner"></span>
    </div>

    <div id="scm-content-area" class="scm-content-display">
        <div id="scm-group-link-area" style="margin-bottom: 10px; display: none;">
            <a id="scm-group-link" href="#" target="_blank" class="button button-secondary">Open Group URL</a>
        </div>
        <div id="scm-group-info" style="margin-bottom: 10px; font-weight: bold;"></div>
        <textarea id="scm-post-content" rows="10" readonly></textarea>
        <div style="margin-top: 10px;">
            <button type="button" class="button button-primary" id="scm-copy-btn">Copy Content</button>
            <button type="button" class="button" id="scm-used-btn">Used</button>
        </div>
        <div class="scm-success-msg">Success! Content copied/marked as used.</div>
        <div class="scm-error-msg"></div>
    </div>

    <hr>

    <div class="scm-stats-area" style="margin-top: 30px;">
        <h2>Usage Statistics</h2>
        <div id="scm-stats-content">Loading statistics...</div>
        <button type="button" class="button" id="scm-refresh-stats">Refresh Stats</button>
        <button type="button" class="button" id="scm-reset-usage" style="color: #dc3232;">Reset All Usage Tracking</button>
    </div>
</div>
