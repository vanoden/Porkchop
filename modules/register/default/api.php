		<span class="title">Porkchop Register API Version <?=$_package["version"]?></span>
		<div id="apiScroller">
			<div class="h3 apiMethodTitle">Request</div>
			<pre id="requestContent" style="text-align: left; width: 550px; height: 100px; overflow: auto; font-size: 11px; border: 1px dashed blue;"><?=print_r($_REQUEST)?></pre>
			<form method="post" action="<?=PATH?>/_register/api" name="ping">
			<input type="hidden" name="method" value="ping">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">ping</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="getCustomer">
			<input type="hidden" name="method" value="getCustomer">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getCustomer</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">login</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="addCustomer">
			<input type="hidden" name="method" value="addCustomer">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addCustomer</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">first_name</span>
					<input type="text" name="first_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">last_name</span>
					<input type="text" name="last_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">password</span>
					<input type="text" name="password" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="updateCustomer">
			<input type="hidden" name="method" value="updateCustomer">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">updateCustomer</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">first_name</span>
					<input type="text" name="first_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">last_name</span>
					<input type="text" name="last_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">password</span>
					<input type="text" name="password" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="verifyEmail">
			<input type="hidden" name="method" value="verifyEmail">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">verifyEmail</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">login</span>
					<input type="text" name="login" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">validation_key</span>
					<input type="text" name="validation_key" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="authenticateSession">
			<input type="hidden" name="method" value="authenticateSession">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">authenticateSession</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">login</span>
					<input type="text" name="login" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">password</span>
					<input type="text" name="password" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="findCustomers">
			<input type="hidden" name="method" value="findCustomers">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findCustomers</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">login</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">first_name</span>
					<input type="text" name="first_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">last_name</span>
					<input type="text" name="last_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">active</span>
					<input type="checkbox" name="active" class="input value methodInput" value=1 checked />
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="findRoleMembers">
			<input type="hidden" name="method" value="findRoleMembers">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findRoleMembers</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">id</span>
					<input type="text" name="id" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="addRole">
			<input type="hidden" name="method" value="addRole">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addRole</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="notifyContact">
			<input type="hidden" name="method" value="notifyContact">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">notifyContact</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">id</span>
					<input type="text" name="id" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">subject</span>
					<input type="text" name="subject" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">body</span>
					<input type="text" name="body" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">from_address</span>
					<input type="text" name="from_address" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">from_name</span>
					<input type="text" name="from_name" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="getOrganization">
			<input type="hidden" name="method" value="getOrganization"/>
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getOrganization</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="findOrganizations">
			<input type="hidden" name="method" value="findOrganizations">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findOrganizations</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="addOrganization">
			<input type="hidden" name="method" value="addOrganization">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addOrganization</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">code</span>
					<input type="text" name="code" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">name</span>
					<input type="text" name="name" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="findContacts">
			<input type="hidden" name="method" value="findContacts">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findContacts</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">person</span>
					<input type="text" name="person" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">type</span>
					<input type="text" name="type" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">value</span>
					<input type="text" name="value" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="findOrganizationOwnedProducts">
			<input type="hidden" name="method" value="findOrganizationOwnedProducts">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">findOrganizationOwnedProducts</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">product</span>
					<input type="text" name="product" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="getOrganizationOwnedProduct">
			<input type="hidden" name="method" value="getOrganizationOwnedProduct">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getOrganizationOwnedProduct</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">product</span>
					<input type="text" name="product" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="addOrganizationOwnedProduct">
			<input type="hidden" name="method" value="addOrganizationOwnedProduct">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addOrganizationOwnedProduct</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">organization</span>
					<input type="text" name="organization" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">product</span>
					<input type="text" name="product" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">quantity</span>
					<input type="text" name="quantity" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="expireAgingCustomers">
			<input type="hidden" name="method" value="expireAgingCustomers">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">expireAgingCustomers</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="expireInactiveOrganizations">
			<input type="hidden" name="method" value="expireInactiveOrganizations">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">expireInactiveOrganizations</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="addRolePrivilege">
			<input type="hidden" name="method" value="addRolePrivilege">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">addRolePrivilege</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">role</span>
					<input type="text" name="role" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">privilege</span>
					<input type="text" name="privilege" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="getRolePrivileges">
			<input type="hidden" name="method" value="getRolePrivileges">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">getRolePrivileges</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">role</span>
					<input type="text" name="role" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
			<form method="post" action="<?=PATH?>/_register/api" name="customerHasPrivilege">
			<input type="hidden" name="method" value="customerHasPrivilege">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">customerHasPrivilege</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">login</span>
					<input type="text" name="login" class="input value methodInput"/>
				</div>
				<div class="apiMethodParameter">
					<span class="label apiLabel">privilege</span>
					<input type="text" name="privilege" class="input value methodInput"/>
				</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		</div>