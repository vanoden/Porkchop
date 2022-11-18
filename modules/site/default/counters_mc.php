<?php
$page = new \Site\Page('site','counters');
$page->requirePrivilege('see site counters');

$counterWatched = new \Site\CounterWatched();
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (!empty($_POST['key'])) {
                $counterWatched->add(array('key'=> $_POST['key'], 'notes'=> $_POST['notes']));
                $page->success = 'Key (' . $_POST['key'] . ') has been added to watch';
            } else $page->addError('Please Specify the Cache Key by name to watch');
            break;
    
        case 'remove':
            if (!empty($_POST['key'])) {
                $counterWatched->deleteByKey($_POST['key']);
                $page->success = 'Key (' . $_POST['key'] . ') has been removed from watch';
            } else $page->addError('Please Specify the Cache Key by name to remove');
            break;
    
        default:
            $page->addError('Invalid Action for Counter Keys');
            break;
    }
}

// get data for page
$countersWatchedList = new \Site\CountersWatchedList();
$countersWatchedList = $countersWatchedList->find();
$counterList = new \Site\CounterList();
$completeCounterList = $counterList->find(array('showCacheObjects' => false));
