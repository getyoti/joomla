<?php

defined('_JEXEC') or die; // No direct access

require_once JPATH_SITE.'/components/com_yoti/YotiHelper.php';
require_once JPATH_SITE . '/components/com_yoti/sdk/boot.php';
JPluginHelper::importPlugin('user');

/**
 * Class YotiController
 *
 * @author Moussa Sidibe <moussa.sidibe@yoti.com>
 */
class YotiController extends JControllerLegacy
{
    /**
     * @param bool $cachable
     * @param array $urlparams
     * @return JControllerLegacy
     */
    public function display($cachable = false, $urlparams = array())
    {
        $helper = new YotiHelper;
        $config = YotiHelper::getConfig();

        $redirect = (!empty($_GET['redirect'])) ? $_GET['redirect'] : 'index.php';
        switch ($this->input->get('task'))
        {
            case 'login':
                $userLinked = $helper->link();
                if ($userLinked && empty($_GET['redirect'])) {
                    $redirect = JRoute::_($config['yoti_success_url']);
                } else if(!$userLinked) {
                    // Redirect to failed URL
                    $failedUrl = ($config['yoti_failed_url'] == "/") ? "index.php" : $config['yoti_failed_url'];
                    $redirect = JRoute::_($failedUrl);
                }
                $this->setRedirect($redirect, false);
                return;
                break;

            case 'unlink':
                $helper->unlink();
                $this->setRedirect($redirect, false);
                //JFactory::getApplication()->redirect($redirect);
                return;
                break;

            case 'bin-file':
                $helper->binFile('selfie');
                exit;
                break;

            default:
                $this->setRedirect('index.php', false);
                return;
        }

        return parent::display($cachable, $urlparams);
    }

}