import classnames from 'classnames';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {JSX} Element to render.
 */
export default function albumEmbedSave( props ) {
	const { className, attributes } = props;
	const { blockID, currentTrack, displayType, height, width } = attributes;

	const classes = classnames( className, 'album-embed' );

	return (
		<div className={ classes } id={ blockID }>
			<div className="container">
				<div className={ 'sfwe-episode' }>
					{ displayType === 'single' && currentTrack.id && (
						<iframe
							id={ 'sfwe-track-' + currentTrack.id }
							frameBorder="0"
							allowFullScreen=""
							allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
							loading="lazy"
							width={ width ? width : '100%' }
							height={ height ? height : '200' }
							src={
								'https://open..com/embed/track/' +
								currentTrack.id
							}
						></iframe>
					) }
					{ displayType === 'full' && (
						<iframe
							id={
								'sfwe-album-' +
								PulseShareAdminVars.pulseshare_options.album_id
							}
							frameBorder="0"
							allowFullScreen=""
							allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
							loading="lazy"
							width={ width ? width : '100%' }
							height={ height ? height : '380' }
							src={
								'https://open..com/embed/album/' +
								PulseShareAdminVars.pulseshare_options.album_id
							}
						></iframe>
					) }
				</div>
			</div>
		</div>
	);
}
