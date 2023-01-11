<?php	if ($page->errorCount()) { ?>
    <div class="form_error"><?=$page->errorString()?></div>
<?php	} ?>

<h3>Sales Proposal</h3>

<form method="post" action="/_sales/cart">
    <select id="organization_id" name="organization_id">
        <option value="">Select Customer</option>
        <?php
            foreach ($organizations as $organization) {
                $selected = '';
                if ($organization->id == $_REQUEST['organization_id']) $selected = 'selected';
                print "<option value=\"".$organization->id."\" $selected>".$organization->name."</option>";
            }
        ?>
    </select>
    <?php
        // if members then a dropdown of members
        if (count($members)) {
    ?>
        <select id="member_id" name="member_id">
            <?php
                print "<option value=\"\">Select Member</option>";
                foreach ($members as $member) {
                    $selected = '';
                    if ($member->id == $_REQUEST['member_id']) $selected = 'selected';
                    print "<option value=\"".$member->id."\" $selected>".$member->first_name . " " . $member->middle_name . " " . $member->last_name ."</option>";
                }
            ?>
        </select>
        <br/><br/>
    <?php
        }
        // if contacts then a dropdown of contacts for member selected
        if (count($contacts)) {
        ?>
        <p>Select Customer Preferred Contacts:</p>
        <?php
            $currentCategory = "";
            foreach ($contactMethods as $contactMethodType => $contactMethodValue) {
                if (!empty($contactMethodValue)) {
                
                    // default check the first entry
                    $isChecked = false;
                    if ($currentCategory != $contactMethodType) {
                        $isChecked = true;
                        $currentCategory = $contactMethodType;
                    }
                ?>
                    <fieldset>
                        <legend>Select <?=$contactMethodType?>:</legend>
                            <?php
                            foreach ($contactMethodValue as $methodValue) {
                            ?>
                                <div>
                                  <input type="radio" name="<?=$contactMethodType?>" value="<?=$methodValue?>" <?=$isChecked ? "checked='checked'" : ""?>>
                                  <label><?=$methodValue?></label>
                                </div>
                            <?php
                            }
                            ?>
                    </fieldset>
            <?php
                }
            }
        }
    ?>
    <br/><br/>
    <input type="submit" name="btn_submit" value="Continue" />
    <input type="button" name="btn_reset" value="Reset" onclick="window.location.replace('/_sales/cart')" />
</form>
