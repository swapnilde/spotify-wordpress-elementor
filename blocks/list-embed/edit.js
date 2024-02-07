import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	RadioControl,
	ToggleControl,
	PanelBody,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
const { Component } = wp.element;

import classnames from 'classnames';

import './editor.scss';

import axios from 'axios';
import querystring from 'querystring-es3';

/**
 * The edit function describes the structure of your block in the context of the
 * editor.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {JSX} Element to render.
 */
export default class listEmbedEdit extends Component {
	componentDidMount() {
		const { attributes, setAttributes, clientId } = this.props;
		const { blockID, episodesArray } = attributes;

		if ( ! blockID ) {
			setAttributes( { blockID: `list-embed-${ clientId }` } );
		}

		if ( 0 === episodesArray.length ) {
			this.initEpisodes();
		}

		const axiosTokenInstance = axios.create( {
			baseURL: 'https://accounts.spotify.com',
			headers: {
				'Content-Type': 'application/json',
			},
		} );

		const axiosSpotifyInstance = axios.create( {
			baseURL: 'https://api.spotify.com/v1/',
			headers: {
				'Content-Type': 'application/json',
			},
		} );

		axiosSpotifyInstance.interceptors.request.use(
			( config ) => {
				axiosTokenInstance
					.post(
						'/api/token',
						querystring.stringify( {
							grant_type: 'client_credentials',
							client_id:
								PulseShareAdminVars.pulseshare_options.client_id,
							client_secret:
								PulseShareAdminVars.pulseshare_options.client_secret,
						} ),
						{
							headers: {
								'Content-Type':
									'application/x-www-form-urlencoded',
							},
						}
					)
					.then( ( response ) => {
						config.headers.Authorization = `Bearer ${ response.data.access_token }`;
					} );

				return config;
			},
			( error ) => {
				return Promise.reject( error );
			}
		);

		axiosSpotifyInstance.interceptors.response.use(
			( response ) => {
				return response;
			},
			async ( error ) => {
				const originalRequest = error.config;
				if (
					error.response.status === 401 &&
					! originalRequest._retry
				) {
					originalRequest._retry = true;
					try {
						const response = await axiosTokenInstance.post(
							'/api/token',
							querystring.stringify( {
								grant_type: 'client_credentials',
								client_id:
									PulseShareAdminVars.pulseshare_options.client_id,
								client_secret:
									PulseShareAdminVars.pulseshare_options
										.client_secret,
							} ),
							{
								headers: {
									'Content-Type':
										'application/x-www-form-urlencoded',
								},
							}
						);
						axiosSpotifyInstance.defaults.headers.common.Authorization = `Bearer ${ response.data.access_token }`;
						return axiosSpotifyInstance( originalRequest );
					} catch ( _error ) {
						if ( _error.response && _error.response.data ) {
							return Promise.reject( _error.response.data );
						}
						return Promise.reject( _error );
					}
				}

				if ( error.response.status === 403 && error.response.data ) {
					return Promise.reject( error.response.data );
				}
				return Promise.reject( error );
			}
		);

		axiosSpotifyInstance
			.get(
				`shows/${ PulseShareAdminVars.pulseshare_options.show_id }/episodes?market=US&limit=50`
			)
			.then( ( response ) => {
				const { data } = response;
				const { items } = data;
				const episodes = items.map( ( item ) => {
					return {
						id: item.id,
						name: item.name,
						description: item.description,
						html_description: item.html_description,
						release_date: item.release_date,
						images: item.images,
						external_url: item.external_urls.spotify,
						uri: item.uri,
						type: item.type,
					};
				} );
				setAttributes( {
					episodesArray: episodes,
				} );
			} )
			.catch( ( error ) => {
				console.log( error.toJSON() );
			} );
	}

	initEpisodes() {
		const { setAttributes } = this.props;
		setAttributes( {
			episodesArray: [],
		} );
	}

	render() {
		const { attributes, setAttributes, className } = this.props;
		const {
			blockID,
			episodesArray,
			displayType,
			currentEpisode,
			isVideo,
			height,
			width,
		} = attributes;

		const classes = classnames( className, 'list-embed' );

		const video = isVideo ? 'video' : '';

		return (
			<>
				<InspectorControls>
					<div className="sfwe-block-sidebar">
						<PanelBody
							title={ __( 'Settings', 'pulseshare' ) }
							initialOpen={ true }
						>
							<RadioControl
								label={ __( 'Display Type', 'pulseshare' ) }
								help="Select the display type for the episode."
								selected={ displayType ? displayType : 'full' }
								options={ [
									{ label: 'Full Show', value: 'full' },
									{
										label: 'Single Episode',
										value: 'single',
									},
								] }
								onChange={ ( type ) => {
									setAttributes( { displayType: type } );
								} }
							/>

							{ displayType === 'single' && (
								<SelectControl
									__nextHasNoMarginBottom
									label={ __(
										'Select Episode',
										'pulseshare'
									) }
									help="Selected episode will be displayed in the frontend."
									value={
										currentEpisode
											? currentEpisode.id
											: episodesArray[ 0 ].id
									}
									options={ episodesArray.map(
										( episode ) => {
											return {
												label: episode.name,
												value: episode.id,
											};
										}
									) }
									onChange={ ( id ) => {
										setAttributes( {
											currentEpisode: episodesArray.find(
												( episode ) => episode.id === id
											),
										} );
									} }
								/>
							) }

							{ displayType === 'single' && (
								<ToggleControl
									__nextHasNoMarginBottom
									checked={ isVideo ? isVideo : false }
									help={ __(
										'Enable this option if this episode is a video.',
										'pulseshare'
									) }
									label={ __(
										'Is this a video episode?',
										'pulseshare'
									) }
									onChange={ ( state ) => {
										setAttributes( { isVideo: state } );
									} }
								/>
							) }

							<UnitControl
								__next40pxDefaultSize
								label="Height"
								onChange={ ( value ) => {
									setAttributes( { height: value } );
								} }
								units={ [
									{
										a11yLabel: 'Pixels (px)',
										label: 'px',
										step: 1,
										value: 'px',
									},
									{
										a11yLabel: 'Percent (%)',
										label: '%',
										step: 1,
										value: '%',
									},
								] }
								value={ height }
							/>
							<UnitControl
								__next40pxDefaultSize
								label="Width"
								onChange={ ( value ) => {
									setAttributes( { width: value } );
								} }
								units={ [
									{
										a11yLabel: 'Pixels (px)',
										label: 'px',
										step: 1,
										value: 'px',
									},
									{
										a11yLabel: 'Percent (%)',
										label: '%',
										step: 1,
										value: '%',
									},
								] }
								value={ width }
							/>
						</PanelBody>
					</div>
				</InspectorControls>

				<div className={ classes } id={ blockID }>
					<div className="container">
						<div className={ 'sfwe-episode' }>
							{ displayType === 'single' &&
								! currentEpisode.id && (
									<div className="notice notice-info alt">
										<p>
											<i>
												{ __(
													'Please select an episode from the block settings.',
													'pulseshare'
												) }
											</i>
										</p>
									</div>
								) }

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
										currentEpisode.id +
										'/' +
										video
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
			</>
		);
	}
}
