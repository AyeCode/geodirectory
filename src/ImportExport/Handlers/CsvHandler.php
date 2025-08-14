<?php
/**
 * This file contains the handler for CSV file operations.
 *
 * @author   AyeCode
 * @category Handlers
 * @package  GeoDirectory\ImportExport\Handlers
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Handlers;

/**
 * CsvHandler Class
 *
 * Manages all low-level operations for reading from and writing to CSV files,
 * ensuring that data is parsed and formatted correctly.
 */
class CsvHandler
{
	/**
	 * The WordPress Filesystem instance.
	 * @var \WP_Filesystem_Base
	 */
	private $filesystem;

	/**
	 * CsvHandler constructor.
	 *
	 * @param \WP_Filesystem_Base $filesystem An instance of the WP_Filesystem.
	 */
	public function __construct(\WP_Filesystem_Base $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	/**
	 * Reads specific rows from a CSV file.
	 *
	 * It uses the first row of the CSV as keys for all subsequent rows,
	 * returning an associative array for each row.
	 *
	 * @param string $filepath The absolute path to the CSV file.
	 * @param int $offset The row to start reading from (after the header).
	 * @param int $limit The number of rows to read.
	 *
	 * @return array A list of associative arrays representing the CSV rows.
	 */
	public function getRows(string $filepath, int $offset = 0, int $limit = 0): array
	{
		$rows = [];
		$headers = [];
		$currentRow = 0;

		// Set locale to handle special characters correctly.
		$lc_all = setlocale( LC_ALL, 0 );
		setlocale( LC_ALL, 'en_US.UTF-8' );

		if (($handle = fopen($filepath, "r")) !== false) {
			while (($data = fgetcsv($handle, 0, ",")) !== false) {
				// First row is the header.
				if ($currentRow === 0) {
					$headers = $data;
					$currentRow++;
					continue;
				}

				// Skip rows until we reach the desired offset.
				if ($currentRow <= $offset) {
					$currentRow++;
					continue;
				}

				// If a limit is set, stop when we've read enough rows.
				if ($limit > 0 && count($rows) >= $limit) {
					break;
				}

				// Combine header with row data, handling mismatched column counts.
				$headerCount = count($headers);
				$dataCount = count($data);

				if ($headerCount > $dataCount) {
					$data = array_pad($data, $headerCount, '');
				} elseif ($dataCount > $headerCount) {
					$data = array_slice($data, 0, $headerCount);
				}

				$rows[] = array_combine($headers, $data);
				$currentRow++;
			}
			fclose($handle);
		}

		// Restore original locale.
		setlocale(LC_ALL, $lc_all);

		return $rows;
	}

	/**
	 * Writes an array of data to a CSV file.
	 *
	 * @param string $filepath The absolute path to the CSV file.
	 * @param array $data The data to write. Each inner array is a row.
	 * @param bool $overwrite If true, the file will be created or truncated. If false, data will be appended.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function saveRows(string $filepath, array $data, bool $overwrite = true): bool
	{
		if (empty($data)) {
			return false;
		}

		$mode = $overwrite ? 'w+' : 'a+';

		if (($handle = fopen($filepath, $mode)) !== false) {
			foreach ($data as $row) {
				// Sanitize row data to prevent formula injection in spreadsheet software.
				$escaped_row = array_map('geodir_escape_csv_data', $row);
				fputcsv($handle, $escaped_row, ",", '"');
			}
			fclose($handle);
			return true;
		}

		return false;
	}
}
