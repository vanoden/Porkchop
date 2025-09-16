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

<form method="post" action="/_company/configuration">
    <input type="hidden" name="id" value="<?=$company->id?>" />
    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
    
    <div class="form_instruction">Update company information and click 'Save' to complete.</div>

    <!-- ============================================== -->
    <!-- COMPANY BASIC INFORMATION -->
    <!-- ============================================== -->
    <h3>Company Information</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Field</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Company Name</span>
            </div>
            <div class="tableCell">
                <input type="text" name="name" class="value input width-100per" value="<?=$company->name?>" placeholder="Enter company name" required />
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Login Code</span>
            </div>
            <div class="tableCell">
                <input type="text" name="login" class="value input width-100per" value="<?=$company->login?>" placeholder="Enter login code" />
                <small class="help-text">Unique identifier for company login</small>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Status</span>
            </div>
            <div class="tableCell">
                <select name="status" class="value input width-100per">
                    <option value="ACTIVE" <?php if ($company->status == 'ACTIVE') print " selected"; ?>>Active</option>
                    <option value="INACTIVE" <?php if ($company->status == 'INACTIVE') print " selected"; ?>>Inactive</option>
                    <option value="SUSPENDED" <?php if ($company->status == 'SUSPENDED') print " selected"; ?>>Suspended</option>
                </select>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- COMPANY DETAILS -->
    <!-- ============================================== -->
    <h3>Company Details</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Field</div>
            <div class="tableCell width-75per">Value</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Company ID</span>
            </div>
            <div class="tableCell">
                <span class="value"><?=$company->id?></span>
                <small class="help-text">System-generated unique identifier</small>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Primary Domain</span>
            </div>
            <div class="tableCell">
                <span class="value"><?=$company->primary_domain ? $company->primary_domain : 'Not Set'?></span>
                <small class="help-text">Primary domain associated with this company</small>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Deleted</span>
            </div>
            <div class="tableCell">
                <div class="checkbox-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="deleted" value="1" <?php if ($company->deleted) print " checked"; ?> />
                        <span class="value">Mark as Deleted</span>
                        <small class="help-text">Soft delete - marks company as deleted without removing data</small>
                    </label>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- RELATED INFORMATION -->
    <!-- ============================================== -->
    <h3>Related Information</h3>
    <section class="tableBody clean min-tablet">
        <div class="tableRowHeader">
            <div class="tableCell width-25per">Type</div>
            <div class="tableCell width-50per">Count</div>
            <div class="tableCell width-25per">Actions</div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Locations</span>
            </div>
            <div class="tableCell">
                <span class="value"><?=count($company->locations())?> locations</span>
            </div>
            <div class="tableCell">
                <a href="/_company/locations" class="button secondary">Manage</a>
            </div>
        </div>
        <div class="tableRow">
            <div class="tableCell">
                <span class="label">Domains</span>
            </div>
            <div class="tableCell">
                <span class="value"><?=count($company->domains())?> domains</span>
            </div>
            <div class="tableCell">
                <a href="/_company/domains" class="button secondary">Manage</a>
            </div>
        </div>
    </section>

    <!-- ============================================== -->
    <!-- FORM ACTIONS -->
    <!-- ============================================== -->
    <div class="form_footer marginTop_20">
        <input type="submit" name="btn_submit" value="Update" />
        <a href="/_company" class="button secondary">Cancel</a>
    </div>
</form>
