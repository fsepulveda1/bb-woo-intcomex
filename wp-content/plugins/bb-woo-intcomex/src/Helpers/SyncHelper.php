<?php
namespace Bigbuda\BbWooIntcomex\Helpers;

use WC_Product_Simple;
use WP_Term_Query;

class SyncHelper {
    public static function addProductBase($data): ImporterResponse
    {
        $importerResponse = new ImporterResponse();
        $importerResponse->setData($data);

        try {
            if ($existentProduct = self::getProductBySKU($data->Sku)) {
                $action = 'update';
                $importerResponse->setAction('update');
                $product = wc_get_product($existentProduct->ID);
            } else {
                $action = 'create';
                $product = new WC_Product_Simple();
            }

            $category = $data->Category;
            $brand = $data->Brand;
            $productCat = $category ? self::createCategory($category->CategoryId, $category->Description) : null;
            $brandCat = $brand ? self::createCategory($brand->BrandId,$brand->Description,'pa_marca') : null;

            $freight = $data->Freight;
            if($freightPackage = $freight->Package ?? null) {
                $product->set_weight(number_format($freightPackage->Weight * 0.4535, '1', '.'));
                $product->set_height(number_format($freightPackage->Height * 2.54, '1', '.'));
                $product->set_width(number_format($freightPackage->Width * 2.54, '1', '.'));
                $product->set_length($freightPackage->Length ?? null);
            }

            $product->set_name($data->Description);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_manage_stock(true);
            if($action == 'create') {
                $product->set_stock_quantity(0);
            }
            $product->set_sold_individually(false);
            $product->set_sku($data->Sku);
            $product->set_downloadable(false);
            $product->set_virtual(!$data->Type == 'Physical');
            $product->set_category_ids([$productCat]);

            if($brandCat) {
                wp_set_object_terms($product->get_id(), [(int)$brandCat], 'marcas');
            }

            $freightItem = $freight->Item ?? [];
            $product->update_meta_data('_freight_item', json_encode($freightItem));
            $product->update_meta_data('_mpn', $data->Mpn);

            $product->save();

        }
        catch (\Exception $exception) {
            $importerResponse->addError($exception->getMessage().
                sprintf("(Código Intcomex: %s)",$data->Sku));
        }

        return $importerResponse;
    }

    public static function addExtendedProductInfo($data, $forceUpdate = false): ImporterResponse {
        $importerResponse = new ImporterResponse();
        $importerResponse->setData($data);

        try {
            if ($existentProduct = self::getProductBySKU($data->localSku)) {
                $importerResponse->setAction('update');
                $product = wc_get_product($existentProduct->ID);

                $values = [];
                foreach((array) $data as $key => $item) {
                    $header = explode('/',$key);
                    if(count($header) > 1) {
                        $values[$header[0]][$header[1]] = $item;
                    }
                }

                if(!$product->meta_exists('_intcomex_attrs') || $forceUpdate) {
                    $product->update_meta_data('_intcomex_attrs', $values);
                }

                if(!empty($data->Imagenes)) {
                    $mainImg = $data->Imagenes[0];
                    unset($data->Imagenes[0]);

                    if(!$product->get_image_id() || $forceUpdate) {
                        self::removeProductImages($product);
                        self::setProductImages(urldecode($mainImg->url), $product);
                    }

                    if(!count($product->get_gallery_image_ids()) || $forceUpdate) {
                        self::removeProductImages($product,'gallery');
                        foreach($data->Imagenes as $img) {
                            self::setProductImages(urldecode($img->url), $product, false);
                        }
                    }
                }
            } else {
                $importerResponse->addError('El producto con SKU:'.$data->localSku.' no existe');
            }
        }
        catch (\Exception $exception) {
            $importerResponse->addError($exception->getMessage().
                sprintf("(Código Intcomex: %s)",$data->localSku));
        }

        return $importerResponse;
    }

    public static function syncProductInventory($intcomexProduct) {
        $response = new ImporterResponse();
        if ($existentProduct = self::getProductBySKU($intcomexProduct->Sku)) {
            $stock = $intcomexProduct->InStock;
            $product = wc_get_product($existentProduct->ID);
            $product->set_stock_quantity($stock);
            if($stock > 0) {
                $product->set_stock_status('outofstock');
            }
            else {
                $product->set_stock_status();
            }
            $product->save();
        }
        else {
            $response->addError('No se encontró un producto con sku '.$intcomexProduct->Sku);
        }
        return $response;
    }

    public static function syncProductPrice($intcomexProduct, $USD2CLP, $profitMargin) {
        $response = new ImporterResponse();
        if ($existentProduct = self::getProductBySKU($intcomexProduct->Sku)) {
            $CLPPrice = ceil($intcomexProduct->Price->UnitPrice * $USD2CLP);
            $CLPFee = ceil($CLPPrice * ($profitMargin/100));
            $CLPFinalPrice = $CLPPrice + $CLPFee;

            $product = wc_get_product($existentProduct->ID);
            $product->set_price($CLPFinalPrice);
            $product->set_regular_price($CLPFinalPrice);

            $product->update_meta_data('_intcomex_price_origin', $intcomexProduct->Price->UnitPrice ?? null);
            $product->update_meta_data('_intcomex_price_clp', $CLPPrice);
            $product->update_meta_data('_intcomex_price_cur', $intcomexProduct->Price->CurrencyId ?? null);
            $product->update_meta_data('_intcomex_fee_clp', $CLPFee);


            $product->save();
        }
        else {
            $response->addError('No se encontró un producto con sku '.$intcomexProduct->Sku);
        }
        return $response;
    }
    public static function getTermByExternalId($taxonomy,$intcomexId) {
        $term_query = new WP_Term_Query(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'meta_query' => array(
                array(
                    'key' => '_intcomex_id',
                    'value' => $intcomexId,
                    'compare' => '='
                )
            )
        ));

        $rs = $term_query->get_terms();
        return $rs[0] ?? null;
    }

    public static function getProductBySKU($id) {
        $params = array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'meta_query' => array(
                array('key' => '_sku', //meta key name here
                    'value' => $id,
                    'compare' => '=',
                )
            ),
        );

        $query = new \WP_Query($params);
        if($query->have_posts()) {
            $posts = $query->get_posts();
            return end($posts);
        }

        return false;
    }


    public static function createCategory($id,$description, $taxonomy = 'product_cat')
    {
        $productCat = self::getTermByExternalId($taxonomy, $id);
        if (!$productCat) {
            if($term = get_term_by('name',$description,$taxonomy)) {
                return $term->term_id;
            }

            $newCat = wp_insert_term($description, $taxonomy);
            if (is_array($newCat)) {
                add_term_meta($newCat['term_id'], '_intcomex_id', $id);
                $productCat = get_term($newCat['term_id']);
            }
            if($newCat instanceof \WP_Error) {
                wp_send_json_error($newCat->get_error_messages());
            }
        }

        return $productCat->term_id;
    }

    /**
     * Setea las imágenes del producto a partir de la url de un producto de la BD manager
     * @param $url
     * @param \WC_Product $product
     * @return boolean
     */
    public static function setProductImages($url, \WC_Product $product, $isMain = true) {
        if(empty($url)) {
            return false;
        }
        $pathParts = explode("/",$url);
        $filenameWithExtension = end($pathParts);

        if(!$image_id = self::uploadFile($url,$filenameWithExtension, $product->get_id(), false)) {
            return false;
        }

        if($isMain) {
            $product->set_image_id($image_id);
        }
        else {
            $gallery_images_ids = $product->get_gallery_image_ids();
            $gallery_images_ids[] = $image_id;
            $product->set_gallery_image_ids($gallery_images_ids);
        }

        $product->save();
        return true;
    }



    /**
     * Elimina las imágenes asociadas a un producto.
     * @param $product
     * @return void
     */
    public static function removeProductImages($product, $type = 'featured') {
        if($type == 'featured' || $type == 'all') {
            $featured_image_id = $product->get_image_id();
            if (!empty($featured_image_id)) {
                wp_delete_post($featured_image_id);
            }
        }

        if($type == 'gallery' || $type == 'all') {
            $image_galleries_id = $product->get_gallery_image_ids();
            if (!empty($image_galleries_id)) {
                foreach ($image_galleries_id as $single_image_id) {
                    wp_delete_post($single_image_id);
                }
            }
        }
    }

    /**
     * Sube una imagen a partir de una URL y la relaciona con un POST a partir del id
     * @param $url
     * @param $filename
     * @param $post_id
     * @return int|\WP_Error|null
     */
    public static function uploadFile($url,$filename, $post_id, $update_exists) {

        $file = [];
        $file['name'] = $filename;
        $file['tmp_name'] = download_url($url);
        $file_id = null;

        if($update_exists && ($file_id = self::mediaFileAlreadyExists($file['name']))) {
            wp_delete_post($file_id);
        }

        if (is_wp_error($file['tmp_name'])) {
            @unlink($file['tmp_name']);
        } else {
            $attachmentId = media_handle_sideload($file, $post_id);

            if ( is_wp_error($attachmentId) ) {
                @unlink($file['tmp_name']);
            } else {
                $file_id = $attachmentId;
            }

        }

        return $file_id;
    }

    /**
     * @param $filename
     * @return mixed
     */
    public static function mediaFileAlreadyExists($filename){
        global $wpdb;
        $query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%/$filename' LIMIT 1";
        $rs = $wpdb->get_results($query);
        return end($rs)->post_id ?? null;
    }
}