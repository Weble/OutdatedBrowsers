<?php
/**
 * @copyright   (C) 2016 Weble S.r.l. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

/**
 * System Plugin to integrate with Outdatedbrowser.com
 */
class PlgSystemOutdatedbrowsers extends JPlugin
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationCms
     */
    protected $application;

    /**
     * Load the application once
     */
    public function __construct($subject, array $config)
    {
        parent::__construct($subject, $config);

        $this->application = JFactory::getApplication();
    }

    /**
     * Add The required JS and CSS files
     */
    public function onAfterDispatch()
    {
        // Check that we are in the site application.
        if ($this->application->isAdmin()) {
            return true;
        }

        // Check if it should be activated in this environment.
        if (JFactory::getDocument()->getType() !== 'html' || $this->application->input->get('tmpl', '', 'cmd') === 'component') {
            return true;
        }

        // Which browser should we target?
        $version = $this->params->get("browser_version", "transform");

        // Add the required files
        JFactory::getDocument()->addScript('plugins/system/outdatedbrowsers/outdatedbrowser/js/outdatedbrowser.min.js');
        JFactory::getDocument()->addStylesheet('plugins/system/outdatedbrowsers/outdatedbrowser/css/outdatedbrowser.min.css');

        // call the js plugin without jQuery
        JFactory::getDocument()->addScriptDeclaration("
        //event listener: DOM ready
		function outdatedBrowsersAddLoadEvent(func) {
			var oldonload = window.onload;
			if (typeof window.onload != 'function') {
				window.onload = func;
			} else {
				window.onload = function() {
					if (oldonload) {
						oldonload();
					}
					func();
				}
			}
		}
		outdatedBrowsersAddLoadEvent(function(){
			outdatedBrowser({bgColor: '#f25648',color: '#ffffff',lowerThan: '" . $version . "', languagePath:''}); });
		})");

        return true;
    }

    /**
     * Add the piece of html required by outdatedbrowser.com
     */
    public function onAfterRender()
    {
        $input = $this->application->input;

        // Check that we are in the site application.
        if ($this->application->isAdmin()) {
            return true;
        }

        // Check if the highlighter should be activated in this environment.
        if (JFactory::getDocument()->getType() !== 'html' || $input->get('tmpl', '', 'cmd') === 'component') {
            return true;
        }

        // compatibility with old joomla versions
        if (method_exists($this->application, 'getBody')) {
            $buffer = $this->application->getBody();
        } else {
            $buffer = JResponse::getBody();
        }

        // Get the current language (i.e.: en)
        $tag = explode("-", JFactory::getLanguage()->getTag());
        $language = array_shift($tag);

        // Load the correspoding html box from the outdatedbrowser.com package
        $file_path = JPATH_SITE . '/plugins/system/outdatedbrowsers/outdatedbrowser/lang/' . $language . '.html';

        if (!JFile::exists($file_path)) {
            $file_path = JPATH_SITE . '/plugins/system/outdatedbrowsers/outdatedbrowser/lang/en.html';
        }

        // Allow setting a _blank target since the default of outdatedbrowser.com is the same page
        $target = $this->params->get("anchor_target", "_blank");
        $template = JFile::read($file_path);
        $template = str_replace('id="btnUpdateBrowser"', 'id="btnUpdateBrowser" target="' . $target . '"', $template);

        // Add the piece of html
        $buffer .= '<div id="outdated">' . $template . '</div>';

        // Replace the buffer
        if (method_exists($this->application, 'setBody')) {
            $this->application->setBody($buffer);
        } else {
            JResponse::setBody($buffer);
        }

        return true;
    }
}