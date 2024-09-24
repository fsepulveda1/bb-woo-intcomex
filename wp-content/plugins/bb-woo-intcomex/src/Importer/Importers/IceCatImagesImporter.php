<?php

namespace Bigbuda\BbWooIntcomex\Importer\Importers;

use Bigbuda\BbWooIntcomex\Helpers\SyncHelper;
use WP_Query;
use WP_Term;

class IceCatImagesImporter extends BaseImporter implements ImporterInterface  {

    public int $rowsPerPage = 20;

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
                    try {
                        $rs = $this->iceCatAPI->getProductByMpn($brand, $mpn);
                        if($data = $this->iceCatAPI->getDataArray($rs)) {
                            SyncHelper::syncProductIceCat($product,$data);
                            $errors[] = 'Producto Actualizado ('.$product->get_sku().")";
                            $errors[] = $product->get_permalink();
                        }
                    }
                    catch (\Exception $exception) {
                        if($exception->getCode() == 404) {
                            $errors[] = 'Producto no encontrado (SKU: '.$product->get_sku().", Marca: ".$brand.")";
                        }
                        elseif($exception->getCode() == 403) {
                            $errors[] = 'No tienes acceso a este producto (SKU: '.$product->get_sku().", Marca: ".$brand.")";
                        }
                        else {
                            $errors[] = $exception->getMessage();
                        }
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
