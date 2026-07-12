<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/cms_admin/Cms_admin_base.php';

class Prices extends Cms_admin_base
{
    public function __construct()
    {
        parent::__construct();
        $this->load_cms_model('prices');
    }

    public function index()
    {
        $this->data['categories'] = $this->cms_prices_model->get_active_category_tree();
        $this->data['cms_enhancements'] = true;

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => lang('products') . ' Price'),
        );
        $meta = array('page_title' => lang('Products') . ' Price', 'bc' => $bc);
        $this->cms_page_construct('cms_admin/prices/manage', $meta, $this->data);
    }

    public function filter_products()
    {
        $filter = array(
            'category_id' => isset($_GET['category_id']) ? $_GET['category_id'] : null,
            'subcategory_id' => isset($_GET['subcategory_id']) ? $_GET['subcategory_id'] : 0,
        );

        $products = $this->cms_prices_model->get_active_filter_products($filter);
        if (!$products || !count($products)) {
            echo '<div class="cms-empty-cell" style="padding:24px;text-align:center;color:#94a3b8;">No active products found for this selection.</div>';
            return;
        }

        // Keep product rows deterministic and A->Z regardless of DataTable state.
        $products = array_values($products);
        usort($products, function ($a, $b) {
            $an = isset($a['name']) ? (string) $a['name'] : '';
            $bn = isset($b['name']) ? (string) $b['name'] : '';
            return strcasecmp($an, $bn);
        });

        $uploads_base = site_url('assets/mdata/' . $this->Customer_assets . '/uploads/');
        $uploads_rel = 'assets/mdata/' . $this->Customer_assets . '/uploads/';
        $uploads_abs = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $uploads_rel);
        $thumbs_abs = rtrim($uploads_abs, '/\\') . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR;

        $resolve_image_urls = function ($image_name) use ($uploads_base, $uploads_abs, $thumbs_abs) {
            $file = trim((string) $image_name);
            if ($file === '') {
                $file = 'no_image.png';
            }

            $thumb_file_abs = $thumbs_abs . $file;
            $full_file_abs = rtrim($uploads_abs, '/\\') . DIRECTORY_SEPARATOR . $file;

            if (!is_file($thumb_file_abs) && !is_file($full_file_abs)) {
                $file = 'no_image.png';
            }

            return array(
                'thumb' => $uploads_base . 'thumbs/' . $file,
                'full' => $uploads_base . $file,
                'placeholder' => $uploads_base . 'no_image.png',
            );
        };

        $table = '<div class="cms-table-wrap cms-dt-panel cms-prices-dt-panel"><table class="ws-modern-table cms-prices-product-table" id="cmsPricesProductsTable" width="100%">';
        $table .= '<thead><tr>';
        $table .= '<th class="col-img">Image</th>';
        $table .= '<th class="col-product">Product &amp; e-shop name</th>';
        $table .= '<th class="col-mrp">MRP</th>';
        $table .= '<th class="col-eshop-price">E-shop price<br><span class="cms-th-sub">Incl. tax</span></th>';
        $table .= '</tr></thead><tbody>';

        foreach ($products as $product) {
            $prod_uid = (int) $product['id'];
            $product['eshop_name'] = !empty($product['eshop_name']) ? $product['eshop_name'] : $product['name'];
            $base_price = isset($product['price']) ? $product['price'] : 0;
            $product['eshop_price'] = ((bool) $product['eshop_price']) ? $product['eshop_price'] : $base_price;
            $product_image = ($product['image'] == '') ? 'no_image.png' : $product['image'];
            $product['eshop_mrp'] = ((bool) $product['mrp']) ? $product['mrp'] : $product['eshop_price'];
            $image_urls = $resolve_image_urls($product_image);
            $thumb_src = $image_urls['thumb'];
            $full_src = $image_urls['full'];
            $placeholder = $image_urls['placeholder'];
            $erp_name = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
            $eshop_name_val = htmlspecialchars($product['eshop_name'], ENT_QUOTES, 'UTF-8');
            $eshop_name_ph = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');

            $table .= '<tr class="cms-price-product-row prdrow_' . $prod_uid . '" data-product-id="' . $prod_uid . '">';
            $table .= '<td class="col-img">';
            $table .= '<span class="cms-thumb-wrap"><img src="' . htmlspecialchars($thumb_src, ENT_QUOTES, 'UTF-8') . '"';
            $table .= ' data-fallback="' . htmlspecialchars($full_src, ENT_QUOTES, 'UTF-8') . '"';
            $table .= ' data-placeholder="' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '"';
            $table .= ' alt="" class="ws-product-img cms-img-fallback" loading="lazy" /></span></td>';
            $table .= '<td class="col-product">';
            $table .= '<div class="cms-price-product-block">';
            $table .= '<div class="cms-price-product-erp" title="ERP product name">' . $erp_name . '</div>';
            $table .= '<input title="E-commerce display name" type="text" name="eshop_name[' . $prod_uid . ']" id="eshop_name_' . $prod_uid . '"';
            $table .= ' value="' . $eshop_name_val . '" placeholder="' . $eshop_name_ph . '" required="required"';
            $table .= ' class="form-control cms-price-input input_' . $prod_uid . '" />';
            $table .= '</div></td>';
            $table .= '<td class="col-mrp">';
            $table .= '<input type="text" name="eshop_mrp[' . $prod_uid . ']" id="eshop_mrp_' . $prod_uid . '" value="' . round($product['eshop_mrp']) . '"';
            $table .= ' readonly="readonly" class="form-control cms-price-input cms-readonly-field input_' . $prod_uid . '" title="MRP" /></td>';
            $table .= '<td class="col-eshop-price">';
            $table .= '<input type="text" name="eshop_price[' . $prod_uid . ']" id="eshop_price_' . $prod_uid . '" value="' . round($product['eshop_price']) . '"';
            $table .= ' class="form-control cms-price-input input_' . $prod_uid . '" title="E-shop price incl. tax" /></td>';
            $table .= '</tr>';

            if (is_array($product['varants']) && count($product['varants'])) {
                usort($product['varants'], function ($a, $b) {
                    $an = isset($a['variant_name']) ? (string) $a['variant_name'] : '';
                    $bn = isset($b['variant_name']) ? (string) $b['variant_name'] : '';
                    return strcasecmp($an, $bn);
                });
                $variant_count = count($product['varants']);
                $table .= '<tr class="cms-price-variants-row prdrow_' . $prod_uid . '_variants">';
                $table .= '<td colspan="4" class="cms-price-variants-cell">';
                $table .= '<details class="cms-price-variants" open>';
                $table .= '<summary class="cms-price-variants-summary">';
                $table .= '<span class="cms-price-variants-summary-icon"><i class="fa fa-sitemap"></i></span>';
                $table .= '<span class="cms-price-variants-summary-text">' . $variant_count . ' variant' . ($variant_count > 1 ? 's' : '') . '</span>';
                $table .= '<span class="cms-price-variants-chevron" aria-hidden="true"><i class="fa fa-chevron-down"></i></span>';
                $table .= '</summary>';
                $table .= '<div class="cms-price-variant-list">';
                $table .= '<div class="cms-price-variant-row-head" aria-hidden="true">';
                $table .= '<span>Variant</span><span>E-shop name</span><span>MRP</span><span>E-shop price</span>';
                $table .= '</div>';
                foreach ($product['varants'] as $variant) {
                    $variant_id = (int) $variant['variant_id'];
                    $variant_name = $variant['variant_name'];
                    $variant_eshop_name = $variant['variant_eshop_name'] ? $variant['variant_eshop_name'] : $variant_name;
                    $variant_eshop_price = (bool) $variant['variant_eshop_price'] && $variant['variant_eshop_price'] >= $product['eshop_price']
                        ? $variant['variant_eshop_price']
                        : ($product['eshop_price'] + $variant['variant_price']);
                    $variant_eshop_mrp = (bool) $variant['variant_eshop_mrp']
                        ? $variant['variant_eshop_mrp']
                        : ($product['eshop_mrp'] + $variant['variant_price']);
                    $v_name = htmlspecialchars($variant_name, ENT_QUOTES, 'UTF-8');
                    $v_eshop_name = htmlspecialchars($variant_eshop_name, ENT_QUOTES, 'UTF-8');
                    $v_qty = number_format($variant['variant_unit_quantity'], 2);
                    $table .= '<div class="cms-price-variant-item cms-price-variant-item--row">';
                    $table .= '<div class="cms-price-variant-row-full">';
                    $table .= '<div class="cms-price-variant-cell cms-price-variant-cell--meta">';
                    $table .= '<span class="cms-price-variant-name" title="' . $v_name . '">' . $v_name . '</span>';
                    $table .= '<span class="cms-price-variant-qty-badge">Qty ' . $v_qty . '</span>';
                    $table .= '</div>';
                    $table .= '<div class="cms-price-variant-cell cms-price-variant-cell--eshop">';
                    $table .= '<input type="text" name="variant_eshop_name[' . $variant_id . ']" value="' . $v_eshop_name . '"';
                    $table .= ' required="required" placeholder="E-shop name" class="form-control cms-price-input cms-price-input--sm input_' . $prod_uid . '" /></div>';
                    $table .= '<div class="cms-price-variant-cell cms-price-variant-cell--mrp">';
                    $table .= '<input type="text" name="variant_eshop_mrp[' . $variant_id . ']" value="' . round($variant_eshop_mrp) . '"';
                    $table .= ' readonly="readonly" title="MRP" class="form-control cms-price-input cms-price-input--sm cms-readonly-field input_' . $prod_uid . '" /></div>';
                    $table .= '<div class="cms-price-variant-cell cms-price-variant-cell--price">';
                    $table .= '<input type="text" name="variant_eshop_price[' . $variant_id . ']" value="' . round($variant_eshop_price) . '"';
                    $table .= ' title="E-shop price" class="form-control cms-price-input cms-price-input--sm input_' . $prod_uid . '" /></div>';
                    $table .= '</div></div>';
                }
                $table .= '</div></details></td></tr>';
            }
        }

        echo $table . '</tbody></table></div>';
    }

    public function save()
    {
        if ($this->input->post('action') !== 'save_changes') {
            $this->session->set_flashdata('error', lang('Inter server error'));
            return redirect($this->cms_url('prices'));
        }

        $productsData = array();
        $variantData = array();

        $eshop_names = $this->input->post('eshop_name');
        if (is_array($eshop_names) && count($eshop_names)) {
            foreach ($eshop_names as $product_id => $product) {
                $productsData[] = array(
                    'id' => $product_id,
                    'eshop_name' => trim($this->input->post("eshop_name[$product_id]")),
                    'mrp' => trim($this->input->post("eshop_mrp[$product_id]")),
                    'eshop_price' => trim($this->input->post("eshop_price[$product_id]")),
                );
            }
        }

        $variant_names = $this->input->post('variant_eshop_name');
        if (is_array($variant_names) && count($variant_names)) {
            foreach ($variant_names as $variant_id => $variant) {
                $variantData[] = array(
                    'id' => $variant_id,
                    'eshop_name' => trim($this->input->post("variant_eshop_name[$variant_id]")),
                    'eshop_mrp' => trim($this->input->post("variant_eshop_mrp[$variant_id]")),
                    'eshop_price' => trim($this->input->post("variant_eshop_price[$variant_id]")),
                );
            }
        }

        $this->cms_prices_model->save_eshop_prices($productsData, $variantData);

        $this->session->set_flashdata('message', lang('Eshop Products price updated'));
        redirect($this->cms_url('prices'));
    }
}
