<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @author    Ruel Mindo
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 * @since     0.0.1
 * 
 */

namespace Craftsware\Lib;



/**
 * Time
 *
 */
class Time {


    /**
    * Time Settings
    *
    * @param array $database
    */
    private $settings = [];



    /**
    * Constructor
    *
    * @param array $args
    */
    public function __construct($settings)
    {
        if(is_array($settings)) {

            $this->settings = $settings;
        }
    }



    /**
     * Get Time Offset
     */
    public function offset($timezone)
    {
        $timeOffset = (new \DateTimeZone($timezone))->getOffset(new \DateTime('now', new \DateTimeZone('UTC')));

        if($timeOffset < 0) {

            return 'UTC -'. gmdate('H:i', -$timeOffset);

        } else {

            return 'UTC +'. gmdate('H:i', $timeOffset);
        }
    }


    /**
     * Set Date and Time
     */
    public function datetime($datetime, $timezone = null, $format = 'M/d/Y - h:i:s A')
    {
        $datetime = new \DateTime($datetime);

        if($timezone) {

            $datetime->setTimezone(new \DateTimeZone($timezone));
        }

        return $datetime->format($format); 
    }



    /**
     * Get Timezone
     */
    public function timezones()
    {
        $timezone = [];

        foreach(timezone_identifiers_list() as $key => $value) {

            $timezone[$key] = [
                'timezone' => $value,
                'offset' => $this->offset($value)
            ];
        }

        return $timezone;
    }
}