<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Config;
use think\Log;
use think\Cache;

class Cron extends Command
{
	protected function configure()
	{
		$this->setName('cron')->setDescription('Clear the candidate. ');
	}
	
	protected function execute(Input $input, Output $output)
	{
		$this->clearCandidate();//更新所有过期的人选
		$this->clearQuitEmployee();//清理离职顾问人选
		$this->deleteCandidate();//删除已丢弃人选
		$output->writeln('success');
	}	
	
	/**
	 * 清理过期人选
	 */
	private function clearCandidate()
	{
		$business = controller('customer/CandidateBiz', 'business');
		$business->updateAllExpired(Config::get('cron_candidate_expire'));
	}
	
	/**
	 * 清理离职顾问人选
	 */
	private function clearQuitEmployee()
	{
		$business = controller('customer/CandidateBiz', 'business');
		$business->updateQuitEmployee();
	}
	
	/**
	 * 清理丢弃人选
	 */
	private function deleteCandidate()
	{
		$business = controller('customer/CandidateBiz', 'business');
		$business->deleteDepose();
	}
}