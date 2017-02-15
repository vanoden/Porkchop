<?
    for ($i = 1;$i <= 4; $i++)
    {
        for ($j = 9; $j >= 1; $j--)
        {
            $history[$i][$j] = $history[$i][$j-1];
            $total[$i] += $history[$i][$j];
            if ($history[$i][$j] > $max[$i]) $max[$i] = $history[$i][$j];
            if ($history[$i][$j] < $min[$i]) $min[$i] = $history[$i][$j];
            if (! $status[$i])
            {
                $max[$i] = 0;
                $min[$i] = 0;
                $total[$i] = 0;
            }
        }
        $value[$i] = rand(0,800)/100;
        $history[$i][0] = $value[$i];
        $total[$i] += $history[$i][$j];
        if ($history[$i][0] > $max[$i]) $max[$i] = $history[$i][0];
        if ($history[$i][0] < $min[$i]) $min[$i] = $history[$i][0];
        $avg[$i] = $total[$i] / 10;
    }
?>
<html>
<head>
    <title> Spectros Mobile Monitor </title>
    <script type="text/javascript">
        function startTimer()
        {
            setTimeout("reloadme()",10000);
            return true;
        }
        function reloadme()
        {
            document.miniZone.submit();
            return true;
        }
        function toggle(elem)
        {
            elem.id.match(/(\d+)/);
            var ID = RegExp.$1;
            
            if (elem.value == 'Start')
            {
                elem.value = 'Stop';
                document.getElementById('status['+ID+']') = 0;
            }
            else
            {
                elem.value = 'Start';
                document.getElementById('status['+ID+']') = 1;
            }
            return true;
        }
    </script>
    <style>
        * {font-size: 10px}
    </style>
</head>
<body onLoad="startTimer();">
<form name="miniZone" action="/_monitor/minizones" method="Post">
<input type="hidden" name="history[<?=$i?>][<?=$j?>]" value="<?=$history[$i][$j]?>">
<div style="height: 240px; width: 340px;border: 1px solid black">
<?  for ($i = 1; $i <= 4; $i ++) { ?>
    <?  for ($j = 0;$j <= 9; $j++) { ?>
    <input type="hidden" name="history[<?=$i?>][<?=$j?>]" value="<?=$history[$i][$j]?>">
    <?  } ?>
    <input type="hidden" name="status[<?=$i?>]" value="<?=$status[$i]?>">
    <div class="panel" style="text-align: center;float: left; width: 85px">
        <div class="panel_head" style="border: 1px solid black; width: 100%">
            <span class="title" style="display: block">Zone <?=$i?></span>
            <input type="button" class="btn" value="Start" style="display: block; width: 100%" onclick="toggle(this)" />
            <input type="text" class="label" style="width: 100%;display:block"/>
        </div>
        <div class="panel_body" style="border: 1px solid black; width: 100%">
            <span style="display: block">Current</span>
            <span style="display: block"><?=sprintf("%0.2f",$value[$i])?> ppm</span>
            <span style="display: block">Last 10</span>
            <span style="display: block">Min <?=sprintf("%0.2f",$min[$i])?> ppm</span>
            <span style="display: block">Max <?=sprintf("%0.2f",$max[$i])?> ppm</span>
            <span style="display: block">Avg <?=sprintf("%0.2f",$avg[$i])?> ppm</span>
            <span style="display: block">Run Time</span>
            <span style="display: block"><?=$time[$i]?></span>
        </div>
    </div>
<?  } ?>
</div>
</form>
</body>
</html>
<?  exit; ?>