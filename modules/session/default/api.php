		<span class="title">Porkchop Session API Version <?=$_package["version"]?></span>
		<div id="apiScroller">
		<form method="post" action="/_session/api" name="ping">
		    <input type="hidden" name="method" value="ping">
		    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		    <div class="apiMethod">
			    <div class="h3 apiMethodTitle">ping</div>
			    <div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		    </div>
		</form>
			<form method="post" action="<?=PATH?>/_session/api" name="schemaVersion">
			<input type="hidden" name="method" value="schemaVersion">
			<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
			<div class="apiMethod">
				<div class="h3 apiMethodTitle">schemaVersion</div>
				<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
			</div>
			</form>
		<form method="post" action="/_session/api">
		    <input type="hidden" name="method" value="getSession">
		    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		    <div class="apiMethod">
			    <span class="h3 apiMethodTitle">getSession</span>
			    <div class="apiMethodParameter">
				    <span class="label apiLabel">code</span>
				    <input class="value input apiInput" type="text" name="code"/>
			    </div>
			    <div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		    </div>
		</form>
		<form method="post" action="/_session/api">
		<input type="hidden" name="method" value="getSessionHits">
		<input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		<div class="apiMethod">
			<span class="h3 apiMethodTitle">getSessionHits</span>
			<div class="apiMethodParameter">
				<span class="label apiLabel">code</span>
				<input class="value input apiInput" type="text" name="code"/>
			</div>
			<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		</div>
		</form>
		<form method="post" action="/_session/api">
		    <input type="hidden" name="method" value="addSession">
		    <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
		    <div class="apiMethod">
			    <span class="h3 apiMethodTitle">addSession</span>
			    <div class="apiMethodParameter">
				    <span class="label apiLabel">login</span>
				    <input type="text" name="code" class="value input apiInput"/>
			    </div>
			    <div class="apiMethodParameter">
				    <span class="label apiLabel">password</span>
				    <input type="password" name="password" class="value input apiInput"/>
			    </div>
			    <div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>
		    </div>
		</form>
		</div>
