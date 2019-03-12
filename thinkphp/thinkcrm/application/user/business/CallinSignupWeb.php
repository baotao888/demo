<?php
namespace app\user\business;

use app\user\model\CrmCallinUser;
use app\user\business\Callin;

class CallinSignupWeb extends CallinSignup
{
	protected $from = 'web';
}