<style>
    * {
      box-sizing: border-box;
    }

     form.form input[type=text] {
      padding: 10px;
      font-size: 17px;
      border: 1px solid grey;
      float: left;
      width: 80%;
      background: #f1f1f1;
    }

     form.form button {
      float: left;
      width: 20%;
      padding: 0px;
      background: #007ba8;
      color: white;
      font-size: 17px;
      border: 1px solid grey;
      border-left: none;
      cursor: pointer;
      margin-top: 4px;
    }

     form.form button:hover {
      background: #0b7dda;
    }

     form.form::after {
      content: "";
      clear: both;
      display: table;
    }
</style>
<span class="title">Site Search</span>

<?php if ($page->errorCount() > 0) { ?>
<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>

<?php	} else if ($page->success) { ?>
<section id="form-message">
	<ul class="connectBorder progressText">
		<li><?=$page->success?></li>
	</ul>
</section>
<?php	} ?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<form class="form" method="POST" action="/_site/search">
  <input type="text" placeholder="Enter Search..." name="string" value="<?=$_REQUEST['string']?>">
  <input type="hidden" name="csrfToken" value="<?=$GLOBALS['_SESSION_']->getCSRFToken()?>">
  <button type="submit"><i class="fa fa-search"></i></button>
</form>

<?php
    if (!empty($messages)) {
?>
    <br/><u>Search Results</u>
<?php
    }
    foreach ($messages as $message) {
    ?>
        <h3><a href="/<?=$message->target?>" target="_blank"><?=empty(($message->name)) ? 'Spectros Instruments' : ucfirst($message->name)?></a></h3>
        <p><?=$message->content?></p>
    <?php
    }
?>
<br/><hr/>
<p><span style="float: right;">Total Result(s): <?=count($messages)?></span></p>

