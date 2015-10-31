define(function (require) {

	var elgg = require('elgg');
	var zxcvbn = require('zxcvbn/zxcvbn');
	require('elements/forms/validation');

	window.Parsley.addValidator('minstrength', {
		requirementType: 'string',
		validateString: function (value, requirement) {
			// @todo: add other user inputs
			var result = zxcvbn(value);
			return result.score >= requirement;
		},
		messages: {
			_: 'validation:error:type:minstrength'
		}
	});
	
});