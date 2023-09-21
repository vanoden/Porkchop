<?php
$page = new \Site\Page('site','counters');
$page->requirePrivilege('see site reports');

$counterList = new \Site\CounterList();
$counters = $counterList->find();