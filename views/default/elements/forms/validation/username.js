define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	require('elements/forms/validation');
	
	window.Parsley.addValidator('validusername', {
		requirementType: 'string',
		validateString: function (value) {
			return $.ajax({
				url: elgg.security.addToken(elgg.normalize_url('/action/validation/validusername')),
				method: 'POST',
				dataType: 'json',
				data: {
					username: value,
				}
			});
		},
		messages: {
			_: 'validation:error:type:validusername'
		}
	});

	window.Parsley.addValidator('availableusername', {
		requirementType: 'string',
		validateString: function (value) {
			return $.ajax({
				url: elgg.security.addToken(elgg.normalize_url('/action/validation/availableusername')),
				method: 'POST',
				dataType: 'json',
				data: {
					username: value,
				}
			});
		},
		messages: {
			_: 'validation:error:type:availableusername'
		}
	});
});