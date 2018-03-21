<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Web-based fumigation monitoring to optimize assets and increase operational efficiency.">
	<meta name="keywords" content="web-based fumigation monitoring, gas fumigation, advanced infrared sensors, gas measurement, temperature measurement, non-dispersed infrared sensors">
	<title>Spectros Instruments</title>
	<!--Bootstrap Includes-->
	<script src="https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
	<!--	CSS Styles-->
	<link href="css/main.css" type="text/css" rel="stylesheet">
	<link href="css/contact.css" type="text/css" rel="stylesheet">
	<!--	Favicon Versions-->
	<link rel="apple-touch-icon-precomposed" sizes="57x57" href="img/favicon/apple-touch-icon-57x57.png" />
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/favicon/apple-touch-icon-114x114.png" />
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/favicon/apple-touch-icon-72x72.png" />
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="img/favicon/apple-touch-icon-144x144.png" />
	<link rel="apple-touch-icon-precomposed" sizes="60x60" href="img/favicon/apple-touch-icon-60x60.png" />
	<link rel="apple-touch-icon-precomposed" sizes="120x120" href="img/favicon/apple-touch-icon-120x120.png" />
	<link rel="apple-touch-icon-precomposed" sizes="76x76" href="img/favicon/apple-touch-icon-76x76.png" />
	<link rel="apple-touch-icon-precomposed" sizes="152x152" href="img/favicon/apple-touch-icon-152x152.png" />
	<link rel="icon" type="image/png" href="img/favicon/favicon-196x196.png" sizes="196x196" />
	<link rel="icon" type="image/png" href="img/favicon/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/png" href="img/favicon/favicon-32x32.png" sizes="32x32" />
	<link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png" sizes="16x16" />
	<link rel="icon" type="image/png" href="img/favicon/favicon-128.png" sizes="128x128" />
	<meta name="application-name" content="Spectros Instruments"/>
	<meta name="msapplication-TileColor" content="#FFFFFF" />
	<meta name="msapplication-TileImage" content="img/favicon//mstile-144x144.png" />
	<meta name="msapplication-square70x70logo" content="img/favicon/mstile-70x70.png" />
	<meta name="msapplication-square150x150logo" content="img/favicon/mstile-150x150.png" />
	<meta name="msapplication-wide310x150logo" content="img/favicon/mstile-310x150.png" />
	<meta name="msapplication-square310x310logo" content="img/favicon/mstile-310x310.png" />

	<!-- Porkchop JS Includes -->
	<script src="/js/porkchop_api.js" type="text/javascript"></script>
	<script src="/js/register.js" type="text/javascript"></script>
	<script src="/js/site.js" type="text/javascript"></script>
	<script src="https://www.google.com/recaptcha/api.js"></script>
</head>

<body onload="setNav()">
<div id="page">
<header>
	<a class="logo" href="index.html"><span>Spectros Instruments</span></a>
</header>

<div id="container"> <!-- START Container -->
<!-- ========================================== -->	
	
<section class="bnr-sales bnr-short" id="banner">
	<article class="segment">
		<div class="banner-text">
			<h1>Service &amp; Support</h1>
			<h2>We're Here to Help</h2>
<!--			<p>Your Dashboard provides advanced monitoring, alerts, graphing and reports from anywhere in the world.</p>-->
		</div>
		<div class="hero"></div>
	</article>
</section>
	
<section class="top-50">
	<article class="segment">
		<div class="row">
			<div class="col-md-12">
			<?	if ($GLOBALS['_page']->error) { ?>
			<p class="form_error"><?=$GLOBALS['_page']->error?></p>
			<?	} ?>
			<h1>Tell Us About Your Service Needs</h1>
			<p>We would be glad to help you with servicing your product(s). Please be as descriptive as possible and be sure to check off an include all accessories with your product.</p>
			<div class="error">Fields outlined in red are required</div>
			<form class="form-narrow">
				<div class="row">
				<div class="col-md-12">
					<h5>Company Name</h5>
					<input type="text" name="company" value="">
				</div>
				<div class="col-md-6">
					<h5>First Name</h5>
					<input type="text" name="firstname" value="" required>
				</div>
				<div class="col-md-6">
					<h5>Last Name</h5>
					<input type="text" name="lastname" value="" required>
				</div>
				<div class="col-md-6">
					<h5>Phone Number</h5>
					<input type="text" name="phone" value="">
				</div>
				<div class="col-md-6">
					<h5>Fax Number</h5>
				<input type="text" name="fax" value="">
				</div>
				<div class="col-md-6">
					<h5>Purhcase Order #</h5>
				<input type="text" name="purchase-order" value="" required>
				</div>
				<div class="col-md-12">
					<h2>Information About Your Instrument</h2>
					<h5>Model Number</h5>
					<select name="choose-model" required>
						<option value="PM200">PM-EC</option>
						<option value="PM200">PM200</option>
						<option value="PM200">PM400</option>
						<option value="PM200">SFExplorIR</option>
						<option value="PM200">SFReportIR</option>
						<option value="PM200">SFContainIR</option>
						<option value="PM200">SF400</option>
						<option value="PM200">MBContainIR</option>
						<option value="PM200">MB400</option>
					</select>
				</div>
				<div class="col-md-6">
					<h5>Serial Number</h5>
					<input type="text" name="serial" value="" label="serial" required maxlength="8">
				</div>
				<div class="col-md-12">
						<h5 class="tool-header">Inlcuded Accessories</h5>
					<div class="tooltip">
						<img src="img/_global/form-question.svg" alt="questions">
						<div class="tooltiptext">
							<h5>What Does Your Product Come With?</h5>
							<ul>
								<li><b>MBContainIR, SFExplorIR, SFContainIR:</b> everything below except WiFi Hotspot</li>
								<li><b>PM400, SF400, MB400:</b> External Filters, WiFi</li>
								<li><b>SFReportIR, PM200, PM-EC:</b> External Filters only</li>
							</ul>
						</div>
					</div>
					<div class="error">You must include all accessories and confirm by checking them off in order for us to service your product.</div>
					<div><input type="checkbox" name="confirm-acc" value="" required> I have included all the accessories checked below.</div>
					<div class="row form-shaded">
						<div class="col-md-4"><input type="checkbox" name="case" value="">External Case</div>
						<div class="col-md-4"><input type="checkbox" name="bag" value="">Nylon Bag</div>
						<div class="col-md-4"><input type="checkbox" name="filters" value="">External Filters</div>
						<div class="col-md-4"><input type="checkbox" name="power" value="">A/C Power Supply</div>
						<div class="col-md-4"><input type="checkbox" name="hotspot" value="">WiFi Hotspot</div>
						<div class="col-md-4"><input type="checkbox" name="batter" value="">Lithium-ion Battery</div>
						<div class="col-md-4"><input type="checkbox" name="printer" value="">Thermal Printer</div>					
					</div>	
				</div>
				<div class="col-md-12">
					<h5>Describe you service needs (symptoms, problems, comments):</h5>
					<textarea name="message"></textarea><h2>Shipping Address</h2>
				</div>
				<div class="col-md-12">
					<h5>Company</h5>
					<input type="text" name="ship-company" value="">
				</div>
				<div class="col-md-12">
					<h5>Country</h5>
					<select name="Choose Country">
						<option value="AF">Afghanistan</option>
						<option value="AX">Åland Islands</option>
						<option value="AL">Albania</option>
						<option value="DZ">Algeria</option>
						<option value="AS">American Samoa</option>
						<option value="AD">Andorra</option>
						<option value="AO">Angola</option>
						<option value="AI">Anguilla</option>
						<option value="AQ">Antarctica</option>
						<option value="AG">Antigua and Barbuda</option>
						<option value="AR">Argentina</option>
						<option value="AM">Armenia</option>
						<option value="AW">Aruba</option>
						<option value="AU">Australia</option>
						<option value="AT">Austria</option>
						<option value="AZ">Azerbaijan</option>
						<option value="BS">Bahamas</option>
						<option value="BH">Bahrain</option>
						<option value="BD">Bangladesh</option>
						<option value="BB">Barbados</option>
						<option value="BY">Belarus</option>
						<option value="BE">Belgium</option>
						<option value="BZ">Belize</option>
						<option value="BJ">Benin</option>
						<option value="BM">Bermuda</option>
						<option value="BT">Bhutan</option>
						<option value="BO">Bolivia, Plurinational State of</option>
						<option value="BQ">Bonaire, Sint Eustatius and Saba</option>
						<option value="BA">Bosnia and Herzegovina</option>
						<option value="BW">Botswana</option>
						<option value="BV">Bouvet Island</option>
						<option value="BR">Brazil</option>
						<option value="IO">British Indian Ocean Territory</option>
						<option value="BN">Brunei Darussalam</option>
						<option value="BG">Bulgaria</option>
						<option value="BF">Burkina Faso</option>
						<option value="BI">Burundi</option>
						<option value="KH">Cambodia</option>
						<option value="CM">Cameroon</option>
						<option value="CA">Canada</option>
						<option value="CV">Cape Verde</option>
						<option value="KY">Cayman Islands</option>
						<option value="CF">Central African Republic</option>
						<option value="TD">Chad</option>
						<option value="CL">Chile</option>
						<option value="CN">China</option>
						<option value="CX">Christmas Island</option>
						<option value="CC">Cocos (Keeling) Islands</option>
						<option value="CO">Colombia</option>
						<option value="KM">Comoros</option>
						<option value="CG">Congo</option>
						<option value="CD">Congo, the Democratic Republic of the</option>
						<option value="CK">Cook Islands</option>
						<option value="CR">Costa Rica</option>
						<option value="CI">Côte d'Ivoire</option>
						<option value="HR">Croatia</option>
						<option value="CU">Cuba</option>
						<option value="CW">Curaçao</option>
						<option value="CY">Cyprus</option>
						<option value="CZ">Czech Republic</option>
						<option value="DK">Denmark</option>
						<option value="DJ">Djibouti</option>
						<option value="DM">Dominica</option>
						<option value="DO">Dominican Republic</option>
						<option value="EC">Ecuador</option>
						<option value="EG">Egypt</option>
						<option value="SV">El Salvador</option>
						<option value="GQ">Equatorial Guinea</option>
						<option value="ER">Eritrea</option>
						<option value="EE">Estonia</option>
						<option value="ET">Ethiopia</option>
						<option value="FK">Falkland Islands (Malvinas)</option>
						<option value="FO">Faroe Islands</option>
						<option value="FJ">Fiji</option>
						<option value="FI">Finland</option>
						<option value="FR">France</option>
						<option value="GF">French Guiana</option>
						<option value="PF">French Polynesia</option>
						<option value="TF">French Southern Territories</option>
						<option value="GA">Gabon</option>
						<option value="GM">Gambia</option>
						<option value="GE">Georgia</option>
						<option value="DE">Germany</option>
						<option value="GH">Ghana</option>
						<option value="GI">Gibraltar</option>
						<option value="GR">Greece</option>
						<option value="GL">Greenland</option>
						<option value="GD">Grenada</option>
						<option value="GP">Guadeloupe</option>
						<option value="GU">Guam</option>
						<option value="GT">Guatemala</option>
						<option value="GG">Guernsey</option>
						<option value="GN">Guinea</option>
						<option value="GW">Guinea-Bissau</option>
						<option value="GY">Guyana</option>
						<option value="HT">Haiti</option>
						<option value="HM">Heard Island and McDonald Islands</option>
						<option value="VA">Holy See (Vatican City State)</option>
						<option value="HN">Honduras</option>
						<option value="HK">Hong Kong</option>
						<option value="HU">Hungary</option>
						<option value="IS">Iceland</option>
						<option value="IN">India</option>
						<option value="ID">Indonesia</option>
						<option value="IR">Iran, Islamic Republic of</option>
						<option value="IQ">Iraq</option>
						<option value="IE">Ireland</option>
						<option value="IM">Isle of Man</option>
						<option value="IL">Israel</option>
						<option value="IT">Italy</option>
						<option value="JM">Jamaica</option>
						<option value="JP">Japan</option>
						<option value="JE">Jersey</option>
						<option value="JO">Jordan</option>
						<option value="KZ">Kazakhstan</option>
						<option value="KE">Kenya</option>
						<option value="KI">Kiribati</option>
						<option value="KP">Korea, Democratic People's Republic of</option>
						<option value="KR">Korea, Republic of</option>
						<option value="KW">Kuwait</option>
						<option value="KG">Kyrgyzstan</option>
						<option value="LA">Lao People's Democratic Republic</option>
						<option value="LV">Latvia</option>
						<option value="LB">Lebanon</option>
						<option value="LS">Lesotho</option>
						<option value="LR">Liberia</option>
						<option value="LY">Libya</option>
						<option value="LI">Liechtenstein</option>
						<option value="LT">Lithuania</option>
						<option value="LU">Luxembourg</option>
						<option value="MO">Macao</option>
						<option value="MK">Macedonia, the former Yugoslav Republic of</option>
						<option value="MG">Madagascar</option>
						<option value="MW">Malawi</option>
						<option value="MY">Malaysia</option>
						<option value="MV">Maldives</option>
						<option value="ML">Mali</option>
						<option value="MT">Malta</option>
						<option value="MH">Marshall Islands</option>
						<option value="MQ">Martinique</option>
						<option value="MR">Mauritania</option>
						<option value="MU">Mauritius</option>
						<option value="YT">Mayotte</option>
						<option value="MX">Mexico</option>
						<option value="FM">Micronesia, Federated States of</option>
						<option value="MD">Moldova, Republic of</option>
						<option value="MC">Monaco</option>
						<option value="MN">Mongolia</option>
						<option value="ME">Montenegro</option>
						<option value="MS">Montserrat</option>
						<option value="MA">Morocco</option>
						<option value="MZ">Mozambique</option>
						<option value="MM">Myanmar</option>
						<option value="NA">Namibia</option>
						<option value="NR">Nauru</option>
						<option value="NP">Nepal</option>
						<option value="NL">Netherlands</option>
						<option value="NC">New Caledonia</option>
						<option value="NZ">New Zealand</option>
						<option value="NI">Nicaragua</option>
						<option value="NE">Niger</option>
						<option value="NG">Nigeria</option>
						<option value="NU">Niue</option>
						<option value="NF">Norfolk Island</option>
						<option value="MP">Northern Mariana Islands</option>
						<option value="NO">Norway</option>
						<option value="OM">Oman</option>
						<option value="PK">Pakistan</option>
						<option value="PW">Palau</option>
						<option value="PS">Palestinian Territory, Occupied</option>
						<option value="PA">Panama</option>
						<option value="PG">Papua New Guinea</option>
						<option value="PY">Paraguay</option>
						<option value="PE">Peru</option>
						<option value="PH">Philippines</option>
						<option value="PN">Pitcairn</option>
						<option value="PL">Poland</option>
						<option value="PT">Portugal</option>
						<option value="PR">Puerto Rico</option>
						<option value="QA">Qatar</option>
						<option value="RE">Réunion</option>
						<option value="RO">Romania</option>
						<option value="RU">Russian Federation</option>
						<option value="RW">Rwanda</option>
						<option value="BL">Saint Barthélemy</option>
						<option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
						<option value="KN">Saint Kitts and Nevis</option>
						<option value="LC">Saint Lucia</option>
						<option value="MF">Saint Martin (French part)</option>
						<option value="PM">Saint Pierre and Miquelon</option>
						<option value="VC">Saint Vincent and the Grenadines</option>
						<option value="WS">Samoa</option>
						<option value="SM">San Marino</option>
						<option value="ST">Sao Tome and Principe</option>
						<option value="SA">Saudi Arabia</option>
						<option value="SN">Senegal</option>
						<option value="RS">Serbia</option>
						<option value="SC">Seychelles</option>
						<option value="SL">Sierra Leone</option>
						<option value="SG">Singapore</option>
						<option value="SX">Sint Maarten (Dutch part)</option>
						<option value="SK">Slovakia</option>
						<option value="SI">Slovenia</option>
						<option value="SB">Solomon Islands</option>
						<option value="SO">Somalia</option>
						<option value="ZA">South Africa</option>
						<option value="GS">South Georgia and the South Sandwich Islands</option>
						<option value="SS">South Sudan</option>
						<option value="ES">Spain</option>
						<option value="LK">Sri Lanka</option>
						<option value="SD">Sudan</option>
						<option value="SR">Suriname</option>
						<option value="SJ">Svalbard and Jan Mayen</option>
						<option value="SZ">Swaziland</option>
						<option value="SE">Sweden</option>
						<option value="CH">Switzerland</option>
						<option value="SY">Syrian Arab Republic</option>
						<option value="TW">Taiwan, Province of China</option>
						<option value="TJ">Tajikistan</option>
						<option value="TZ">Tanzania, United Republic of</option>
						<option value="TH">Thailand</option>
						<option value="TL">Timor-Leste</option>
						<option value="TG">Togo</option>
						<option value="TK">Tokelau</option>
						<option value="TO">Tonga</option>
						<option value="TT">Trinidad and Tobago</option>
						<option value="TN">Tunisia</option>
						<option value="TR">Turkey</option>
						<option value="TM">Turkmenistan</option>
						<option value="TC">Turks and Caicos Islands</option>
						<option value="TV">Tuvalu</option>
						<option value="UG">Uganda</option>
						<option value="UA">Ukraine</option>
						<option value="AE">United Arab Emirates</option>
						<option value="GB">United Kingdom</option>
						<option value="US" selected>United States</option>
						<option value="UM">United States Minor Outlying Islands</option>
						<option value="UY">Uruguay</option>
						<option value="UZ">Uzbekistan</option>
						<option value="VU">Vanuatu</option>
						<option value="VE">Venezuela, Bolivarian Republic of</option>
						<option value="VN">Viet Nam</option>
						<option value="VG">Virgin Islands, British</option>
						<option value="VI">Virgin Islands, U.S.</option>
						<option value="WF">Wallis and Futuna</option>
						<option value="EH">Western Sahara</option>
						<option value="YE">Yemen</option>
						<option value="ZM">Zambia</option>
						<option value="ZW">Zimbabwe</option>
					</select>
				</div>
				<div class="col-md-12">
					<h5>Street Address</h5><input type="text" name="ship-addr1" value="">
				</div>
				<div class="col-md-12">
					<h5>Apt/Suite/Other</h5><input type="text" name="ship-addr2" value="">
				</div>
				<div class="col-md-12">
					<div class="form-group"><h5>City</h5><input type="text" name="ship-city" value=""></div>
					<div class="form-group"><h5>State</h5><input type="text" name="ship-state" value=""></div>
					<div class="form-group"><h5>ZIP</h5><input type="text" name="ship-zip" value=""></div>
				</div>
				<div class="col-md-12"><h2>Billing Address</h2></div>
				<div class="col-md-12">
					<input type="checkbox" name="bill-ship" id="same-ship"><p style="display: inline; padding-left: 10px;">Same as Shipping Address</p>
				</div>
				<div class="col-md-12">
					<h5>Company</h5><input type="text" name="bill-company" value="">
				</div>
				<div class="col-md-12">
					<h5>Address Line 1</h5><input type="text" name="bill-addr1" value="">
				</div>
				<div class="col-md-12">
					<h5>Address Line 2</h5><input type="text" name="bill-addr2" value="">
				</div>
				<div class="col-md-12">
					<div class="form-group"><h5>City</h5><input type="text" name="bill-city" value=""></div>
					<div class="form-group"><h5>State</h5><input type="text" name="bill-state" value=""></div>
					<div class="form-group"><h5>ZIP</h5><input type="text" name="bill-zip" value=""></div>
				</div>
				</div><!-- end row inside form -->
				<input type="submit" value="Submit" class="button">
			</form>
			</div>
			
<!--General Contact Information-->
<!--
			<div class="sidebar col-md-12">
				<div class="row">
				<div class="col-md-6">
					<img src="img/contact/icon_form-phone.svg" alt="Call Us">
					<h5>Local Telephone</h5>
					<p>(508) 478-1648</p>
					<h5>Fax</h5>
					<p>(508) 590-0262</p>
				</div>
				<div class="col-md-6">
					<img src="img/contact/icon_form-mail.svg" alt="Email">
					<h5>General Information</h5>
					<p>info@spectrosinstruments.com</p>
					<h5>Service</h5>
					<p>service@spectrosinstruments.com</p>
					<h5>Sales</h5>
					<p>sales@spectrosinstruments.com</p>
				</div>
				</div>
			</div>
-->
			
		</div><!-- end row -->
	</article>
</section>
	
	
<!-- ========================================== -->	
</div><!-- END Container -->
	
<nav id="navbar"></nav>
<div id="totop" class="backtotop"></div>
<div id="adminbar" class="login"></div>

</div><!-- end "page" ID -->
<footer id="footer"></footer>

	<script type="text/javascript" src="includes/navigation.js"></script>
	<script type="text/javascript" src="includes/footer.js"></script>
	<script>
		// When the user scrolls down 20px from the top of the document, slide down the navbar
		window.onscroll = function() {scrollFunction()};

		function scrollFunction() {
				if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
						document.getElementById("totop").style.visibility = "visible";
				} else {
						document.getElementById("totop").style.visibility = "hidden";
				}
		}
	</script>

</body>
</html>
