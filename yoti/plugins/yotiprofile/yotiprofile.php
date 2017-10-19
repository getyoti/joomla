<?php
/**
 * @version
 * @copyright    Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('JPATH_BASE') or die;

require_once JPATH_ROOT . '/components/com_yoti/sdk/boot.php';
require_once JPATH_ROOT . '/components/com_yoti/YotiHelper.php';

// Load the Joomla Model framework
jimport('joomla.application.component.model');
// Load YotiUserModel
JLoader::register('YotiModelUser', JPATH_ROOT . '/components/com_yoti/models/user.php');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * UserYotiprofile plugin.
 *
 * @package        Joomla.Plugins
 * @subpackage    user.profile
 * @version        2.5
 */
class plgUserYotiprofile extends JPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor.
     *
     * @param   object  &$subject  The object to observe
     * @param   array   $config    An array that holds the plugin configuration
     *
     * @since   1.0.0
     */
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        JFormHelper::addFieldPath(__DIR__ . '/fields');
    }

    /**
     * @param	string	The context for the data
     * @param	int		The user id
     * @param	object
     * @return	boolean
     * @since	2.5
     */
    public function onContentPrepareData($context, $data)
    {
        // Check we are manipulating a valid form.
        if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile'))){
            return true;
        }

        $userId = (isset($data->id)) ? $data->id : 0;

        // Load the profile data from the database.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('data')
            ->from($db->quoteName(YotiHelper::YOTI_USER_TABLE_NAME))
            ->where($db->quoteName('joomla_userid') . '=' . $db->quote($userId))
            ->setLimit('1');
        $result = $db->setQuery($query)->loadAssoc();

        // Check for a database error.
        if ($db->getErrorNum()) {
            $this->_subject->setError($db->getErrorMsg());
            return false;
        }

        // Merge the profile data.
        $data->yotiprofile = [];
        $profileArr = (!empty($result['data'])) ? unserialize($result['data']) : [];

        foreach ($profileArr as $key => $value) {
            if ($key == YotiHelper::ATTR_SELFIE_FILE_NAME) {
                //$profilePic = '<img src="' . JRoute::_('index.php?option=com_yoti&task=bin-file&field=selfie') . '" width="100" />';
                //$data->yotiprofile[$key] = $profilePic;
                $data->yotiprofile[$key] = 'Edit your profile to see your Selfie';
                $profile_image = JHtml::_('image', "http://".$_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_yoti&task=bin-file&field=selfie'), 'my profile');
                //$profile_link = JHtml::_('link', "http://".$_SERVER['HTTP_HOST'].JRoute::_('index.php?option=com_yoti&task=bin-file&field=selfie'), 'my profile');
                //$data->yotiprofile[$key] = $profile_link;

            } else {
                $data->yotiprofile[$key] = $value;
            }
        }
        return true;
    }

    /**
     * @param    JForm    The form to be altered.
     * @param    array    The associated data for the form.
     * @return    boolean
     * @since    1.6
     */
    public function onContentPrepareForm($form, $data)
    {
        // Load user_profile plugin language
        //$lang = JFactory::getLanguage();
        //$lang->load('plg_user_yotiprofile', JPATH_SITE);
        $config = YotiHelper::getConfig();

        if (!($form instanceof JForm))
        {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }

        if (
            $form->getName() === 'com_users.login'
            && $config['yoti_only_existing_user']
            && !is_null(YotiHelper::getYotiUserFromSession())
        ) {
            // Reorder the form to put the warning message on top
            JForm::addFieldPath(dirname(__FILE__) . '/fields');
            $yotiLoginXml = simplexml_load_file(dirname(__FILE__) . "/profiles/login.xml");
            $formXml = $form->getXML();
            $form->reset(true);
            $form->setFields($yotiLoginXml);
            $form->setFields($formXml);
        }

        // Check we are manipulating a valid form.
        $forms = array('com_users.profile', 'com_users.registration', 'com_users.user', 'com_admin.profile');
        if (!in_array($form->getName(), $forms))
        {
            return true;
        }

        if (
            !empty($data->yotiprofile)
            && ($form->getName() == 'com_users.profile'
            || $form->getName() == 'com_users.user')
        )
        {
            JForm::addFormPath(dirname(__FILE__) . '/profiles');
            $form->loadFile('profile', false);
        }

        return true;
    }

    /**
     * Remove all user profile information for the given user ID
     * Method is called after user data is deleted from the database
     *
     * @param $user
     * @param $success
     * @param $msg
     * @return bool
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if (!$success)
        {
            return false;
        }

        $yotiUserModel = new YotiModelUser();

        $userId = (isset($user['id'])) ? $user['id'] : 0;

        if ($userId)
        {
            try
            {
                if ($yotiUserModel->getYotiUserById($userId)) {
                    $yotiUserModel->deleteYotiUser($userId);
                }
            }
            catch (\Exception $e)
            {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Triggered after user login process
     *
     * @param array $user
     * @param array $options
     */
    public function onUserLogin($user, $options) {
        if(!YotiHelper::getYotiUserFromSession()) {
            $yotiUserModel = new YotiModelUser();
            $yotiUserData = $yotiUserModel->getYotiUserById($user['id']);
            if(!empty($yotiUserData) && isset($yotiUserData['data'])) {
                // After successful login store Yoti user data in the session
                $yotiuserProfile = YotiHelper::makeYotiUserProfile(unserialize($yotiUserData['data']), $user['id']);
                YotiHelper::storeYotiUserInSession($yotiuserProfile);
            }
        }
    }

    /**
     * Create or delete Yoti user from Joomla.
     * Method is called after a user has logged in.
     *
     * @param $options
     * @return bool
     */
    public function onUserAfterLogin($options)
    {
        $input  = JFactory::getApplication()->input;
        $user = $options['user'];
        $userId = (is_object($user)) ? $user->id : 0;
        $yotiUserModel = new YotiModelUser();

        if ($input->post) {
            $postData = $input->post->getArray();
            // If Yoti nolink option is ticked then remove Yoti user
            if (isset($postData['credentials']['yoti_nolink']) && $input->post->get('credentials')) {
                try {
                    if($yotiUserModel->getYotiUserById($userId)) {
                       $yotiUserModel->deleteYotiUser($userId);
                    }
                } catch(\Exception $e) {
                    $this->_subject->setError($e->getMessage());
                    return false;
                }
            } else if (YotiHelper::getYotiUserFromSession()) {
                // If the session is set then create Yoti user.
                $activityDetails = YotiHelper::getYotiUserFromSession();

                if ($activityDetails) {
                    try {
                        $yotiHelper = new YotiHelper();
                        $yotiHelper->createYotiUser($activityDetails, $userId);
                    } catch(\Exception $e) {
                        $this->_subject->setError($e->getMessage());
                        return false;
                    }
                }
            }
            YotiHelper::clearYotiUserFromSession();
        }

        return true;
    }

}