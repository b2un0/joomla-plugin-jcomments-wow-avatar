<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class plgJCommentsWoW_Avatar extends JPlugin
{
    public function onPrepareAvatar(&$comment)
    {
        $comments = array();
        $comments[0] =& $comment;
        $this->onPrepareAvatars($comments);
    }

    public function onPrepareAvatars(&$comments)
    {
        $wow = WoW::getInstance();

        try {
            $result = $wow->getAdapter('WoWAPI')->getData('members');
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage('JComments - WOW Avatar: ' . $e->getMessage(), 'error');
            return;
        }

        foreach ($comments as &$comment) {
            $name = $comment->{$this->params->get('mapping', 'name')};
            $name = JString::strtolower($name);

            foreach ($result->body->members as $member) {
                if ($name == JString::strtolower($member->character->name)) {
                    $url = 'http://' . $wow->params->get('region') . '.battle.net/static-render/' . $wow->params->get('region') . '/' . $member->character->thumbnail;
                    $comment->avatar = JHtml::_('image', $url, '');
                    break;
                }
            }
        }
    }
}