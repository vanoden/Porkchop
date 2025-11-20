<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->



<form id="importForm" action="/_site/import_content" method="post">

    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>

    <input type="checkbox" id="marketingContent" class="contentCheckbox" name="content[]" value="Marketing" data-label="Marketing Content" <?=isChecked('Marketing')?>>
    <label for="marketingContent">Marketing Content - Web Site page content and page settings</label><br>

    <input type="checkbox" id="navigation" class="contentCheckbox" name="content[]" value="Navigation" data-label="Navigation" <?=isChecked('Navigation')?>>
    <label for="navigation">Navigation - Full menu hierarchy</label><br>

    <input type="checkbox" id="termsOfUse" class="contentCheckbox" name="content[]" value="Terms" data-label="Terms of Use" <?=isChecked('Terms')?>>
    <label for="termsOfUse">Terms of Use - All TOU objects and versions</label><br>

    <br/>

    <input id="submitButton" type="submit" value="Import">

    <br/>Paste JSON data here (for import):<br/>
    <textarea id="jsonData" name="jsonData" class="site-import-content-textarea" oninput="toggleSubmitButton()"></textarea>
    
</form>

<script>
    var checkboxes = document.querySelectorAll(".contentCheckbox");
    var submitButton = document.getElementById("submitButton");
    var importForm = document.getElementById("importForm");
    submitButton.disabled = true;

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', toggleSubmitButton);
    });

    function toggleSubmitButton() {
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                submitButton.disabled = false;
                return;
            }
        }
        submitButton.disabled = true;
    }

    // Add confirmation alert on form submit
    importForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get checked checkboxes
        var checkedItems = [];
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                var label = checkbox.getAttribute('data-label') || checkbox.value;
                checkedItems.push(label);
            }
        });
        
        if (checkedItems.length === 0) {
            return false;
        }
        
        // Build comma-separated list
        var itemsList = checkedItems.join(', ');
        
        // Show confirmation alert
        if (confirm('This will delete all existing data for ' + itemsList + '. Are you sure you want to continue?')) {
            // User confirmed, submit the form
            importForm.submit();
        }
        // If user cancels, do nothing (form won't submit)
    });
</script>
