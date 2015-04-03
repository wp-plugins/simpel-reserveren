angular.module('sr.services', [])
.factory('srService', function() {
    return {
        getUrlParameter: function(sParam) {
	        var sPageURL = window.location.search.substring(1);
	        var sURLVariables = sPageURL.split('&');
	        for (var i = 0; i < sURLVariables.length; i++) 
	        {
	            var sParameterName = sURLVariables[i].split('=');
	            if (sParameterName[0] == sParam) 
	            {
	                return sParameterName[1];
	            }
	        }
	    },

	    formatMoney: function(amount) {
	        var n = parseFloat(amount),
	            decPlaces = 2,
	            decSeparator = ',',
	            thouSeparator = '.',
	            sign = n < 0 ? "-" : "",
	            i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
	            j = (j = i.length) > 3 ? j % 3 : 0;
	        return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
	    },

	    unformatMoney: function(amount) {
	    	if(amount === undefined) return;
	        amount = amount.toString();
	        amount = amount.replace('.', '');
	        amount = amount.replace(',', '.');
	        return parseFloat(amount);
	    }
    };
});