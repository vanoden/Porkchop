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
    button {
        background-color: #4CAF50;
    }
</style>

<script>
// copy the JSON data to the clipboard
async function copyText() {
  var text = document.getElementById("JSONContent").value;
  try {
    await navigator.clipboard.writeText(text);
    alert('Text copied to clipboard');
  } catch (err) {
    // copy for older browsers
    var text = document.getElementById("JSONContent");
    text.select();
    document.execCommand("copy");
    alert("Text copied to clipboard");
  }
}

// toggle the debug content
function toggleCollapse() {
    var content = document.getElementById("content");
    var chevron = document.getElementById("chevron");

    if (content.style.display === "none") {
        content.style.display = "block";
        chevron.innerHTML = "&#9660;";
    } else {
        content.style.display = "none";
        chevron.innerHTML = "&#9655;";
    }
}
</script>

<form action="/_site/export_content" method="post">

    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>"/>

    <input type="checkbox" id="marketingContent" class="contentCheckbox" name="content[]" value="Marketing" <?=isChecked('Marketing')?>>
    <label for="marketingContent">Marketing Content - Web Site page content and page settings</label><br>

    <input type="checkbox" id="navigation" class="contentCheckbox" name="content[]" value="Navigation" <?=isChecked('Navigation')?>>
    <label for="navigation">Navigation - Full menu hierarchy</label><br>

    <input type="checkbox" id="configurations" class="contentCheckbox" name="content[]" value="Configurations" <?=isChecked('Configurations')?>>
    <label for="configurations">Configurations - Site configuration data</label><br>

    <input type="checkbox" id="termsOfUse" class="contentCheckbox" name="content[]" value="Terms" <?=isChecked('Terms')?>>
    <label for="termsOfUse">Terms of Use - All TOU objects and versions</label><br>

    <input id="submitButton" type="submit" value="Export">
    
    <button onclick="copyText()" class="secondary" type="button">&#x1F4CB; Copy to clipboard</button>
    <textarea id="JSONContent"><?=$siteData->getJSON()?></textarea>

    <?php
    if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'true'):
    ?>
        <div id="collapsibleDiv">
            <button onclick="toggleCollapse()" class="secondary chevron-btn" type="button">
                Debug <span id="chevron">&#9655;</span>
            </button>
            <div id="content" style="display: none;">
                <textarea><?=$siteData->viewData()?></textarea>
            </div>
        </div>
    <?php
    endif;
    ?>
    
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
