<?php
/**
 * This is the configuration-object for the agenda module
 *
 * @author Wouter Verstuyf <info@webflow.be>
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
final class FrontendAgendaConfig extends FrontendBaseConfig
{
	/**
	 * The default action
	 *
	 * @var string
	 */
	protected $defaultAction = 'index';

	/**
	 * The disabled actions
	 *
	 * @var array
	 */
	protected $disabledActions = array();
}
