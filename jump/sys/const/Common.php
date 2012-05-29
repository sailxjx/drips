<?php

/**
 * Document: Common
 * Created on: 2012-4-16, 17:20:27
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Const_Common {
	// commands

	const C_START = 'start';
	const C_STOP = 'stop';
	const C_RESTART = 'restart';
	const C_KILL = 'kill';

	//options
	const OL_HELP = '--help';
	const OS_VERSION = '-v';
	const OL_VERSION = '--version';
	const OS_LOG = '-l';
	const OL_LOG = '--log';
	const OS_DAEMON = '-d';
	const OL_DAEMON = '--daemon';
	const OS_LISTEN = '-w';
	const OL_LISTEN = '--listen';
	const OS_QUIET = '-q';
	const OL_QUIET = '--quiet';

	//params
	const P_DAEMON_NUM = 'daemon_num';
	const P_LOG_FILE = 'log_file';
	const P_PRE_HOOK = 'pre_hook';
	const P_POST_HOOK = 'post_hook';
	const P_MIN_DAEMON_NUM = 'min_daemon_num';
	const P_PID = 'pid';
	const P_OTHER = 'other';

}
