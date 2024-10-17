<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Query;
use WP_Term;

class RegenerateThumbnailsImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 20;

    public function count():int
    {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_thumbnail_id',
                    'compare' => 'EXISTS',
                ],
            ],
        );

        $query = new WP_Query( $args );
        return $query->found_posts;
    }

    public function process($page, array $options): array
    {
        $errors = [];
        $processed = 0;

        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $this->rowsPerPage,
            'offset' => ($page-1)*$this->rowsPerPage,
            'meta_query'     => [
                [
                    'key'     => '_thumbnail_id',
                    'compare' => 'EXISTS', // Solo obtener productos que tengan una imagen destacada
                ],
            ],
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                try {
                    $this->regenerateThumbnail($product_id);
                }
                catch (\Exception $exception) {
                    $errors[] = $exception->getMessage();
                }

                $processed++;
            }
        }

        return [$processed, $errors];
    }

    private function regenerateThumbnail($product_id) {
        $thumbnail_id = get_post_thumbnail_id($product_id);
        if ($thumbnail_id) {
            if (function_exists('wp_update_attachment_metadata')) {
                $metadata = wp_generate_attachment_metadata($thumbnail_id, get_attached_file($thumbnail_id));
                wp_update_attachment_metadata($thumbnail_id, $metadata);
            }
        }
    }
}
