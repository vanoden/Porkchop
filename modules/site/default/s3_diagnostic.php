<style>
.diagnostic-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}

.diagnostic-output {
    background-color: #1e1e1e;
    color: #f8f8f2;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.4;
    padding: 20px;
    border-radius: 8px;
    white-space: pre-wrap;
    word-wrap: break-word;
    overflow-x: auto;
    max-height: 800px;
    overflow-y: auto;
    border: 1px solid #444;
    margin-top: 20px;
}

.diagnostic-controls {
    background-color: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

.diagnostic-warning {
    background-color: #fff3cd;
    color: #856404;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #ffeaa7;
    margin-bottom: 20px;
}

.run-button {
    background-color: #007bff;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}

.run-button:hover {
    background-color: #0056b3;
}

.status-indicator {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.status-success { background-color: #28a745; }
.status-error { background-color: #dc3545; }
.status-warning { background-color: #ffc107; }
.status-info { background-color: #17a2b8; }
</style>

<div class="diagnostic-container">
    <?=$page->showAdminPageInfo()?>
    
    <div class="diagnostic-warning">
        <strong>⚠️ Temporary Diagnostic Tool</strong><br>
        This page is for troubleshooting S3 connectivity issues. It will be removed after the issue is resolved.
        The diagnostic tests AWS credentials, bucket access, and network connectivity.
    </div>

    <div class="diagnostic-controls">
        <h3>S3 Connection Diagnostic</h3>
        <p>This tool will comprehensively test your S3 configuration including:</p>
        <ul>
            <li>AWS environment variables and credential files</li>
            <li>S3 repository configurations in the database</li>
            <li>Bucket existence and permissions</li>
            <li>Network connectivity to AWS endpoints</li>
            <li>Actual read/write tests</li>
        </ul>
        
        <form method="post" action="/_site/s3_diagnostic">
            <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
            <input type="hidden" name="run_diagnostic" value="true">
            <button type="submit" class="run-button">🔍 Run S3 Diagnostic</button>
        </form>
    </div>

    <?php if ($diagnostic_complete): ?>
        <div class="diagnostic-output"><?=htmlspecialchars($diagnostic_output)?></div>
        
        <div style="margin-top: 20px; padding: 15px; background-color: #e8f5e9; border-radius: 8px;">
            <h4>Next Steps:</h4>
            <ol>
                <li>Review the diagnostic output above for any ✗ (failed) or ⚠️ (warning) indicators</li>
                <li>Focus on the "🎯 THIS IS THE FAILING BUCKET" section for specific issues</li>
                <li>Common fixes:
                    <ul>
                        <li>If bucket doesn't exist: Create it in the correct region</li>
                        <li>If IAM permissions failed: Add S3 permissions to the IAM role</li>
                        <li>If network issues: Check Docker networking and security groups</li>
                        <li>If region mismatch: Update repository configuration</li>
                    </ul>
                </li>
                <li>After making changes, run the diagnostic again to verify the fix</li>
            </ol>
        </div>
    <?php endif; ?>
</div>