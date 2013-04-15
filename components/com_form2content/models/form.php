<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'project.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'formbase.php');

class Form2ContentModelForm extends Form2ContentModelFormBase
{
	public function save($data)
	{ 		
		$isNew			= empty($data['id']);
		$contentTypeId	= (int)$data['projectid'];
		$item			= $this->getItem((int)$data['id']);
		$app			= JFactory::getApplication();
		$user 			=& JFactory::getUser();
		$config 		=& JFactory::getConfig();

		// Get the current date and time
		$dateNow = JFactory::getDate('now', 'UTC');
		$dateNow->setTimezone(new DateTimeZone($config->get('offset')));
		$dateNow = $dateNow->toMySQL();						

		$translatedDateFormat = F2cDateTimeHelper::getTranslatedDateFormat();
		
		// Load the Content Type Settings
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($contentTypeId);
		$contentTypeSettings = new JRegistry();
		$contentTypeSettings->loadArray($contentType->settings);
		
		// Verify the captcha
		if($contentTypeSettings->get('captcha_front_end'))
		{
			if($app->getUserState('F2cCaptchaState', '0') == '0')
			{
				$this->setError(JText::_('COM_FORM2CONTENT_ERROR_CAPTCHA_INCORRECT'));
				return false;
			}

			// reset captcha
			$app->setUserState('F2cCaptchaState', '0');			
		}
		
		if(!$this->canSubmitArticle($contentTypeId, $item->id))
		{
			return false;
		}

		if($contentTypeSettings->get('date_created_front_end'))
		{
			if($data['created'])
			{
				if($date = F2cDateTimeHelper::ParseDate($data['created'], '%Y-%m-%d'))
				{
					$data['created'] = $date->toMySQL();			
				}
				else
				{
					$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_CREATED_LABEL'), $translatedDateFormat));
					return false;
				}
			}
		}
		else 
		{
			$data['created'] = $isNew ? $dateNow : $item->created;
		}
				
		if($contentTypeSettings->get('frontend_pubsel'))
		{
			if($data['publish_up'])
			{
				if($date = F2cDateTimeHelper::ParseDate($data['publish_up'], '%Y-%m-%d'))
				{
					$data['publish_up'] = $date->toMySQL();			
				}
				else
				{
					$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_UP_LABEL'), $translatedDateFormat));
					return false;
				}
			}

			if($data['publish_down'])
			{
				if($date = F2cDateTimeHelper::ParseDate($data['publish_down'], '%Y-%m-%d'))
				{
					$data['publish_down'] = $date->toMySQL();			
				}
				else
				{
					$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_DOWN_LABEL'), $translatedDateFormat));
					return false;
				}
			}
		}

		if(!$contentTypeSettings->get('title_front_end'))
		{
			$data['title'] = $isNew ? $contentTypeSettings->get('title_default') : $item->title;
		}

		if(!$contentTypeSettings->get('author_front_end'))
		{
			$data['created_by'] = $isNew ? $user->id : $item->created_by;
		}

		if(!$contentTypeSettings->get('author_alias_front_end'))
		{
			$data['created_by_alias'] = $isNew ? '' : $item->created_by_alias;
		}

		if(!$contentTypeSettings->get('frontend_templsel'))
		{
			$data['intro_template'] = $isNew ? $contentTypeSettings->get('intro_template') : $item->intro_template;
			$data['main_template'] = $isNew ? $contentTypeSettings->get('main_template') : $item->main_template;
		}
		
		if(!$contentTypeSettings->get('metadesc_front_end'))
		{
			$data['metadesc'] = $isNew ? $contentType->metadesc : $item->metadesc;
		}
		
		if(!$contentTypeSettings->get('metakey_front_end'))
		{
			$data['metakey'] = $isNew ? $contentType->metakey : $item->metakey;
		}

		if(!$contentTypeSettings->get('state_front_end'))
		{
			$data['state'] = $isNew ? $contentTypeSettings->get('state_default') : $item->state;
		}

		if(!$contentTypeSettings->get('featured_front_end'))
		{
			$data['featured'] = $isNew ? $contentTypeSettings->get('featured_default') : $item->featured;
		}

		if(!$contentTypeSettings->get('access_level_front_end'))
		{
			$data['access'] = $isNew ? $contentTypeSettings->get('access_default') : $item->access;
		}
		
		if(!$contentTypeSettings->get('frontend_catsel'))
		{
			$data['catid'] = $isNew ? $contentTypeSettings->get('catid') : $item->catid;
		}

		if(!$contentTypeSettings->get('language_front_end'))
		{
			$data['language'] = $isNew ? $contentTypeSettings->get('language_default') : $item->language;
		}
		
		$data['attribs'] = $isNew ? $contentType->attribs : $item->attribs;
		$data['metadata'] = $isNew ? $contentType->metadata : $item->metadata;
		
		return parent::save($data, false);
	}
	
	/*
	 * Special save function for import.
	 * This function skips all user related checks.
	 */
	public function saveCron($data, $saveFormOnly = false, $preparedFieldData)
	{
		if($data['created'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['created'], '%Y-%m-%d'))
			{
				$data['created'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_CREATED_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_up'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_up'], '%Y-%m-%d'))
			{
				$data['publish_up'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_UP_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_down'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_down'], '%Y-%m-%d'))
			{
				$data['publish_down'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_DOWN_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		return parent::save($data, $saveFormOnly, $preparedFieldData);
	}
	
	function canSubmitArticle($contentTypeId, $id)
	{
		$user =& JFactory::getUser();
		
		// Load the Content Type Settings
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($contentTypeId);
		$contentTypeSettings = new JRegistry();
		$contentTypeSettings->loadArray($contentType->settings);
		
		$maxForms = (int)$contentTypeSettings->get('max_forms');
		
		if($maxForms == 0)
		{
			// no limit
			return true;
		}
		else
		{
			$db =& $this->getDbo();
		
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$query->from('#__f2c_form');
			$query->where('projectid = ' . (int)$contentTypeId);
			$query->where('created_by = ' . (int)$user->id);
			$query->where('id <> ' . (int)$id);
	
			$db->setQuery($query->__toString());

			$result = $db->loadResult();
			
			if($result >= $maxForms)
			{
				if($maxForms > 1)
				{
					$numArticles = '('.$maxForms.' '.JText::_('COM_FORM2CONTENT_FORMS').')'; 			
				}
				else
				{
					$numArticles = '('.$maxForms.' '.JText::_('COM_FORM2CONTENT_FORM').')'; 					
				}
				
				$this->setError(JText::_('COM_FORM2CONTENT_ERROR_MAX_FORMS_EXCEEDED') . ' ' . $numArticles);
				return false; 				
			}
		}
		
		return true;
	}
	
	/*
	 * Load the Id of the newest article that the current user has written.
	 * The ContentTypeId may be predefined.
	 * If no article is found, the id for a new article is returned 
	 */
	public function getDefaultArticleId($contentTypeId)
	{
		$user 	=& JFactory::getUser();
		$db 	=& $this->getDbo();
		$query 	= $db->getQuery(true);
		
		$query->select('id');
		$query->from('#__f2c_form');
		$query->where('created_by = ' . (int)$user->id);
		$query->where('projectid = ' . (int)$contentTypeId);
		$query->where('state IN (' . F2C_STATE_PUBLISHED . ', ' . F2C_STATE_UNPUBLISHED . ')');
		$query->order('created DESC LIMIT 1');

		$db->setQuery($query);
		$id = $db->loadResult();

		return ($id) ? $id : 0;		
	}		
}
?>