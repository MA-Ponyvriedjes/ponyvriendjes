<?php
	/**
	 * @version		$Id: index.php WaseemSadiq $
	 * @package		Joomla
	 * @subpackage	Templates / basic skeleton template
	 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
	 * @license		GNU/GPL, see LICENSE.php
	 * Joomla! is free software. This version may have been modified pursuant to the
	 * GNU General Public License, and as distributed it includes or is derivative
	 * of works licensed under the GNU General Public License or other free or open
	 * source software licenses. See COPYRIGHT.php for copyright notices and
	 * details.
	 */

	// no direct access
	defined('_JEXEC') or die('Restricted access');

	include_once("includes/template_config.php");
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="<?php echo $this->language; ?>"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="<?php echo $this->language; ?>"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="<?php echo $this->language; ?>"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"> <!--<![endif]-->

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="mssmarttagspreventparsing" content="true" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<!--<meta name="viewport" content="target-densitydpi=device-dpi" /> -->

	<!-- CMS FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
	
	<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/less/install.less" rel="stylesheet/less" type="text/css" media="screen" />	
	<?php if($this->params->get('stylesheet')=='css'): ?>	
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/install.css" rel="stylesheet" type="text/css" media="screen" />	
	<?endif;?>

	<jdoc:include type="head" />
	<!--[if IE 7]>
		<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/ie_7.css" media="screen, projection" />
	<![endif]-->
	
	<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	
	<script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/libs/less-1.3.3.min.js" type="text/javascript"></script>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/libs/jquery-1.9.1.min.js"></script>
	<?php if ($this->params->get('caroufredsel') == 1): ?> 
		<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/libs/jquery.carouFredSel-6.0.1-packed.js"></script>
	<?php endif; ?>
	
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/default/cms.js"></script>
		


	<?php if ($user->guest!=1) :?>
		<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/default/inlineedit.js"></script>	
	<?php endif; ?>
	
	
	<!-- LOAD JS FOR THOSE THAT DON'T HAVE A HTML FILE -->
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/mod_menu/cms.js"></script>
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/mod_menu/mainmenu.js"></script>

	<!-- LOAD JS FOR TEMPLATES -->
	<script type="text/javascript" src="templates/marvinvk/js/templates/ct-article/intro.js"></script>
	<script type="text/javascript" src="templates/marvinvk/js/templates/ct-article/main.js"></script>

</head>

	<body id="<?php echo $bodyid;?>" class="<?php echo $bodyclass ?>">
		<?php if ($user->guest!=1) :?>
			<div class="mod_menu cms">
				<div class="cms" class="<?php if($startOfSession==true){ echo 'open';} ?>">
					
					<div class="cms-menu-intro">
						<a class="logo-small" href="http://www.marvinvk.nl" target="_blank"></a>
						<h1>Content Management System</h1>
						<p class="welcome">Welkom <?php $user =& JFactory::getUser(); echo $user->name;?> hier kunt u de content aanpassen.</p>
					</div>
					<div class="redirect-path-cms" style="display:none;"><?php echo $this->baseurl ?>/index.php</div>

					<?php if ($this->countModules('cms-module')) : ?>	
						<div id="cms-module">
							<div class="container">
								<jdoc:include type="modules" name="cms-module" style="<?php echo $this->params->get('cms-module-style') ;?>" />					
							</div>
						</div>
					<?php endif?>

				</div>
			</div>

		<?php endif;?> 
		
		<div id="page-holder">

			<?php if ($this->countModules('header-module')) : ?>	
				<header id="header-modules">
					<div class="container">
						<jdoc:include type="modules" name="header-module" style="<?php echo $this->params->get('header-module-style') ;?>" />					
					</div>
				</header>
			<?php endif?>

			<?php if ($this->countModules('top-module')) : ?>
				<div id="top-modules">
					<jdoc:include type="modules" name="top-module" style="<?php echo $this->params->get('top-module-style') ;?>" />	
				</div>
			<?php endif ?>

			<div id="middle-modules">
				<div class="container">
					
					<?php if ($this->countModules('left-module')) : ?>
						<div id="left-modules">
							<div class="container">
								<jdoc:include type="modules" name="left-module" style="<?php echo $this->params->get('left-module-style') ;?>" />	
							</div>
						</div>	
					<?php endif?>


					<div id="middle-module">
						
						<?php if ($this->countModules('subtop-module')) : ?>
							<div id="subtop-modules">
								<div class="container">
									<jdoc:include type="modules" name="subtop-module" style="<?php echo $this->params->get('subtop-module-style') ;?>" />	
								</div>
							</div>
						<?php endif;?>

						<?php if ($this->getBuffer('message')) : ?>
							<jdoc:include type="message" />
						<?php endif; ?>
											
						<jdoc:include type="component" />

						<?php if ($this->countModules('subbottom-module')) : ?>
							<div id="subbottom-modules">
								<div class="container">
									<jdoc:include type="modules" name="subbottom-module" style="<?php echo $this->params->get('subbottom-module-style') ;?>" />	
								</div>
							</div>
						<?php endif ?>
						
					</div>

					<?php if ($this->countModules('right-module')) : ?>
						<div id="right-modules">
							<div class="container">
								<jdoc:include type="modules" name="right-module" style="<?php echo $this->params->get('right-module-style') ;?>" />	
							</div>
						</div>	
					<?php endif?>
				
				</div><!-- end container -->
			</div><!-- end middle-modules -->

			<?php if ($this->countModules('bottom-module')) : ?>

				<jdoc:include type="modules" name="bottom-module" style="<?php echo $this->params->get('bottom-module-style') ;?>" />	
			
			<?php endif ?>

			<?php if ($this->countModules('footer-module')) : ?>
			<footer id="footer-modules">
				<div class="container">
					<jdoc:include type="modules" name="footer-module" style="<?php echo $this->params->get('footer-module-style') ;?>" />	
				</div>
			</footer>
			<?php endif ?>
			</div>
		</div>

		<?php if ($this->countModules('debug')) : ?>
		<div id="debug"><jdoc:include type="modules" name="debug" /></div>
		<?php endif; ?>

		<div id="login" style="display:none">
			<div id="login-panel">
				<jdoc:include type="modules" name="login-module" style="onewrapper" />
			</div>
		</div>

		<div id="popup-module-holder">
			
			<div id="popup-module">
				<jdoc:include type="modules" name="popup-module" style="inside" />
			</div>

			<?php if(JRequest::getVar('userform')=='success') : ?>
				<a href="#form-popup-module" id="form-succes-popup" ></a>
				<div id="form-popup-module">
					<jdoc:include type="modules" name="form-succes-module" style="raw" />
				</div>
			<?php endif; ?>
		</div>

		<?php if ($this->countModules('fixed-module')) : ?>
			<jdoc:include type="modules" name="fixed-module" style="inside" />
		<?php endif; ?>

		<!--[if IE 6]><script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/javascript/ie6/warning.js"></script><script>window.onload=function(){e("<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/javascript/ie6/")}</script><![endif]-->
		
	</body>
</html>