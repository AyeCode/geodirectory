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
use AyeCode\GeoDirectory\Core\Services\Geolocation;
use AyeCode\GeoDirectory\Core\Services\LocationFormatter;
use AyeCode\GeoDirectory\Core\Utils\Formatter;
use AyeCode\GeoDirectory\Core\Services\Images;
use AyeCode\GeoDirectory\Core\Services\Debug;
use AyeCode\GeoDirectory\Core\Services\BusinessHours;
use AyeCode\GeoDirectory\Core\Services\Templates;
use AyeCode\GeoDirectory\Core\Utils\Helpers;
use AyeCode\GeoDirectory\Core\Services\Media;
use AyeCode\GeoDirectory\Core\Services\Reviews;
use AyeCode\GeoDirectory\Core\Services\Seo;
use AyeCode\GeoDirectory\Core\Services\Statuses;
use AyeCode\GeoDirectory\Core\Services\Tables;
use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Core\Services\Maps;
use AyeCode\GeoDirectory\Core\Services\PostTypes;
use AyeCode\GeoDirectory\Core\Services\Posts;
use AyeCode\GeoDirectory\Core\Utils\Utils;
use AyeCode\GeoDirectory\Core\Services\Taxonomies;
use AyeCode\GeoDirectory\Core\Services\PostSaveService;
use AyeCode\GeoDirectory\Database\Repository\ReviewRepository;
use AyeCode\GeoDirectory\Fields\FieldsService;



/**
 * The main GeoDirectory class.
 *
 * This class acts as a service locator and provides access to various services
 * within the GeoDirectory system. It is designed to lazy-load services and cache them
 * for optimized performance.
 *
 * @property-read \AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface $locations The Locations service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Geolocation $geolocation The Geolocation service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\LocationFormatter $location_formatter The Location Formatter service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Formatter $formatter The Formatter service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Images $images The Images service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Debug $debug The Debug service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\BusinessHours $business_hours The Business Hours service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Templates $templates The Templates service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Helpers $helpers The Helpers service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Reviews $reviews The Reviews service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Seo $seo The SEO service.
 * @property-read \AyeCode\GeoDirectory\Database\Repository\ReviewRepository $reviewRepository The Review Repository.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Tables $tables The Tables service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Settings $settings The Settings service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Maps $maps The Maps service.
 * @property-read \AyeCode\GeoDirectory\Core\Utils\Utils $utils The Utils service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Media $media The Media service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Statuses $statuses The Statuses service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\PostTypes $post_types The Statuses service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Posts $posts The Posts service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\Taxonomies $taxonomies The Taxonomies service.
 * @property-read \AyeCode\GeoDirectory\Core\Services\PostSaveService $postSaveService The Post Save service.
 * @property-read \AyeCode\GeoDirectory\Fields\FieldsService $fields The Fields Service.
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
			case 'geolocation':
				$service_id = Geolocation::class;
				break;
			case 'location_formatter':
				$service_id = LocationFormatter::class;
				break;
			case 'formatter':
				$service_id = Formatter::class;
				break;
			case 'images':
				$service_id = Images::class;
				break;
			case 'debug':
				$service_id = Debug::class;
				break;
			case 'business_hours':
				$service_id = BusinessHours::class;
				break;
			case 'templates':
				$service_id = Templates::class;
				break;
			case 'helpers':
				$service_id = Helpers::class;
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
			case 'post_types':
				$service_id = PostTypes::class;
				break;
			case 'posts':
				$service_id = Posts::class;
				break;
			case 'fields':
				$service_id = FieldsService::class;
				break;
			case 'taxonomies':
				$service_id = Taxonomies::class;
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
