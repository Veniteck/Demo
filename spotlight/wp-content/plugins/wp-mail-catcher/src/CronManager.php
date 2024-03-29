<?php

namespace WpMailCatcher;

class CronManager
{
	public $currentIntervals = null;
	public $prefix;
	private $cronTasks = [];
	static private $instance = false;

	private function __construct()
	{
		$this->prefix = GeneralHelper::$adminPageSlug . '_';
		add_filter('cron_schedules', [$this, 'addIntervals']);
	}

	public static function getInstance()
	{
		if (self::$instance == false) {
			self::$instance = new CronManager();
		}

		return self::$instance;
	}

	public function addTask($callback, $interval)
	{
		$identifier = $this->prefix . count($this->cronTasks);

		add_action($identifier, $callback);
		$this->cronTasks[] = $identifier;

		if (wp_next_scheduled($identifier)) {
			return;
		}

		if ($this->currentIntervals == null) {
			$this->currentIntervals = wp_get_schedules();
		}

		$nextRun = time() + $this->currentIntervals[$interval]['interval'];

		wp_schedule_event($nextRun, $interval, $identifier);
	}

	public function getTasks()
	{
		$cronTasks = _get_cron_array();
		$events = [];

		foreach ($cronTasks as $time => $cron) {
			foreach ($cron as $hook => $dings) {
				foreach ($dings as $sig => $data) {
					if (strpos($hook, $this->prefix) === false) {
						continue;
					}

					$events[] = [
						'hook'     => $hook,
						'time'     => $time,
						'sig'      => $sig,
						'args'     => $data['args'],
						'schedule' => $data['schedule'],
						'interval' => isset($data['interval']) ? $data['interval'] : null,
						'nextRun' => GeneralHelper::getHumanReadableTimeFromNow($time)
					];
				}
			}
		}

		return $events;
	}

	public function clearTasks($task = null)
	{
		if ($task != null) {
			wp_clear_scheduled_hook($task);
			return;
		}

		foreach ($this->cronTasks as $task) {
			wp_clear_scheduled_hook($task);
		}
	}

	public function addIntervals($schedules)
	{
		$schedules['weekly'] = [
			'interval' => 604800,
			'display' => __('Once Weekly', 'WpMailCatcher')
		];

		$schedules['monthly'] = [
			'interval' => 2635200,
			'display' => __('Once a month', 'WpMailCatcher')
		];

		return $schedules;
	}
}
