<div style="display: table; width 800px">
    <div style="display:table-cell; width: 750px;">
        <div class="title">Outstanding Requests</div>
    </div>
    <div style="display:table-cell; width: 150px;">
        <a href="/_spectros/admin_request" style="float: right; font-weight: bold; text-decoration: none; font-size: 16px">New Request</a>
    </div>
</div>
<table class="body form">
<tr><td class="label column_header">Request</td>
    <td class="label column_header">Date</td>
    <td class="label column_header">Status</td>
    <td class="label column_header">Customer</td>
    <td class="label column_header">Assigned To</td>
</tr>
<?  foreach ($requests as $request) { ?>
<tr><td class="value"><a href="/_spectros/admin_request_detail/<?=$request->code?>"><?=$request->code?></a></td>
    <td class="value"><?=$request->date_request?></td>
    <td class="value"><?=$request->status?></td>
    <td class="value"><?=$request->user_requested_name()?></td>
    <td class="value"><?=$request->user_assigned_name()?></td>
</tr>
<?  } ?>
</table>