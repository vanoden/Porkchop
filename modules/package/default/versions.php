<div class="title">Versions</div>
<script language="JavaScript">
    function publish(id) {
        document.forms[0].version_id.value = id;
        document.forms[0].dothis.value = 'publish';
        document.forms[0].submit();
    }
    function hide(id) {
        document.forms[0].version_id.value = id;
        document.forms[0].dothis.value = 'hide';
        document.forms[0].submit();
    }
</script>
<form name="versionListForm" method="POST" action="/_package/versions">
<input type="hidden" name="version_id" value="" />
<input type="hidden" name="dothis" value="" />
</form>
<table class="body">
<tr><th>Version</th>
    <th>Status</th>
    <th>User</th>
    <th>Date Created</th>
    <th>Action</th>
</tr>
<?  foreach ($versions as $version) { ?>
<tr><td><?=$version->version()?></td>
    <td><?=$version->status?></td>
    <td><?=$version->user->full_name()?></td>
    <td><?=$version->date_created?></td>
    <td>
        <? if ($version->status != 'PUBLISHED') { ?><input type="button" name="btn_submit" class="button" value="Publish" onclick="publish(<?=$version->id?>);" /><? } ?>
        <? if ($version->status != 'HIDDEN') { ?><input type="button" name="btn_submit" class="button" value="Hide" onclick="hide(<?=$version->id?>);" /><? } ?>
    </td>
</tr>
<?  } ?>
</table>
<div class="form_footer">
    <input type="button" name="btn_packages" class="button" value="Back" onclick="window.location.href='/_package/package/<?=$package->code?>';" />
</div>