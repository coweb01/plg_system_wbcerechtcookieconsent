<?php

/**
 * @package WBCERECHTCOOKIECONSENT
 * @copyright Copyright (C) das webconcept.de, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://das-webconcept.de
 * @author email co@das-webconcept.de
 * @version 1.0.0
 * @date 2024-08-23
 * 
 * 
 * Fügt den Cookie Consent Code von Usercentricts und eRecht24 hinzu.
 * Fügt eine Info Box für die ausgeblendeten Inhalte hinzu.
 * Fügt einen Link zu den Cookie-Einstellungen hinzu
 * 
 */
namespace Joomla\Plugin\System\WbcErechtCookieConsent\Extension; 

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

final class WbcErechtCookieConsent extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Blocked Content ID's
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $JsonBlockesContentItems = [];

	/**
	 * Blocked Content plugin params
	 *
	 * @var    object
	 * @since  3.1
	 */
	
	protected $wbcblockedcontent = [];

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onAfterRender' => 'onAfterRender',
			'onAfterInitialise' => 'onAfterInitialise'
		];
	}


	/**
	 * Plugin method is the array value in the getSubscribedEvents method
	 * The plugin then modifies the Event object (if it's not immutable)
	 */

	function onAfterInitialise() {
		$app 			= Factory::getApplication();

		if ( $app->isClient('administrator') ) {
			return;
		}
		// Pfad zur JSON-Datei
		$jsonFilePath = JPATH_ROOT . '/media/plg_system_wbcerechtcookieconsent/files/blocked_elements.json';

		if (file_exists($jsonFilePath)) {
			$jsonContent 		 = file_get_contents($jsonFilePath);
			$this->JsonBlockesContentItems = json_decode($jsonContent, true);
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				Factory::getApplication()->enqueueMessage( json_last_error_msg() , 'error');
				exit;
			}
		} else {
			Factory::getApplication()->enqueueMessage(TEXT::_('PLG_WBCBLOCKERECHTCOOKIECONSENT_JSON_FILE_NOT_FOUND') . $jsonFilePath, 'error');
			exit;
		}
		
		$this->wbcblockedcontent = $this->params->get('wbcblockedcontent');
	}

    function onBeforeCompileHead() {
		$app 			= Factory::getApplication();
		if ( $app->isClient('administrator') ) {
			return;
		}
		$doc 			= Factory::getDocument();
		$wa 			= $doc->getWebAssetManager();

		
		// WebAssetManager css cookie consent hinzufügen
		$wa = $doc->getWebAssetManager();
		$wa->registerAndUseStyle('cookieconsent', 'media/plg_system_wbcerechtcookieconsent/css/wbcerechtcookieconsent.css', [], [], []);
	 
		// Cookie Consent Banner
		$wbcerechtcookieid 				= $this->params->get('wbcerechtcookieId');
		$wbcerechtcookieurl 			= $this->params->get('wbcerechtcookieUrl');
        if (strpos($wbcerechtcookieurl, 'https://') !== 0) {
            $wbcerechtcookieurl = 'https://' . $wbcerechtcookieurl;
        }
		// Cookie Consent Data Protector
		$wbcerechtcookiedataprotector = $this->params->get('wbcerechtcookieDataprotector');
		if (strpos($wbcerechtcookiedataprotector, 'https://') !== 0) {
            $wbcerechtcookiedataprotector = 'https://' . $wbcerechtcookiedataprotector;
        }
		
		if (empty($wbcerechtcookieurl) || empty($wbcerechtcookiedataprotector)) {
			return;
		}

		/* WebAssetManager css cookie consent hinzufügen  */
		
		//$wa->registerAndUseStyle('cookieconsent', 'media/plg_system_wbcerechtcookieconsent/css/wbcerechtcookieconsent.css', [], [], []);
		
		// CSS Anpassung für das Overlay der blockierten Elemente
		$btnbgcolor 		= $this->params->get('wbcerechtbtnbgcolor');
		$btncolor 			= $this->params->get('wbcerechtbtncolor');
		$btnhovercolor 		= $this->params->get('wbcerechtbtnhovercolor');
		$btnhoverbgcolor 	= $this->params->get('wbcerechtbtnhoverbgcolor');
		$btnactivecolor 	= $this->params->get('wbcerechtbtnactivecolor');
		$btnactivebgcolor 	= $this->params->get('wbcerechtbtnactivebgcolor');
		$overlaybgcolor 	= $this->params->get('wbcerechtoverlaybgcolor');
		$overlaycolor 		= $this->params->get('wbcerechtoverlaycolor');
		$btnborderradius    = $this->params->get('wbcerechtbtnborderradius');
		$linkcolor 			= $this->params->get('wbcerechtlinkcolor"');
		$linkhovercolor 	= $this->params->get('wbcerechtlinkhovercolor');
		$linkactivecolor 	= $this->params->get('wbcerechtlinkactivecolor');
		$containercolor 	= $this->params->get('wbcerechtcontainercolor');

		$uc_css  = ".uc-embedding-buttons > button {border-radius: $btnborderradius;}";
		$uc_css .= ".uc-embedding-buttons > .uc-embedding-accept { background: $btnbgcolor; color: $btncolor; border-radius: $btnborderradius; }"; 
		$uc_css .= ".uc-embedding-buttons > .uc-embedding-accept:hover, .uc-embedding-buttons > .uc-embedding-accept:focus { background: $btnhoverbgcolor; color: $btnhovercolor; }";
		$uc_css .= ".uc-embedding-buttons > .uc-embedding-accept:active  { background: $btnactivebgcolor; color: $btnactivecolor; }";
		$uc_css .= ".uc-embedding-buttons > .uc-embedding-more-info { border-radius: $btnborderradius; }"; 
		$uc_css .= ".uc-embedding-container { border-radius: 0; width: 100%!important; height: 100%!important; background: $overlaybgcolor!important; }";
		$uc_css .= ".uc-embedding-container > .uc-embedding-wrapper { background-color: $containercolor; border-radius: 0; box-shadow: none; width: 100%!important;}";
		$uc_css .= ".description-text, uc-embedding-wrapper h3  { color: $overlaycolor; }";
		$uc_css .= ".uc-embedding-wrapper .a { color: $linkcolor; }";
		$uc_css .= ".uc-embedding-wrapper .a:hover, .uc-embedding-wrapper a:focus { color: $linkhovercolor; }";
		$uc_css .= ".uc-embedding-wrapper .a:active { color: $linkactivecolor; }";

		$wa->addInlineStyle($uc_css);

		// Container für die blockierten Elemente finden
		$i = 0;
		foreach ($this->wbcblockedcontent as $element) {
			if(array_key_exists($element, $this->JsonBlockesContentItems)){
				$blockedcontentItems = $this->JsonBlockesContentItems[$element];
				$i++;
			}
		}

		if ($blockedcontentItems) {
			
			$blockedcontent = '';
			$reloadIds	  	= '';

			foreach ($blockedcontentItems as $blockedcontentItem) {
				$id = $blockedcontentItem['id'];
				$container = $blockedcontentItem['container'];	
				$blockedcontent  .= "'$id':'$container',";
				$reloadIds       .= "'$id', ";
			}
		}

		// externes Skript 
		echo HtmlHelper::_('script', $wbcerechtcookieurl, [], ['id' => 'usercentrics-cmp', 'async' => true, 'data-eu-mode' => 'true', 'data-settings-id' => $wbcerechtcookieid]);
		echo HtmlHelper::_('script', $wbcerechtcookiedataprotector, [], []);
		echo HtmlHelper::_('script', 'https://privacy-proxy.usercentrics.eu/legacy/uc-block.bundle.js', [], []);
		
		// script für die overlays der blockierten Elemente
		if(!empty($blockedcontent)){
			$ScriptoverlayBlockesContent = "uc.blockElements({";
			$ScriptoverlayBlockesContent .= $blockedcontent;
			$ScriptoverlayBlockesContent .= "});";
			$wa->addInlineScript($ScriptoverlayBlockesContent);	
 		
			// script zum Reload der Seite nach Consent Aenderung
			$ScriptReload  = "uc.reloadOnOptIn(";
			$ScriptReload .= $reloadIds;
			$ScriptReload .= ");";
			$ScriptReload .= "uc.reloadOnOptOut(";
			$ScriptReload .= $reloadIds;
			$ScriptReload .= ");";
			$wa->addInlineScript($ScriptReload);	
		}
		// Cookiebanner auf Impresum und Datenschutz ausblenden
		$wbcblockerechtcookiebanner 	 = $this->params->get('wbcblockerechtcookiebanner');
		$wbcblockerechtcookiebanneritems = [];
		$menu 							 = $app->getMenu();
		$activeMenuItem 				 = $menu->getActive();
		
		if ($wbcblockerechtcookiebanner == 1) {
			$wbcblockerechtcookiebanneritems[] = $this->params->get('wbcimprint');
			$wbcblockerechtcookiebanneritems[] = $this->params->get('wbcprivacy');

			if (!empty($wbcblockerechtcookiebanneritems)){
				foreach ($wbcblockerechtcookiebanneritems as $item) {
					if ($activeMenuItem->id == $item) {
						$wa->addInlineScript('var UC_UI_SUPPRESS_CMP_DISPLAY=true;');					
					}
				}
			}
		}
		return true;
	}

	function onAfterRender() {


		$app = Factory::getApplication();
		$doc = Factory::getDocument();

		if ( $app->isClient('administrator') ) {
			return;
		}
		
		$documentFormat 						= $app->input->getCmd('format', 'html');

		$wbcerechtcookielinkposition 			= $this->params->get('wbcerechtcookielinkposition');
		$wbcerechtcookielink 					= $this->params->get('wbcerechtcookieLink');
		$wbcerechtcookielinktext 				= $this->params->get('wbcerechtcookieLinkText');
        $wbcerechtcookiebannerplaceholderimg    = $this->params->get('wbcerechtcookiebannerplaceholderimg');
		$htmlreplace = array();


		// Cookielink
		$htmlreplace[0]							= ' <!-- start wbcerechtcookieconsent --> ';	
		$htmlreplace[0] 			  		   .= '<ul class="wbc-erechtcookie nav"><li class="nav-item">';
		$htmlreplace[0]              		   .= HtmlHelper::_('link', $wbcerechtcookielink, $wbcerechtcookielinktext, ['class' => 'wbc-erechtcookie_link', 'role' => 'button', 'tabindex' => '0']);
		$htmlreplace[0]				   		   .= '</li></ul>';
		$htmlreplace[0]				   		   .= ' <!-- end wbcerechtcookieconsent --> ';	

		if ( ($documentFormat == 'html' || is_null($documentFormat)) ) {
			$html = $app->getBody();

			// Body extrahieren
			if(preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $firstmatches)) {
				$body = $firstmatches[1];

				switch ($wbcerechtcookielinkposition) {
					// keinen Cookie Link einfügen Yootheme
					case 'none':
						break;
					// cookie Link im Footer unten
					case 'footerbottom':
						if (preg_match("/<div id=\"footer-bottom\"[^>]*>(.*?)<\/div>/is", $body, $matches)) {
							$footerBottom = $matches[1];
					
							if (preg_match("/<(ul|ol)[^>]*class=[\"'][^\"']*\\bnav\\b[^\"']*[\"'][^>]*>(.*?)<\/\\1>/is", $footerBottom, $matches)) {
								$match   		   = $matches[0];
								$htmlcontent 	   = $match . $htmlreplace[0];

							}
						}
						break;	
					// cookie Link nach der Haupnavigation im Header	
					case 'navbar':
						if(preg_match("/<nav id=\"navigation-main\"[^>]*>(.*?)<\/nav>/is", $body, $matches)) {
						$navigationMain = $matches[1];
							
							if (preg_match("/<(ul|ol)[^>]*class=[\"'][^\"']*\\bmod-menu\\b[^\"']*[\"'][^>]*>(.*?)<\/\\1>/is", $navigationMain, $matches)) {
								$match   		 	= $matches[0];
								$htmlcontent 		= $match . $htmlreplace[0];
							}
						}
						break;
					
				}
				
			}

			// Ersetzen des alten Body durch den neuen Body
			if (isset($htmlcontent)) {
				$newbody = str_replace($match, $htmlcontent, $body);
				$html = str_replace($firstmatches[1], $newbody, $html);
				$app->setBody($html);
			}
		}

		return true;
    }
	function searchJS($html) {
		$pattern = '/<script\b[^>]*>(.*?)<\/script>/is';
		preg_match_all($pattern, $html, $matches);
		return $matches;
	}
}
?>