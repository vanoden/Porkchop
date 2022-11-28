<style>
    ul {
        list-style-type: none;
    }
    input.radio {
        box-shadow:  unset;
    }
    .errorText{
        color:red;
    }
    .progressText {
        color:green;
    
    }
    #formError {
        display:none;
    }
</style>
<script>
function selectUsers(type) {
    if (type == 'role') {
        document.getElementById("role").disabled = false;
        document.getElementById("role").value = '';
        document.getElementById("customer").disabled = true;
        document.getElementById("customer").value = '';
    } else {
        document.getElementById("role").disabled = true;
        document.getElementById("role").value = '';
        document.getElementById("customer").disabled = false;
        document.getElementById("customer").value = '';
    }
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
    
    // make sure a recipient is selected
    document.getElementById("createMessageForm").submit();
}
</script>
<span class="title">Create In-Site Message</span>
<?php if ($page->errorCount() > 0) { ?>
    <section id="form-message">
	    <ul class="connectBorder errorText">
		    <li><?=$page->errorString()?></li>
	    </ul>
    </section>
<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>

<span id="formError" class="errorText"></span>

<div class="container">
  <form id="createMessageForm" action="/_site/send_customer_message" method="POST">

	<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">

    <p>Select Send To:</p>    
    <input  id="user_select_radio" class="radio" type="radio" name="selectSendTo" value="role" onclick="selectUsers('role')">
    
    <label for="role">All Users in Role</label>
    <select id="role" name="role" disabled="disabled">
      <option value=""></option>
      <?php
      foreach ($rolesUsersIn as $role) {
      ?>
          <option value="<?=$role?>"><?=$role?></option>
      <?php
      }
      ?>
    </select>

    <br/> -or-<br/>
    
    <input id="customer_select_radio" class="radio" type="radio" name="selectSendTo" value="customer" onclick="selectUsers('customer')">
    
    <label for="customer">Customer</label>
    <select id="customer" name="customer" disabled="disabled">
      <option value=""></option>
      <?php
      foreach ($customersInOrg as $customer) {
      ?>
          <option value="<?=$customer->id?>"><?=$customer->first_name?> <?=$customer->last_name?></option>
      <?php
      }
      ?>
    </select>  
    
    <br/>
    <hr/>
    <input type="checkbox" id="important" name="important" value="important">
    <label for="important">Important?</label><br/>

    <label for="subject">Subject</label>
    <input type="text" id="subject" name="subject">

    <label for="content">Message Content</label>
    <textarea id="content" name="content" style="height:200px"></textarea>
    <input type="hidden" value="submit" name="method"/>
    <input type="button" value="Submit" onclick="createMessage()">

  </form>
</div>
