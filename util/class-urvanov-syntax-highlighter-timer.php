<?php
/**
 * Timer Class
 *
 * @package   Crayon Syntax Highlighter
 * @author    Fedor Urvanov, Aram Kocharyan
 * @copyright Copyright 2013, Aram Kocharyan
 * @link      https://urvanov.ru
 */

defined( 'ABSPATH' ) || exit;

/**
 * Used to measure execution time.
 *
 * Class UrvanovSyntaxHighlighterTimer
 */
class UrvanovSyntaxHighlighterTimer {

	/**
	 * No set.
	 */
	const NO_SET = -1;

	/**
	 * Start time.
	 *
	 * @var int
	 */
	private $start_time = self::NO_SET;

	/**
	 * UrvanovSyntaxHighlighterTimer constructor.
	 */
	public function __construct() {}

	/**
	 * Start.
	 */
	public function start() {
		$this->start_time = microtime( true );
	}

	/**
	 * Stop.
	 *
	 * @return float|int
	 */
	public function stop() {
		if ( self::NO_SET !== $this->start_time ) {
			$end_time         = microtime( true ) - $this->start_time;
			$this->start_time = self::NO_SET;

			return $end_time;
		} else {
			return 0;
		}
	}
}
