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
										<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 141.6 40.44"><defs><style>.qubes-1{fill:#8dc63f;}.qubes-2{fill:#7fb539;}.qubes-3{fill:#6d9d31;}.qubes-4,.qubes-6{fill:#424143;}.qubes-5{fill:url(#linear-gradient);}.qubes-6{font-size:8px;font-family:Montserrat-Regular, Montserrat;}.qubes-7{letter-spacing:-0.01em;}.qubes-8{letter-spacing:-0.02em;}.qubes-9{letter-spacing:-0.01em;}.qubes-10{letter-spacing:0.01em;}</style><linearGradient id="linear-gradient" x1="49.27" y1="24.38" x2="141.6" y2="24.38" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#8dc63f"/><stop offset="1" stop-color="#fff"/></linearGradient></defs><path class="qubes-1" d="M596,494c0,.14-.07.27-.12.41-.75,2.22-2.55,5.34-6.83,8.09l7.31,4.23v10.65L589,521.6c4.29,2.75,6.09,5.86,6.83,8.09.07.2.12.39.17.58A20.19,20.19,0,0,0,596,494Zm-15.73-.1c1,2.89,3.38,5.47,6.87,7.49,3.49-2,5.86-4.6,6.87-7.49.08-.23.14-.45.2-.67a19.87,19.87,0,0,0-14.14,0c.06.22.12.43.2.66Z" transform="translate(-566.9 -491.92)"/><path class="qubes-1" d="M579.82,516.25l7.29,4.21,7.29-4.21v-8.42l-7.29-4.21-7.29,4.21" transform="translate(-566.9 -491.92)"/><path class="qubes-1" d="M578.37,529.69c.75-2.23,2.55-5.34,6.83-8.09l-7.32-4.23V506.71l7.33-4.23c-4.29-2.75-6.09-5.86-6.83-8.09,0-.14-.08-.27-.12-.4a20.19,20.19,0,0,0-.06,36.28c0-.19.1-.38.17-.58Zm15.61.5c-1-2.88-3.38-5.46-6.87-7.49h0c-3.49,2-5.86,4.61-6.87,7.49-.1.3-.18.58-.25.85a20.05,20.05,0,0,0,7.12,1.32,20.27,20.27,0,0,0,7.12-1.31c-.07-.27-.15-.56-.25-.85Z" transform="translate(-566.9 -491.92)"/><path class="qubes-2" d="M587.11,503.58l-7.33,4.23,7.33,4.23,7.34-4.23" transform="translate(-566.9 -491.92)"/><path class="qubes-1" d="M579.78,516.28l7.33,4.23V512l-7.33-4.23" transform="translate(-566.9 -491.92)"/><path class="qubes-3" d="M594.45,516.28v-8.47L587.11,512v8.47" transform="translate(-566.9 -491.92)"/><path class="qubes-4" d="M615.12,504.4a7.87,7.87,0,1,1,15.74,0,8.05,8.05,0,0,1-2.05,5.54l1.27,1.43-1.87,1.57-1.34-1.5a8.23,8.23,0,0,1-3.87.93,7.64,7.64,0,0,1-7.88-8Zm10,5.1-1.91-2.16,1.89-1.57,1.92,2.16a6,6,0,0,0,1-3.53c0-3.18-2-5.56-5.1-5.56s-5.1,2.38-5.1,5.56,2,5.57,5.1,5.57a5.08,5.08,0,0,0,2.17-.47Zm8.29-3.5v-9.31h2.75v9.24c0,2.44,1.36,4,3.92,4s3.93-1.59,3.93-4v-9.24h2.74V506c0,3.81-2.19,6.38-6.67,6.38s-6.67-2.57-6.67-6.38Zm16.62,6.09v-15.4h7.58c2.83,0,4.38,1.76,4.38,3.93a3.47,3.47,0,0,1-2.68,3.53,3.75,3.75,0,0,1,3,3.76c0,2.4-1.57,4.18-4.48,4.18h-7.78Zm9.19-11a1.94,1.94,0,0,0-2.14-2h-4.34v4h4.34a1.92,1.92,0,0,0,2.14-2Zm.3,6.51a2.12,2.12,0,0,0-2.33-2.14h-4.46v4.29h4.46a2.06,2.06,0,0,0,2.33-2.14Zm5.5,4.53v-15.4h10.55v2.38h-7.85v4h7.68v2.38h-7.68v4.29h7.85v2.38Zm12.33-2.17,1.52-2.1a6.61,6.61,0,0,0,4.87,2.14c2.22,0,3.07-1.08,3.07-2.12,0-3.23-9-1.22-9-6.88,0-2.56,2.21-4.52,5.6-4.52a8,8,0,0,1,5.75,2.17l-1.52,2a6.29,6.29,0,0,0-4.48-1.77c-1.57,0-2.59.78-2.59,1.92,0,2.88,9,1.1,9,6.83,0,2.56-1.82,4.78-6,4.78a8.41,8.41,0,0,1-6.27-2.45Z" transform="translate(-566.9 -491.92)"/><rect class="qubes-5" x="49.27" y="23.31" width="92.33" height="2.14"/><text class="qubes-6" transform="translate(61.2 35.61)">A BioQUE<tspan class="qubes-7" x="39.43" y="0">S</tspan><tspan x="44.27" y="0">T </tspan><tspan class="qubes-8" x="50.96" y="0">P</tspan><tspan class="qubes-9" x="56.52" y="0">r</tspan><tspan x="59.62" y="0">oje</tspan><tspan class="qubes-10" x="71.66" y="0">c</tspan><tspan x="76.21" y="0">t</tspan></text></svg>
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
