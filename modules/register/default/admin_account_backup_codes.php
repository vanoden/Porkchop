<!-- Page Header -->
<?= $page->showAdminPageInfo() ?>
<!-- End Page Header -->

<?php $activeTab = 'backup_codes'; ?>

<div class="tabs">
    <a href="/_register/admin_account?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='login'?'active':'' ?>">Login / Registration</a>
    <a href="/_register/admin_account_contacts?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='contacts'?'active':'' ?>">Methods of Contact</a>
    <a href="/_register/admin_account_password?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='password'?'active':'' ?>">Change Password</a>
    <a href="/_register/admin_account_roles?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='roles'?'active':'' ?>">Assigned Roles</a>
    <a href="/_register/admin_account_auth_failures?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='auth_failures'?'active':'' ?>">Recent Auth Failures</a>
    <a href="/_register/admin_account_terms?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='terms'?'active':'' ?>">Terms of Use History</a>
    <a href="/_register/admin_account_locations?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='locations'?'active':'' ?>">Locations</a>
    <a href="/_register/admin_account_images?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='images'?'active':'' ?>">User Images</a>
    <a href="/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='backup_codes'?'active':'' ?>">Backup Codes</a>
    <a href="/_register/admin_account_search_tags?customer_id=<?= $customer_id ?>" class="tab <?= $activeTab==='search_tags'?'active':'' ?>">Search Tags</a>
</div>

<form id="admin-account-form" name="register" action="<?= PATH ?>/_register/admin_account_backup_codes?customer_id=<?= $customer_id ?>" method="POST">

  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <input type="hidden" name="target" value="<?= $target ?>" />
  <input type="hidden" name="customer_id" value="<?= $customer_id ?>" />
  <input type="hidden" name="login" value="<?= $customer->code ?>" />

  <section id="form-message">
    <ul class="connectBorder infoText">
      <li>Make changes and click 'Apply' to complete.</li>
    </ul>
  </section>

  <!-- ============================================== -->
  <!-- BACKUP CODES -->
  <!-- ============================================== -->
  <h3>Backup Codes</h3>
  <div class="tableBody min-tablet">
    <p><strong>Generate 6 backup codes for this user. Generating new codes will erase all previous backup codes.</strong></p>
    <input type="submit" class="button" name="generate_backup_codes" value="Generate Backup Codes">
    <?php if (isset($generatedBackupCodes) && is_array($generatedBackupCodes)) { ?>
      <div class="backup-codes-list margin-top-10px">
        <div class="backup-codes-warning" style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 15px; margin: 10px 0;">
          <h4 style="color: #856404; margin: 0 0 10px 0; font-size: 16px;">⚠️ Important: Save These Backup Codes</h4>
          <p style="color: #856404; margin: 0; font-size: 14px;">These codes are shown only once. Save them in a secure location like a password manager or write them down and store them safely.</p>
        </div>
        
        <div class="backup-codes-display" style="background-color: #f8f9fa; border: 2px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 15px 0; font-family: 'Courier New', monospace;">
          <h4 style="color: #495057; margin: 0 0 15px 0; font-size: 16px; text-align: center;">🔐 New Backup Codes</h4>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; text-align: center;">
            <?php if (!empty($generatedBackupCodes)) { 
              $counter = 1;
              foreach ($generatedBackupCodes as $code) { ?>
                <div style="background-color: #ffffff; border: 1px solid #ced4da; border-radius: 4px; padding: 12px; font-size: 18px; font-weight: bold; color: #212529; letter-spacing: 1px;">
                  <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">Code #<?= $counter ?></div>
                  <?= htmlentities($code) ?>
                </div>
            <?php 
              $counter++;
              } 
            } ?>
          </div>
          <div style="text-align: center; margin-top: 15px;">
            <button type="button" onclick="copyBackupCodes()" style="background-color: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
              📋 Copy All Codes
            </button>
          </div>
        </div>
      </div>
      
      <script>
        function copyBackupCodes() {
          const codes = <?= json_encode($generatedBackupCodes) ?>;
          const codeText = codes.join('\n');
          
          // Check if clipboard API is available
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(codeText).then(function() {
              alert('Backup codes copied to clipboard!');
            }).catch(function(err) {
              console.error('Clipboard API failed: ', err);
              fallbackCopyToClipboard(codeText);
            });
          } else {
            // Use fallback for browsers that don't support clipboard API
            fallbackCopyToClipboard(codeText);
          }
        }
        
        function fallbackCopyToClipboard(text) {
          try {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
              alert('Backup codes copied to clipboard!');
            } else {
              alert('Unable to copy. Please select and copy the codes manually.');
            }
          } catch (err) {
            console.error('Fallback copy failed: ', err);
            alert('Unable to copy. Please select and copy the codes manually.');
          }
        }
      </script>
    <?php } ?>
    <?php if (isset($allBackupCodes) && count($allBackupCodes) > 0) { ?>
      <div class="backup-codes-list margin-top-10px">
        <p><strong>Current Backup Codes:</strong></p>
        <table class="table-backup-codes">
          <tr><th>Code</th><th>Status</th></tr>
          <?php if (!empty($allBackupCodes)) { foreach ($allBackupCodes as $bcode) { ?>
            <tr>
              <td class="register-admin-account-backup-codes-td">
                <?= htmlentities($bcode['code']) ?>
              </td>
              <td class="register-admin-account-backup-codes-status-td">
                <?php if ($bcode['used']) { ?>
                  <span class="register-admin-account-backup-codes-used">Used</span>
                <?php } else { ?>
                  <span class="register-admin-account-backup-codes-unused">Unused</span>
                <?php } ?>
              </td>
            </tr>
          <?php } } ?>
        </table>
      </div>
    <?php } ?>
  </div>
  <!-- End Backup Codes Section -->

  <!-- entire page button submit -->
  <div id="submit-button-container" class="tableBody min-tablet">
    <div class="tableRow button-bar">
      <input id="btn_submit" type="submit" name="method" class="button" value="Apply" />
    </div>
  </div>
</form>
