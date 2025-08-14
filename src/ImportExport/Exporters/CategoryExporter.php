<?php
/**
 * This file contains the class for exporting Categories.
 *
 * @author   AyeCode
 * @category Exporters
 * @package  GeoDirectory\ImportExport\Exporters
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Exporters;

use AyeCode\GeoDirectory\ImportExport\Contracts\ExporterInterface;

/**
 * CategoryExporter Class
 *
 * Handles the business logic for exporting a post type's categories to a CSV file.
 */
class CategoryExporter implements ExporterInterface
{
	/**
	 * The post type whose categories we are exporting.
	 * @var string
	 */
	private $post_type;

	/**
	 * The taxonomy name derived from the post type.
	 * @var string
	 */
	private $taxonomy;

	/**
	 * CategoryExporter constructor.
	 *
	 * @param string $post_type The GeoDirectory post type (e.g., 'gd_place').
	 */
	public function __construct(string $post_type)
	{
		$this->post_type = $post_type;
		$this->taxonomy = $post_type . 'category';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return 'categories';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumns(): array
	{
		// Category columns are fixed, so we can define them directly.
		return [
			'cat_id', 'cat_name', 'cat_slug', 'cat_posttype', 'cat_parent', 'cat_schema',
			'cat_font_icon', 'cat_color', 'cat_description', 'cat_top_description',
			'cat_bottom_description', 'cat_image', 'cat_icon',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTotalCount(): int
	{
		// This is a global function from the old codebase.
		return (int) geodir_get_terms_count($this->post_type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(int $limit, int $offset): array
	{
		// Prepare arguments for get_terms().
		$args = [
			'taxonomy'   => $this->taxonomy,
			'hide_empty' => false,
			'orderby'    => 'id',
			'number'     => $limit,
			'offset'     => $offset,
		];

		// To ensure we get all terms, we temporarily remove filters,
		// matching the behavior of the original codebase.
		remove_all_filters('get_terms');
		$terms = get_terms($args);

		if (is_wp_error($terms) || empty($terms)) {
			return [];
		}

		$csv_rows = [];
		foreach ($terms as $term) {
			$parent_name = '';
			if ($term->parent > 0 && ($parent_term = get_term($term->parent, $this->taxonomy))) {
				if (!is_wp_error($parent_term) && $parent_term) {
					$parent_name = $parent_term->name;
				}
			}

			$csv_rows[] = [
				'cat_id'                 => $term->term_id,
				'cat_name'               => $term->name,
				'cat_slug'               => $term->slug,
				'cat_posttype'           => $this->post_type,
				'cat_parent'             => $parent_name,
				'cat_schema'             => get_term_meta($term->term_id, 'ct_cat_schema', true),
				'cat_font_icon'          => get_term_meta($term->term_id, 'ct_cat_font_icon', true),
				'cat_color'              => get_term_meta($term->term_id, 'ct_cat_color', true),
				'cat_description'        => $term->description,
				'cat_top_description'    => get_term_meta($term->term_id, 'ct_cat_top_desc', true),
				'cat_bottom_description' => get_term_meta($term->term_id, 'ct_cat_bottom_desc', true),
				'cat_image'              => geodir_get_cat_image($term->term_id, true),
				'cat_icon'               => geodir_get_cat_icon($term->term_id, true),
			];
		}

		return $csv_rows;
	}
}
