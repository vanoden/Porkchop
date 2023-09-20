<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

<style>	
    input[type="submit"]:disabled {
        background-color: grey;
        color: white;
    }
    
    textarea {
        width: 100%;
        height: 1000px;
        overflow: auto;
    }
    
</style>

<form action="/_site/import_content" method="post">

    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>

    <input type="checkbox" id="marketingContent" class="contentCheckbox" name="content[]" value="Marketing" <?=isChecked('Marketing')?>>
    <label for="marketingContent">Marketing Content - Web Site page content and page settings</label><br>

    <input type="checkbox" id="navigation" class="contentCheckbox" name="content[]" value="Navigation" <?=isChecked('Navigation')?>>
    <label for="navigation">Navigation - Full menu hierarchy</label><br>

    <input type="checkbox" id="configurations" class="contentCheckbox" name="content[]" value="Configurations" <?=isChecked('Configurations')?>>
    <label for="configurations">Configurations - Site configuration data</label><br>

    <input type="checkbox" id="termsOfUse" class="contentCheckbox" name="content[]" value="Terms" <?=isChecked('Terms')?>>
    <label for="termsOfUse">Terms of Use - All TOU objects and versions</label><br>

    <input id="submitButton" type="submit" value="Import">

    <br/>Paste JSON data here (for import):<br/>
    <textarea id="jsonData" name="jsonData"></textarea>
    
</form>

<script>
    var checkboxes = document.querySelectorAll(".contentCheckbox");
    var submitButton = document.getElementById("submitButton");
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
</script>
