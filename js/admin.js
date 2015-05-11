jQuery(document).ready(function($) {


    $('table.wp-list-table a').each(function(index, element) {
        var a = $(this);
		a.parent().parent().click(function(){
			document.location = a.attr('href');
		});
    });

    $('.facil-icon').click(function(){
    	$('.facil-icon').removeClass('selected');
    	$(this).addClass('selected');

    	$('#icon').val($(this).attr('data-value'));
    });
	
	$('#foto-list .foto img').live('click', function(){
		var foto_id = $(this).attr('data-id');
		var fotos = $('#fotos').val();
		fotos = fotos.replace(','+foto_id+',', ',');

		$('#fotos').val(fotos);
					
		$(this).parent().remove();
	});

	$( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

	if($('#ajax-table').length > 0)
	{
		var options = {
			"sScrollY": "600px",
			"sPaginationType": "full_numbers",
			"iDisplayLength": 100,
			"aaSorting": [[ 1, "desc"]],
			'ajax': {
				url: ajaxurl,
				type: 'POST',
				data: function ( d ) {
			      return $.extend( {}, d, {
			        "action": 'getTableData',
					'db-table': $('#db-table').val(),
					'type': $('#db-type').val()
			     } );
				}
			},
			'footerCallback': function ( row, data, start, end, display ) {
	            var api = this.api(), data;
	 
	            // Remove the formatting to get integer data for summation
	            var intVal = function ( i ) {
	                return typeof i === 'string' ?
	                    i.replace(/[\$,]/g, '')*1 :
	                    typeof i === 'number' ?
	                        i : 0;
	            };
	 
	            // Total over all pages
	            data = api.column( 6, { search: 'applied' } ).data();
	            total = data.length ?
	                data.reduce( function (a, b) {
	                        return intVal(a) + intVal(b);
	                } ) :
	                0;
	
	 
	            // Update footer
	            $( api.column( 4 ).footer() ).html(
	                '€'+ total.toFixed(2)
	            );
	        }
		};
		

		var oTable = $('#ajax-table').DataTable( options );


		$('#ajax-table tbody').on('click', 'tr', function(){
			var data = oTable.row(this).data();
			var id = data[0];
    		window.location = window.location + '&id=' + id;
    	});

    	$('#boek-jaar').change(function(){
    		var value = $(this).val();
    		if($('#boek-maand').val() != '' && value != '') {
    			value += '-' + $('#boek-maand').val();
    		}
    		oTable.column(1).search(value).draw();
    	});

    	$('#boek-maand').change(function(){
    		if($('#boek-jaar').val() != '') {
    			var value = $('#boek-jaar').val() + '-' + $(this).val();
    			oTable.column(1).search(value).draw();
    		}
    	});
	}

	$(window).resize(resizeTable);
	resizeTable();	

	$('#show-aanbiedingen').click(function(){
		if( $(this).attr('checked') == 'checked' )
		{
			$('td.aanbieding, th.aanbieding').css('display', 'table-cell');
			$('th.periode').attr('colspan', 2);
		} else
		{
			$('td.aanbieding, th.aanbieding').css('display', 'none');
			$('th.periode').attr('colspan', 1);
			
		}
	});

	
	$('.uploader').on('click', function(event) {
        event.preventDefault();
 
        var button = $(this);
        var id = button.attr('id').replace('_button', '');
        var multiple = $(this).hasClass('multiple');
        var camping_acco = ($('#camping_id').length > 0 ? 'acco' : 'camping');
 
        var frame = wp.media({
            title: "Selecteer afbeelding" + (multiple ? 'en' : ''),
            multiple: multiple,
            library: { type: 'image' },
            button : { text : 'Selecteer afbeelding' + (multiple ? 'en' : '') }
        });
        frame.on( 'select', function() {
            var selection = frame.state().get('selection');
            if(multiple) var imgs = $('#fotos').val().split(',');
            selection.each(function(attachment) {
                if(multiple)
                {
                    var div = $('<div class="foto"/>');
                    var img = $('<img width="150"/>').prop('src', attachment.attributes.url);
                    img.appendTo(div);
                    div.appendTo($('#foto-list'));
                    imgs.push(attachment.attributes.id);
                    $('#fotos').val(imgs);
                } else
                {
                    $("#"+id).val(attachment.attributes.url);
                    $("#"+id+'_id').val(attachment.attributes.id);
                }
            });
           $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'add_img',
                    id: jQuery.getUrlVar('id'),
                    imgs: imgs,
                    type: camping_acco
                },
                success: function(data){
                    //alert(data);
                }
            });
 
        });
 
        frame.open();
 
    });


	function resizeTable()
	{
		var height = $(window).height();
		$('.dataTables_scrollBody').css('height', height-230);
	}

});

jQuery.extend({
  getUrlVars: function(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
    }
    return vars;
  },
  getUrlVar: function(name){
    return jQuery.getUrlVars()[name];
  }
});


function beschikbaarheid_next(amount)
{
	var month = parseInt(jQuery('#month').val());	
	var year = parseInt(jQuery('#year').val());	
	
	month += amount;
	if(month > 12)
	{
		month -= 12;
		year++;	
	}
	jQuery('#month').val(month);
	jQuery('#year').val(year);
	update_cal();
}
function beschikbaarheid_prev(amount)
{
	var month = parseInt(jQuery('#month').val());	
	var year = parseInt(jQuery('#year').val());	
	
	month -= amount;
	if(month < 1)
	{
		month += 12;
		year--;	
	}
	jQuery('#month').val(month);
	jQuery('#year').val(year);
	update_cal();
}
function update_cal()
{
	jQuery('#beschikbaarheid-data').html('data laden...');
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'update_cal',
			id: jQuery.getUrlVar('id'),
			nr_months: 3,
			month: jQuery('#month').val(),
			year: jQuery('#year').val()
		},
		success: function(data){
			jQuery('#beschikbaarheid-data').html(data);
		}
	});
}



function flip_beschikbaarheid(el, date)
{
	if(jQuery(el).hasClass('free'))
	{
		jQuery(el).removeClass('free').addClass('occupied');
	} else
	{
		jQuery(el).removeClass('occupied').addClass('free');
	}
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'set_beschikbaarheid',
			id: jQuery.getUrlVar('id'),
			date: date,
			beschikbaar: jQuery(el).hasClass('free')
		},
		success: function(data){
			//alert(data);
		}
	});
}


function priceJson()
{
    var data = formToJSON('#simpel-reserveren input');
    jQuery('#json').val(JSON.stringify(data));
}

function formToJSON( selector )
{
    var form = {};
    jQuery(selector).each( function() {
        var self = jQuery(this);
        if(self.attr('id') == 'json') return;

        var name = self.attr('name');
        if (form[name]) {
            form[name] = form[name] + ',' + self.val();
        }
        else {
            if(self.attr('type') == 'checkbox') {
                form[name] = self.prop('checked');
            } else {
                form[name] = self.val();
            }
        }
    });

    return form;
}

function toggleArrangementen(arrangement_id)
{
    var checkboxes = jQuery('.arr'+arrangement_id);
    if(checkboxes.length == 0) return;

    var check = !(checkboxes.prop('checked'));

    jQuery('.arr'+arrangement_id).prop('checked', check);
}