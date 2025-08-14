<?php
/**
 * This file defines the contract for all data exporters.
 *
 * @author   AyeCode
 * @category Contracts
 * @package  GeoDirectory\ImportExport\Contracts
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Contracts;

/**
 * ExporterInterface Interface
 *
 * Any class that exports data (e.g., Posts, Categories, SEO data) must
 * implement this interface. This ensures that the core export logic can
 * interact with any exporter in a consistent way, making the system
 * modular and easily extendable by addons.
 */
interface ExporterInterface
{
	/**
	 * Provides the unique string identifier for this exporter.
	 *
	 * This is used to register and retrieve the correct exporter.
	 * For example: 'posts', 'categories', or 'addon-locations'.
	 *
	 * @return string The unique identifier.
	 */
	public function getIdentifier(): string;

	/**
	 * Provides the column headers for the export file (e.g., a CSV).
	 *
	 * This array of strings will be used as the first row in the export file.
	 *
	 * @return array A list of column header strings.
	 */
	public function getColumns(): array;

	/**
	 * Calculates and returns the total number of items to be exported.
	 *
	 * This is primarily used for the UI to display progress.
	 *
	 * @return int The total number of items.
	 */
	public function getTotalCount(): int;

	/**
	 * Fetches a paginated chunk of data for the export.
	 *
	 * This method contains the specific query logic to retrieve the data
	 * for this exporter's data type. It should return an array of arrays,
	 * where each inner array represents a row in the export file.
	 *
	 * @param int $limit The number of items to retrieve in this chunk.
	 * @param int $offset The starting position from which to retrieve items.
	 *
	 * @return array The chunk of data rows.
	 */
	public function getData(int $limit, int $offset): array;
}
