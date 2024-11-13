<?php

namespace Mini\Cms\Modules\Cron;

use Mini\Cms\Configurations\ConfigFactory;
use Mini\Cms\Modules\Modal\RecordCollection;
use Mini\Cms\Services\Services;

class Cron
{
    public function __construct()
    {

        /**@var $configs ConfigFactory **/
        $configs = Services::create('config.factory');

        $cron_modal = new CronModal();

        $crons = $configs->get('crons');
        if(!empty($crons)) {

            $runnable_cron = [];
            foreach ($crons as $cron) {

                if(class_exists($cron)) {
                    $cron = new $cron();

                    if($cron instanceof CronInterface) {
                        $cron_data = $cron_modal->get($cron->cronId(),'cron_id')->getAt(0);
                        if($cron_data instanceof RecordCollection) {

                            // Convert fixed interval to seconds
                            $fixed_interval_seconds = $this->convertMinutesToUnixTimestamp($cron->when());

                            // Get the current timestamp
                            $now_timestamp = time();

                            // Example previous timestamp (replace with your actual previous timestamp)
                            $previous_timestamp = $cron_data->last_run;

                            // Calculate the difference between now and previous in seconds
                            $time_difference_seconds = $now_timestamp - $previous_timestamp;

                            // Check if the time difference is greater than or equal to the fixed interval
                            if ($time_difference_seconds >= $fixed_interval_seconds) {
                                $runnable_cron[] = $cron;
                                $cron_modal->update(['last_run'=>$now_timestamp], $cron_data->cron);
                            } else {
                                echo "skipping: {$cron_data->cron_id}".PHP_EOL;
                            }
                        }else {
                            $runnable_cron[] = $cron;
                            $now = time();
                            $cron_modal->store(['last_run'=>$now,'cron_id'=>$cron->cronId()]);
                        }
                    }

                }
            }

            // Running cron ready.
            if($runnable_cron) {
                foreach ($runnable_cron as $value) {
                    if($value instanceof CronInterface) {
                        $value->execute();
                    }
                }
            }
        }

    }

    /**
     * Convert minute to unix timestamp
     * @param $minutes
     * @return int
     */
    public function convertMinutesToUnixTimestamp($minutes): int
    {
        // Convert minutes to seconds
         return $minutes * 60;
    }
}