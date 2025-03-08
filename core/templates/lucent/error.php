<?php

// No Direct Access
defined('_HZEXEC_') or die();

// Get browser info to set some classes
$browser = new \Hubzero\Browser\Detector();
$cls = array(
	'no-js',
	$browser->name(),
	$browser->name() . $browser->major(),
	$this->direction
);

$code = (is_numeric($this->error->getCode()) && $this->error->getCode() > 100 ? $this->error->getCode() : 500);

Lang::load('tpl_' . $this->template) ||
Lang::load('tpl_' . $this->template, __DIR__);
?>
<!DOCTYPE html>
<html dir="<?php echo $this->direction; ?>" lang="<?php echo $this->language; ?>" class="<?php echo implode(' ', $cls); ?>">
	<head>
		<meta name="viewport" content="width=device-width" />
		<title><?php echo Config::get('sitename') . ' - ' . $code; ?></title>

		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/less/main.css" />
		<script type="text/javascript" src="<?php echo \Html::asset('script', 'jquery.js', false, true, true); ?>"></script>
		<script type="text/javascript" src="<?php echo str_replace('/core', '', $this->baseurl); ?>/templates/<?php echo $this->template; ?>/js/core.js"></script>
		<script type="text/javascript" src="<?php echo str_replace('/core', '', $this->baseurl); ?>/templates/<?php echo $this->template; ?>/js/hub.js"></script>

		<!--[if lt IE 9]><script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/js/html5.js"></script><![endif]-->
	</head>
	<body>
		<a href="#maincontent" class="vh">Skip to main content</a>

		<section class="hub-top">
			<?php echo Module::position('notices'); ?>
		</section>

		<div class="wrap">

			<!-- Header -->
			<div class="page-head">
				<div class="inner">
					<header>
						<div class="inner">
							<div class="logo">
								<a href="<?php echo Request::root(); ?>" title="<?php echo Config::get('sitename'); ?>">
									Lucent
								</a>
							</div>
	
							<nav class="nav">
								<nav aria-label="Site main navigation" class="main-nav">
									<?php echo Module::position('user3'); ?>
								</nav>

								<nav class="subnav">
									<ul>
										<li><a class="search-trigger" href="/search" role="button"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 244.487 244.487"><path d="M241.877 230.131l-72.929-72.929c13.973-16.67 22.465-38.004 22.465-61.061 0-52.204-43.503-95.707-95.707-95.707S0 43.938 0 96.142s43.503 95.707 95.707 95.707c22.621 0 43.573-8.179 60.104-21.682l72.145 72.145c1.74 1.74 3.48 1.74 6.96 1.74s5.22 0 6.96-1.74c3.482-3.48 3.482-8.7.001-12.181zm-146.17-55.683c-43.503 0-78.305-34.802-78.305-78.305s34.802-78.305 78.305-78.305 78.305 34.802 78.305 78.305-34.802 78.305-78.305 78.305z"/></svg><span class="vh">Search</span></a></li>
										<?php if (!User::isGuest()) { ?>
											<li>
												<div class="menu-button-links user-account">
													<button type="button" class="" id="menubutton" aria-haspopup="true" aria-controls="menu2">
													<span class="user-image">
														<img src="<?php echo User::picture(); ?>" alt="<?php echo User::get('name'); ?>" />
													</span>
													</button>

													<ul id="menu2" role="menu" aria-labelledby="menubutton" class="account-details">
														<li role="none">
															<a role="menuitem" class="user-name" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id')); ?>" class="cf"><?php echo stripslashes(User::get('name')) . ' (' . stripslashes(User::get('username')) . ')'; ?><br>
																<span><?php echo User::get('email'); ?></span></a>
														</li>
														<li role="none" class="sub" id="account-dashboard">
															<a role="menuitem" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=dashboard'); ?>"><span><?php echo Lang::txt('TPL_ACCOUNT_DASHBOARD'); ?></span></a>
														</li>
														<li role="none" class="sub" id="account-profile">
															<a role="menuitem" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=profile'); ?>"><span><?php echo Lang::txt('TPL_ACCOUNT_PROFILE'); ?></span></a>
														</li>
														<li role="none" class="sub" id="account-logout">
															<a role="menuitem" href="<?php echo Route::url('index.php?option=com_users&view=logout'); ?>"><span><?php echo Lang::txt('TPL_LOGOUT'); ?></span></a>
														</li>
													</ul>
												</div>
											</li>
										<?php } else { ?>
											<li>
												<a href="<?php echo Route::url('index.php?option=com_users&view=login'); ?>" title="<?php echo Lang::txt('TPL_LOGIN'); ?>" class="user-account-link loggedout"><?php echo Lang::txt('TPL_LOGIN'); ?></a>
											</li>
										<?php } ?>
										<?php if (false && $this->countModules('helppane')) : ?>
											<li class="subnav-helpme helpme">
												<a href="<?php echo Route::url('index.php?option=com_support'); ?>" title="<?php echo Lang::txt('Help'); ?>"><!-- set module REPORTPROBLEMS parameter to have it work with .helpme -->
													<span><?php echo Lang::txt('Help'); ?></span>
												</a>
											</li>
										<?php endif; ?>
									</ul>
								</nav>
							</nav>

							<nav class="mobile-menu">
								<button><span>Menu</span></button>
							</nav>
						</div>
					</header>
				</div>
			</div>

			<main id="maincontent" class="page">
				<div class="inner">
	
					<div class="contentpane item-page">
						<div class="content-header">
							<h2>Error (<?php echo $code; ?>)</h2>
						</div><!-- / .content-header -->
	
	
	
	
						<div class="contentpaneopen">
	
							<p class="error"><?php
								if ($this->debug)
								{
									$message = $this->error->getMessage();
								}
								else
								{
									switch ($this->error->getCode())
									{
										case 404:
											$message = Lang::txt('TPL_404_HEADER');
											break;
										case 403:
											$message = Lang::txt('TPL_403_HEADER');
											break;
										case 500:
										default:
											$message = Lang::txt('TPL_500_HEADER');
											break;
									}
								}
								echo $message;
								?></p>
	
						</div>
					</div>
	
				</div><!-- / .inner -->
			</main>
	
	
			<?php if ($this->debug) { ?>
				<div class="backtrace-wrap">
					<?php echo $this->renderBacktrace(); ?>
				</div>
			<?php } ?>
		</div>

		<div class="hub-overlay"></div>
		<div id="big-search" class="template-panel">
			<div class="inner">
				<?php echo Module::position('search'); ?>
			</div>
			<button class="close">
				<span>close search</span>
			</button>
		</div>

		<?php echo Module::position('endpage'); ?>
	</body>
</html>
