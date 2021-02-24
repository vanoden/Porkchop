<style>
    @import url('https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600|Lato:100,200,300,400');

    body { margin: 0; color: #6e6f72; color: #4a4743; font-size: 18px; font-weight: 300; line-height: 1.5; }

    body,button,input,select, textarea { font-family: 'Lato', 'Helvetica', 'Arial', sans-serif;  }
    input[type="textbox"] { width: 100px; }
    img .accent-bottom { width: 100%; height: auto; box-sizing: border-box; }
    .pad_1rem {  padding: 1rem; }

    /*--------------------------------------------------------------
        General styling
    --------------------------------------------------------------*/

    .site {	margin: 0 auto; background-color: #fff; }
    .masthead, #info_bar, #hero, section, .content-group, .footer-content { margin: 0; box-sizing: border-box; }
    #hero, .footer-content { padding: 2rem 2rem; }
    #hero { height: 250px; background-image: url('../img/bnr/bnr_lt-flank.png'); background-repeat: no-repeat; background-position: top left; background-size: 150%; }
    #hero p { color: #95a0ab; margin: 0; }
    .footer-content { background-color: #1C1A17; padding-bottom: 6em; }
    #info_bar { padding: .5rem 2rem; }
    main section { margin: 2.4rem 2rem;}
    .masthead { background-color: #1c1a17; display: flex; align-items: center; padding: 1rem 2rem; }
    .masthead img.site-logo { width: 285px; margin: 0 auto; }
    .masthead .register_form { display: none;}

    nav { position: absolute; top: 63px; left: 0; width: 100%; font-size: 1.333rem; font-weight: 100; font-family: Montserrat,Helvetica,sans-serif; }
    nav li a { text-decoration: none; color: #fff; }
    #main-menu { padding-left: 0; padding-top: 0; margin: 0; }
    #main-menu > li { display: none; margin: 0; border-radius: 0; padding: 0; width: 100%; text-align: center; 
	    background: #1c1a17; border-bottom: 2px solid rgba(255,255,255,0.5); text-align: center; }
    #main-menu li img {  display: none;} 

    /* Set up second level nav for larger screens */
    .secondary-menu { position: absolute; left: -1px; /* aligns blue border with top nav border*/ top: 32px; padding-left: 0px;}
    .secondary-menu > li { display: none; margin-left: 0; padding: .2rem .4rem; }

    /* Create checkbox and label to visualize hamburger menu on mobile screens */
    nav input[type="checkbox"]{ visibility: hidden; position: absolute; }
    nav label { color: #CB2828; cursor: pointer; font-size: 15px; margin: 0; }
    nav label:before { position: absolute; top: -30px; left: 20px; height: 2px; width: 20px; background: #fff; display: inline-block; content: "";
	    box-shadow: 0px -5px 0px 0px #fff, 0px -10px 0px 0px #fff; transition: all .5s; opacity: 1; }
    #collapse:checked ~ li{ display: flex; }
    #collapse:checked + label:before{ opacity:1; } /* change hamburger menu when menu is active */

    /*--------------------------------------------------------------
        Navigation
    --------------------------------------------------------------*/

    #info_bar { background-color: #d9ee48; display: block; }
    #info_bar h4, #info_bar p { margin: 0; line-height: 1.2rem; }
    #info_bar p { font-size: 0.8rem; }
    #info_bar img { width: 25px; margin-right: 15px; }
    #info_bar select, #info_bar input { border: 0; background: none; padding: 0; max-height: 3em }
    #info_bar ul { display: flex; list-style-type: none; padding: 0; margin: 0; justify-content: space-between; gap: 1.5em; flex-wrap: wrap; }
    #info_bar ul li { flex: 1 0 auto; }
    #info_bar ul li > * {display: inline-block;}

    main { background: #fff;}
    .accent-top, .accent-bottom { display: block; height: 12px; padding: 0; margin-top: 3rem; }
    .accent-bottom { margin-bottom: 2rem; }
    .sect-heading {	display: flex; justify-content: space-between; }
    .heading-icon { width: 72px; margin: 0 auto; }

    /*============== Form Styling ============*/

    form input { }
    form ul {
	    display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;
	    padding: 0.9rem; margin: 0;;
    }
    form ul.shade_10 { background: rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.1); margin: 0.35rem 0; }
    form li { list-style-type: none; flex:1; }
    form input#gasPerYear { min-width: 210px; }
    form input[type=text] { padding: 2px 5px; }
    form input[type=button] { display: block; margin: 1rem 0 1rem; background: #2FC61E; color: white; font-weight: 800; border: none; border-radius: 4px; padding: .4rem .6rem; }
    form label, .savingsColumn p { font-size: 0.9rem; font-weight: 600; display: block; padding: 0; margin: 1.1rem 0 0rem;; color: rgb(76 157 234);}
    .savingsColumn span,
    form input[type=number],
    form select,
    input[type=date],
    input[type=time] { 
	    padding: 0.1rem 0.4rem; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.1); margin-bottom: 1rem; font-weight: 400; font-size: 0.9rem; min-height: 1.1rem; min-width: 200px;
    }
    span#cost::before, span#savings::before { content:'$';}
    .savingsColumn { display: flex; flex-direction: column; box-sizing: border-box; padding: 1em 2em 0 0;}
    .savingsColumn > * { display: flex; flex-direction: row;}
    form .or-separator { width: 100%; background-color: rgba(0,0,0,0.2); text-align:center; font-weight: 800;}

    div.sect-heading > div img { width: 210px; height: 10px; }

    .tiles-gas > * { padding: .7em 0 .7em 0; }
    /*--------------------------------------------------------------
        Typography
    --------------------------------------------------------------*/

    h1,h2,h3,h4,h5,h6 {	clear: both; margin: 0; padding: 0; font-family: Montserrat, Helvetica, Arial, sans-serif; font-weight: 200; }
    h1 { font-size: 1.67em; font-weight: 300; margin: 0 0 .4rem; line-height: 2.1rem;  }
    h2, h2 span, label { font-size: 1.56rem;  margin: 1.25rem 0 0.4rem; color: #1c1a17; }
    h1, h2 span { color: #4c9dea; }
    h3, #info_bar select { font-size: 1.4rem; font-weight: 600; line-height: 1.7rem; color: #1c79bf;  }
    h3 span { font-size: 1rem; color: #A8AAAD; text-transform: uppercase; }
    h4 { font-size: 0.88rem; font-weight: 500; }

    /* Global buttons */
    .content-button {	padding: .5em; background-color: #EDEEEF; border: 3px solid #EDEEEF ; border-radius: 5px; font-weight: 600; }
    .content-button img { height: 12px; }
    a.content-button { height: 24px; color: #000; text-decoration: none; text-transform: uppercase; display: flex; justify-content: space-between; align-items: center; width: 225px; }
    a.content-button:focus, a.content-button:hover { background: #fff;  }

    #info_bar form ul > * { display: flex; flex-direction: column; flex: 1 0 auto; }
    #info_bar form > * { padding: 0; justify-content: space-between; gap: 1em; }


    @media screen and (min-width: 750px) {
	    body {font-size: 16px;}
	    .site {	max-width: 1400px; background-color: #B8BAC2;}
	    #hero, #info_bar, main section { padding-left: 3rem; padding-right: 3rem; }
	    .masthead { padding: 0 2rem;} /* zero vertical padding for nav bar border */
	    .masthead img.site-logo { width: 300px; margin: 0; }
	    h1 { font-weight: 200; margin: 0.4rem 0;  }
	    
        /*	NAVIGATION SETTINGS FOR WIDE BROWSERS*/
        /*	Set First Level Navigation*/
	    #main-menu { display: flex; margin: 0; padding: 0 0 0 2rem; height: 100%; } /*reset margins for main nav from mobile */
	    #main-menu > li { position: relative; text-align: left; padding: 0rem .4rem; display: flex; align-items: center; height: 100%; margin: 0 14px 0 0; background: none; border-style: solid; border-width: 0 0 0 1px; border-color: rgba(0,0,0,0); width: auto; color: #4c9dea; }
	    #main-menu > li:last-child { margin-right:0; }
	    #main-menu > li a { color: #4c9dea; padding-left: 10px;  }
	    #main-menu > li:hover { border-left: 1px solid #4c9dea; background: linear-gradient(180deg, rgba(115,207,250,0) 0%, rgba(115,207,250,0.1) 50%, rgba(115,207,250,0) 100%);; transition: background-color 0.6s; }
        /*	Show and style NAV LEVEL 2*/
	    #main-menu li:hover .secondary-menu > li { display: block; }
	    #main-menu li img {  display: none; position: absolute; top: 99px; left: -190px; width: 450px; }
	    #main-menu li:hover img { display: block; }
	    .secondary-menu { background: rgba(28,26,23,0.95); top: 100px; border-left: 1px solid #4c9dea; }
	    .secondary-menu li { font-size: 1.1rem; width: 340px; border-left: 3px solid rgba(28,26,23,0.95); border-bottom: 1px solid #33312f; }
	    .secondary-menu li:hover { background: #2d2a27; border-left: 3px solid #4c9dea;  }
	    .secondary-menu > li > a { color: #929292 !important; }
	    .secondary-menu > li > a:hover { color:#4c9dea !important; }
	    
	    .register_form { position: absolute; top: 0; right: 1em;}
	    #hero { 
		    display: flex; flex-wrap: wrap; flex-direction: column; align-content: flex-start; justify-content: center;
		    height: 300px; background-image: url(../img/bnr/bnr_lt-flank.png), url(../img/bnr/bnr_atoms.jpg);
            background-position: top left, top right; background-repeat: no-repeat, no-repeat; background-size: 830px, cover;
	    }
	    #hero > * { width: 30%; max-width: 540px;}
	    .accent-top { width : 48px; margin-top: 0; }
	    .accent-bottom { width: 410px; margin-bottom: 0; }
	    .tiles-gas > * { padding: 0; }
    }

    @media screen and (min-width: 1000px) {
	    body {font-size: 20px;}
	    nav { font-size: 1.333rem; margin-right: 32px; }
	    #main-menu > li:last-child {margin-right:0;}
	    .masthead img.site-logo { width: 415px; }
    }
    
    /* CSS GRID Updated 12/16/20 */
    @media screen and (min-width: 750px) {
	    .site {
		    display: grid;
		    grid-template-columns: 1fr 1fr;
		    grid-template-rows: 100px auto 300px auto auto;
		    grid-template-areas:
			    "masthead masthead"
			    "title title"
			    "hero hero"
			    "main main"
			    "footer footer";
		    grid-gap: 0em;
	    }
	    .masthead {	grid-area: masthead; }
	    #info_bar {	grid-area: title; }
	    #hero {grid-area: hero;}
	    #page_content {	grid-area: main;}
	    .footer-content { 
		    grid-area: footer;
		    display: grid;
		    grid-template-columns: 1fr 1fr;
	    }
        
        .site-clean {
            grid-template-rows: 100px auto;
		    grid-template-areas:
			    "masthead masthead"
			    "main main"
			    "footer footer";
        }
	    
        /*	Three-column layouts*/
	    .page_3col_wide-left {
		    display:grid;
		    grid-template-columns: 1fr 1fr;
		    grid-template-areas: "copy-fill image-fill";
		    grid-gap: 1em;
		    align-items: center;
	    }
	    .page_3col_wide-right {
		    display:grid;
		    grid-template-columns: 1fr 1fr;
		    align-items: flex-start;
		    grid-template-rows: "narr-col wide-col";
		    grid-gap: 1em;
	    }
	    .wide-col {
		    display: grid;
		    grid-template-columns: 1fr;
		    grid-gap: 1em;
	    }
	    .wide-col > * { display: flex; }
	    .content_copy { grid-area: copy-fill; }
	    .content_image { grid-area: image-fill; }
	    
	    .sub_2col {
		    display: grid;
		    grid-template-columns: 1fr 1fr;
		    grid-gap: 1em;		
	    } 
	    
	    .page_3col_even{
		    display:grid;
		    grid-template-columns: 1fr 1fr 1fr;
		    grid-gap: 1em;
	    }
	    
        /*	Four-column layouts*/
	    .page_4col {
		    display:grid;
		    grid-template-columns: 1fr 1fr 1fr 1fr;
		    grid-gap: 1em;
	    }
    }

    @media screen and (min-width: 960px) {
	    .site {
		    grid-template-columns: 1fr 1fr 1fr 1fr;
		    grid-template-rows: 100px auto 300px auto auto;
		    grid-template-areas:
			    "masthead masthead masthead masthead"
			    "title title title title"
			    "hero hero hero hero"
			    "main main main main"
			    "footer footer footer footer";
	    }
        
        .site-clean {
            grid-template-rows: 100px auto;
		    grid-template-areas:
			    "masthead masthead masthead masthead"
			    "main main main main"
			    "footer footer footer footer";
        }
	    
	    .footer-content { 
		    grid-area: footer;
		    display: grid;
		    grid-template-columns: 1fr 1fr 1fr;
	    }
	    /*	Three-column layouts*/
	    .page_3col_wide-left {
		    grid-template-columns: 2fr 1fr;
	    }
	    .page_3col_wide-right {
		    grid-template-columns: 1fr 2fr;
	    }
	    .wide-col {
		    grid-template-columns: 1fr 1fr;
	    }
    }
</style>
	<main id="page_content">
	<section class="page_3col_wide-left">
		<div class="content_copy">
			<h2>Alert Profiles</h2>
			<h3>Create/Edit your monitoring alert profiles</h3>
		</div>
	</section>
	<section>
		<div class="content-copy">
			<img src="/img/web-portal/section_bkt-btm.png" class="accent-bottom" alt="border over text">
			<form>
				<ul>
					<li>
					<label for="name-object">Object:</label>
					<select name="" id="">
							<option value="sf400-8">SF400 NDIR 4-Zone</option>
							<option value="sf400-8">SF400 NDIR 8-Zone</option>
					</select>
					</li>
				</ul>
				<ul class="shade_10">
					<li>
					<label for="sensor-type">Sensor Type:</label>
					<select name="" id="">
							<option value="sf-high">SF IR Low Range</option>
							<option value="sf-high">SF IR High Range</option>
							<option value="sf-high">PH3 EC Low Range</option>
							<option value="sf-high">PH3 IR High Range</option>
					</select>
					</li>
					<li>
					<label for="concentration">Concentration:</label>
					<select name="" id="">
							<option value="sf-high">is greater than</option>
							<option value="sf-high">is less than</option>
							<option value="sf-high">is equal to</option>
							<option value="sf-high">is not equal to</option>
					</select>
					</li>
					<li>
						<label for="value">Value PPM:</label>
						<input type="number" id="value" name="" min="0.01" step="0.01" max="" value=""/>
					</li>
					<li>
						<input type="button" onClick="multiplyBy()" Value="and" style="background: rgba(0,0,0,0.2); color: black;" />
					</li>
				</ul>
				<ul class="shade_10">
					<li>
					<label for="sensor-type">Sensor Type:</label>
					<select name="" id="">
							<option value="sf-high">Temperature</option>
							<option value="sf-high">Humidity</option>
							<option value="sf-high">Wind Speed</option>
					</select>
					</li>
					<li>
					<label for="concentration">Temperature:</label>
					<select name="" id="">
							<option value="sf-high">is greater than</option>
							<option value="sf-high">is less than</option>
							<option value="sf-high">is equal to</option>
							<option value="sf-high">is not equal to</option>
					</select>
					</li>
					<li>
						<label for="value">Value ºF:</label>
						<input type="number" id="temp" name="" min="0.01" step="0.01" max="" value=""/>
					</li>
					<li>
						<input type="button" onClick="multiplyBy()" Value="+" />
					</li>
				</ul>
				<div class="or-separator">OR</div>
				<ul class="shade_10">
					<li>
					<label for="sensor-type">Sensor Type:</label>
					<select name="" id="">
							<option value="sf-high">Humidity</option>
							<option value="sf-high">Temperature</option>
							<option value="sf-high">Wind Speed</option>
					</select>
					</li>
					<li>
					<label for="concentration">Temperature:</label>
					<select name="" id="">
							<option value="sf-high">is greater than</option>
							<option value="sf-high">is less than</option>
							<option value="sf-high">is equal to</option>
							<option value="sf-high">is not equal to</option>
					</select>
					</li>
					<li>
						<label for="value">Value ºF:</label>
						<input type="number" id="temp2" name="" min="0.01" step="0.01" max="" value=""/>
					</li>
					<li>
						<input type="button" onClick="multiplyBy()" Value="+" />
					</li>
				</ul>
                <!--				Escalation Event Details ================= -->
				<ul class="shade_10">
					<li>
					<label for="escalation-event">Escalation Trigger:</label>
					<select name="" id="">
							<option value="time range">Time Range</option>
							<option value="sf-high">Temperature</option>
							<option value="sf-high">Wind Speed</option>
					</select>
					</li>
					<li>
					<label for="start-date">Start Date:</label>
					<input type="date" id="start-date" name="start-date">
					</li>
					<li>
					<label for="start-time">Start Time:</label>
					<input type="time" id="start-time" name="start-time">
					</li>
					<li>
					<label for="end-date">End Date:</label>
					<input type="date" id="end-date" name="end-date">
					</li>
					<li>
					<label for="end-time">End Time:</label>
					<input type="time" id="end-time" name="end-time">
					</li>
					<li>
						<input type="button" onClick="multiplyBy()" Value="+" />
					</li>
				</ul>
                <!--				Add a Grouping ======================= -->
				<ul style="margin:0;">
					<li>
						<input type="button" onClick="multiplyBy()" Value="Add a Grouping" style="background: rgb(76 157 234);" />
					</li>
				</ul>
			<img src="/img/web-portal/section_bkt-btm.png" class="accent-bottom" alt="border below text" style="margin-top:0;">
				<ul>
					<li>
						<input type="button" onClick="multiplyBy()" Value="Add a New Event" />
					</li>
				</ul>
			</form>
			<span id="error" style="color: red; font-size: 1rem; font-weight: 400;"></span>
        </div>
		<img src="/img/web-portal/section_bkt-btm.png" class="accent-bottom" alt="border below text">
	</section>
	</main>

    <script>
        function multiplyBy() {
				error = document.getElementById("error");
                gasYearly = document.getElementById("gasYearly").value;
				gasMonitored = document.getElementById("gasMonitored").value * 0.01;
				prodPrice = document.getElementById("prodPrice").value;
				if(gasMonitored >= .01 && gasMonitored <= 1.0) {
					    error.textContent = "";
					    document.getElementById("cost").innerHTML = gasYearly * gasMonitored;
					    gasSavings = document.getElementById("savings").innerHTML = (gasYearly * gasMonitored) * 0.3;
					    document.getElementById("amortization").innerHTML = (prodPrice/gasSavings).toFixed(2);
					    document.getElementById("amort-months").innerHTML = ((prodPrice/gasSavings)*12).toFixed(2); 
					} else { 
						error.textContent = "Please enter a percentage between 1 and 100";
						document.getElementById("cost").innerHTML = "";
						document.getElementById("savings").innerHTML = "";
						document.getElementById("amortization").innerHTML = "";
					}
        }
			
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    </script>
