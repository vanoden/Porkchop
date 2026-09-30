<link href="/css/upload.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="/includes/register-account.js"></script>

<section id="reg_form" class="body">
	<div class="organization-page-wrapper" style="display: flex; flex-direction: column; width: 100%;">
	
	<!-- Success/Error Messages Section -->
	<div id="pageSubHeading">
		<div id="pageTitle">
			<div class="register-account-page-heading">
<?php if (!empty($customerThumbnailUrl)) { ?>
				<img src="<?= htmlspecialchars($customerThumbnailUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" class="register-admin-account__thumbnail" width="48" height="48">
<?php } ?>
				<h1 id="page_title"><?= htmlspecialchars($page->title()) ?></h1>
<?php if ($GLOBALS['_SESSION_']->customer->can('edit site pages')) { ?>
				<a id="icon_settings" href="/_site/page?module=<?= $page->module() ?>&view=<?= $page->view() ?>&index=<?= $page->index ?>"></a>
<?php } ?>
			</div>
			<?= $page->showBreadcrumbs() ?>
		</div>
		<?= $page->showMessages() ?>
	</div>
	<?php 
	if ($canView) {
		?>
		<form name="register" id="register_form" action="<?= PATH ?>/_register/account" method="POST">
			<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
			<input type="hidden" name="target" value="<?= $target ?>" />
			<input type="hidden" name="customer_id" value="<?= $customer->id ?>" />
			<input type="hidden" name="deleteImage" id="deleteImage" value="" />
			<input type="hidden" id="default_image_id" name="default_image_id" value="" />
			<input type="hidden" id="updateImage" name="updateImage" value="" />
			<input id="method" type="hidden" name="method" value="" />

			<!-- Profile Visibility Section -->
			<?php if (!$readOnly) { ?>
				<section class="form-group profile-visibility">
					<h2>Profile Visibility</h2>
					<div class="section-flex cluster">
						<label class="radio-label">
							<input type="radio" name="profile"
								onchange="document.getElementById('method').value = 'Apply'; document.register.submit();"
								value="public" <?php if ($customer->profile == "public")
									print "checked"; ?>>
							<span class="radio-content">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
									<path
										d="M15 12c0 1.654-1.346 3-3 3s-3-1.346-3-3 1.346-3 3-3 3 1.346 3 3zm9-.449s-4.252 8.449-11.985 8.449c-7.18 0-12.015-8.449-12.015-8.449s4.446-7.551 12.015-7.551c7.694 0 11.985 7.551 11.985 7.551zm-7 .449c0-2.757-2.243-5-5-5s-5 2.243-5 5 2.243 5 5 5 5-2.243 5-5z" />
								</svg>
								Profile Public
							</span>
						</label>
						<label class="radio-label">
							<input type="radio" name="profile"
								onchange="document.getElementById('method').value = 'Apply'; document.register.submit();"
								value="private" <?php if ($customer->profile == "private")
									print "checked"; ?>>
							<span class="radio-content">
								<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
									<path
										d="M19.604 2.562l-3.346 3.137c-1.27-.428-2.686-.699-4.243-.699-7.569 0-12.015 6.551-12.015 6.551s1.928 2.951 5.146 5.138l-2.911 2.909 1.414 1.414 17.37-17.035-1.415-1.415zm-6.016 5.779c-3.288-1.453-6.681 1.908-5.265 5.206l-1.726 1.707c-1.814-1.16-3.225-2.65-4.06-3.66 1.493-1.648 4.817-4.594 9.478-4.594.927 0 1.796.119 2.61.315l-1.037 1.026zm-2.883 7.431l5.09-4.993c1.017 3.111-2.003 6.067-5.09 4.993zm13.295-4.221s-4.252 7.449-11.985 7.449c-1.379 0-2.662-.291-3.851-.737l1.614-1.583c.715.193 1.458.32 2.237.32 4.791 0 8.104-3.527 9.504-5.364-.729-.822-1.956-1.99-3.587-2.952l1.489-1.46c2.982 1.9 4.579 4.327 4.579 4.327z" />
								</svg>
								Profile Private
							</span>
						</label>
					</div>
				</section>

				<section id="form-message" class="message info">
					<ul class="pageMessage">
						<li class="pageMessage--info">Make changes and click 'Apply' to complete.</li>
					</ul>
				</section>
			<?php } ?>

			<!-- Account Information Section -->
			<section class="form-group account-info">
				<h2>Account Information</h2>
				<div class="section-grid grid-col-4">
					<div id="accountEmailQuestion" class="form-field">
						<label for="status">Status</label>
						<input type="text" id="status" value="<?= $queuedCustomer->status ?>" readonly>
						<?php if (! $readOnly &&$queuedCustomer->status == "VERIFYING") { ?>
							<button type="submit" name="method" value="Resend Email">Resend Email</button>
						<?php } ?>
					</div>
					<div id="accountLoginQuestion" class="form-field">
						<label for="user_name">Login</label>
						<input type="text" id="user_name" value="<?= $customer->code ?>" readonly>
					</div>
					<div id="accountFirstNameQuestion" class="form-field">
						<label for="first_name">*First Name</label>
					<?php if ($readOnly) { ?>
						<input type="text" id="first_name" value="<?= $customer->first_name ?>" readonly>
					<?php } else { ?>
						<input type="text" id="first_name" name="first_name" value="<?= $customer->first_name ?>" />
					<?php } ?>
					</div>
					<div id="accountLastNameQuestion" class="form-field">
						<label for="last_name">*Last Name</label>
					<?php if ($readOnly) { ?>
						<input type="text" id="last_name" value="<?= $customer->last_name ?>" readonly>
					<?php } else { ?>
						<input type="text" id="last_name" class="value registerValue registerLastNameValue" name="last_name"
							value="<?= $customer->last_name ?>" />
					<?php } ?>
					</div>
					<div id="accountOrganizationQuestion" class="form-field">
						<label for="organization">*Organization</label>
						<input type="text" id="organization" value="<?= $customer->organization() ? $customer->organization()->name : 'No Organization' ?>" readonly>
					</div>
					<div id="accountTimeZoneQuestion" class="form-field">
						<label for="timezone">*Time Zone</label>
					<?php if ($readOnly) { ?>
						<input type="text" id="timezone" value="<?= $customer->timezone ?>" readonly>
					<?php } else { ?>
						<select id="timezone" name="timezone" class="value input collectionField">
							<?php foreach (timezone_identifiers_list() as $timezone) {
								if (isset($customer->timezone))
									$selected_timezone = $customer->timezone;
								else
									$selected_timezone = 'UTC';
								?>
								<option value="<?= $timezone ?>" <?php if ($timezone == $selected_timezone)
									  print " selected"; ?>>
									<?= $timezone ?></option>
							<?php } ?>
						</select>
					<?php } ?>
					</div>
					<div id="accountJobTitleQuestion" class="form-field">
						<label for="job_title">*Job Title</label>
					<?php if ($readOnly) { ?>
						<input type="text" id="job_title" value="<?= $customer->getMetadata('job_title') ?>" readonly>
					<?php } else { ?>
						<input type="text" id="job_title" name="job_title" value="<?= $customer->getMetadata('job_title') ?>" />
					<?php } ?>
					</div>
					<div id="accountJobDescriptionQuestion" class="form-field col-span-4">
						<label for="job_description">*Job Description</label>
					<?php if ($readOnly) { ?>
						<textarea id="job_description" readonly><?= $customer->getMetadata('job_description') ?></textarea>
					<?php } else { ?>
						<textarea id="job_description" name="job_description" class="register-account-textarea"><?= $customer->getMetadata('job_description') ?></textarea>
					<?php } ?>
					</div>
				</div>
			</section>

			<!-- Contact Methods Section -->
			<section class="form-group contact-methods">
				<h2>Methods of Contact</h2>
				<table id="contact-main-table" class="table--banded">
					<thead>
						<tr>
							<th scope="col">Types</th>
							<th scope="col">Description</th>
							<th scope="col">Address/Number or Email</th>
							<th scope="col">Notes</th>
							<th scope="col">Notify</th>
							<th scope="col">Public</th>
							<th scope="col">Drop</th>
						</tr>
					</thead>
					<tbody>
					<?php if (count($contacts) > 0) {
						foreach ($contacts as $contact) { ?>
						<tr>
							<td data-label="Types">
								<select class="contact_type_value value input" name="type[<?= $contact->id ?>]" <?php if ($readOnly)
									  echo 'disabled'; ?>>
									<?php foreach (array_keys($contact_types) as $contact_type) { ?>
										<option value="<?= $contact_type ?>" <?php if ($contact_type == $contact->type)
											  print " selected"; ?>><?= $contact_types[$contact_type] ?></option>
									<?php } ?>
								</select>
							</td>
							<td data-label="Description">
								<input type="text" name="description[<?= $contact->id ?>]"
									class="value input contactDescriptionColumn"
									value="<?= strip_tags($contact->description) ?>" <?php if ($readOnly)
										  echo 'disabled'; ?> />
							</td>
							<td data-label="Address/Number or Email">
								<input type="text" name="value[<?= $contact->id ?>]" class="value input contactValueColumn"
									value="<?= $contact->value ?>" <?php if ($readOnly)
										  echo 'disabled'; ?> />
							</td>
							<td data-label="Notes">
								<input type="text" name="notes[<?= $contact->id ?>]" class="value input contactNotesColumn"
									value="<?= strip_tags($contact->notes) ?>" <?php if ($readOnly)
										  echo 'disabled'; ?> />
							</td>
							<td data-label="Notify">
								<input type="checkbox" class="contact_notify" name="notify[<?= $contact->id ?>]" value="1" <?php if ($contact->notify)
									  print "checked"; ?> 		<?php if ($readOnly)
													 echo 'disabled'; ?> />
							</td>
							<td data-label="Public">
								<input type="checkbox" class="contact_public" name="public[<?= $contact->id ?>]" value="1" <?php if ($contact->public)
									  print "checked"; ?> 		<?php if ($readOnly)
													 echo 'disabled'; ?> />
							</td>
							<td data-label="Drop" class="text-align--center">
								<button type="button" name="drop_contact[<?= $contact->id ?>]" class="deleteButton"
									onclick="submitDelete(<?= $contact->id ?>)" <?php if ($readOnly)
										  echo 'disabled'; ?>>X</button>
							</td>
						</tr>
					<?php }}
						else { ?>
						<tr>
							<td colspan="7">No contact methods available</td>
						</tr>
					<?php } ?>
					<!-- New Contact Row -->
					<?php if ($my_account) { ?>
					<tr class="new-contact">
						<td data-label="Types">
							<select class="value input" name="type[0]" <?php if ($readOnly)
								echo 'disabled'; ?>>
								<option value="0">Select</option>
								<?php foreach (array_keys($contact_types) as $contact_type) { ?>
									<option value="<?= $contact_type ?>"><?= $contact_types[$contact_type] ?></option>
								<?php } ?>
							</select>
						</td>
						<td data-label="Description"><input type="text" name="description[0]"
								class="value input contactDescriptionColumn" <?php if ($readOnly)
									echo 'disabled'; ?> />
						</td>
						<td data-label="Address/Number or Email"><input type="text" name="value[0]" class="value input contactValueColumn"
								<?php if ($readOnly)
									echo 'disabled'; ?> /></td>
						<td data-label="Notes"><input type="text" name="notes[0]" class="value input contactNotesColumn"
								<?php if ($readOnly)
									echo 'disabled'; ?> /></td>
						<td data-label="Notify">
							<input type="checkbox" class="contact_notify" name="notify[0]" value="1" <?php if ($readOnly)
								echo 'disabled'; ?> />
						</td>
						<td data-label="Public">
							<input type="checkbox" class="contact_public" name="public[0]" value="1" <?php if ($readOnly)
								echo 'disabled'; ?> />
						</td>
						<td data-label="Drop"></td>
					</tr>
					<?php } ?>
					</tbody>
				</table>
			</section>

			<?php if (!$readOnly) { ?>
				<!-- Two-Factor Authentication Section -->
				<?php
					$configurations = new \Site\Configuration(); 
					if ($configurations->getValueBool("use_otp")) { ?>
					<section class="form-group two-factor pageSect_full">
						<h2>Time Based Password [Google Authenticator]</h2>
						<div class="checkbox-group">
							<input id="time_based_password" type="checkbox" name="time_based_password" value="1" <?php if (!empty($customer->time_based_password))
								echo "checked"; ?> 			<?php
											   $roles = $customer->roles();
											   $requiresTOTP = false;
											   $rolesRequiringTOTP = [];
											   foreach ($roles as $role) {
												   if ($role && isset($role->time_based_password) && $role->time_based_password) {
													   $requiresTOTP = true;
													   $rolesRequiringTOTP[] = $role->name;
												   }
											   }
											   $organization = $customer->organization();
											   $orgRequiresTOTP = $organization && isset($organization->time_based_password) && $organization->time_based_password;
											   if ($requiresTOTP || $orgRequiresTOTP)
												   echo "disabled checked";
											   ?>>
							<label for="time_based_password">Enable Two-Factor Authentication</label>
						</div>
						<?php if ($requiresTOTP) { ?>
							<div class="note pageSect_half"><em>TOTP is required by the following roles:
									<?= implode(', ', $rolesRequiringTOTP) ?></em></div>
						<?php } elseif ($orgRequiresTOTP) { ?>
							<div class="note pageSect_half"><em>TOTP is required by the organization:
									<?= $organization->name ?></em></div>
						<?php } ?>
					</section>
				<?php } ?>

			<?php } ?>

			<?php if ($customer->profile == "public" || !$readOnly) { ?>
				<!-- Form Actions Section -->
				<section class="form-group form-actions">
					<div class="button-group pageSect_full">
						<?php if ($customer->profile == "public") { ?>
							<button type="button" class="btn-secondary" onclick="window.open('/_register/businesscard?customer_id=<?= $customer->id ?>', '_blank')">Preview Business Card</button>
						<?php } ?>
						<?php if (!$readOnly) { ?>
							<button type="button" name="method" value="Change Password" class="btn-secondary" onclick="return passChange();">Change Password</button>
						<?php } ?>
					</div>
					<?php if (!$readOnly) { ?>
						<div class="button-group pageSect_full">
							<button type="submit" name="method" value="Apply" onclick="return submitForm();">Apply Changes</button>
						</div>
					<?php } ?>
				</section>
			<?php } ?>

			<section class="form-group image-selection-section">
				<h2>Click to select new customer image</h2>
				<?php $defaultImageId = $customer->getMetadata('default_image'); ?>
				<div class="register-admin-account-images__gallery">
<?php if (empty($customerImages)) { ?>
					<p class="register-admin-account-images__empty">No images found for this customer.</p>
<?php } else {
					foreach ($customerImages as $image) {
						$isDefault = ($defaultImageId == $image->id);
?>
					<div class="register-admin-account-images__item<?= $isDefault ? ' register-admin-account-images__item--default' : '' ?>" id="ItemImageDiv_<?= (int)$image->id ?>">
						<div class="register-admin-account-images__thumb">
							<img src="/_storage/downloadfile?file_id=<?= (int)$image->id ?>" alt="<?= htmlspecialchars($image->name, ENT_QUOTES, 'UTF-8') ?>" />
						</div>
						<p class="register-admin-account-images__caption" title="<?= htmlspecialchars($image->name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($image->name) ?></p>
						<?php if ($isDefault) { ?>
						<span class="register-admin-account-images__badge">Default</span>
						<?php } elseif (!$readOnly) { ?>
						<button type="button" class="button btn-secondary register-admin-account-images__set-default" onclick="updateDefaultImage('<?= (int)$image->id ?>');">Set Default</button>
						<?php } ?>
					</div>
<?php }
				} ?>
				</div>
			</section>
		</form>

		<!-- Image Upload Section -->
		<?php if (!$readOnly && !empty($repository) && !empty($repository->id)) { ?>
			<section class="form-group image-upload">
				<form name="repoUpload" action="/_register/account/<?= rawurlencode($customer->code) ?>" method="post" enctype="multipart/form-data" class="register-admin-account-images__upload-form">
					<h2>Upload Image for this customer</h2>
					<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
					<input type="hidden" name="repository_id" value="<?= (int)$repository->id ?>" />
					<input type="file" name="uploadFile" accept="image/*" />
					<input type="submit" name="btn_submit" class="button" value="Upload" />
				</form>
			</section>
		<?php } elseif (!$readOnly) { ?>
			<section class="form-group image-upload">
				<h2>Upload Image for this customer</h2>
				<p class="error-message">Repository not found. (please create an S3, Local, Google or Dropbox repository to upload images for this customer)</p>
			</section>
		<?php } ?>

		<!-- Hidden Delete Contact Form -->
		<form id="delete-contact" name="delete-contact" action="<?= PATH ?>/_register/account" method="post"
			class="hidden-form">
			<input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
			<input type="hidden" id="submit-type" name="submit-type" value="delete-contact" />
			<input type="hidden" id="register-contacts-id" name="register-contacts-id" value="" />
		</form>
	<?php } ?>
	</div>
</section>
