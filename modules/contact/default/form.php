<style>
    .label {
        width: 150px;
		display: block;
		float: left;
        vertical-align: top;
    }   
    .input {
        margin-bottom: 6px;
    }
	.labelRequired:after {
		content: "*";
	}
	.form_instruction {
		font-style: italic;
		display: block;
	}
</style>
<script type="text/javascript">
    function validateForm() {
        // Check if email addresses match
        var email = document.getElementsByName("email_address")[0].value;
        var emailConfirm = document.getElementsByName("email_address_confirm")[0].value;
        
        if (email !== emailConfirm) {
            alert("Email addresses do not match. Please check and try again.");
            return false;
        }
        
        // Validate email format
        var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }
        
        return true;
    }
</script>
<h2>Contact Information</h2>
<?php	if ($page->errorCount() > 0) { ?>
<span class="form_error"><?=$page->errorString()?></span>
<?php	} else if ($page->success) { ?>
<span class="form_success"><?=$page->success?></span>
<?php	} ?>
<form method="POST" action="/_contact/form" onsubmit="return validateForm();">
<table class="body">
<tr><td id="registrationFormLeftColumn">
		<span class="form_instruction">Fields with * are required</span>
		<div class="newLine">
			<span class="label labelRequired">First Name</span>
			<input type="text" name="first_name" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Last Name</span>
			<input type="text" name="last_name" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Job Title</span>
			<input type="text" name="title" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Company</span>
			<input type="text" name="organization" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Address 1</span>
			<input type="text" name="address_1" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Address 2</span>
			<input type="text" name="address_2" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">City</span>
			<input type="text" name="city" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">State/Province</span>
			<input type="text" name="state" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Zip/Postal Code</span>
			<input type="text" name="zip_code" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Country</span>
			<input type="text" name="country" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Phone</span>
			<input type="text" name="phone" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label">Fax</span>
			<input type="text" name="fax" class="value input"/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Email</span>
			<input type="email" name="email_address" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label labelRequired">Re-type Email</span>
			<input type="email" name="email_address_confirm" class="value input" required/>
		</div>
		<div class="newLine">
			<span class="label">What Can We Help With?</span>
			<textarea name="comments" class="value input"></textarea>
		</div>
		<div class="newLine">
			<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=<?=$GLOBALS['_config']->captcha->public_key ?>"></script>
			<noscript>
				<iframe src="http://www.google.com/recaptcha/api/noscript?k=<?=$GLOBALS['_config']->captcha->public_key ?>" height="300" width="500" frameborder="0"></iframe><br>
				<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
				<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
			</noscript>
		</div>
	</td>
</tr>
<tr><td colspan="2">
	<input type="submit" name="btn_submit" class="button" />
	</td>
</tr>
</table>
</form>
