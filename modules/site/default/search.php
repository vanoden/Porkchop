<style>
    .search-results-table {
      border-collapse: collapse;
      border: 1px solid #ddd;
    }
    .search-results-table th,
    .search-results-table td {
      border: 1px solid #ddd;
      padding: 5px;
    }
  </style>

<h1 class="title">Site Search</h1>

<?php if ($page->errorCount() > 0) { ?>
  <section id="form-message">
    <ul class="connectBorder errorText">
      <li><?= $page->errorString() ?></li>
    </ul>
  </section>

<?php  } else if ($page->success) { ?>
  <section id="form-message">
    <ul class="connectBorder progressText">
      <li><?= $page->success ?></li>
    </ul>
  </section>
<?php  } ?>

<form class="form" method="POST" action="/_site/search">
  <input type="text" placeholder="Enter Search..." name="string" value="<?= isset($_REQUEST['string']) ? $_REQUEST['string'] : '' ?>">
  <input type="hidden" name="csrfToken" value="<?= $GLOBALS['_SESSION_']->getCSRFToken() ?>">
  <div class="checkboxes">
    <?php foreach ($definitionValues as $value) { ?>
      <label>
        <input type="checkbox" name="definitions[]" value="<?= $value ?>" <?= isset($_REQUEST['definitions']) && in_array($value, $_REQUEST['definitions']) ? 'checked' : '' ?>>
        <?= $value ?>
      </label>
    <?php } ?>
  </div>
  <button type="submit">Search</button>
</form>

<?php
if (!empty($results)) {
?>
  <h2> Search Results </h2>
  
  <table class="search-results-table">
    <thead>
      <tr>
        <th>Type</th>
        <th>Summary</th>
        <th>URL</th>
        <?php if($canAdministor) { ?>
          <th>Admin URL</th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($results as $result) { ?>
        <tr>
          <td><?= $result->type ?></td>
          <td><?= $result->summary ?></td>
          <td><a href="<?= $result->customer_url ?>"><?= $result->customer_url ?></a></td>
          <?php if($canAdministor) { ?>
            <td><a href="<?= $result->admin_url ?>"><?= $result->admin_url ?></a></td>
          <?php } ?>
        </tr>
      <?php } ?>
    </tbody>
  </table>
<?php
}
?>

<br />
<hr />
<?php
$totalString = (is_array($results) && count($results) > 0) ? count($results) : 0;
?>
<p><span style="float: right;">Total Result(s): <?= $totalString ?></span></p>