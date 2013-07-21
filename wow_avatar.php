<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class plgJCommentsWoW_Avatar extends JPlugin {

	public function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->params->set('guild', rawurlencode(JString::strtolower($this->params->get('guild'))));
		$this->params->set('realm', rawurlencode(JString::strtolower($this->params->get('realm'))));
		$this->params->set('region', JString::strtolower($this->params->get('region')));
	}

	public function onPrepareAvatar(&$comment) {
		$comments = array();
		$comments[0] =& $comment;
		$this->onPrepareAvatars($comments);
	}

	public function onPrepareAvatars(&$comments) {
		$members = $this->getMembers();
		
		if(!is_array($members)) {
			JFactory::getApplication()->enqueueMessage('JComments - WOW Avatar: ' . $members, 'error');
			return;
		}
			
		foreach($comments as &$comment) {
			$name = $comment->{ $this->params->get('mapping', 'name') };
			$name = JFilterOutput::stringURLSafe($name);
		
			foreach($members as $member) {
				if($name == strtolower(JFilterOutput::stringURLSafe($member->character->name))) {
					$url = 'http://' . $this->params->get('region') . '.battle.net/static-render/' . $this->params->get('region') . '/' . $member->character->thumbnail;
					$comment->avatar = JHtml::_('image', $url, '');
					break;
				}
			}
		}
	}

	protected function getMembers() {
		$url = 'http://' . $this->params->get('region') . '.battle.net/api/wow/guild/' . $this->params->get('realm') . '/' . $this->params->get('guild') . '?fields=members';
		
		$cache = JFactory::getCache('wow', 'output');
		$cache->setCaching(1);
		$cache->setLifeTime($this->params->get('cache_time', 60));
		 
		$key = md5($url);
		 
		if(!$result = $cache->get($key)) {
			try {
				$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
				$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; JComments Avatar WoW Character; php/' . phpversion());
		
				$result = $http->get($url, null, $this->params->get('timeout', 10));
			}catch(Exception $e) {
				return $e->getMessage();
			}
		
			$cache->store($result, $key);
		}
		
		if($result->code != 200) {
			return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
		}
		
		$result->body = json_decode($result->body);
		
		return $result->body->members;		 
	}
}