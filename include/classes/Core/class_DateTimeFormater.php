<?php
class DateTimeFormater
{
    private $registry;

    private $formats = array();
    private $defaultFormat = 'SHORT';

    public function __construct()
    {
        global $website;

        $this -> registry = $website;

        $this -> formats = array(
                               'FULL'        => IntlDateFormatter::FULL,
                               'LONG'        => IntlDateFormatter::LONG,
                               'MEDIUM'      => IntlDateFormatter::MEDIUM,
                               'SHORT'       => IntlDateFormatter::SHORT,
                               'GREGORIAN'   => IntlDateFormatter::GREGORIAN,
                               'TRADITIONAL' => IntlDateFormatter::TRADITIONAL,
                           );
    }

    public function __destruct()
    {
        unset($this -> registry);
    }

    public function convertDateTime($dateTimeSource, $format)
    {
        $intlFormat = $this -> _getIntlFormat($format);

        try {
            // Create a DateTime object from the date string
            $dateTime = new DateTime($dateTimeSource);

            // Get the current locale (default locale of the system)
            $locale   = locale_get_default();
            $timeZone = date_default_timezone_get();

            // Create an IntlDateFormatter for date and time for the submitted format
            $formatter = new IntlDateFormatter(
                             $locale,
                             $intlFormat,
                             $intlFormat,
                             $timeZone
                         );

            // Format the date according to the locale
            return $formatter -> format($dateTime);
        }
        catch(Exception $e) {
            $logMessage = 'Error for DateTime convert from ' . $dateTimeSource . "\n" .
                          $e -> getMessage();

            new Logging('datetime_exception', $logMessage);

            return $this -> registry -> user_lang['admin']['date_time_error'];
        }
    }

    private function _getIntlFormat($format)
    {
        $format = strtoupper(trim($format));
        if ( !array_key_exists($format, $this -> formats) ) {
            return $this -> formats[$format];
        }
        else {
            return $this -> formats[$this -> defaultFormat];
        }
    }
}