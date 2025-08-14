<?php
/**
 * This file defines the contract for all data importers.
 *
 * @author   AyeCode
 * @category Contracts
 * @package  GeoDirectory\ImportExport\Contracts
 */

namespace AyeCode\GeoDirectory\ImportExport\Contracts;

/**
 * ImporterInterface Interface
 *
 * Any class that imports data from a file must implement this interface.
 */
interface ImporterInterface
{
	/**
	 * Provides the unique string identifier for this importer.
	 */
	public function getIdentifier(): string;

	/**
	 * Validates the structure and content of the uploaded file.
	 */
	public function validateFile(string $filename);

	/**
	 * Processes a single chunk of the import file.
	 *
	 * @param string $filename The name of the file to process.
	 * @param int    $offset   The row number to start processing from.
	 * @param int    $limit    The number of rows to process in this chunk.
	 * @param array  $options  An array of import options, such as 'update_existing'.
	 *
	 * @return array A summary of the chunk processing.
	 */
	public function process(string $filename, int $offset, int $limit, array $options = []): array;
}
