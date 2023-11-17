<style>
    ul {
        list-style-type: none;
    }

    input.radio {
        box-shadow: unset;
    }

    .errorText {
        color: red;
    }

    .progressText {
        color: green;

    }

    #formError {
        display: none;
    }
</style>
<script>
    function selectUsers(type) {
        if (type == 'role') {
            document.getElementById("role").disabled = false;
            document.getElementById("role").value = 'All';
            document.getElementById("customer").disabled = true;
            document.getElementById("customer").value = 'All';
            document.getElementById("organization").disabled = true;
            document.getElementById("organization").value = 'All';
        } else {
            document.getElementById("role").disabled = true;
            document.getElementById("role").value = 'All';
            document.getElementById("customer").disabled = false;
            document.getElementById("customer").value = 'All';
            document.getElementById("organization").disabled = false;
            document.getElementById("organization").value = 'All';            
        }
    }

    // update form for organization change
    function changeOrganization() {
        document.getElementById("method").value = 'organizationUpdated';
        document.getElementById("createMessageForm").submit();
    }

    function createMessage() {

        document.getElementById("formError").style.display = 'none';

        // make sure a recipient is selected
        var roleDropdown = document.getElementById("role");
        var customerDropdown = document.getElementById("customer");
        if (!roleDropdown.options[roleDropdown.selectedIndex].value && !customerDropdown.options[customerDropdown.selectedIndex].value) {
            document.getElementById("formError").style.display = 'block';
            document.getElementById("formError").innerHTML = "Please select a role or individual customer to send messages to";
            return false;
        }

        // make sure a message and subject is filled out
        if (!document.getElementById("subject").value || !document.getElementById("content").value) {
            document.getElementById("formError").style.display = 'block';
            document.getElementById("formError").innerHTML = "Please fill out a subject and message to send";
            return false;
        }

        // submit final form
        document.getElementById("method").value = 'submit';
        document.getElementById("createMessageForm").submit();
    }
</script>

<h2 class="title">Create In-Site Message</h2>
<?php
	$page->showAdminPageInfo();
?>

<span id="formError" class="errorText"></span>
<div class="container">
    <form id="createMessageForm" action="/_site/send_customer_message" method="POST">
        <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
        <div class="tableBody">
            <div class="tableRowHeader">
                <div class="tableCell">Select Send To:</div>
            </div>
            <div class="tableRow">
                <div class="tableCell">
                    <input id="user_select_radio" class="radio" type="radio" name="selectSendTo" value="role" onclick="selectUsers('role')">
                    <label for="role">All Users in Role</label>
                    <select id="role" name="role" disabled="disabled">
                        <option value="All">All</option>
                        <?php
                        foreach ($userRoles as $role) {
                            ?>
                            <option value="<?= $role->id ?>">
                                <?= $role->name ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>

                    <br /> -or-<br />

                    <input id="customer_select_radio" class="radio" type="radio" name="selectSendTo" value="customer" <?=isset($_REQUEST['organization']) && !empty($_REQUEST['organization']) ? "checked=\"checked\"" : ""; ?> onclick="selectUsers('customer')">
                    <label for="organization">Organization</label>
                    <select id="organization" name="organization" <?=isset($_REQUEST['organization']) && !empty($_REQUEST['organization']) ? "" : "disabled=\"disabled\""?> onchange="changeOrganization()">
                        <option value="All">All</option>
                        <?php
                        foreach ($organizations as $organization) {
                            ?>
                            <option value="<?= $organization->id ?>"<?php if (isset($_REQUEST['organization']) && !empty($_REQUEST['organization']) && ($organization->id == $_REQUEST['organization'])) echo "selected=\"selected\""?>>
                                <?= $organization->name ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>

                    <label for="customer">Customer</label>
                    <select id="customer" name="customer" <?=isset($_REQUEST['organization']) && !empty($_REQUEST['organization']) ? "" : "disabled=\"disabled\""?>>
                        <option value="All">All</option>
                        <?php
                        foreach ($customersInOrg as $customer) {
                            ?>
                            <option value="<?= $customer->id ?>" <?php if (isset($_REQUEST['customer']) && !empty($_REQUEST['customer']) && ($customer->id == $_REQUEST['customer'])) echo "selected=\"selected\""?>>
                                <?= $customer->first_name ?>
                                <?= $customer->last_name ?>
                            </option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="tableRowHeader">
                <div class="tableCell">Create Message</div>
            </div>
            <div class="tableRow">
                <div class="tableCell">
                    <br /><input type="checkbox" id="important" name="important" value="important" <?=isset($_REQUEST['important']) && !empty($_REQUEST['important']) ? "checked=\"checked\"" : ""?>>
                    <label for="important">Important?</label><br />
                    <br /><label for="subject">Subject</label><br />
                    <input type="text" id="subject" name="subject" style="min-width: 100%;" value="<?=$_REQUEST['subject']?>"><br /><br />
                    <label for="content">Message Content</label><br />
                    <textarea id="content" name="content" style="height:200px"><?=$_REQUEST['content']?></textarea>
                    <input type="hidden" id="method" value="submit" name="method" />
                    <input type="button" value="Submit" onclick="createMessage()">
                </div>
            </div>
        </div>
    </form>
</div>
