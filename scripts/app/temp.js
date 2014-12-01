jQuery(function($) {

    var viewport = $('body').width();


    $('html').click(function() {
        $('#widgetCalendar').slideUp({duration: 200});
        $('#bookgroup-panel').slideUp({duration: 200});
    });

    $('#widgetCalendar, #start, #end, #bookgroup-panel, #bookgroup').click(function(event){
        event.stopPropagation();
    });

    $.material.init();
    $('#start, #end').on('focus', function(e){
        $('#widgetCalendar').slideDown({duration: 200});
        $('#bookgroup-panel').slideUp({duration: 200});
    });

    $('#bookgroup').on('focus', function(){
        $('#bookgroup-panel').slideDown({duration: 200});
    });
    

    $("input[type=number]").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) || 
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});