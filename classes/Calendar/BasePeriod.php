<?php
    namespace Calendar;

    class BasePeriod Extends \BaseClass {
        protected \DateTime $_timestamp_start;
        protected \DateTime $_timestamp_end;
        protected int $_interval;           // Number of seconds in a part of block (hours for a day, days for a week, days for a month, etc)

        public function __construct($date = null, $length = null) {
            if (!($date instanceof \DateTime)) {
                $date = new \DateTime("@$date");
                $this->_timestamp_start = $date;
            }
            elseif ($date === null) {
                $date = new \DateTime();
                $this->_timestamp_start = $date;
            }

            if ($length !== null) {
                $enddate = clone $date;
                $enddate->modify("+$length seconds");
                $this->_timestamp_end = $enddate; 
            }
            else {
                $this->_timestamp_end = $this->_timestamp_start;
            }
        }

        /** @method public startTime
         * Get/Set the start of the period based on some
         * provided time within the period, ie now()
         * @param DateTime seed - Optional timestamp within period
         * @return DateTime startTime - first second of the period
         */
        public function startTime($seed = null) {
            return $this->_timestamp_start;
        }

        /** @method public endTime    
         * Get/Set the end of the period based on some
         * provided time within the period, ie now()
         * @param DateTime seed - Optional timestamp within period
         * @return DateTime endTime - final second of the period
         */
        public function endTime($seed = 0) {
            return $this->_timestamp_end;
        }

        /** @method public conflicts(event)
         * Determine if an event conflicts with this period
         * @param Event event - event to check
         * @return bool conflicts - true if event conflicts with period
         */
        public function conflicts(\Calendar\Event $event) {
            if ($event->startTime()->getTimeStamp() <= $this->endTime()->getTimestamp() &&
                $event->endTime()->getTimeStamp() >= $this->startTime()->getTimestamp()) {
                return true;
            }
            return false;
        }
    }