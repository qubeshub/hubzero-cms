<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();

if ($title = $this->getTitle())
{
	$this->setTitle(Config::get('sitename') . ' - ' . $title);
}
$this->setMetaData('viewport', 'width=device-width, initial-scale=1.0');

$bgColor  = '#ffffff';
$tblColor = '#ffffff';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<style type="text/css">
		<?php /* Client-specific Styles */ ?>
		html {
			background-color: <?php echo $bgColor; ?>;
			margin: 0;
			padding: 0;
			height: 100%;
		}
		body {
			width: 100% !important;
			font-family: 'Helvetica Neue', Helvetica, Verdana, Arial, sans-serif !important;
			background-color: <?php echo $bgColor; ?> !important;
			margin: 0 !important;
			padding: 0 !important;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%;
			font-size: 16px;
			line-height: 1.4em;
			color: #000;
			text-rendering: optimizeLegibility;
			height: 100%;
		}
		<?php /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */ ?>
		<?php /* Force Hotmail to display emails at full width */ ?>
		.ExternalClass {
			width: 100%;
		}
		<?php /* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */?>
		.ExternalClass,
		.ExternalClass p,
		.ExternalClass span,
		.ExternalClass font,
		.ExternalClass td,
		.ExternalClass div {
			line-height: 100%;
		}
		#backgroundTable {
			margin: 0;
			padding: 0;
			width: 100% !important;
			min-width: 100%;
			line-height: 100% !important;
			background-color: <?php echo $bgColor; ?>;
		}
		<?php /* End reset */ ?>

		<?php
		/* Some sensible defaults for images
		1. "-ms-interpolation-mode: bicubic" works to help ie properly resize images in IE. (if you are resizing them using the width and height attributes)
		2. "border:none" removes border when linking images.
		3. Updated the common Gmail/Hotmail image display fix: Gmail and Hotmail unwantedly adds in an extra space below images when using non IE browsers. You may not always want all of your images to be block elements. Apply the "image_fix" class to any image you need to fix.

		Bring inline: Yes.
		*/
		?>
		img {
			outline: none !important;
			text-decoration: none !important;
			-ms-interpolation-mode: bicubic;
		}
		a img {
			border: none;
		}
		.image_fix {
			display: block !important;
		}

		<?php /* Yahoo paragraph fix: removes the proper spacing or the paragraph (p) tag. To correct we set the top/bottom margin to 1em in the head of the document. */ ?>
		p {
			margin: 1em 0;
		}

		<?php /* Remove spacing around Outlook 07, 10 tables */ ?>
		table {
			border-collapse: collapse;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
		}
		table tr {
			border-collapse: collapse;
		}
		<?php /* Outlook 07, 10 Padding issue */ ?>
		table td {
			border-collapse: collapse;
		}
		table.tbl-wrap {
			background-color: <?php echo $tblColor; ?>;
		}
		table.tbl-spacer td div {
			color: <?php echo $tblColor; ?> !important;
			height: 30px !important;
			visibility: hidden;
		}
		table.tbl-header {
			border-bottom: 2px solid #d1d1d1;
		}
		table.tbl-header td.sitename {
			font-size: 1.4em;
			color: #656565; /* #999; */
			padding: 0 10px 5px 0;
			text-align: left;
		}
        table.tbl-header td.sitename.group {
            font-size: 1.2em;
            color: #656565; /* #999; */
			padding: 5px 0 5px 10px;
			text-align: right;
			vertical-align: middle;
        }
		table.tbl-header td.tagline {
			line-height: 1;
			padding: 0 0 5px 10px;
		}
		table.tbl-header span.home {
			font-weight: bold;
			font-size: 0.85em;
			color: #666;
			-webkit-text-size-adjust: none;
		}
		table.tbl-header span.home a {
			color: #666;
			font-weight: bold;
			text-decoration: none;
			border: none;
		}
		table.tbl-header span.description {
			font-size: 0.85em;
			color: #666;
			-webkit-text-size-adjust: none;
		}
		table.tbl-header td.component {
			border-left: 2px solid #d1d1d1;
			font-size: 1.2em;
			color: #656565; /* #999;*/
			padding: 0 0 5px 10px;
			text-align: right;
			vertical-align: bottom;
		}
        table.tbl-header td.component.left {
            border-left: none;
            border-right: 2px solid #d1d1d1;
            padding: 5px 10px 5px 10px;
            text-align: left;
			vertical-align: middle;
        }
		table.tbl-footer {
			border-collapse: collapse;
			border-top: 2px solid #d1d1d1;
		}
		table.tbl-footer.group {
			margin-bottom: -20px;
		}
		table.tbl-footer td {
			line-height: 1;
			padding: 5px 0 0 0;
		}
		table.tbl-footer td span {
			font-size: 0.85em;
			color: #666;
			-webkit-text-size-adjust: none;
		}
		table.tbl-footer table.social table tr td:nth-child(2) {
			padding-left: 5px;
			padding-top: 3px;
		}

		@media only screen and (max-device-width: 480px) {
			body {
				-webkit-text-size-adjust: 100% !important;
				-ms-text-size-adjust: 100% !important;
				font-size: 100% !important;
			}
			table.tbl-wrap,
			table.tbl-wrap td.tbl-body {
				width: auto !important;
				margin: 0 2em !important;
			}
			table.tbl-header td {
				width: auto !important;
			}
			td.tbl-body .mobilehide {
				display: none !important;
			}
		}

		</style>

		<?php /* Targeting Windows Mobile
		<!--[if IEMobile 7]>
		<style type="text/css">
		</style>
		<![endif]-->
		 */ ?>

		<?php /* Outlook 2007/10 List Fix */ ?>
		<!--[if gte mso 9]>
		<style type="text/css" >
		.article-content ol, .article-content ul {
			margin: 0 0 0 24px;
			padding: 0;
			list-style-position: inside;
		}
		</style>
		<![endif]-->

		<jdoc:include type="head" name="email" />
	</head>
	<body bgcolor="<?php echo $bgColor; ?>">
		<!-- Start Body Wrapper Table -->
		<table width="100%" cellpadding="0" cellspacing="0" border="0" id="backgroundTable" bgcolor="<?php echo $bgColor; ?>">
			<tbody>
				<tr>
					<td bgcolor="<?php echo $bgColor; ?>" align="center">

						<!-- Start Content Wrapper Table -->
						<table class="tbl-wrap" width="670" cellpadding="0" cellspacing="0" border="0">
							<tbody>
								<tr>
									<td bgcolor="<?php echo $tblColor; ?>" width="10"></td>
									<td class="tbl-body" bgcolor="<?php echo $tblColor; ?>" width="650" align="left">

										<!-- Start Header Spacer -->
										<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
											<tbody>
												<tr>
													<td height="30"></td>
												</tr>
											</tbody>
										</table>
										<!-- End Header Spacer -->

										<!-- Start content -->
										<jdoc:include type="component" />
										<!-- End content -->

										<!-- Start Footer Spacer -->
										<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
											<tbody>
												<tr>
													<td height="30"><div>----</div></td>
												</tr>
											</tbody>
										</table>
										<!-- End Footer Spacer -->

										<!-- Start Footer -->
										<table class="tbl-footer" width="100%" cellpadding="0" cellspacing="0" border="0">
											<tbody>
												<tr> <!-- Donation -->
													<td align="center" valign="bottom">
														<span>Check out our <a href="https://secure.givelively.org/donate/bioquest-curriculum-consortium" style="color:#0000FF;text-decoration:underline;" title="donation page">donation page</a> to support <a href="<?php echo Request::base(); ?>groups/bioquest">BioQUEST</a>, the organization that hosts the <a href="<?php echo Request::base(); ?>"><?php echo Config::get('sitename'); ?></a> platform.</span>
													</td>
												</tr> <!-- Donation -->
												<tr>
													<td align="center" valign="bottom">
														<span><a href="https://qubeshub.org/news/newsletter/subscribe" title="Newsletter Subscribe" style="color:#0000FF;text-decoration:underline;">Sign up</a> for our newsletter.</span>
													</td>
												</tr>
												<tr> <!-- Socal media -->
													<td align="center" valign="top">
														<table class="social" width="100%" cellpadding="0" cellspacing="0" border="0">
															<thead>
																<tr>
																	<th colspan="4" align="center" valign="top">
																		<span>Follow us on social media:</span>
																	</th>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td width="25%" align="center" valign="top">
																		<table cellpadding="0" cellspacing="0" border="0">
																			<tbody>
																				<tr>
																					<td width="24px" align="center" valign="middle">
																						<a href="https://www.facebook.com/BioQUESTed" title="Facebook" style="color:#0000FF;text-decoration:underline;">
																							<img src="https://qubeshub.org/app/site/media/images/emails/facebook_email_icon.png" alt="Facebook" width="24" height="24" style="border:none;" />
																						</a>
																					</td>
																					<td class="mobilehide" align="left" valign="middle">
																						<a href="https://www.facebook.com/BioQUESTed" title="Facebook" style="color:#0000FF;text-decoration:underline;">
																							<span>Facebook</span>
																						</a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td width="25%" align="center" valign="top">
																		<table cellpadding="0" cellspacing="0" border="0">
																			<tbody>
																				<tr>
																					<td width="24px" align="center" valign="middle">
																						<a href="https://twitter.com/bioquested" title="Twitter/X" style="color:#0000FF;text-decoration:underline;">
																							<img src="https://qubeshub.org/app/site/media/images/emails/twitter_email_icon.png" alt="Twitter/X" width="24" height="24" style="border:none;" />
																						</a>
																					</td>
																					<td class="mobilehide" align="left" valign="middle">
																						<a href="https://twitter.com/bioquested" title="Twitter/X" style="color:#0000FF;text-decoration:underline;">
																							<span>Twitter/X</span>
																						</a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td width="25%" align="center" valign="top">
																		<table cellpadding="0" cellspacing="0" border="0">
																			<tbody>
																				<tr>
																					<td width="24px" align="center" valign="middle">
																						<a href="https://www.linkedin.com/company/bioquest-curriculum-consortium" title="LinkedIn" style="color:#0000FF;text-decoration:underline;">
																							<img src="https://qubeshub.org/app/site/media/images/emails/linkedin_email_icon.png" alt="LinkedIn" width="24" height="24" style="border:none;" />
																						</a>
																					</td>
																					<td class="mobilehide" align="left" valign="middle">
																						<a href="https://www.linkedin.com/company/bioquest-curriculum-consortium" title="LinkedIn" style="color:#0000FF;text-decoration:underline;">
																							<span>LinkedIn</span>
																						</a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td width="25%" align="center" valign="top">
																		<table cellpadding="0" cellspacing="0" border="0">
																			<tbody>
																				<tr>
																					<td width="24px" align="center" valign="middle">
																						<a href="https://www.instagram.com/bioquested" title="LinkedIn" style="color:#0000FF;text-decoration:underline;">
																							<img src="https://qubeshub.org/app/site/media/images/emails/instagram_email_icon.png" alt="LinkedIn" width="24" height="24" style="border:none;" />
																						</a>
																					</td>
																					<td class="mobilehide" align="left" valign="middle">
																						<a href="https://www.instagram.com/bioquested" title="LinkedIn" style="color:#0000FF;text-decoration:underline;">
																							<span>Instagram</span>
																						</a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr> <!-- Socal media -->
												<tr> <!-- Support ticket -->
													<td align="center" valign="top">
														<span>Comments or concerns? <a href="https://qubeshub.org/support/ticket/new" style="color:#0000FF;text-decoration:underline;">Submit a support ticket</a>.</span>
													</td>
												</tr>
												<tr> <!-- Address -->
													<td align="center" valign="top">
														<table width="100%" cellpadding="0" cellspacing="0" border="0">
															<tbody>
																<tr>
																	<td align="center" valign="bottom">
																		<span>BioQUEST Curriculum Consortium</span><br />
																		<span>19 Glen Ridge Rd, Raymond, NH, 03077</span><br />
																		<span>United States</span>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr> <!-- Address -->
											</tbody>
										</table>
										
										<!-- End Footer -->

									</td>
									<td bgcolor="<?php echo $tblColor; ?>" width="10"></td>
								</tr>
							</tbody>
						</table>
						<!-- End Content Wrapper Table -->
					</td>
				</tr>
			</tbody>
		</table>
		<!-- End Body Wrapper Table -->
	</body>
</html>