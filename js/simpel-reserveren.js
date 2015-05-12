jQuery( document ).ready(function( $ ) {
	// Simple Reserveren OrderBox Fixed Layout Width fix. 

	$(document).ready(srOrderBoxFixed);
	$(window).resize(srOrderBoxFixed);

	$(document).ready(srOrderBoxStatic);
	$(window).resize(srOrderBoxStatic);

	$(document).ready(srResults);
	$(window).resize(srResults);

	function srOrderBoxFixed() {
	  $('.sr-order-box.fixed').each(function(){
	  	if($(this).parent().parent().hasClass('widgets'))
	  	{
	  		var parent 	= $(this).parent().parent();
	  		var width 	= parent.width();
	  		var outerWidth = parent.outerWidth();
	  		$(this).width( outerWidth );
	  		$(this).css('margin-left', (outerWidth - width) / -2);
	  	} else
	  	{
	    	$(this).width($(this).parent().outerWidth());
	    }
	  });
	}



	function srOrderBoxStatic() {

	  var responsive_viewport = $(window).width();



	  if (responsive_viewport > 768) {

	    $('.sr-order-box.static').waypoint(function() {
	      $(this).toggleClass('top');
	    }, {
	      offset: function() {
	        return -$(this).height();
	      }
	    });
	  }
	  
	}


	function srResults() {

	  var responsive_viewport = $(window).width();


	  if (responsive_viewport < 768) {

	    $('#filtersTab').click(function (e) {
	      e.preventDefault()
	      $(this).parent().toggleClass('active');
	      $('#filters').toggleClass('visible');
	      $('#resultatenTab').parent().removeClass('active');
	      $('#resultaten').removeClass('visible');
	    })

	     $('#resultatenTab').click(function (e) {
	      e.preventDefault()
	      $(this).parent().toggleClass('active');
	      $('#resultaten').toggleClass('visible');
	      $('#filtersTab').parent().removeClass('active');
	      $('#filters').removeClass('visible');

	    })

	     $('#filtersTerugTab').click(function (e) {
	      e.preventDefault()
	      $('#filtersTab').parent().removeClass('active');
	      $('#filters').removeClass('visible');
	      $('#resultaten').toggleClass('visible');
	      $('#resultatenTab').parent().toggleClass('active');

	    })

	     $('#kassabonTab').click(function (e) {
	      e.preventDefault()
	      $(this).parent().toggleClass('active');
	      $('#prijsberekening').toggleClass('visible');
	      $('#boekenTab').parent().removeClass('active');
	      $('#boeken').removeClass('visible');
	    })

	     $('#boekenTab').click(function (e) {
	      e.preventDefault()
	      $(this).parent().toggleClass('active');
	      $('#boeken').toggleClass('visible');
	      $('#kassabonTab').parent().removeClass('active');
	      $('#prijsberekening').removeClass('visible');

	    })

	    $('.plattegrond-phone-header').click(function(e){
	    	$('.plattegrond-form').show();
	    });
	    $('.plattegrond-form').hide();
	    $('.sr-close-form').click(function(e){
	    	$('.plattegrond-form').hide();
	    });
	  }


	}

	$.extend({
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
	    return $.getUrlVars()[name];
	  }
	});

	function resize_content_wrapper()
	{
		return;
		if( $('#content').height() + 150 < $(document).height())
		{
			$('#content').css('height', $(document).height() - 150);
		}
		$('#sidebar').css('height', 'auto');

		if($('#sidebar').height() > $('#content').height())
		{
			$('#content').css('height', $('#sidebar').height() + 31);
		} else
		{
			$('#sidebar').css('height', $('#content').height() - 31);
		}
	}


	$(document).ready(function(){
		$('.view-modes a').click(function(){
			$('.view-modes a').removeClass('selected');
			$(this).addClass('selected');

			$('#resultaten').removeClass('list square').addClass($(this).attr('data-view'));
		});

		

		$('.btn.discount, .extra-info').popover({trigger: 'hover'});

        $('.sr-tip').tooltip({html: true});

		// zet de aankomst, vertrek input velden aan de hand van de cookie
		$.cookie.json = true;
		var cookie = {};
		if($.cookie('simpelreserveren') === undefined) {
			cookie = {
				'aankomst'	: date('d-m-Y'),
				'vertrek'	: date('d-m-Y', strtotime('+' + sr_translations.std_aantal_nachten + ' days')),
				'volw'		: 2,
				'kind'		: 0
			}
		} else {
			cookie = $.cookie('simpelreserveren');
		}

		if(cookie.force == '1') {
			// force the use of the cookie once
			cookie.force = '0';
		} else if($.getUrlVar('aankomst') != null) {
			cookie.aankomst = date('d-m-Y', strtotime($.getUrlVar('aankomst')));
			cookie.vertrek  = date('d-m-Y', strtotime($.getUrlVar('vertrek')));
			if($.getUrlVar('volw') != null) cookie.volw = $.getUrlVar('volw');
			if($.getUrlVar('kind') != null) cookie.kind = $.getUrlVar('kind');
			if($.getUrlVar('type') != null) cookie.type = $.getUrlVar('type');
		}
	
		if(strtotime(cookie.aankomst) < strtotime(sr_translations.vanaf_datum)) {
			cookie.aankomst = date('d-m-Y', strtotime(sr_translations.vanaf_datum));
			cookie.vertrek 	= date('d-m-Y', strtotime('+1 week', strtotime(cookie.aankomst)));			
		}
		if(strtotime(cookie.aankomst) < strtotime(date('d-m-Y', new Date())) ) {
			cookie.aankomst = date('d-m-Y');
			cookie.vertrek	= date('d-m-Y', strtotime('+' + sr_translations.std_aantal_nachten + ' days'));
		}
		//if(cookie.volw == null || cookie.volw == '0' || cookie.volw === undefined) cookie.volw = 2;
		$.cookie('simpelreserveren', cookie );


		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) 
		{
			if($('#aankomst').length > 0)
			{
				$('#aankomst').val(date('Y-m-d', strtotime(cookie.aankomst)));
				$('#vertrek').val(date('Y-m-d', strtotime(cookie.vertrek)));
				$('#aankomst, #vertrek').attr('type', 'date');
				$('#volw').val(cookie.volw);
			}
			if($('#kalender').length > 0)
			{
				$('#aankomst, #vertrek').blur(function(){
					$('#kalender').datepick('setDate', $.datepicker.formatDate('dd-mm-yy', aankomst), $.datepicker.formatDate('dd-mm-yy', vertrek) );
				});
				
			} 

			$('#volw, #youth, #kind, #baby').attr('type', 'number');
		} else
		{
			$.datepicker.regional['nl'] = {
				closeText: 'Sluiten',
				prevText: '←',
				nextText: '→',
				currentText: 'Vandaag',
				monthNames: ['januari', 'februari', 'maart', 'april', 'mei', 'juni',
				'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
				monthNamesShort: ['jan', 'feb', 'maa', 'apr', 'mei', 'jun',
				'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
				dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
				dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
				dayNamesMin: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
				weekHeader: 'Wk',
				dateFormat: 'dd-mm-yy',
				firstDay: 1,
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: ''};
			$.datepicker.setDefaults($.datepicker.regional['nl']);

			if($('#kalender').length > 0) {
				$('#aankomst').val(strtotime(cookie.aankomst)*1000);
				$('#vertrek').val(strtotime(cookie.vertrek)*1000);
			} else {
				$('#aankomst').val(cookie.aankomst);
				$('#vertrek').val(cookie.vertrek);
			}
			$('#volw').val(cookie.volw);
			$('#kind').val(cookie.kind);
			if(cookie.type != null && cookie.type != '') $('#type').val(cookie.type);
			
			$('#aankomst, #vertrek').addClass('jquery-ui');
			var dates = $( "#aankomst, #vertrek" ).datepicker({
				defaultDate: "+1w",
				changeMonth: false,
				numberOfMonths: 1,
				minDate: 0,
				beforeShow: function() {
					setTimeout(function() {
						$('#ui-datepicker-div').css('zIndex', 1000);
					}, 100);
				},
				onSelect: function( selectedDate ) {
                    $(this).trigger('select');
					var option = this.id == "aankomst" ? "minDate" : "maxDate",
						instance = $( this ).data( "datepicker" ),
						date = $.datepicker.parseDate(
							instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							selectedDate, instance.settings );
					dates.not( this ).datepicker( "option", option, date );
					if(this.id == 'aankomst')
					{
						var date_vertrek = $.datepicker.parseDate(
							instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							$('#vertrek').val(), instance.settings );
						if(date_vertrek <= date)
						{
							date_vertrek.setDate(date.getDate()+parseInt(sr_translations.std_aantal_nachten));
							if (date_vertrek.getDate() > 0) {
								$('#vertrek').val( date_vertrek.getDate() + '-' + (date_vertrek.getMonth()+1) +  '-' + date_vertrek.getFullYear() );
							}
						}
					}
					// zet ook de data van de andere kalender
					
					if($('#kalender').length > 0)
					{
						$('#kalender').datepick('setDate', $('#aankomst').val(), $('#vertrek').val() );
					} 
				}
			});		
		}

		$('.simpel-reserveren article.sr-zoekresultaat .thumb img').popover({
            placement: 'bottom',
            html: true,
            trigger: 'hover'
		});
		/*
		$(".fancybox").fancybox({
			'autoSize':            true,
			'transitionIn':         'elastic',
			'transitionOut':        'elastic'
		});*/

		if($('#kalender').length > 0)
		{
			$('#kalender').datepick({ 
				rangeSelect: true,
				minDate: 0,
				dateFormat: (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ? 'yyyy-mm-dd' : 'dd-mm-yyyy'), 
				onSelect: function(dates) { 
					if(dates[0] != dates[1])
					{
						if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
						{
							$('#aankomst').val($.datepicker.formatDate('yy-mm-dd', dates[0]));
							$('#vertrek').val($.datepicker.formatDate('yy-mm-dd', dates[1]));
						} else
						{
							$('#aankomst').val(date('d-m-Y', Math.round(dates[0].getTime()/1000)));
							$('#vertrek').val(date('d-m-Y', Math.round(dates[1].getTime()/1000)));	
						}
						var cookie = $.cookie('simpelreserveren');
						cookie.aankomst = $('#aankomst').val();
						cookie.vertrek = $('#vertrek').val();
						$.cookie('simpelreserveren', cookie);

						$('.sr-accommodatie-prijs').html('prijs laden...');
						$.ajax({
							url: sr_translations.admin_ajax,
							type: 'POST',
							data: {
								action: 'get_prices',
								aankomst: dates[0].getTime()/1000,
								vertrek: dates[1].getTime()/1000,
                                volw: cookie.volw,
                                kind: cookie.kind,
								accommodatie_id: $('#accommodatie').val(),
								arrangement_id: $.getUrlVar('arrangement') || $('#arrangement').val(),
                                show: $('#arrangement').length > 0 ? 'accommodaties' : 'prices',
								_new: 1,
								refer: $.getUrlVar('refer')
							},
							success: function(data){
								$('.sr-accommodatie-prijs').html(data);
								$('.sr-accommodatie-prijs .fa').tooltip({html: true});
							}

						});
						update_accommodatie_blokken();
					}
				}, 
				onDate: function(datum, current){
					if(!current || !datum) return {};
					var jaar = $.datepick.formatDate('yyyy', datum);
					var dagen = beschikbaarheid[jaar];
					if(dagen == null)
					{
						return  { dateClass: 'occupied', selectable: false };
					}

					var z = date('z', datum);
					var dag 		= dagen[z];
					var vorige_dag 	= dagen[z-1];
					
					if(dag == 'O') {
						if(vorige_dag != 'O')
						{
							return {dateClass : 'vorige-free', selectable: true};
						} 
						return {dateClass : 'occupied', selectable: false};
					} 

					if(vorige_dag == 'O') {
						return {dateClass : 'vorige-occupied', selectable: true };
					} 

					return {dateClass : 'free', selectable: (dag == 'X') };
						
					
				}
			});
			update_price();

		}

		$('#sr-children-yes, #sr-children-no').click(function(){
			if($(this).val() == '1')
			{
				$('.has-children').removeClass('hide');
			} else
			{
				$('.has-children').addClass('hide');
			}
		});

		if($('#sr-formBoeken').length > 0)
		{
			$("#sr-formBoeken").validationEngine('attach', {
				faled: function(el){
				}
			}).bind('jqv.field.result', function(event, input, error, msg) {
				var lbl = $('#lbl-'+input.attr('id'));
				if(typeof lbl.attr('title') === 'undefined') lbl.attr('title', lbl.html()); 
				if(error)
				{
					var msgs = msg.split('<br/>');
					lbl.html( lbl.attr('title') + msgs[0] )
						.addClass('error');
					input.addClass('error').removeClass('valid');
				} else
				{
					lbl.html( lbl.attr('title') )
						.removeClass('error');
					input.removeClass('error').addClass('valid');
				}

				if($('#sr-formBoeken input.valid').length == 4 || ($('#sr-formBoeken input.valid').length == 3 && !$('#voornaam').hasClass('valid')))
				{
					$('#u-bent-klaar').show();
				} else 
				{
					$('#u-bent-klaar').hide();
				}
			});
			$('#voornaam, #achternaam').blur(function(){
				var voornaam = $('#voornaam').val(); 
				var achternaam = $('#achternaam').val();
				var naam = (voornaam != '' ? voornaam + ' ' : '') + achternaam;
				$('#gastnaam').val(naam);
			});

			if($('#stap').val() == '1')
			{
				update_block_personen();
				
						
				$('#aankomst, #vertrek').blur(function(){
					setTimeout(updateKassabon, 300);
				});
				$('#volw, #kind, #klein_kind, #baby').change(function(){
					setTimeout(updateKassabon, 300);
					update_block_personen();
				});

				$('#sr-formBoeken input, #sr-formBoeken select').change(function(event){
					var field = $(this);
					var checked = this.checked;
					$('#kassabon').html('laden...');

					$('#kassabon').html('laden...');
					$.getJSON( sr_translations.admin_ajax, {
						'action'		: 'get_kassabon',
						'field'			: field.attr('id'),
						'value'			: field.val(),
						'checked'		: (field.hasClass('check') ? checked : ''),
						'id'			: $('#id').val()
					},
					function(data) {
						$('#kassabon').html(data.html);
						$('.sr-total').html(data.prijs);
                        $('.sr-tip').tooltip({html: true});

                        resize_content_wrapper();
					});
				});
			}
			if($('#stap').val() == '3')
			{
				$("#sr-formBoeken").validationEngine('attach', {
					onValidationComplete: function(form, status){
						if($('#stap').val() == '3')
						{
							if(!status) return;
							$('#naw-submit').hide();
							$('#ajax-message').html('gegevens worden opgeslagen...');
							$.post(	sr_translations.admin_ajax, $('#sr-formBoeken').serialize(), function(data){
								$('#ajax-message').html('Uw gegevens zijn opgeslagen.<br/>U keert nu automatisch terug naar de homepage').addClass('alert alert-success');
								setTimeout(function(){
									document.location = sr_translations.root.replace('boeken.', '');
								}, 10000);
							} );
						}
					}
				});
			}


		}

		$('#boek-op-plattegrond').click(function(){

			$('#plattegrond-layer').height( $(window).height() )
				.width( $(window).width() )
				.css('display', 'block')
				.appendTo( $('body') );
			
			$('#zoom1').smoothZoom({
				width: '100%',
				height: '100%',
				responsive: true,
				zoom_OUT_TO_FIT: false
			});
		});
		$('#plattegrond-layer, #plattegrond-layer button').click(function(){
			var txt = $('#plaats_voorkeur').val();
			$('#hidden_plaats_voorkeur').val(txt);
			$('#sr-plattegrond-voorkeur').html(txt);
			$('#plattegrond-layer').css('display', 'none');
			if(txt != '')
			{
				$('input.check[data-voorkeursplaats="1"]').attr('checked', 'checked').attr('disabled', 'disabled').trigger('change');
			} else
			{
				$('input.check[data-voorkeursplaats="1"]').removeAttr('checked').removeAttr('disabled').trigger('change');
			}
		});
		$('#plattegrond-layer div div').click(function(e){
			e.stopPropagation();
		});

		$(window).resize(function(){
			$('#plattegrond-layer').height( $(window).height() ).width( $(window).width() )
		});
		
		$("a[href$='.jpg'],a[href$='.JPG'],a[href$='.jpeg'],a[href$='.png'],a[href$='.gif']").attr('rel', 'gallery').fancybox(); 

		
		update_accommodatie_blokken();

		return;

		resize_content_wrapper();
		$(window).resize(resize_content_wrapper);


		$(".fancybox-big").fancybox({
			'autoSize':            true,
			'transitionIn':         'elastic',
			'transitionOut':        'elastic'
		});

		



		$('#accommodatie').change(function(){
			update_price();
			$.ajax({
				url: sr_translations.admin_ajax,
				type: 'POST',
				data: {
					action: 'get_beschikbaarheid',
					accommodatie_id: $(this).val()
				},
				success: function(data){
					beschikbaarheid = $.parseJSON(data);
					$('#kalender').datepick('changeMonth', 1);
					$('#kalender').datepick('changeMonth', -1);
				}
			});
		});

	    $('.zoeken-result .aanbiedingen li[title], #primary .aanbiedingen li[title]').tooltip({
	    	effect: 'fade',
	    	position: {
	    		my: 'center top',
	    		at: 'center bottom+5'
	    	}
	    });

	    $('table.wp-list-table a').each(function(index, element) {
	        var a = $(this);
			a.parent().parent().click(function(){
				document.location = a.attr('href');
			});
	    });
		
		$('#foto-list .foto img').on('click', function(){
			var foto_id = $(this).attr('data-id');
			var fotos = $('#fotos').val();
			fotos = fotos.replace(','+foto_id+',', ',');

			$('#fotos').val(fotos);
			$(this).parent().remove();
		});

		
		
		
		
		$('#accommodatie-lees-meer').click(function(){
			$('#content-inner').css('height', 'auto').css('overflow', 'show');
			$(this).remove();
		});

		var $tabs = $( "#tabs" ).tabs();

		$('.omschrijving.meer .inner-meer').click(function(){
			$('.omschrijving.part-2').slideDown();
			$('.omschrijving .inner-meer').hide();
			$('.omschrijving .inner-minder').show();
		});
		$('.omschrijving.meer .inner-minder').click(function(){
			$('.omschrijving.part-2').slideUp();
			$('.omschrijving .inner-minder').hide();
			$('.omschrijving .inner-meer').show();
		});

	});

	function update_price()
	{
		var aankomst = new Date( parseInt($('#aankomst').val()) );
		var vertrek  = new Date( parseInt($('#vertrek').val()) );
		
		$('#kalender').datepick('setDate', aankomst, vertrek); 

		if($('#aankomst').val().split('-').length <= 1) {
			$('#aankomst').val( aankomst.getDate() + '-' + (aankomst.getMonth()+1) + '-' + aankomst.getFullYear() );
			$('#vertrek').val( vertrek.getDate() + '-' + (vertrek.getMonth()+1) + '-' + vertrek.getFullYear() );
		}
	}

	function update_block_personen()
	{
		$('#volw, #kind, #klein_kind, #baby').children().each(function(el){
			if($(this).parent().attr('id') == 'volw')
			{
				var max_personen = _max_personen - parseInt($('#kind').val()) - parseInt($('#klein_kind').val()) - parseInt($('#baby').val());
			} else if($(this).parent().attr('id') == 'kind')
			{
				var max_personen = _max_personen - parseInt($('#volw').val()) - parseInt($('#klein_kind').val())  - parseInt($('#baby').val()) ;
			} else if($(this).parent().attr('id') == 'klein_kind')
			{
				var max_personen = _max_personen - parseInt($('#volw').val()) - parseInt($('#kind').val())  - parseInt($('#baby').val()) ;
			} else
			{
				var max_personen = _max_personen - parseInt($('#volw').val()) - parseInt($('#kind').val()) - parseInt($('#klein_kind').val());
			}
			if(parseInt(this.value) > max_personen)
			{
				$(this).attr('disabled', 'disabled');
			} else
			{
				$(this).removeAttr('disabled');
			}
		});	
	}

	function updateKassabon()
	{
		$('#kassabon').html('laden...');
		$.getJSON( sr_translations.admin_ajax, {
			'action'		: 'get_kassabon',
			'aankomst'		: $('#aankomst').val(),
			'vertrek'		: $('#vertrek').val(),
			'volw'			: $('#volw').val(),
			'kind'			: $('#kind').val(),
			'klein_kind'	: $('#klein_kind').val(),
			'baby'			: $('#baby').val(),
			'id'			: $('#id').val()
		},
		function(data) {
			$('#kassabon').html(data.html);
			$('#btn-reserveer').attr('value', 'Boek nu voor € ' + data.prijs );
            $('.sr-tip').tooltip({html: true});

        });
	}

	function update_accommodatie_blokken() {
		if($('#accommodatie-arrangementen').length > 0) {
			$.ajax({
				url: sr_translations.admin_ajax,
				type: 'POST',
				data: {
					action: 'get_accommodatie_arrangementen',
					id: $('#accommodatie').val()
				},
				success: function(r){
					$('#accommodatie-arrangementen').html(r);
				}
			});
		}

		if($('#accommodatie-alternatieve-prijzen').length > 0) {
			$.ajax({
				url: sr_translations.admin_ajax,
				type: 'POST',
				data: {
					action: 'get_accommodatie_alternatieve_prijzen',
					id: $('#accommodatie').val()
				},
				success: function(r){
					$('#accommodatie-alternatieve-prijzen').html(r);
				}
			});
		}		
	}

	var go_done = false;
	$('.sr-zoekresultaat').click(function(){
		if(!go_done && $(this).attr('data-url'))
		{
			product_click({
				'name': $(this).attr('data-name'),
				'id': $(this).attr('data-id'),
				'price': $(this).attr('data-price'),
				'brand': 'Simpel Reserveren',
				'category': $(this).attr('data-category'),
				'variant': '',
				'url': $(this).attr('data-url')
			});

			go_done = true;
			document.location = $(this).attr('data-url');
		}
			
	});

	$('.sr-boeken-arrangement').click(function(){
		if(!go_done && $(this).attr('data-url'))
		{
			product_click({
				'name': $(this).attr('data-title'),
				'id': $(this).attr('data-id'),
				'price': $(this).attr('data-price'),
				'brand': 'Simpel Reserveren',
				'category': 'Arrangement',
				'variant': '',
				'url': $(this).attr('data-url')
			});

			go_done = true;
			document.location = $(this).attr('data-url');
		}

	});

    if($('#arr-accommodaties').length > 0) {
        $('#aankomst, #vertrek').on('select', show_arr_accommodaties);
        setTimeout(function() {
            show_arr_accommodaties();
        }, 100);

    }

});

function show_arr_accommodaties() {
    $.post(	sr_translations.admin_ajax, {
        action: 'get_arr_accommodaties',
        arrangement: $('#arrangement').val(),
        aankomst: $('#aankomst').val(),
        vertrek: $('#vertrek').val()
    }, function(data){
        $('#arr-accommodaties').html(data);
    } );

}

function product_click(productObj) {
  dataLayer.push({
    'event': 'productClick',
    'ecommerce': {
      'click': {
        'actionField': {'list': 'Search Results'},      // Optional list property.
        'products': [{
          'name': productObj.name,                      // Name or ID is required.
          'id': productObj.id,
          'price': productObj.price,
          'brand': productObj.brand,
          'category': productObj.cat,
          'variant': productObj.variant
         }]
       }
     },
     'eventCallback': function() {
       document.location = productObj.url
     }
  });
}

function maxPersonen(field, rules, i, options){
	var value = field.val();
	if(!value.match(/^\d*$/)) return ' * alleen getallen toegestaan';
	
	var total = parseInt(jQuery('#volw').val());
	total += (parseInt(jQuery('#youth').val()) || 0);
	total += (parseInt(jQuery('#kind').val()) || 0);
	total += (parseInt(jQuery('#baby').val()) || 0);

	if(total > _max_personen) return ' maximaal ' + _max_personen + ' personen toegestaan in totaal';
	
	jQuery('#volw, #youth, #kind, #baby').each(function(){
		jQuery(this).removeClass('error');
		var lbl = jQuery('#lbl-'+jQuery(this).attr('id'));
		lbl.removeClass('error')
			.html(lbl.attr('title'));
	});
	jQuery('#lbl-volw, #lbl-youth, #lbl-kind, #lbl-baby').removeClass('error');
}

function date(format, timestamp) {

  var that = this;
  var jsdate, f;
  // Keep this here (works, but for code commented-out below for file size reasons)
  // var tal= [];
  var txt_words = [
    'Sun', 'Mon', 'Tues', 'Wednes', 'Thurs', 'Fri', 'Satur',
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  // trailing backslash -> (dropped)
  // a backslash followed by any character (including backslash) -> the character
  // empty string -> empty string
  var formatChr = /\\?(.?)/gi;
  var formatChrCb = function(t, s) {
    return f[t] ? f[t]() : s;
  };
  var _pad = function(n, c) {
    n = String(n);
    while (n.length < c) {
      n = '0' + n;
    }
    return n;
  };
  f = {
    // Day
    d: function() { // Day of month w/leading 0; 01..31
      return _pad(f.j(), 2);
    },
    D: function() { // Shorthand day name; Mon...Sun
      return f.l()
        .slice(0, 3);
    },
    j: function() { // Day of month; 1..31
      return jsdate.getDate();
    },
    l: function() { // Full day name; Monday...Sunday
      return txt_words[f.w()] + 'day';
    },
    N: function() { // ISO-8601 day of week; 1[Mon]..7[Sun]
      return f.w() || 7;
    },
    S: function() { // Ordinal suffix for day of month; st, nd, rd, th
      var j = f.j();
      var i = j % 10;
      if (i <= 3 && parseInt((j % 100) / 10, 10) == 1) {
        i = 0;
      }
      return ['st', 'nd', 'rd'][i - 1] || 'th';
    },
    w: function() { // Day of week; 0[Sun]..6[Sat]
      return jsdate.getDay();
    },
    z: function() { // Day of year; 0..365
      var a = new Date(f.Y(), f.n() - 1, f.j());
      var b = new Date(f.Y(), 0, 1);
      return Math.round((a - b) / 864e5);
    },

    // Week
    W: function() { // ISO-8601 week number
      var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3);
      var b = new Date(a.getFullYear(), 0, 4);
      return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
    },

    // Month
    F: function() { // Full month name; January...December
      return txt_words[6 + f.n()];
    },
    m: function() { // Month w/leading 0; 01...12
      return _pad(f.n(), 2);
    },
    M: function() { // Shorthand month name; Jan...Dec
      return f.F()
        .slice(0, 3);
    },
    n: function() { // Month; 1...12
      return jsdate.getMonth() + 1;
    },
    t: function() { // Days in month; 28...31
      return (new Date(f.Y(), f.n(), 0))
        .getDate();
    },

    // Year
    L: function() { // Is leap year?; 0 or 1
      var j = f.Y();
      return j % 4 === 0 & j % 100 !== 0 | j % 400 === 0;
    },
    o: function() { // ISO-8601 year
      var n = f.n();
      var W = f.W();
      var Y = f.Y();
      return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
    },
    Y: function() { // Full year; e.g. 1980...2010
      return jsdate.getFullYear();
    },
    y: function() { // Last two digits of year; 00...99
      return f.Y()
        .toString()
        .slice(-2);
    },

    // Time
    a: function() { // am or pm
      return jsdate.getHours() > 11 ? 'pm' : 'am';
    },
    A: function() { // AM or PM
      return f.a()
        .toUpperCase();
    },
    B: function() { // Swatch Internet time; 000..999
      var H = jsdate.getUTCHours() * 36e2;
      // Hours
      var i = jsdate.getUTCMinutes() * 60;
      // Minutes
      var s = jsdate.getUTCSeconds(); // Seconds
      return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
    },
    g: function() { // 12-Hours; 1..12
      return f.G() % 12 || 12;
    },
    G: function() { // 24-Hours; 0..23
      return jsdate.getHours();
    },
    h: function() { // 12-Hours w/leading 0; 01..12
      return _pad(f.g(), 2);
    },
    H: function() { // 24-Hours w/leading 0; 00..23
      return _pad(f.G(), 2);
    },
    i: function() { // Minutes w/leading 0; 00..59
      return _pad(jsdate.getMinutes(), 2);
    },
    s: function() { // Seconds w/leading 0; 00..59
      return _pad(jsdate.getSeconds(), 2);
    },
    u: function() { // Microseconds; 000000-999000
      return _pad(jsdate.getMilliseconds() * 1000, 6);
    },

    // Timezone
    e: function() { // Timezone identifier; e.g. Atlantic/Azores, ...
      // The following works, but requires inclusion of the very large
      // timezone_abbreviations_list() function.
      /*              return that.date_default_timezone_get();
       */
      throw 'Not supported (see source code of date() for timezone on how to add support)';
    },
    I: function() { // DST observed?; 0 or 1
      // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
      // If they are not equal, then DST is observed.
      var a = new Date(f.Y(), 0);
      // Jan 1
      var c = Date.UTC(f.Y(), 0);
      // Jan 1 UTC
      var b = new Date(f.Y(), 6);
      // Jul 1
      var d = Date.UTC(f.Y(), 6); // Jul 1 UTC
      return ((a - c) !== (b - d)) ? 1 : 0;
    },
    O: function() { // Difference to GMT in hour format; e.g. +0200
      var tzo = jsdate.getTimezoneOffset();
      var a = Math.abs(tzo);
      return (tzo > 0 ? '-' : '+') + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
    },
    P: function() { // Difference to GMT w/colon; e.g. +02:00
      var O = f.O();
      return (O.substr(0, 3) + ':' + O.substr(3, 2));
    },
    T: function() { // Timezone abbreviation; e.g. EST, MDT, ...
      // The following works, but requires inclusion of the very
      // large timezone_abbreviations_list() function.
      /*              var abbr, i, os, _default;
      if (!tal.length) {
        tal = that.timezone_abbreviations_list();
      }
      if (that.php_js && that.php_js.default_timezone) {
        _default = that.php_js.default_timezone;
        for (abbr in tal) {
          for (i = 0; i < tal[abbr].length; i++) {
            if (tal[abbr][i].timezone_id === _default) {
              return abbr.toUpperCase();
            }
          }
        }
      }
      for (abbr in tal) {
        for (i = 0; i < tal[abbr].length; i++) {
          os = -jsdate.getTimezoneOffset() * 60;
          if (tal[abbr][i].offset === os) {
            return abbr.toUpperCase();
          }
        }
      }
      */
      return 'UTC';
    },
    Z: function() { // Timezone offset in seconds (-43200...50400)
      return -jsdate.getTimezoneOffset() * 60;
    },

    // Full Date/Time
    c: function() { // ISO-8601 date.
      return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
    },
    r: function() { // RFC 2822
      return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
    },
    U: function() { // Seconds since UNIX epoch
      return jsdate / 1000 | 0;
    }
  };
  this.date = function(format, timestamp) {
    that = this;
    jsdate = (timestamp === undefined ? new Date() : // Not provided
      (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
        new Date(timestamp * 1000) // UNIX timestamp (auto-convert to int)
      );
    return format.replace(formatChr, formatChrCb);
  };
  return this.date(format, timestamp);
}