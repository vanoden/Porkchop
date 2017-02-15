<html>
<head>
	<title>Spectros Intruments Fumigation Portal - Console ~ Monitors</title>
	<link rel="stylesheet" href="/main.css" type="text/css" media="Screen"/>
	<link rel="stylesheet" href="/handheld.css" media="handheld, only screen and (max-device-width:480px)"/>
    <style>
        * {
            font-family: Verdana, Helvetica, Arial, sans-serif;
            margin: 0px;
            padding: 0px;
        }
		body {
			margin-top: 0px;
			margin-left: 0px;
			background-color: #3A4244;
		}
    </style>
    <script language="Javascript">
        function goUrl(url)
        {
            location.href = url;
            return true;
        }
        function goMethodUrl(url)
        {
            location.href = '/_monitor/api/&amp;method='+url;
            return true;
        }
		function submitForm(monitorID)
		{
			document.mainform.action = '/_monitor/api';
			document.mainform.method.value = 'getMonitor';
			document.mainform.monitor.value=monitorID;
			document.mainform.submit();
			return true;
		}
    </script>
</head>
<body style="margin-top: 0px">
<form name="mainform" method="post">
<input type="hidden" name="monitor"/>
<input type="hidden" name="method"/>
<div id="wrapper">
	<div id="main">
		<div id="logo"></div>
		<div class="buttonMenuBarBg">
			<input type="button" class="buttonMenuBar" name="homeBtn" value="Home" onclick="goUrl('/_register/welcome');" />
			<input type="button" class="buttonMenuBar" name="eventsBtn" value="Events" onclick="goMethodUrl('getEvents');" />
			<input type="button" class="buttonMenuBar" name="monitorsBtn" value="Monitors" onclick="goMethodUrl('getMonitors');" />
			<input type="button" class="buttonMenuBar" name="hubsBtn" value="Hubs" onclick="goMethodUrl('getHubs');" />
		</div>
		<div class="api_body" style="width: 700px">
            <div class="monitorsHeading">
                <div class="label" style="float: left; width: 70px;">Code</div>
                <div class="label" style="float: left; width: 190px;">Label</div>
                <div class="label" style="float: left; width: 100px;">Model</div>
                <div class="label" style="float: left; width: 60px;">Points</div>
                <div class="label columnJobSite" style="float: left; width: 200px;">Last Calibration</div>
            </div>
<?	foreach ($monitors as $monitor) { ?>
		<div class="monitorsRow">
                <div class="value monitorCodeValue" style="float: left; width: 100px;"><a href="javascript:void(0)" onclick="submitForm('{code}')"><?=$monitor['code']?></a></div>
                <div class="value monitorLabelValue" style="float: left; width: 190px;"><?=$monitor['label']?></div>
                <div class="value monitorModelValue" style="float: left; width: 100px;"><?=$monitor['model']?></div>
                <div class="value monitorLabelValue" style="float: left; width: 60px;"><?=$monitor['points']?></div>
                <div class="value columnJobSite monitorLastCalValue" style="float: left; width: 200px;"><?=$monitor['last_calibrated']?></div>
                <div style="visibility: none; clear: both;"></div>
            </div>
<?	} ?>
        </div>
    </div>
</div>
</form>
</body>
</html>