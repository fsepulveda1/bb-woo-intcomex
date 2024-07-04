<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Query;
use WP_Term;

class IceCatImagesImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 30;

    public function count():int
    {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
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
            'offset' => ($page-1)*$this->rowsPerPage
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                $terms = get_the_terms( $product->get_id() , 'marcas' );
                $brand = $terms ? reset($terms) : null;
                $mpn = $product->get_meta('_mpn');

                if($brand && $mpn) {
                    $xml = $this->iceCatAPI->getArticleByMPN($brand->name, $mpn);
                    $data = $this->iceCatAPI->xml2array($xml);
                    $this->iceCatAPI->getProductData($data);
                    $productDataAttrs = $this->iceCatAPI->getProductDataAttributes();
                    $productDataDesc = $this->iceCatAPI->getProductDescriptions();
                    if(!empty($productDataAttrs['ErrorMessage'])) {
                        $errors[] = $productDataAttrs['ErrorMessage']. " (".$brand->name.",".$mpn.")";
                    }
                    else {
                        //wp_send_json_error(['desc' => $productDataDesc ,'rs' => $productDataAttrs]);
                        //$errors[] = $xml->attributes('ErrorMessage');
                    }

                    $processed++;
                }
                else {
                    $errors[] = 'El producto no tiene marca o mpn asociado';
                }

            }
        }

        return [$processed, $errors];
    }
}
