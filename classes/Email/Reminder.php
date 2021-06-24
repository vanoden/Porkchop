<?php
namespace Email;

/**
 * Maintenance Cron Reminder
 *
 * remind business users of
 *  pending customer and engineering actions stuck pending
 */
class Reminder {

    public $currentYear;
    public $holidays;
    public $workingDays = 5;
    public $pendingNewCustomers;
    public $pendingNewOrganizations;
    public $openTasksCount;

    public function __construct($argument = null) {
        date_default_timezone_set('America/New_York');
        $this->currentYear = date('Y');
        $new_year = $this->observedDate(date('Y-m-d', strtotime("first day of january $this->currentYear")));
        $mlk_day = date('Y-m-d', strtotime("january $this->currentYear third monday"));
        $presidents_day = date('Y-m-d', strtotime("february $this->currentYear third monday"));
        $memorial_day = (new \DateTime("Last monday of May"))->format("Ymd");
        $independence_day = $this->observedDate(date('Y-m-d', strtotime("july 4 $this->currentYear")));
        $labor_day = date('Y-m-d', strtotime("september $this->currentYear first monday"));
        $columbus_day = date('Y-m-d', strtotime("october $this->currentYear second monday"));
        $veterans_day = $this->observedDate(date('Y-m-d', strtotime("november 11 $this->currentYear")));
        $thanksgiving_day = date('Y-m-d', strtotime("november $this->currentYear fourth thursday"));
        $christmas_day = $this->observedDate(date('Y-m-d', strtotime("december 25 $this->currentYear")));        
        $this->holidays = array($new_year, $mlk_day, $presidents_day, $memorial_day, $independence_day, $labor_day, $columbus_day, $veterans_day, $thanksgiving_day, $christmas_day);        
    }

    /**
     * gather needed reminders about the following actions
     *    that need to be taken by support / engineering users
     *
     * Unapproved customers
     * Incomplete assigned tasks
     * Unassigned tasks
     */
    public function gather() {

        # Unapproved customers
        $customers = new \Register\CustomerList();
        $newCustomers = $customers->find(array('status'=>'NEW'));
        $this->pendingNewCustomers = count($newCustomers);
        
        # Unapproved organizations
        $organizations = new \Register\OrganizationList();
        $newOrganizations = $organizations->find(array('status'=>'NEW'));
        $this->pendingNewOrganizations = count($newOrganizations);

        # Open Engineering Tasks
        $this->openTasksCount = 0;
        $taskList = new \Engineering\TaskList();
        $openTasks = $taskList->find(array('status'=>'NEW'));
        foreach($openTasks as $openTask) {
            $daySince = $this->getWorkingDays($openTask->date_added, date('Y-m-d'));
            if ($daySince > $this->workingDays) $this->openTasksCount++;
        }
    }

    /**
     * send reminders out to needed users
     */
    public function remind() {
        $reminderArray = array();
        $reminderArray[] = 'there are ' . $this->pendingNewCustomers . ' new customers more than ' . $this->workingDays . ' days old';
        $reminderArray[] ='there are ' . $this->pendingNewOrganizations . ' new organizations more than ' . $this->workingDays . ' days old';
        $reminderArray[] ='there are ' . $this->openTasksCount . ' open tasks more than ' . $this->workingDays . ' days old';
        return $reminderArray;
    }

    /**
     * get number of business days between two dates [skipping the holidays]
     */
    protected function getWorkingDays($startDate, $endDate) {

        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);
        $days = ($endDate - $startDate) / 86400 + 1;

        $numFullWeeks = floor($days / 7);
        $numRemainingDays = fmod($days, 7);

        $firstDayOfWeek = date("N", $startDate);
        $lastDayOfWeek = date("N", $endDate);
        if ($firstDayOfWeek <= $lastDayOfWeek) {
            if ($firstDayOfWeek <= 6 && 6 <= $lastDayOfWeek) $numRemainingDays--;
            if ($firstDayOfWeek <= 7 && 7 <= $lastDayOfWeek) $numRemainingDays--;
        } else {

            // the day of the week for start is later than the day of the week for end
            if ($firstDayOfWeek == 7) {

                // if the start date is a Sunday, then subtract 1 day
                $numRemainingDays--;

                if ($lastDayOfWeek == 6) $numRemainingDays--;
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                //      so we skip an entire weekend and subtract 2 days
                $numRemainingDays -= 2;
            }
        }

        $workingDays = $numFullWeeks * 5;
        if ($numRemainingDays > 0) $workingDays += $numRemainingDays;

        // we subtract the holidays
        foreach ($this->holidays as $holiday) {
            $timeStamp = strtotime($holiday);

            // if the holiday doesn't fall in weekend
            if ($startDate <= $timeStamp && $timeStamp <= $endDate && date("N", $timeStamp) != 6 && date("N", $timeStamp) != 7) $workingDays--;
        }

        return $workingDays;
    }
      
    /**
     * get observed date by holiday name
     */
    private function observedDate($holiday){
        $observedDate = false;
        $day = date("w", strtotime($holiday));
        if ($day == 6) {
            $observedDate = (int)$holiday -1;
        } elseif ($day == 0) {
            $observedDate = (int)$holiday +1;
        } else {
            $observedDate = (int)$holiday;
        }
        return $observedDate;
    }
}
