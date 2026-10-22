<?php if ($page->errorCount() > 0) { ?>
<ul class="pageMessage">
	<li class="pageMessage--error"><span class="pageMessage__content"><?=htmlspecialchars($page->errorString())?></span></li>
</ul>
<?php } else if ($page->success) { ?>
<ul class="pageMessage">
	<li class="pageMessage--progress"><span class="pageMessage__content"><?=htmlspecialchars($page->success)?></span></li>
</ul>
<?php } ?>

<section class="register-thanks">
	<h1>Thank you for registering with <?=htmlspecialchars($company->name)?></h1>
	<p class="register-thanks__banner">You will receive an email soon to confirm your email address is valid. Messages are sent from <strong class="register-thanks__email"><?=htmlspecialchars($send_from)?></strong>.</p>
	<ol class="register-thanks__steps">
		<li>
			<div>
				<h2>Add the sender</h2>
				<p>Add <strong class="register-thanks__email"><?=htmlspecialchars($send_from)?></strong> to your contacts.</p>
			</div>
		</li>
		<li>
			<div>
				<h2>Check other folders</h2>
				<p>Look in spam, junk, or bulk mail if the message is not in your inbox.</p>
			</div>
		</li>
	</ol>
</section>
