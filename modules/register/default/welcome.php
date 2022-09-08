<style>
	.table-group img { width: 60%; padding: 5%;	}
	.table-group li { text-align: center; }
	.table-group h3, .faq h3 { font-weight: 600; }

	@media screen and (min-width: 750px) {
	.table-group img { width: 100%; max-width: 300px; padding: 5%; }
	.table-group li { text-align: left; }
	.form-grid { grid-gap: 3rem;}
	ul { padding-left: 40px; }
	}
</style>
<h2>Welcome to the Monitor Portal</h2>
<p>Access is authorized to customer of Spectros intstruments only.</p>

<section class="table-group">
	<ul class="form-grid four-col">
		<li>
			<h3 class="textAlignCenter">My Account</h3>
			<a href="/_register/account"><img src="/img/icons/icon_portal_account.png" alt="icon for jobs"></a>
			<ul>
				<li>Update contact information</li>
				<li>Change or reset your password</li>
				<li>Update shipping and billing preferences</li>
			</ul>
			<button onclick="window.location.href='/_register/account';">View Your Account</button>
		</li>
		<li>
			<h3 class="textAlignCenter">Monitors</h3>
			<a href="/_monitor/assets"><img src="/img/icons/icon_portal_monitors.png" alt="icon for jobs"></a>
			<ul>
				<li>View details of all monitors in your organization</li>
				<li>Request support for any monitor</li>
				<li>Run calibration for any monitor</li>
			</ul>
			<button onclick="window.location.href='/_monitor/assets';">Visit Monitors Page</button>
		</li>
		<li>
			<h3 class="textAlignCenter">Jobs</h3>
			<a href="/_monitor/collections"><img src="/img/icons/icon_portal_jobs.png" alt="icon for jobs"></a>
			<ul>
				<li>View history of all jobs within your organization</li>
				<li>View the dashboard for running jobs</li>
				<li>Create new jobs</li>
			</ul>
			<button onclick="window.location.href='/_monitor/collections';">Visit Jobs Page</button>
		</li>
		<li>
			<h3 class="textAlignCenter">Support</h3>
			<a href="/_support/tickets"><img src="/img/icons/icon_portal_support.png" alt="icon for jobs"></a>
			<ul>
				<li>View all support tickets within your organization</li>
				<li>Create a new support request and RMA</li>
				<li>See the status of open tickets</li>
			</ul>
			<button onclick="window.location.href='/_support/tickets';">Visit Support Page</button>
		</li>
	</ul>
</section>