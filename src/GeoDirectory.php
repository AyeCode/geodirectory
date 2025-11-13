<?php
/**
 * Main GeoDirectory Application Class
 *
 * This class acts as a public facade for accessing the plugin's core services.
 * It uses the __get magic method to lazy-load services only when they are first requested.
 *
 * @package GeoDirectory
 * @since 3.0.0
 *
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory;

use AyeCode\GeoDirectory\Core\Container;
use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Media;
use AyeCode\GeoDirectory\Core\Reviews;
use AyeCode\GeoDirectory\Core\Seo;
use AyeCode\GeoDirectory\Core\Statuses;
use AyeCode\GeoDirectory\Core\Tables;
use AyeCode\GeoDirectory\Core\Utils\Settings;
use AyeCode\GeoDirectory\Core\Utils\Maps;
use AyeCode\GeoDirectory\Core\Utils\Image;
use AyeCode\GeoDirectory\Core\Utils\Utils;
use AyeCode\GeoDirectory\Database\Repository\ReviewRepository;


/**
 * The main GeoDirectory class.
 *
 * This class acts as a service locator and provides access to various services
 * within the GeoDirectory system. It is designed to lazy-load services and cache them
 * for optimized performance.
 *
 * @property-read \AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface $locations The Locations service.
 * @property-read \AyeCode\GeoDirectory\Core\Reviews $reviews The Reviews service.
 * @property-read \AyeCode\GeoDirectory\Core\Seo $seo The SEO service.
 * @property-read \AyeCode\GeoDirectory\Database\Repository\ReviewRepository $reviewRepository The Review Repository.
 * @property-read \AyeCode\GeoDirectory\Core\Tables $tables The Tables service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Settings $settings The Settings service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Maps $maps The Maps service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Image $image The Image service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Utils $utils The Utils service.
 * @property-read \AyeCode\GeoDirectory\Core\Media $media The Media service.
 * @property-read \AyeCode\GeoDirectory\Core\Statuses $statuses The Statuses service.
 */
final class GeoDirectory {

	/**
	 * A cache for services that have already been loaded.
	 *
	 * @var array
	 */
	private array $services = [];

	/**
	 * The dependency injection container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container The fully configured service container.
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Magic getter to lazy-load services from the container.
	 *
	 * @param string $name The name of the service to load (e.g., 'locations').
	 * @return mixed The requested service instance.
	 * @throws \InvalidArgumentException If the service is not registered.
	 */
	public function __get( string $name ) {
		// Check if we've already loaded this service.
		if ( isset( $this->services[ $name ] ) ) {
			return $this->services[ $name ];
		}

		// Figure out which service class to load from the container.
		// This is the PHP 7.4 compatible version of a `match` expression.
		switch ( $name ) {
			case 'utils':
				$service_id = Utils::class;
				break;
			case 'tables':
				$service_id = Tables::class;
				break;
			case 'settings':
				$service_id = Settings::class;
				break;
			case 'locations':
				$service_id = LocationsInterface::class;
				break;
			case 'reviews':
				$service_id = Reviews::class;
				break;
			case 'media':
				$service_id = Media::class;
				break;
			case 'statuses':
				$service_id = Statuses::class;
				break;
			case 'seo':
				$service_id = Seo::class;
				break;
			case 'reviewRepository':
				$service_id = ReviewRepository::class;
				break;
			case 'maps':
				$service_id = Maps::class;
				break;
			case 'image':
				$service_id = Image::class;
				break;
			default:
				throw new \InvalidArgumentException( "Error: The service '{$name}' is not registered in the main GeoDirectory class." );
		}

		// Get the service from our container and cache it for the next time.
		$this->services[ $name ] = $this->container->get( $service_id );

		return $this->services[ $name ];
	}

	/**
	 * Provides direct access to the service container.
	 *
	 * This is used internally by Service Providers to get services.
	 *
	 * @return Container The service container instance.
	 */
	public function container(): Container {
		return $this->container;
	}
}
