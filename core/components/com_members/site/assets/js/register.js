/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

if (!HUB) {
	var HUB = {};
}

if (!jq) {
	var jq = $;
}

HUB.Register = {
	disableIndie: function() {
		var $ = jq;

		$('#type-indie').attr('checked', false);
		$('#username').attr('disabled', false);
		$('#passwd').attr('disabled', false);
	},

	disableDomains: function() {
		var $ = jq;

		$('#username').val('');
		$('#username').attr('disabled', true);
		$('#passwd').val('');
		$('#passwd').attr('disabled', true);
		$('#type-indie').attr('checked', true);
		$('.option').each(function(i, input) {
			var name = $(input).attr('name');
			var value = $(input).val();
			if (name == 'domain' && value != '') {
				if ($(input).attr('checked')) {
					$(input).attr('checked', false);
				}
			}
		});
	},

	checkLogin: function() {
		var $ = jq,
			submitTo = $('#base_uri').val() + '/members/register/checkusername?userlogin=' + $('#userlogin').val(),
			usernameStatus = $('#usernameStatus');

		$.getJSON(submitTo, function(data) {
			usernameStatus.html(data.message);
			usernameStatus.removeClass('ok');
			usernameStatus.removeClass('notok');
			if (data.status == 'ok') {
				usernameStatus.addClass('ok');
			} else {
				usernameStatus.addClass('notok');
			}
		});
	},

	checkPassword: function(password, rules, username) {
		$.ajax({
			url: "/api/members/checkpass",
			type: "POST",
			data: {
				"password1": password,
				username
			},
			dataType: "json",
			cache: false,
			success: function(json) {
				if (json.html.length > 0 && password !== '') {
					rules.html(json.html);
				} else {
					// Probably deleted password, so reset classes
					rules.find('li').switchClass('error passed', 'empty', 200);
				}
			}
		});
	}
}

jQuery(document).ready(function($){
	var $ = jq,
		w = 760,
		h = 520;

	$('.com_register a.popup').each(function(i, trigger) {
		href = $(this).attr('href');
		if (href.indexOf('?') == -1) {
			href += '?tmpl=component';
		} else {
			href += '&tmpl=component';
		}
		$(this).attr('href', href);
	});

	// Look for the "type-linked" element
	var typeindie = $('#type-indie');
	if (typeindie.length) {
		// Found it - means we're on the initial registration
		// form where users choose a linked account or not
		$('#username').attr('disabled', true);
		$('#passwd').attr('disabled', true);
		$('.option').each(function(i, input) {
			var name = $(input).attr('name');
			var value = $(input).val();
			var checked = $(input).attr('checked');
			if (name == 'domain' && value != '') {
				$(input).on('click', HUB.Register.disableIndie);

				if (checked == 'checked') {
					$('#username').attr('disabled', false);
					$('#passwd').attr('disabled', false);
				}
			}
		});
		$(typeindie).on('click', HUB.Register.disableDomains);
	}

	var userlogin = $('#userlogin');
	var usernameStatusAfter = $('#userlogin');
	var passwd = $('#password');
	var passrule = $('#passrules');

	if (passwd.length > 0 && passrule.length > 0) {
		passwd.on('keyup', function(){
			HUB.Register.checkPassword(passwd.val(), passrule, userlogin.val())
		});
	}

	if (userlogin.length > 0) {
		usernameStatusAfter.parent().append('<p class="hint" id="usernameStatus">&nbsp;</p>');

		userlogin.focusout(function(obj) {
			var timer = setTimeout('HUB.Register.checkLogin()', 200);
		});
	}
});

$(function(){
	if ($(".rorApiAvailable")[0]) {
		$("#profile_organization").autocomplete({
			source: function(req, resp){
				var rorURL = "index.php?option=com_members&controller=profiles&task=getOrganizations&term=" + $("#profile_organization").val();
	
				$.ajax({
					url: rorURL,
					data: null,
					dataType: "json",
					success:function(result){
						resp(result);
					},
					error:function(jqXHR, textStatus, errorThrown){
						console.log(textStatus);
						console.log(errorThrown);
						console.log(jqXHR.responseText);
					}
				});
			}
		});
	} 
});