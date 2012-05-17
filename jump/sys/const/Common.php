<?php

/**
 * Document: Common
 * Created on: 2012-4-16, 17:20:27
 * @author: jxxu
 * GTalk: sailxjx@gmail.com
 */
abstract class Const_Common {
	//commands

	const C_START = 'start';
	const C_STOP = 'stop';
	const C_RESTART = 'restart';
	const C_KILL = 'kill';

	//options
	const OL_HELP = '--help';
	const OS_VERSION = '-v';
	const OL_VERSION = '--version';
	const OS_LOG = '-l';
	const OL_LOG = '--changelog';
	const OS_DAEMON = '-d';
	const OL_DAEMON = '--daemon';
	const OS_LISTEN = '-w'; //需要监控
	const OL_LISTEN = '--listen'; //需要监控

}
