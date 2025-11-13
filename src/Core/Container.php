<?php
/**
 * GeoDirectory Service Container
 *
 * A simple but powerful Inversion of Control (IoC) container that manages
 * class dependencies and bindings. It's responsible for building objects
 * and injecting their required dependencies automatically.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * The main DI container for the plugin.
 *
 * This class uses PHP's Reflection API to automatically
 * resolve dependencies for the classes it builds.
 *
 * @since 3.0.0
 */
final class Container {
	/**
	 * A cache for storing resolved object instances (singletons).
	 *
	 * @var array
	 */
	private array $instances = [];

	/**
	 * The registered bindings and factories for services.
	 *
	 * @var array
	 */
	private array $bindings = [];

	/**
	 * Binds a service to the container.
	 *
	 * This registers a "blueprint" for how to build a service.
	 * The binding is filterable to allow addons to replace the implementation.
	 *
	 * @param string                $id       The abstract ID (interface or class name).
	 * @param string|callable|null  $concrete The concrete class name or a factory closure.
	 * @return void
	 */
	public function bind( string $id, $concrete = null ): void {
		if ( $concrete === null ) {
			$concrete = $id;
		}

		/**
		 * Filters the factory used to create a service.
		 *
		 * This allows third-party addons to replace the implementation of a core service.
		 *
		 * @param callable|string $concrete The concrete class or factory function.
		 * @param Container       $container The container instance.
		 */
		$this->bindings[ $id ] = apply_filters( "geodirectory/factory/{$id}", $concrete, $this );
	}

	/**
	 * Gets a service from the container, building it if necessary.
	 *
	 * @param string $id The ID of the service to get (class or interface name).
	 * @return object The resolved service instance.
	 * @throws \Exception If the service cannot be resolved.
	 */
	public function get( string $id ) {
		// If we already have an instance, return it immediately.
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		// If there's no blueprint for this service, we can't build it.
		if ( ! isset( $this->bindings[ $id ] ) ) {
			throw new \Exception( "Service '{$id}' is not registered in the container. Check it is added in geodirectory_boot()" );
		}

		$concrete = $this->bindings[ $id ];

		// Build the object and store it for the next request.
		$this->instances[ $id ] = $this->resolve( $concrete );

		return $this->instances[ $id ];
	}

	/**
	 * Checks if a service is registered in the container.
	 *
	 * @param string $id The ID of the service to check.
	 * @return bool True if the service is registered, false otherwise.
	 */
	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] );
	}

	/**
	 * Resolves a concrete implementation from the container.
	 *
	 * This method uses the Reflection API to automatically build a class
	 * and all of its nested dependencies.
	 *
	 * @param string|callable $concrete The class name or factory to resolve.
	 * @return object The resolved instance.
	 * @throws \Exception If a dependency cannot be resolved.
	 */
	private function resolve( $concrete ): object {
		// If the "concrete" is a callable factory, just run it.
		if ( is_callable( $concrete ) ) {
			return $concrete( $this );
		}

		// It's a class name, so we use reflection to build it.
		$reflector = new \ReflectionClass( $concrete );

		if ( ! $reflector->isInstantiable() ) {
			throw new \Exception( "Class '{$concrete}' is not instantiable." );
		}

		$constructor = $reflector->getConstructor();

		// If there's no constructor, we can just create a new instance.
		if ( $constructor === null ) {
			return new $concrete();
		}

		// If there is a constructor, get its parameters (dependencies).
		$parameters   = $constructor->getParameters();
		$dependencies = [];

		foreach ( $parameters as $parameter ) {
			$type = $parameter->getType();

			// Check if the parameter has a class/interface type-hint.
			if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
				// The parameter is a class/interface, so we recursively resolve it from the container.
				$dependencies[] = $this->get( $type->getName() );
			} else {
				// The parameter is a built-in type (string, int) or has no type-hint.
				if ( $parameter->isDefaultValueAvailable() ) {
					$dependencies[] = $parameter->getDefaultValue();
				} else {
					throw new \Exception( "Cannot resolve primitive parameter '{$parameter->getName()}' in class '{$concrete}'" );
				}
			}
		}

		// Create a new instance of the class with all its resolved dependencies.
		return $reflector->newInstanceArgs( $dependencies );
	}
}
