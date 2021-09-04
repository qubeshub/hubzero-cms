<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No Direct Access
defined('_HZEXEC_') or die();

$menu = App::get('menu');
Html::behavior('framework', true);
Html::behavior('modal');

$this->addStylesheet($this->baseurl . '/templates/' . $this->template . '/css/site.css');
$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/vendor/hammer.js');
$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/hub.js?v=' . filemtime(__DIR__ . '/js/hub.js'));
$this->addScript($this->baseurl . '/templates/' . $this->template . '/js/modernizr-custom.js?v=' . filemtime(__DIR__ . '/js/modernizr-custom.js'));

$menu = App::get('menu');
$isFrontPage = ($menu->getActive() == $menu->getDefault());

// Index page files only
$pageClass = 'page-' . Request::getVar('option', '');
if ($isFrontPage)
{
	$pageClass = 'page-home';
}

$browser = new \Hubzero\Browser\Detector();
$cls = array(
	$this->direction,
	$browser->name(),
	$browser->name() . $browser->major()
);

// Some crazy stuff to match the current page to the subnav that is supposed to be shown. Basically, a lookup table mapping the component name to a main navigation item
// Then the javascript will get the corresponting subnav and will display it in the template

// Get the URL
$subnavUrl = trim(explode('?', $_SERVER['REQUEST_URI'], 2)[0], '/');
// Get the component
$subnavComponnent = Request::getVar('option', '');

// the lookup table (load it from plugin)
$subnavMap = Event::trigger('system.onSubnavRequest');

$showSubnav = 'none';
if (!empty($subnavMap))
{
	$subnavMap = $subnavMap[0];

	$pages = $subnavMap['url'];
	$components = $subnavMap['com'];

	// Find the page
	// first priority is URL
	if (array_key_exists($subnavUrl, $pages))
	{
		$showSubnav = $pages[$subnavUrl];
	}
	elseif (array_key_exists($subnavComponnent, $components))
	{
		$showSubnav = $components[$subnavComponnent];
	}
}

$this->setTitle(Config::get('sitename') . ' - ' . $this->getTitle());
?>
<!DOCTYPE html>
<html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="<?php echo implode(' ', $cls); ?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<!-- <meta http-equiv="X-UA-Compatible" content="IE=edge" /> Doesn't validate... -->

		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo \Hubzero\Document\Assets::getSystemStylesheet(); ?>" />

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.20.3/TweenMax.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ScrollMagic/2.0.5/ScrollMagic.js"></script>
		<jdoc:include type="head" />

		<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/html5.js"></script><![endif]-->
	</head>
	<body class="<?php echo $pageClass; ?>" data-component="<?php echo Request::getVar('option', ''); ?>" data-subnav="<?php echo $showSubnav; ?>">
		<div class="wrap">
			<div class="content-panel <?php echo $subnavComponnent; ?>">
				<?php
				$headerClass = '';
				if ($menu->getActive() != $menu->getDefault()) {
					$headerClass = 'with-sub';
				}
				?>
				<header class="page <?php echo $headerClass; ?>">
					<div class="wrap-main">
						<a href="#maincontent" class="skip-link">Skip to Main Content</a>
						<div class="main cf">
							<div class="brand">
								<a href="<?php echo Request::root(); ?>" title="<?php echo Config::get('sitename'); ?>">
									<div class="logo">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 141.6 40.44" xmlns:v="https://vecta.io/nano"><defs><linearGradient id="A" x1="49.27" y1="24.38" x2="141.6" y2="24.38" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#8dc63f"/><stop offset="1" stop-color="#fff"/></linearGradient></defs><path d="M29.09 2.07c0 .13-.07.26-.12.4-.75 2.23-2.55 5.34-6.84 8.09l7.32 4.23v10.66l-7.32 4.22c4.28 2.75 6.09 5.87 6.83 8.09a7.64 7.64 0 0 1 .17.59 20.19 20.19 0 0 0 0-36.28zm-15.73-.1c1 2.89 3.37 5.47 6.87 7.5 3.49-2 5.86-4.61 6.86-7.5.08-.23.15-.45.2-.66a20 20 0 0 0-7.07-1.3 20.25 20.25 0 0 0-7.07 1.29 10.21 10.21 0 0 0 .21.67zm-.43 22.36l7.29 4.21 7.29-4.21v-8.42l-7.29-4.21-7.29 4.21m-1.46 21.86c.75-2.23 2.55-5.34 6.83-8.1l-7.31-4.22V14.79l7.32-4.23c-4.28-2.75-6.08-5.87-6.83-8.09-.05-.14-.08-.27-.12-.41a20.18 20.18 0 0 0-.05 36.28c.05-.19.1-.38.17-.58zm15.62.5c-1-2.88-3.38-5.47-6.87-7.49h0c-3.49 2-5.86 4.61-6.87 7.49a8 8 0 0 0-.25.85 19.86 19.86 0 0 0 14.24 0 12.87 12.87 0 0 0-.25-.85z" fill="#8dc63f"/><path d="M20.22 11.65l-7.34 4.24 7.33 4.23 7.34-4.24" fill="#7fb539"/><path d="M12.88 24.35l7.33 4.24v-8.47l-7.33-4.23" fill="#8dc63f"/><path d="M27.55 24.35v-8.47l-7.34 4.24v8.47" fill="#6d9d31"/><path d="M48.23 12.48a7.87 7.87 0 1 1 15.74 0 8.09 8.09 0 0 1-2.05 5.54l1.27 1.43-1.87 1.57-1.34-1.5a8.23 8.23 0 0 1-3.88.92 7.63 7.63 0 0 1-7.87-8zm10 5.1l-1.92-2.17 1.9-1.57 1.91 2.17a6.08 6.08 0 0 0 1-3.53c0-3.19-2-5.57-5.1-5.57s-5.1 2.38-5.1 5.57 2 5.56 5.1 5.56a5.08 5.08 0 0 0 2.17-.46zm8.29-3.51v-9.3h2.75v9.2c0 2.45 1.36 4 3.92 4s3.92-1.59 3.92-4v-9.2h2.77v9.3c0 3.81-2.19 6.37-6.67 6.37s-6.67-2.56-6.67-6.37zm16.62 6.09V4.77h7.57c2.84 0 4.39 1.75 4.39 3.92a3.48 3.48 0 0 1-2.68 3.53 3.77 3.77 0 0 1 3 3.77c0 2.4-1.57 4.18-4.48 4.18h-7.76zm9.19-11a2 2 0 0 0-2.15-2h-4.3v4h4.34a1.93 1.93 0 0 0 2.15-2zm.3 6.51a2.13 2.13 0 0 0-2.33-2.15h-4.42v4.3h4.46a2.07 2.07 0 0 0 2.33-2.15zm5.5 4.52V4.77h10.54v2.37h-7.79v4h7.68v2.38h-7.68v4.29h7.84v2.38zm12.36-2.22l1.53-2.1a6.62 6.62 0 0 0 4.87 2.15c2.21 0 3.07-1.09 3.07-2.13 0-3.23-9-1.22-9-6.87 0-2.57 2.22-4.53 5.61-4.53a8 8 0 0 1 5.75 2.17l-1.52 2a6.32 6.32 0 0 0-4.48-1.78c-1.57 0-2.59.79-2.59 1.92 0 2.88 9 1.11 9 6.83 0 2.57-1.83 4.78-6 4.78a8.39 8.39 0 0 1-6.28-2.45z" fill="#424143"/><path d="M49.27 23.31h92.33v2.14H49.27z" fill="url(#A)"/><path d="M54.25 33.97h-3.51l-.76 1.69h-.69l2.88-6.3h.71l2.88 6.3h-.7zm-.24-.54l-1.52-3.46-1.51 3.38zm10.37.54c0 1.08-.8 1.69-2.37 1.69h-2.9v-6.3h2.77c1.4 0 2.21.59 2.21 1.62a1.47 1.47 0 0 1-1 1.43 1.5 1.5 0 0 1 1.29 1.56zm-4.61-4.07v2.29h2c1 0 1.6-.38 1.6-1.14s-.59-1.15-1.6-1.15zm3.94 4c0-.82-.6-1.18-1.71-1.18h-2.23v2.37H62c1.11-.03 1.71-.39 1.71-1.22zm1.9-4.52a.47.47 0 0 1 .47-.45.46.46 0 0 1 .47.44.47.47 0 1 1-.94 0zm.15 1.49h.64v4.74h-.64zm1.94 2.37a2.41 2.41 0 1 1 2.42 2.41 2.33 2.33 0 0 1-2.42-2.41zm4.17 0a1.76 1.76 0 1 0-1.75 1.85 1.73 1.73 0 0 0 1.76-1.85zm8.43 2.9a1.91 1.91 0 0 1-1.53.72 2.93 2.93 0 0 1-2.17-1.2 3.25 3.25 0 1 1 .82-.06 1.9 1.9 0 0 0 1.36.73 1.5 1.5 0 0 0 1.2-.58zm-3.59-1.07a2.61 2.61 0 1 0-2.65-2.61 2.56 2.56 0 0 0 2.65 2.61zm4.75-2.1v-3.61h.66v3.58c0 1.49.7 2.18 1.93 2.18s1.93-.69 1.93-2.18v-3.58h.65v3.61a2.59 2.59 0 1 1-5.17 0zm11.63 2.06v.58h-4.45v-6.3h4.32v.57H89.3v2.24h3.26v.57H89.3v2.34zm.96-.19l.27-.51a3.09 3.09 0 0 0 2.07.76c1.18 0 1.7-.49 1.7-1.11 0-1.73-3.88-.67-3.88-3 0-.93.72-1.73 2.32-1.73a3.55 3.55 0 0 1 2 .56l-.22.53a3.25 3.25 0 0 0-1.73-.52c-1.15 0-1.67.51-1.67 1.14 0 1.73 3.88.69 3.88 3 0 .93-.73 1.72-2.35 1.72a3.41 3.41 0 0 1-2.39-.84zm7.28-4.96h-2.22v-.57h5.1v.57h-2.22v5.73h-.66zm11.23 1.59c0 1.34-1 2.15-2.57 2.15h-1.7v2h-.66v-6.3h2.36c1.6-.01 2.57.8 2.57 2.15zm-.67 0c0-1-.66-1.59-1.92-1.59h-1.68v3.15h1.68c1.26 0 1.91-.57 1.91-1.56zm4.24-.63v.62h-.15a1.53 1.53 0 0 0-1.59 1.75v2.41h-.64v-4.75h.61v.93a1.78 1.78 0 0 1 1.77-.96zm.63 2.4a2.41 2.41 0 1 1 2.41 2.41 2.33 2.33 0 0 1-2.41-2.41zm4.17 0a1.76 1.76 0 1 0-1.76 1.85 1.73 1.73 0 0 0 1.76-1.85zm.25 3.85l.23-.48a1.09 1.09 0 0 0 .73.24c.51 0 .79-.31.79-.89v-5.09h.64v5.1a1.3 1.3 0 0 1-1.39 1.44 1.53 1.53 0 0 1-1-.32zm1.6-7.71a.47.47 0 0 1 .47-.45.46.46 0 0 1 .47.44.47.47 0 1 1-.94 0zm6.69 4.06h-4a1.75 1.75 0 0 0 1.86 1.65 1.85 1.85 0 0 0 1.45-.63l.36.42a2.5 2.5 0 0 1-4.31-1.64 2.28 2.28 0 0 1 2.31-2.4 2.25 2.25 0 0 1 2.3 2.4c.04.05.03.13.03.2zm-4-.48h3.41a1.68 1.68 0 1 0-3.36 0zm4.84.28a2.33 2.33 0 0 1 2.44-2.4 2.06 2.06 0 0 1 1.85.93l-.48.33a1.59 1.59 0 0 0-1.37-.71 1.85 1.85 0 1 0 0 3.7 1.58 1.58 0 0 0 1.37-.7l.48.33a2.08 2.08 0 0 1-1.85.93 2.33 2.33 0 0 1-2.44-2.41zm8.09 2.08a1.49 1.49 0 0 1-1 .33 1.25 1.25 0 0 1-1.39-1.37v-2.87h-.84v-.54h.84v-1h.64v1h1.44v.54h-1.44v2.83a.76.76 0 0 0 .82.86 1.06 1.06 0 0 0 .7-.24z" fill="#424143"/></svg>
									</div>
								</a>
							</div>
							<nav class="nav-primary" aria-label="main navigation">
								<jdoc:include type="modules" name="mainmenu" />
							</nav>
							<button class="mobile-menu">
								<div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><style>.st2{fill:#999999;}</style><path class="st2" d="M46.7 25.3L32.3 39.8 17.8 25.3c-.8-.8-2-.8-2.8 0-.8.8-.8 2 0 2.8L30.8 44c.2.2.6.4.9.5.2 0 .3.1.5.1.5 0 1-.2 1.4-.6l15.9-15.9c.8-.8.8-2 0-2.8s-2-.8-2.8 0z"/><path class="st2" d="M32 0C14.4 0 0 14.4 0 32s14.4 32 32 32 32-14.4 32-32S49.6 0 32 0zm0 60C16.6 60 4 47.4 4 32S16.6 4 32 4s28 12.6 28 28-12.6 28-28 28z"/></svg></div>
								<span>&nbsp;</span>
							</button>

							<div class="aux">
								<nav class="nav-secondary" aria-label="secondary menu">
									<jdoc:include type="modules" name="secondarymenu" />
									<ul>
									  <li id="help" class="helpme">
											<a href="<?php echo Route::url('index.php?option=com_support'); ?>" title="<?php echo Lang::txt('Need help? Send a trouble report to our support team.'); ?>">
												<span><?php echo Lang::txt('Help'); ?></span>
											</a>
										</li>
									</ul>
								</nav>
								<nav class="buttons">
									<button class="fullscreen-search" aria-label="search" aria-haspopup="true" aria-expanded="false">
										<a>
											<div class="icon">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 475.1 475.1"><path d="M464.5 412.8l-97.9-97.9c23.6-34.1 35.4-72 35.4-113.9 0-27.2-5.3-53.2-15.9-78.1-10.6-24.8-24.8-46.3-42.8-64.2s-39.4-32.3-64.2-42.8C254.2 5.3 228.2 0 201 0c-27.2 0-53.2 5.3-78.1 15.8-24.8 10.6-46.2 24.9-64.2 42.9s-32.3 39.4-42.8 64.2C5.3 147.8 0 173.8 0 201c0 27.2 5.3 53.2 15.8 78.1 10.6 24.8 24.8 46.2 42.8 64.2 18 18 39.4 32.3 64.2 42.8 24.8 10.6 50.9 15.8 78.1 15.8 41.9 0 79.9-11.8 113.9-35.4l97.9 97.6c6.9 7.2 15.4 10.8 25.7 10.8 9.9 0 18.5-3.6 25.7-10.8 7.2-7.2 10.8-15.8 10.8-25.7.2-9.9-3.3-18.5-10.4-25.6zM291.4 291.4c-25 25-55.1 37.5-90.4 37.5-35.2 0-65.3-12.5-90.4-37.5-25-25-37.5-55.1-37.5-90.4 0-35.2 12.5-65.3 37.5-90.4 25-25 55.1-37.5 90.4-37.5 35.2 0 65.3 12.5 90.4 37.5 25 25 37.5 55.1 37.5 90.4 0 35.2-12.5 65.3-37.5 90.4z"/></svg>
											</div>
											&nbsp;
										</a>
									</button>
									<?php if (!User::isGuest()) : ?>
										<div class="dashboard loggedin">
											<a href="/login">
												<img src="<?php echo User::picture(); ?>" alt="<?php echo User::get('name'); ?>" class="profile-pic thumb" />
											</a>
										</div>
									<?php else : ?>
										<div class="dashboard">
											<a href="#">
												<div class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 438.5 438.5"><path d="M414.4 60.7c-16.1-16.1-35.4-24.1-58.1-24.1H265c-2.5 0-4.4.6-5.9 1.9-1.4 1.2-2.4 3.1-2.9 5.6-.5 2.5-.8 4.7-.9 6.7-.1 2-.1 4.5.1 7.6.2 3 .3 4.9.3 5.7.6 1.5.8 2.8.6 3.9-.2 1 .5 1.9 2 2.6 1.5.7 2.3 1.2 2.3 1.6s1.1.7 3.3.9l3.3.3h89c12.6 0 23.3 4.5 32.3 13.4 9 8.9 13.4 19.7 13.4 32.3v201c0 12.6-4.5 23.3-13.4 32.3-8.9 8.9-19.7 13.4-32.3 13.4H265c-2.5 0-4.4.6-5.9 1.9-1.4 1.2-2.4 3.1-2.9 5.6-.5 2.5-.8 4.7-.9 6.7-.1 2-.1 4.5.1 7.6.2 3 .3 4.9.3 5.7 0 2.5.9 4.6 2.7 6.4 1.8 1.8 3.9 2.7 6.4 2.7h91.4c22.6 0 42-8 58.1-24.1s24.1-35.4 24.1-58.1v-201c.1-23.1-7.9-42.4-24-58.5z"/><path d="M338 219.3c0-4.9-1.8-9.2-5.4-12.9L177.3 51.1c-3.6-3.6-7.9-5.4-12.8-5.4s-9.2 1.8-12.9 5.4c-3.6 3.6-5.4 7.9-5.4 12.9v82.2H18.3c-5 0-9.2 1.8-12.9 5.4-3.6 3.6-5.4 7.9-5.4 12.9v109.6c0 4.9 1.8 9.2 5.4 12.8 3.6 3.6 7.9 5.4 12.9 5.4h127.9v82.2c0 4.9 1.8 9.2 5.4 12.8 3.6 3.6 7.9 5.4 12.9 5.4 4.9 0 9.2-1.8 12.8-5.4L332.6 232c3.6-3.5 5.4-7.8 5.4-12.7z"/></svg>
												</div>
												<span>Login</span>
											</a>
										</div>
									<?php endif; ?>
								</nav>
							</div>
							<button class="search" title="Search">
								<div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 475.1 475.1"><path d="M464.5 412.8l-97.9-97.9c23.6-34.1 35.4-72 35.4-113.9 0-27.2-5.3-53.2-15.9-78.1-10.6-24.8-24.8-46.3-42.8-64.2s-39.4-32.3-64.2-42.8C254.2 5.3 228.2 0 201 0c-27.2 0-53.2 5.3-78.1 15.8-24.8 10.6-46.2 24.9-64.2 42.9s-32.3 39.4-42.8 64.2C5.3 147.8 0 173.8 0 201c0 27.2 5.3 53.2 15.8 78.1 10.6 24.8 24.8 46.2 42.8 64.2 18 18 39.4 32.3 64.2 42.8 24.8 10.6 50.9 15.8 78.1 15.8 41.9 0 79.9-11.8 113.9-35.4l97.9 97.6c6.9 7.2 15.4 10.8 25.7 10.8 9.9 0 18.5-3.6 25.7-10.8 7.2-7.2 10.8-15.8 10.8-25.7.2-9.9-3.3-18.5-10.4-25.6zM291.4 291.4c-25 25-55.1 37.5-90.4 37.5-35.2 0-65.3-12.5-90.4-37.5-25-25-37.5-55.1-37.5-90.4 0-35.2 12.5-65.3 37.5-90.4 25-25 55.1-37.5 90.4-37.5 35.2 0 65.3 12.5 90.4 37.5 25 25 37.5 55.1 37.5 90.4 0 35.2-12.5 65.3-37.5 90.4z"></path></svg></div>
								<span>&nbsp;</span>
							</button>
							<button class="dashboard <?php if(!User::isGuest()) { echo ' loggedin'; } ?>" title="Dashboard">
								<div><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 438.5 438.5"><path d="M414.4 60.7c-16.1-16.1-35.4-24.1-58.1-24.1H265c-2.5 0-4.4.6-5.9 1.9-1.4 1.2-2.4 3.1-2.9 5.6-.5 2.5-.8 4.7-.9 6.7-.1 2-.1 4.5.1 7.6.2 3 .3 4.9.3 5.7.6 1.5.8 2.8.6 3.9-.2 1 .5 1.9 2 2.6 1.5.7 2.3 1.2 2.3 1.6s1.1.7 3.3.9l3.3.3h89c12.6 0 23.3 4.5 32.3 13.4 9 8.9 13.4 19.7 13.4 32.3v201c0 12.6-4.5 23.3-13.4 32.3-8.9 8.9-19.7 13.4-32.3 13.4H265c-2.5 0-4.4.6-5.9 1.9-1.4 1.2-2.4 3.1-2.9 5.6-.5 2.5-.8 4.7-.9 6.7-.1 2-.1 4.5.1 7.6.2 3 .3 4.9.3 5.7 0 2.5.9 4.6 2.7 6.4 1.8 1.8 3.9 2.7 6.4 2.7h91.4c22.6 0 42-8 58.1-24.1s24.1-35.4 24.1-58.1v-201c.1-23.1-7.9-42.4-24-58.5z"/><path d="M338 219.3c0-4.9-1.8-9.2-5.4-12.9L177.3 51.1c-3.6-3.6-7.9-5.4-12.8-5.4s-9.2 1.8-12.9 5.4c-3.6 3.6-5.4 7.9-5.4 12.9v82.2H18.3c-5 0-9.2 1.8-12.9 5.4-3.6 3.6-5.4 7.9-5.4 12.9v109.6c0 4.9 1.8 9.2 5.4 12.8 3.6 3.6 7.9 5.4 12.9 5.4h127.9v82.2c0 4.9 1.8 9.2 5.4 12.8 3.6 3.6 7.9 5.4 12.9 5.4 4.9 0 9.2-1.8 12.8-5.4L332.6 232c3.6-3.5 5.4-7.8 5.4-12.7z"/></svg></div>
								<span>&nbsp;</span>
							</button>
						</div>
						<jdoc:include type="modules" name="notices" />
						<jdoc:include type="modules" name="helppane" />
						<div class="search-panel">
							<div class="text-field">
								<jdoc:include type="modules" name="search" />
								<button class="close" aria-label="close search">Close</button>
							</div>
						</div>
					</div>
					<?php if ($menu->getActive() != $menu->getDefault()) { ?>
					<div class="sub">
						<nav aria-label="submenu"></nav>
						<div class="breadcrumbs-wrap">
							<div class="breadcrumbs">
								<div class="wrap">
									<div class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 425.963 425.963"><path d="M213.285 0h-.608c-73.563 0-133.41 59.826-133.41 133.36 0 48.203 21.953 111.818 65.247 189.082 32.098 57.28 64.646 101.152 64.972 101.588.906 1.217 2.334 1.934 3.847 1.934.043 0 .087 0 .13-.002 1.56-.043 3.002-.842 3.868-2.143.322-.487 32.638-49.288 64.518-108.977 43.03-80.563 64.848-141.624 64.848-181.482C346.693 59.826 286.846 0 213.286 0zm61.58 136.62c0 34.124-27.76 61.884-61.885 61.884-34.123 0-61.884-27.76-61.884-61.884s27.76-61.884 61.884-61.884c34.124 0 61.885 27.76 61.885 61.884z"/></svg>
									</div>

									<div class="crumbs-wrap">
										<jdoc:include type="modules" name="breadcrumbs" />
									</div>
								</div>
							</div>
						</div>
						<jdoc:include type="message" />
					</div>
					<?php } else { ?>
						<jdoc:include type="message" />
					<?php } ?>
				</header>

				<div class="page-body">

					<main id="maincontent" class="content">

						<?php if ($this->countModules('left or right')) : ?>
						<div class="inner withmenu">
							<section class="main section">
								<?php endif; ?>

								<?php if ($this->countModules('left')) : ?>
									<aside class="aside">
										<jdoc:include type="modules" name="left" />
									</aside><!-- / .aside -->
								<?php endif; ?>

								<?php if ($this->countModules('left or right')) : ?>
								<div class="subject">
									<?php endif; ?>

									<!-- start component output -->
									<jdoc:include type="component" />
									<!-- end component output -->

									<?php if ($this->countModules('left or right')) : ?>
								</div><!-- / .subject -->
							<?php endif; ?>

								<?php if ($this->countModules('right')) : ?>
									<aside class="aside">
										<jdoc:include type="modules" name="right" />
									</aside><!-- / .aside -->
								<?php endif; ?>

								<?php if ($this->countModules('left or right')) : ?>
							</section><!-- / .main section -->
						</div>
						<?php endif; ?>

					</main>
				</div>

				<footer class="global">
					<?php $errorPage = false; include "footer.php"; ?>
				</footer>
			</div>
			<div class="dashboard-panel" id="dashboard-panel">
				<div class="dashboard-panel-inner">
					<div class="dashboard-panel-content">
						<!-- <header><h2>Dashboard</h2></header> -->
						<div class="scroller">
							<?php if (!User::isGuest()) { ?>
								<section class="user">
									<div class="user-info">
										<a href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id')); ?>">
											<?php echo stripslashes(User::get('name')); ?>
											<span><?php echo User::get('email'); ?></span>
										</a>
									</div>
									<!-- <header><h2>All Categories</h2></header> -->
									<nav class="user-nav" aria-label="user navigation">
										<ul>
											<li id="account-profile">
												<a href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=profile'); ?>"><span class="nav-icon-user"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/user.svg") ?></span><span><?php echo Lang::txt('TPL_BMC_ACCOUNT_PROFILE'); ?></span></a>
											</li>
											<li id="account-dashboard">
												<a href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=dashboard'); ?>"><span class="nav-icon-dashboard"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/th-large.svg") ?></span><span><?php echo Lang::txt('TPL_BMC_ACCOUNT_DASHBOARD'); ?></span></a>
											</li>
											<jdoc:include type="modules" name="minidash" />
											<li id="account-logout">
												<a href="<?php echo Route::url('index.php?option=com_users&view=logout'); ?>"><span class="nav-icon-logout"><?php echo file_get_contents(PATH_CORE . DS . "assets/icons/signout.svg") ?></span><span><?php echo Lang::txt('TPL_BMC_LOGOUT'); ?></span></a>
											</li>
										</ul>
									</nav>
								</section>
							<?php } ?>
						</div>
					</div>
					<a href="#" class="close">Close</a>
				</div>
			</div>
		</div>

		<div class="mobile-panel">
			<div class="inner scroller">
				<div class="subpanel menu">
					<div class="inner">
						<h4><a style="color: #8cc540;" href="/">Home</a></h4>
						<jdoc:include type="modules" name="mainmenu" />
						<jdoc:include type="modules" name="secondarymenu" />
					</div>
				</div>
				<div class="subpanel search">
					<div class="inner">
						<div class="label">Search</div>
						<jdoc:include type="modules" name="search" />
					</div>
				</div>
				<div class="background">
					<div class="panel1"></div>
					<div class="panel2"></div>
					<div class="panel3"></div>
					<div class="panel4"></div>
					<div class="panel5"></div>
					<div class="panel6"></div>
					<div class="panel7"></div>
					<div class="panel8"></div>
				</div>
			</div>
			<div class="close"></div>
		</div>

		<jdoc:include type="modules" name="endpage" />
	</body>
</html>
