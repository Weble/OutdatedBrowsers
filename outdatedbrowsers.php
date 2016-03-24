<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Highlight
 *
 * @copyright   (C) 2016 Weble S.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

/**
 * System plugin to alert for outdated browsers.
 *
 * @since  2.5
 */

class PlgSystemOutdatedbrowsers extends JPlugin
{
	public function onAfterDispatch()
	{
		$input = JFactory::getApplication()->input;

		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		// Check if the highlighter should be activated in this environment.
		if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
		{
			return true;
		}

		$version = $this->params->get("browser_version","transform");

        JFactory::getDocument()->addScript('plugins/system/outdatedbrowsers/outdatedbrowser/js/outdatedbrowser.min.js');
        JFactory::getDocument()->addStylesheet('plugins/system/outdatedbrowsers/outdatedbrowser/css/outdatedbrowser.min.css');
        JFactory::getDocument()->addScriptDeclaration("jQuery(document).ready(function($){ outdatedBrowser({bgColor: '#f25648',color: '#ffffff',lowerThan: '".$version."', languagePath:''}); });");

		return true;
	}

	public function onAfterRender()
	{
		$input = JFactory::getApplication()->input;

		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}

		// Check if the highlighter should be activated in this environment.
		if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component')
		{
			return true;
		}

		$application = JFactory::getApplication();

		if (method_exists($application, 'getBody'))
        {
            $buffer = $application->getBody();
        }
        else
        {
            $buffer = JResponse::getBody();
        }

        $tag = explode("-", JFactory::getLanguage()->getTag());

        $language = array_shift($tag);

        $file_path = JPATH_SITE.'/plugins/system/outdatedbrowsers/outdatedbrowser/lang/'.$language.'.html';

        if(!JFile::exists($file_path)){

        	$file_path = JPATH_SITE.'/plugins/system/outdatedbrowsers/outdatedbrowser/lang/en.html';

        }

        $target = $this->params->get("anchor_target","_blank");

        $template = JFile::read($file_path);

        $template = str_replace('id="btnUpdateBrowser"', 'id="btnUpdateBrowser" target="'.$target.'"', $template);

        $buffer.='<div id="outdated">'.$template.'</div>';

        if (method_exists($application, 'setBody'))
        {
			$application->setBody($buffer);
        }
        else
        {
            JResponse::setBody($buffer);
        }

		return true;
	}
}