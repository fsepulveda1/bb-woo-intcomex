<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Query;
use WP_Term;

class IceCatImagesImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 25;

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
                $brand = $product->get_attribute('pa_marca');
                $mpn = $product->get_meta('_mpn');
                if($brand && $mpn) {
                    $xml = $this->iceCatAPI->getArticleByMPN($brand, $mpn);
                    if($this->iceCatAPI->isValidProduct($xml)) {
                        $data = $this->iceCatAPI->xml2array($xml);
                        $this->iceCatAPI->getProductData($data);
                        $newTitle = $this->iceCatAPI->getProductName();
                        $productDataAttrs = $this->iceCatAPI->getProductDataAttributes();
                        $productDataDesc = $this->iceCatAPI->getProductDescriptions();
                        $productImages = $this->iceCatAPI->getProductImages();

                        $product->set_name($newTitle);
                        $product->update_meta_data('bwi_has_icecat_title',true);

                        if(count($product->get_gallery_image_ids()) >= 1) {
                            $errors[] = 'Se mantienen imágenes de intcomex para el producto '.$product->get_permalink();
                            continue;
                        }

                        if(is_array($productImages) and isset($productImages['HighPic'])) {
                            SyncHelper::setProductImages($productImages['HighPic'],$product,true);
                        }

                        $product->save();
                    }
                    else {
                        $errors[] = $xml->Product->attributes()->ErrorMessage. " (".$brand.",".$mpn.")";
                    }
                }
                else {
                    $errors[] = 'El producto no tiene marca o mpn asociado (mpn: '.$mpn.', marca: '.$brand.')';
                }
                $processed++;
            }
        }

        return [$processed, $errors];
    }
}
