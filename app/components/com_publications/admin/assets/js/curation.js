/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

//-----------------------------------------------------------
//  Ensure we have our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}

//----------------------------------------------------------
// Project Publication Curation Manager JS
//----------------------------------------------------------
if (!jq) {
	var jq = $;
}

HUB.PublicationsCuration = {
	jQuery: jq,
	
	initialize: function() 
	{
		var $ = this.jQuery;
		
		// Enable reordering
		HUB.PublicationsCuration.reorder($('#blockorder'));
		HUB.PublicationsCuration.reorder($('#alignment-list'));

		if ($('#select-alignment').length != 0) {
			$('#select-alignment').val('');
		}
	},
	
	reorder: function(list)
	{
		var $ = this.jQuery;

		if ($('.reorder').length == 0 || $(list).length == 0 || $(list).hasClass('noedit'))
		{
			return false;
		}

		// Drag items
		$(list).sortable(
		{
			items: "> li.reorder",
			update: function()
			{
				if ($(list).attr('id') == 'blockorder') {
					HUB.PublicationsCuration.saveOrder();
				}
		   	}
		});
	},

	saveOrder: function()
	{
		var $ = this.jQuery;
		var items = $('.pick');
		var selections = '';

		if (items.length > 0) 
		{
			items.each(function(i, item)
			{
				var id = $(item).attr('id');
				id = id.replace('s-', '');

				if (id != '' && id != ' ')
				{
					selections = selections + id + '-' ;
				}
			});
		}
		$('#neworder').val(selections);
	},

	addAlignment: function(el)
	{
		var block_id = $('input[name="bid"]').val();
		var element_id = $('span[class="block-id"]').html().split('-')[0].split(':')[1].trim();

		var faid = $(el).val();
		var $option = $('#select-alignment option[value="' + faid + '"]');
		var depth = $option.data('depth');
		var label = $option.html();
		var name_prefix = 'curation[blocks][' + block_id + '][elements][' + element_id + '][params][typeParams][view][alignment][' + faid + ']';

		var li = document.createElement('li');
		li.setAttribute('class', 'pick reorder ui-sortable-handle');
		li.setAttribute('data-id', faid);
		li.setAttribute('data-depth', depth);
		li.innerHTML = label;

		// FA id input
		var input = document.createElement('input');
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', name_prefix + '[faid]');
		input.setAttribute('value', faid);
		li.appendChild(input);

		// Delete button
		var span = document.createElement('span');
		span.setAttribute('class', 'editalignment');
		span.setAttribute('onclick', 'HUB.PublicationsCuration.deleteAlignment(this)');
		span.innerHTML = "Delete";
		li.appendChild(span);

		// Multiple input
		span = document.createElement('span');
		span.setAttribute('class', 'editalignment');
		var label = document.createElement('label');
		label.setAttribute('for', 'multiple-' + faid);
		label.innerHTML = "Radio depth";
		span.appendChild(label);

		input = document.createElement('input');
		input.setAttribute('class', 'numeric-input');
		input.setAttribute('type', 'number');
		input.setAttribute('id', 'multiple-' + faid);
		input.setAttribute('name', name_prefix + '[multiple_depth]');
		input.setAttribute('value', 0);
		input.setAttribute('min', 0);
		input.setAttribute('max', depth);
		span.appendChild(input);
		li.appendChild(span);

		// Mandatory input
		span = document.createElement('span');
		span.setAttribute('class', 'editalignment');
		var label = document.createElement('label');
		label.setAttribute('for', 'mandatory-' + faid);
		label.innerHTML = "Mandatory depth";
		span.appendChild(label);

		input = document.createElement('input');
		input.setAttribute('class', 'numeric-input');
		input.setAttribute('type', 'number');
		input.setAttribute('id', 'mandatory-' + faid);
		input.setAttribute('name', name_prefix + '[mandatory_depth]');
		input.setAttribute('value', 0);
		input.setAttribute('min', 0);
		input.setAttribute('max', depth);
		span.appendChild(input);
		li.appendChild(span);

		$('#alignment-list').append(li);
		$option.attr('disabled', true);
		$option.attr('selected', true);
		$('#select-alignment').val('');
	},

	deleteAlignment: function(el)
	{
		var faid = $(el).parent().data('id');
		$('#select-alignment option[value="' + faid + '"]').removeAttr('disabled selected');
		$('#select-alignment').val('');
		el.parentNode.remove();
	},
}

jQuery(document).ready(function($){
	HUB.PublicationsCuration.initialize();
});	
