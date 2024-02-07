import classnames from 'classnames';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {JSX} Element to render.
 */
export default function listEmbedSave( props ) {
	const { className, attributes } = props;
	const { blockID, currentEpisode, displayType, height, width } = attributes;

	const classes = classnames( className, 'list-embed' );

	return (
		<div className={ classes } id={ blockID }>
			<div className="container">
				<div className={ 'sfwe-episode' }>
					{ displayType === 'single' && currentEpisode.id && (
						<iframe
							id={ 'sfwe-episode-' + currentEpisode.id }
							frameBorder="0"
							allowFullScreen=""
							allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
							loading="lazy"
							width={ width ? width : '100%' }
							height={ height ? height : '200' }
							src={
								'https://open.spotify.com/embed/episode/' +
								currentEpisode.id
							}
						></iframe>
					) }
					{ displayType === 'full' && (
						<iframe
							id={
								'sfwe-show-' +
								PulseShareAdminVars.pulseshare_options.show_id
							}
							frameBorder="0"
							allowFullScreen=""
							allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
							loading="lazy"
							width={ width ? width : '100%' }
							height={ height ? height : '200' }
							src={
								'https://open.spotify.com/embed/show/' +
								PulseShareAdminVars.pulseshare_options.show_id
							}
						></iframe>
					) }
				</div>
			</div>
		</div>
	);
}
