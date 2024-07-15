<?php

namespace UncleCheese\EventCalendar\Helpers;

use Carbon\Carbon;
use UncleCheese\EventCalendar\Models\CalendarDateTime;
use UncleCheese\EventCalendar\Pages\Calendar;

class CalendarUtil
{
	const ONE_DAY = "OneDay";
	const SAME_MONTH_SAME_YEAR = "SameMonthSameYear";
	const DIFF_MONTH_SAME_YEAR = "DiffMonthSameYear";
	const DIFF_MONTH_DIFF_YEAR = "DiffMonthDiffYear";
	const ONE_DAY_HEADER = "OneDayHeader";
	const MONTH_HEADER = "MonthHeader";
	const YEAR_HEADER = "YearHeader";

	/**
	 * @return array
	 */
	private static $format_character_placeholders = [
		'$StartDayNameShort',
		'$StartDayNameLong',
		'$StartDayNumberShort',
		'$StartDayNumberLong',
		'$StartDaySuffix',
		'$StartMonthNumberShort',
		'$StartMonthNumberLong',
		'$StartMonthNameShort',
		'$StartMonthNameLong',
		'$StartYearShort',
		'$StartYearLong',
		'$EndDayNameShort',
		'$EndDayNameLong',
		'$EndDayNumberShort',
		'$EndDayNumberLong',
		'$EndDaySuffix',
		'$EndMonthNumberShort',
		'$EndMonthNumberLong',
		'$EndMonthNameShort',
		'$EndMonthNameLong',
		'$EndYearShort',
		'$EndYearLong'
	];

	/**
	 * @return array
	 */
	public static function format_character_replacements($start, $end)
	{
        $carbonStart = Carbon::createFromTimestamp($start);
        $carbonEnd = Carbon::createFromTimestamp($end);

		return [
			$carbonStart->format('D'),
            $carbonStart->format('l'),
			date ('j', $start),
			date ('d', $start),
			date ('S', $start),
			date ('n', $start),
			date ('m', $start),
            $carbonStart->format('M'),
            $carbonStart->format('F'),
			date ('y', $start),
			date ('Y', $start),
            $carbonEnd->format('D'),
            $carbonEnd->format('l'),
			date ('j', $end),
			date ('d', $end),
			date ('S', $end),
			date ('n', $end),
			date ('m', $end),
            $carbonEnd->format('M'),
            $carbonEnd->format('F'),
			date ('y', $end),
			date ('Y', $end),
		];
	}

	/**
	 * @return string
	 */
	public static function localize($start, $end, $key)
	{
		global $customDateTemplates;
		if (is_array($customDateTemplates) && isset($customDateTemplates[$key])) {
			$template = $customDateTemplates[$key];
		} else {
			$template = _t(Calendar::class.".$key", $key);
		}

		return str_replace(
			self::$format_character_placeholders,
			self::format_character_replacements($start, $end),
			$template
		);
	}

	/**
	 * @return string
	 */
	public static function get_date_from_string($str)
	{
		$str = str_replace('-', '', $str);
		if (is_numeric($str)) {
			$missing = (8 - strlen($str));
			if ($missing > 0) {
				while ($missing > 0) {
					$str .= "01";
					$missing -= 2;
				}
			}
			return substr($str,0,4) . "-" . substr($str,4,2) . "-" . substr($str,6,2);
		}

		return date('Y-m-d');
	}

	/**
	 * @return array|null
	 */
	public static function get_date_string($startDate, $endDate)
	{
		$strStartDate = null;
		$strEndDate = null;

		$start = strtotime($startDate);
		$end = strtotime($endDate);

		$startYear = date("Y", $start);
		$startMonth = date("m", $start);

		$endYear = date("Y", $end);
		$endMonth = date("m", $end);

		// Invalid date. Get me out of here!
		if ($start < 1)	{
			return;
		}

		// Only one day long!
		if ($start == $end || !$end || $end < 1) {
			$key = self::ONE_DAY;
		} elseif ($startYear == $endYear) {
			$key = ($startMonth == $endMonth) ? self::SAME_MONTH_SAME_YEAR : self::DIFF_MONTH_SAME_YEAR;
		} else {
			$key = self::DIFF_MONTH_DIFF_YEAR;
		}
		$dateString = self::localize($start, $end, $key);
		$break = strpos($dateString, '$End');
		if ($break !== false) {
			$strStartDate = substr($dateString, 0, $break);
			$strEndDate = substr($dateString, $break+1, strlen($dateString) - strlen($strStartDate));
			return [$strStartDate, $strEndDate];
		}

		return [$dateString, ""];
	}

	/**
	 * @return string
	 */
	public static function microformat($date, $time, $offset = null)
	{
		if (!$date) {
			return "";
		}
		$ts = strtotime($date . " " . $time);
		if ($ts < 1) {
			return "";
		}
		$ret = date('c', $ts); // ISO 8601 datetime
		if ($offset) {
			// Swap out timezine with specified $offset
			$ret = preg_replace('/((\+)|(-))[\d:]*$/', $offset, $ret);
		}
		return $ret;
	}

	/**
	 * @return array
	 */
	public static function get_months_map($format = 'M')
	{
    	return [
	  		'01' => Carbon::createFromTimestamp(strtotime('2000-01-01'))->format($format),
	  		'02' => Carbon::createFromTimestamp(strtotime('2000-02-01'))->format($format),
	  		'03' => Carbon::createFromTimestamp(strtotime('2000-03-01'))->format($format),
	  		'04' => Carbon::createFromTimestamp(strtotime('2000-04-01'))->format($format),
	  		'05' => Carbon::createFromTimestamp(strtotime('2000-05-01'))->format($format),
	  		'06' => Carbon::createFromTimestamp(strtotime('2000-06-01'))->format($format),
	  		'07' => Carbon::createFromTimestamp(strtotime('2000-07-01'))->format($format),
	  		'08' => Carbon::createFromTimestamp(strtotime('2000-08-01'))->format($format),
	  		'09' => Carbon::createFromTimestamp(strtotime('2000-09-01'))->format($format),
	  		'10' => Carbon::createFromTimestamp(strtotime('2000-10-01'))->format($format),
	  		'11' => Carbon::createFromTimestamp(strtotime('2000-11-01'))->format($format),
	  		'12' => Carbon::createFromTimestamp(strtotime('2000-12-01'))->format($format),
		];
	}

	/**
	 * @return string
	 */
	public static function get_date_format()
	{
		if ($dateFormat = CalendarDateTime::config()->date_format_override) {
			return $dateFormat;
		}
		return _t(__CLASS__.'.DATEFORMAT', 'mdy');
	}

	/**
	 * @return string
	 */
	public static function get_time_format()
	{
		if ($timeFormat = CalendarDateTime::config()->time_format_override) {
			return $timeFormat;
		}
		return _t(__CLASS__.'.TIMEFORMAT', '24');
	}

	/**
	 * @return int
	 */
	public static function get_first_day_of_week()
	{
		$result = strtolower(_t(__CLASS__.'.FIRSTDAYOFWEEK', 'monday'));
		return ($result == "monday") ? Carbon::MONDAY : Carbon::SUNDAY;
	}

	public static function date_sort(&$data)
	{
		uasort($data, [self::class, "date_sort_callback"]);
	}

	/**
	 * Callback used by column_sort
	 */
	public static function date_sort_callback($a, $b)
	{
		if ($a->StartDate == $b->StartDate) {
			if ($a->StartTime == $b->StartTime) {
				return 0;
			} elseif (strtotime($a->StartTime) > strtotime($b->StartTime)) {
				return 1;
			}
			return -1;
		}
		elseif (strtotime($a->StartDate) > strtotime($b->StartDate)) {
			return 1;
		}
		return -1;
	}

	/**
	 * @return string
	 */
	public static function format_time($timeObj)
	{
		return self::get_time_format() == '24'
			? $timeObj->Format('HH:mm')
			: $timeObj->Nice();
	}
}
