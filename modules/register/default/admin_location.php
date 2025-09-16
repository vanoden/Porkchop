<script type="text/javascript" src="/js/geography.js"></script>
<script language="Javascript">
	function popProvinceSelect() {
		var countryID = document.forms[0].country_id.value;
		var country = Object.create(Country);
		country.id = countryID;
		var provinces = country.getProvinces();
		var selectElem = document.forms[0].province_id;
		selectElem.innerHTML = "";
		for (var i = 0; i < provinces.length; i ++) {
			var option = document.createElement('option');
			option.value = provinces[i].id;
			option.innerHTML = provinces[i].name;
			selectElem.appendChild(option);
		}
		return true;
	}
</script>

<!-- Page Header -->
<?=$page->showAdminPageInfo()?>
<!-- End Page Header -->

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

<form name="locationForm" method="post" action="/_register/admin_location">
    <input type="hidden" name="id" value="<?=$location->id?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <div class="form_instruction">Make changes and click 'Save' to complete.</div>

    <!-- ============================================== -->
    <!-- LOCATION CONTEXT -->
    <!-- ============================================== -->
    <?php	if (isset($_REQUEST['organization_id']) || isset($_REQUEST['user_id'])) { ?>
    <section class="tableBody clean">
        <div class="tableRowHeader">
            <div class="tableCell width-50per">Context</div>
            <div class="tableCell width-50per">Details</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Organization</span>
            </div>
            <div class="tableCell">
                <?php	if (isset($_REQUEST['organization_id'])) { ?>
                    <a href="/_register/admin_organization/<?=$organization->code?>" class="value"><?=$organization->name?></a>
                    <input type="hidden" name="organization_id" value="<?=$organization->id?>" />
                <?php	} else { ?>
                    <span class="value">N/A</span>
                <?php	} ?>
            </div>
        </div>
        <?php	if (isset($_REQUEST['user_id'])) { ?>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Customer</span>
            </div>
            <div class="tableCell">
                <span class="value"><?=$user->full_name()?></span>
                <input type="hidden" name="user_id" value="<?=$user->id?>" />
            </div>
        </div>
        <?php	} ?>
    </section>
    <?php	} ?>

    <!-- ============================================== -->
    <!-- LOCATION DETAILS -->
    <!-- ============================================== -->
    <h3>Location Details</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Field</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Location Name</span>
            </div>
            <div class="tableCell">
                <input type="text" name="name" class="value input width-100per" value="<?=$location->name?>" placeholder="Enter location name" />
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- ADDRESS INFORMATION -->
    <!-- ============================================== -->
    <h3>Address Information</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Field</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Address Line 1</span>
            </div>
            <div class="tableCell">
                <input type="text" name="address_1" class="value input width-100per" value="<?=$location->address_1?>" placeholder="Street address" />
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Address Line 2</span>
            </div>
            <div class="tableCell">
                <input type="text" name="address_2" class="value input width-100per" value="<?=$location->address_2?>" placeholder="Apartment, suite, unit, etc. (optional)" />
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">City</span>
            </div>
            <div class="tableCell">
                <input type="text" name="city" class="value input width-100per" value="<?=$location->city?>" placeholder="City" />
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Country</span>
            </div>
            <div class="tableCell">
                <select name="country_id" class="value input width-100per" onchange="popProvinceSelect();">
                    <option value="">Select Country</option>
        <?php	foreach ($countries as $country) { ?>
                    <option value="<?=$country->id?>"<?php	if ($country->id == $selected_country->id) print " selected"; ?>><?=$country->name?></option>
        <?php	} ?>
                </select>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">State/Province</span>
            </div>
            <div class="tableCell">
                <select name="province_id" class="value input width-100per">
        <?php	if (isset($country->id) && $country->id > 0 && is_array($provinces)) {
                    foreach ($provinces as $province) {
        ?>		
                    <option value="<?=$province->id?>"<?php	if ($province->id == $selected_province->id) print " selected"; ?>><?=$province->name?></option>
        <?php			}
                } else { ?>
                    <option value="">Choose Country First</option>
        <?php		} ?>
                </select>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Postal/Zip Code</span>
            </div>
            <div class="tableCell">
                <input type="text" name="zip_code" class="value input width-100per" value="<?=$location->zip_code?>" placeholder="Postal or ZIP code" />
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- DEFAULT SETTINGS -->
    <!-- ============================================== -->
    <?php if (isset($_REQUEST['organization_id'])) { ?>
    <h3>Default Settings</h3>
    <section class="tableBody clean">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Setting</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Default Location</span>
            </div>
            <div class="tableCell">
                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="default_shipping" value="1" />
                        <span class="value">Default Shipping Location</span>
                        <small class="help-text">(overrides any existing default)</small>
                    </label>
                </div>
                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="default_billing" value="1" />
                        <span class="value">Default Billing Location</span>
                        <small class="help-text">(overrides any existing default)</small>
                    </label>
                </div>
            </div>
        </div>
    </section>
    <?php } ?>

    <!-- ============================================== -->
    <!-- FORM ACTIONS -->
    <!-- ============================================== -->
    <div class="form_footer marginTop_20">
        <input type="submit" name="btn_submit" class="button" value="Save" />
        <?php	if (isset($_REQUEST['organization_id'])) { ?>
        <a href="/_register/admin_organization/<?=$organization->code?>" class="button secondary">Cancel</a>
        <?php	} ?>
    </div>
</form>
