/**
 * The module dependencies.
 */
const path = require('path');
const utils = require('./utils');
const settings = require('./settings');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const { ProvidePlugin } = require('webpack');

/**
 * Setup the env.
 */
const { isProd, isDev } = utils.detectEnv();

/**
 * Babel loader setup
 */
const babelLoader = {
	loader: 'babel-loader',
	options: {
		cacheDirectory: isDev,
		comments: false,
		presets: [
			[
				'env',
				{
					targets: {
						browsers: settings.supportedBrowsers
					}
				}
			],
			'stage-2'
		]
	}
};

/**
 * Setup the plugins for different envs.
 */
const plugins = [
	new ProvidePlugin({
		$: 'jquery',
		jQuery: 'jquery',
		'window.jQuery': 'jquery'
	})
];

if (isProd) {
	plugins.push(new UglifyJSPlugin());
}

/**
 * Export the configuration.
 */
module.exports = {
	/**
	 * The output.
	 */
	output: {
		filename: 'bundle.js'
	},

	/**
	 * Resolve modules that can't be installed with NPM.
	 */
	resolve: {
		modules: ['node_modules'],
		alias: {
			masonry: 'masonry-layout',
			isotope: 'isotope-layout',
			'jquery-ui': 'jquery-ui-dist/jquery-ui.js'
		}
	},

	/**
	 * Setup the transformations.
	 */
	module: {
		rules: [
			// Process JS files through Babel.
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [babelLoader]
			},

			// Expose jQuery to the global scope because some plugins like `isotope`
			// access it directly.
			{
				test: require.resolve('jquery'),
				use: [
					{
						loader: 'expose-loader',
						options: 'jQuery'
					}
				]
			}
		],
		noParse: [/raty-js/]
	},

	/**
	 * Setup the transformations.
	 */
	plugins,

	/**
	 * Setup the development tools.
	 */
	cache: isDev,
	bail: false,
	watch: isDev,
	devtool: isDev ? 'source-map' : false
};
