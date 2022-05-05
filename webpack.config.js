const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
module.exports = {
	...defaultConfig,
	entry: {
		'seen-before': './src/seen-before',
		'search-banter': './src/search-banter'
	},
};
