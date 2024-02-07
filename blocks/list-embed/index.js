/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import listEmbedEdit from './edit';
import listEmbedSave from './save';
import metadata from './block.json';
import { listEmbed } from '../../admin/js/block-icons';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata, {
	/**
	 * @see ../../admin/js/block-icons.js
	 */
	icon: {
		src: listEmbed,
		foreground: '#2BA266',
	},
	/**
	 * @see ./edit.js
	 */
	edit: listEmbedEdit,

	/**
	 * @see ./save.js
	 */
	save: listEmbedSave,
} );
