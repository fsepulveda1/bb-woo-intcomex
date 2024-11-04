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
            $productCatID = $category ? self::createCategoryTree($category) : null;
            $brandCatID = $brand ? self::createCategory($brand->BrandId,$brand->Description,'pa_marca') : null;

            if($data->Freight) {
                $freight = $data->Freight;
            }

            if(isset($freight) && ($freightPackage = $freight->Package ?? null)) {
                $product->set_weight(number_format($freightPackage->Weight * 0.4535, '1', '.'));
                $product->set_height(number_format($freightPackage->Height * 2.54, '1', '.'));
                $product->set_width(number_format($freightPackage->Width * 2.54, '1', '.'));
                $product->set_length($freightPackage->Length ?? null);
            }

            if(!$product->get_meta('bwi_has_icecat_title') && !$product->get_meta('_bwi_exclude_title')) {
                $product->set_name($data->Description);
            }
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            $product->set_manage_stock($data->Type == 'Physical');
            if($action == 'create') {
                $product->set_stock_quantity(0);
            }
            $product->set_sold_individually(false);
            $product->set_sku($data->Mpn);
            $product->set_downloadable($data->Type == 'Physical' ? '0': '1');
            $product->set_virtual($data->Type == 'Physical' ? '0': '1');
            $product->set_category_ids([$productCatID]);
            $freightItem = $freight->Item ?? [];
            $product->update_meta_data('_freight_item', json_encode($freightItem));
            $product->update_meta_data('_mpn', $data->Mpn);
            $product->update_meta_data('bwi_intcomex_sku', $data->Sku);
            $product->save();

            if($brandCatID) {
                wp_set_object_terms($product->get_id(), $brandCatID, 'pa_marca', true);
                $attr = array('pa_marca' => array(
                    'name' => 'pa_marca',
                    'value' => $brandCatID,
                    'is_visible' => '1',
                    'is_taxonomy' => '1'
                ));
                update_post_meta($product->get_id(), '_product_attributes', $attr);
            }
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

                if(!$product->meta_exists('bwi_intcomex_attrs') || $forceUpdate) {
                    $values = [];
                    foreach((array) $data as $key => $item) {
                        $header = explode('/',$key);
                        if(count($header) > 1) {
                            $values[$header[0]][$header[1]] = $item;
                        }
                    }
                    $product->update_meta_data('bwi_intcomex_attrs', $values);

                    if(!$product->get_meta('bwi_has_icecat_description')) {
                        $product->set_short_description($data->Descripcion);
                    }

                    $product->save();
                }

                if(!empty($data->Imagenes) && !$product->get_meta('_bwi_exclude_img')) {
                    $mainImg = $data->Imagenes[0];
                    unset($data->Imagenes[0]);

                    if(!$product->get_meta('bwi_has_icecat_image')) {
                        if(!$product->get_image_id() || $forceUpdate) {
                            self::removeProductImages($product);
                            self::setProductImages(urldecode($mainImg->url), $product);
                        }
                    }

                    if(!$product->get_meta('bwi_has_icecat_gallery')) {
                        if (!count($product->get_gallery_image_ids()) || $forceUpdate) {
                            self::removeProductImages($product, 'gallery');
                            foreach ($data->Imagenes as $img) {
                                self::setProductImages(urldecode($img->url), $product, false);
                            }
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

    public static function syncProductIceCat(\WC_Product $product, $data, $forceUpdate = true) {
        if(!empty($data['name']) && !$product->get_meta('_bwi_exclude_title')) {
            $product->set_name($data['name']);
            $product->update_meta_data('bwi_has_icecat_title',true);
        }
        if(!empty($data['description']) && !$product->get_meta('_bwi_exclude_desc')) {
            $description = sprintf('<p>%s</p>',$data['description']);
            if(!empty($data['bullet_points'])) {
                $description .="<ul class='bullet_points'>";
                foreach ($data['bullet_points'] as $bullet_point) {
                    $description .= sprintf('<li>%s</li>', $bullet_point);
                }
                $description .= "</ul>";
            }
            $product->set_description($description);
            $product->update_meta_data('bwi_has_icecat_description',true);

        }
        if(!empty($data['summary_long']) && !$product->get_meta('_bwi_exclude_desc')) {
            $product->set_short_description($data['summary_long']);
        }

        if(!empty(trim($data['image'])) && !$product->get_meta('_bwi_exclude_img')) {
            if(!$product->get_meta('bwi_has_icecat_image') || $forceUpdate) {
                self::removeProductImages($product);
                self::setProductImages($data['image'],$product);
                $product->update_meta_data('bwi_has_icecat_image',true);
            }
        }
        if(!empty($data['gallery']) && !$product->get_meta('_bwi_exclude_img')) {
            if(!$product->get_meta('bwi_has_icecat_gallery') || $forceUpdate) {
                self::removeProductImages($product, 'gallery');
                foreach($data['gallery'] as $image) {
                    self::setProductImages($image->Pic, $product, false);
                }
                $product->update_meta_data('bwi_has_icecat_gallery',true);
            }
        }

        if(!empty($data['features'])) {
            $featuresArray = [];
            foreach($data['features'] as $featureGroup) {
                $group = $featureGroup->FeatureGroup->Name->Value;
                $features = $featureGroup->Features;
                foreach ($features as $feature) {
                    $header = $feature->Feature->Name->Value;
                    $value = $feature->LocalValue;
                    $featuresArray[$group][$header] = $value;
                }
            }

            if(!empty($featuresArray)) {
                $product->update_meta_data('bwi_icecat_features', $featuresArray);
            }
        }
        $product->update_meta_data('bwi_icecat_multimedia', $data['multimedia']);


        $product->save();
    }

    public static function syncProductInventory($intcomexProduct) {
        $response = new ImporterResponse();
        if (!$existentProduct = self::getProductBySKU($intcomexProduct->Sku)) {
            $response->addError('No se encontró un producto con sku '.$intcomexProduct->Sku);
            return $response;
        }

        $product = wc_get_product($existentProduct->ID);

        if($product->is_virtual()) {
            $product->set_stock_status();
            $product->set_manage_stock(false);
        }
        else {
            $stock = $intcomexProduct->InStock;
            $product->set_stock_quantity($stock);
            if ($stock > 0) {
                $product->set_stock_status();
            } else {
                $product->set_stock_status('outofstock');
            }
        }
        $product->save();
        return $response;
    }

    public static function syncProductPrice($intcomexProduct, $USD2CLP, $profitMargin, $paymentMethodMargin) {
        $response = new ImporterResponse();
        if ($existentProduct = self::getProductBySKU($intcomexProduct->Sku)) {
            $intcomexCurrency = $intcomexProduct->Price->CurrencyId ?? null;
            $intcomexPrice = $intcomexProduct->Price->UnitPrice;
            if(strtolower($intcomexCurrency) == 'us') {
                $CLPPrice = ceil($intcomexPrice * $USD2CLP);
            }
            else {
                $CLPPrice = $intcomexPrice;
            }

            $CLPFee = ceil($CLPPrice * ($profitMargin / 100));
            $CLPPreFinalPrice = $CLPPrice + $CLPFee;
            $CLPPaymentFee = ceil($CLPPreFinalPrice * ($paymentMethodMargin / 100));
            $CLPFinalPrice = $CLPPreFinalPrice + $CLPPaymentFee;

            $product = wc_get_product($existentProduct->ID);
            $product->set_price($CLPFinalPrice);
            $product->set_regular_price($CLPFinalPrice);

            $product->update_meta_data('_intcomex_price_origin', $intcomexPrice ?? null);
            $product->update_meta_data('_intcomex_price_clp', $CLPPrice);
            $product->update_meta_data('_intcomex_price_cur', $intcomexCurrency ?? null);
            $product->update_meta_data('_intcomex_fee_clp', $CLPFee);
            $product->update_meta_data('_intcomex_payment_fee_clp', $CLPPaymentFee);


            $product->save();
        }
        else {
            $response->addError('No se encontró un producto con sku '.$intcomexProduct->Sku);
        }
        return $response;
    }

    public static function getTermByExternalId($taxonomy,$intcomexId, $parent) {
        $term_query = new WP_Term_Query(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'parent' => $parent,
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
                array('key' => 'bwi_intcomex_sku',
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

    public static function createCategoryTree($category, $parent = 0) {
        $catID = self::createCategory($category->CategoryId, $category->Description,'product_cat', $parent);
        if(count($category->Subcategories)) {
            foreach ($category->Subcategories as $item) {
                $catID = self::createCategory($item->CategoryId, $item->Description,'product_cat', $catID);
            }
        }
        return $catID;
    }

    public static function createCategory($id,$description, $taxonomy = 'product_cat', $parent = 0)
    {
        $productCat = self::getTermByExternalId($taxonomy, $id, $parent);
        if (!$productCat) {
            if($terms = get_terms([ 'name' => $description, 'taxonomy' => $taxonomy, 'parent' => $parent,'hide_empty' => false])) {
                if($terms instanceof \WP_Error) {
                    throw new \Exception($terms->get_error_message());
                }
                return $terms[0]->term_id;
            }

            $newCat = wp_insert_term($description, $taxonomy, ['parent' => $parent]);
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

        if( $file['tmp_name'] instanceof \WP_Error) {
            return null;
        }

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
                wp_update_attachment_metadata($file_id, wp_generate_attachment_metadata($file_id, get_attached_file($file_id)));
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