<?php

namespace Bigbuda\BbWooIntcomex\Shortcodes;

class ProductDescription {
    public function __construct()
    {
        add_shortcode('intcomex_attributes_table', [$this,'shortcode']);
    }

    public function shortcode() {
        global $product;
        if(!$product) return '';

        $meta_array = $product->get_meta('bwi_icecat_features') ?? $product->get_meta('bwi_intcomex_attrs');

        if (!is_array($meta_array) || empty($meta_array)) {
            return '';
        }

        $output = '<style> 
.bwi_table_responsive { max-width: 100%; overflow-x: auto}
.bwi_attr_table { border: 1px solid #ddd; border-collapse: collapse}
.bwi_attr_table th, .bwi_attr_table td { border-bottom: 1px solid #ddd; padding: .5rem 1rem } 
.bwi_attr_table th { text-align: left }
.bwi_attr_table .header { background-color: #f1f1f1; }
</style>';
        $output .= '<div class="bwi_table_responsive"><table class="bwi_attr_table">';
        $output .= '<tbody>';

        foreach ($meta_array as $section_title => $items) {
            $output .= '<tr><th colspan="2" class="header">'. esc_html($section_title) .'</th></tr>';
            foreach($items as $key => $value) {
                $output .= '<tr>';
                $output .= '<th>' . esc_html($key) . '</th>';
                if(is_array($value)) {
                    $output .= '<td>' . implode(',',$value) . '</td>';
                }
                else {
                    $output .= '<td>' . esc_html($value) . '</td>';
                }
                $output .= '</tr>';
            }
        }

        $output .= '</tbody></table></div>';

        return $output;
    }
}