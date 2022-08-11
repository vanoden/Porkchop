<style>
	.table-group img { width: 60%; padding: 5%;	}
	.table-group li, .table-group.utilityDownloads li { text-align: center; }
	.table-group.faq li { text-align: left; }
	.table-group h3, .faq h3 { font-weight: 600; }
	.faq h2 { font-size: 1.5rem; }
	.faq h3 { font-size: 1.2rem; }
	.faq h4 { font-size: 1.0rem;}
	.faq img { width: 100%; padding: 0%; box-shadow: 0 0 10px rgba(0,0,0,0.3); margin: 0 auto; max-width: 600px; }
	.utilityDownloads img { margin: 0 auto; }
	.faq button { margin: 1.5rem auto 0rem; }
	img + img { margin-top: 1.5rem; }
	/* .faq ol { font-size: 1.2rem;} */
	.faq a { display: inline-block; } /* keep links in a row */
	.notice_warning { background: url('/img/icons/icon_warning_General.svg') no-repeat .5rem .5rem; padding: .7rem .7rem .7rem 3.3rem; background-size: 2rem; background-color: rgba(0,0,0,0.1); }
	.disclaimer { font-size: .8rem;}
	.disclaimer li { margin-top: 0.4rem; }
	span.action-text { 
		background: rgba(47, 198, 30, 0.3);
		display: inline;
		padding: 0.1rem 0.2rem;
		border-radius: 0.3rem;
		margin: 0 .2rem;
		font-weight: 500;
	}

	@media screen and (min-width: 750px) {
	.table-group img { width: 100%; max-width: 300px; padding: 5%; }
	.faq img { width: 100%; padding: 0%; }
	.faq button { margin: 1rem 0 1.2rem 1rem; }
	.faq ol { padding-left: 40px; }
	.faq h2 { font-size: 1.8rem; }
	.faq h3 { font-size: 1.3rem; }
	.faq h4 { font-size: 1.2rem;}
	.faq > ul > li { margin: 0 auto; }
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