<?php class TodaysOffer_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->created_by      = $this->session->userdata('user_id');
       
    }

    ////////////////////////////////   getProductByCategories //////////////////////////////////////////
    public function getProductByCategories($categoryID, $subcategory_id = null) {
        $data = array();
        $query = "
            SELECT 
                products.*,
                 categories.name AS categoryName
            FROM 
                sma_products AS products
                 JOIN 
                sma_categories AS categories ON categories.id = products.category_id
            WHERE 
                products.category_id = " . (int)$categoryID;

        // Only add subcategory condition if it's provided
        if (!is_null($subcategory_id)) {
            $query .= " AND products.subcategory_id = " . (int)$subcategory_id;
        }

        $query .= " ORDER BY products.name ASC";

        $q = $this->db->query($query);

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return FALSE;
    }

public function getProcurmentOrders($status, $order_dispatch = null, $specific_date = null) {
    $this->db->select('
        sma_today_special_items.product_id, 
        sma_today_special_items.id, 
        sma_today_special_items.category_id, 
        sma_today_special_items.title, 
        IFNULL(sma_today_special_items.price, 0) AS price, 
        sma_today_special_items.date, 
        sma_products.name, 
        sma_categories.name as category_name
    ');
    $this->db->from('sma_today_special_items');
    $this->db->join('sma_products', 'sma_today_special_items.product_id = sma_products.id', 'left');
    $this->db->join('sma_categories', 'sma_categories.id = sma_today_special_items.category_id', 'left');

    $today = date('Y-m-d'); // Current date

    if ($status === 'add_items') {
        // For "add_items" only show non-deleted items for today
        $this->db->where('deleted', 0);
        $this->db->where('DATE(sma_today_special_items.date)', $today);

    } elseif ($status === 'previous_order') {
        // For "previous_order" show all items (deleted or not) until today
        $this->db->where('DATE(sma_today_special_items.date) <=', $today);
        $this->db->group_by('sma_today_special_items.id');
        $this->db->or_where('DATE(sma_today_special_items.date) >', $today);
        $this->db->order_by('sma_today_special_items.date DESC');

    } elseif ($status === 'date_wise_item') {
        // For "date_wise_item" show all items (deleted or not)
        $this->db->order_by('sma_today_special_items.date', 'DESC');
    }

    // // Optional filter by order_dispatch flag if passed
    // if (!empty($order_dispatch)) {
    //     $this->db->where('sma_today_special_items.order_dispatch', $order_dispatch);
    // }

    $this->db->order_by('sma_today_special_items.id', 'DESC');

    $q = $this->db->get();

    if ($q->num_rows() > 0) {
        return $q->result();
    }

    return FALSE;
}


public function getProductDetailsByName($name) {
        $q = $this->db->select('id, name')->where('name', $name)->get('sma_products');
        return ($q->num_rows() > 0) ? $q->row() : false;
    }



public function PlaceProcurementOrder($Items) {
    $duplicates = [];
    $updated = [];
    $newItems = [];
    $currentRequestProducts = [];

    foreach ($Items as $item) {
        $productKey = $item['product_id'].'|'.$item['title'].'|'.$item['date'];
        
        if (in_array($productKey, $currentRequestProducts)) {
            $duplicates[] = $this->getProductNameById($item['product_id']);
            continue;
        }
        $currentRequestProducts[] = $productKey;

        $existing = $this->db->where([
                'product_id' => $item['product_id'],
                'title'      => $item['title'],
                'date'       => $item['date']
            ])
            ->get('sma_today_special_items')
            ->row();

        if ($existing) {
            if ($existing->category_id != $item['category_id'] || $existing->price != $item['price']) {
                $this->db->where('id', $existing->id)
                         ->update('sma_today_special_items', [
                             'category_id' => $item['category_id'],
                             'price'       => $item['price']
                         ]);
                $updated[] = $this->getProductNameById($item['product_id']);
            }
        } else {
            $newItems[] = $item;
        }
    }

    if (!empty($newItems)) {
        $this->db->insert_batch('sma_today_special_items', $newItems);
        $addedCount = count($newItems);
    } else {
        $addedCount = 0;
    }

    $response = [
        'status'     => 'success',
        'added'      => $addedCount,
        'updated'    => count($updated),
        'duplicates' => array_unique($duplicates),
        'message'    => ''
    ];

    if ($addedCount > 0) {
        $response['message'] .= "$addedCount product(s) added successfully.";
    }
    if (count($updated) > 0) {
        $response['message'] .= " " . count($updated) . " product(s) updated successfully.";
    }
    if (!empty($duplicates)) {
        $duplicateList = implode(', ', $response['duplicates']);
        $countDuplicates = count($response['duplicates']);
        $response['message'] .= " $countDuplicates duplicate(s) in current request ($duplicateList).";
    }

    return $response;
}

public function cleanupDeletedItems() {
    $this->db->where('deleted', 1);
    return $this->db->delete('sma_today_special_items');
}

public function getProductNameById($product_id) {
    $q = $this->db->select('name')->where('id', $product_id)->get('sma_products');
    return ($q->num_rows() > 0) ? $q->row()->name : 'Unknown Product';
}

    public function getUnitById($id) {
        $q = $this->db->get_where("units", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getWarehouseByID($id) {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data;
        }
      return FALSE;
    }
    public function getProductCategoriesList() {

        $query = $this->db->query("SELECT parent.id AS parent_id, parent.name AS parent_name, sub.id AS subcategory_id, sub.name AS subcategory_name 
        FROM sma_categories AS parent 
        LEFT JOIN sma_categories AS sub ON parent.id = sub.parent_id");

            if ($query->num_rows() > 0) {
            $categories = array();
            foreach ($query->result_array() as $row) {
            $parent_id = $row['parent_id'];
            $subcategory_id = $row['subcategory_id'];

            if (!isset($categories[$parent_id])) {
            $categories[$parent_id] = array(
            'category_id' => $parent_id,
            'category_name' => $row['parent_name'],
            'subcategories' => array()
            );
            }
            if ($subcategory_id !== null) {
            $categories[$parent_id]['subcategories'][] = array(
            'subcategory_id' => $subcategory_id,
            'subcategory_name' => $row['subcategory_name']
            );
            }
            }

            // Sort categories and subcategories alphabetically
            foreach ($categories as &$category) {
            usort($category['subcategories'], function($a, $b) {
            return strcmp($a['subcategory_name'], $b['subcategory_name']);
            });
            }
            unset($category); // unset reference

            // Sort categories by category_name
            usort($categories, function($a, $b) {
            return strcmp($a['category_name'], $b['category_name']);
            });

            return $categories;
            } else {
            return FALSE;
            }

        // echo json_encode($categories);


    }

    public function getCategoryByName($name) {
    if (!$name) {
        return false;
    }

    $this->db->select('id, name');
    $this->db->from('sma_categories');
    $this->db->where('name', $name);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->row();
    } else {
        return false;
    }
}

public function checkDuplicateProduct() {
    $this->load->library('form_validation');
    $this->form_validation->set_rules('product_id', 'Product ID', 'required|numeric');
    $this->form_validation->set_rules('title', 'Title', 'required');
    $this->form_validation->set_rules('requested_delivery_date', 'Requested Delivery Date', 'required');

    if (!$this->form_validation->run()) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => validation_errors()
            ]));
        return;
    }

    $product_id = $this->input->post('product_id');
    $title = $this->input->post('title');
    $requested_delivery_date = $this->input->post('requested_delivery_date');

    // Call model to check duplicate
    $exists = $this->Todaysoffer_model->checkDuplicate(
        $product_id,
        null, // We don’t check category_id here to keep it flexible
        $title,
        $requested_delivery_date
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'exists' => $exists
        ]));
}

public function checkDuplicate($product_id, $category_id, $title, $requested_delivery_date)
{
    $this->db->where('product_id', $product_id);
    if (!empty($category_id)) {
        $this->db->where('category_id', $category_id);
    }
    $this->db->where('title', $title);
    $this->db->where('date', $requested_delivery_date);

    $query = $this->db->get('sma_today_special_items');

    return $query->num_rows() > 0; 
}

public function checkTitleForDate($requested_delivery_date) {
    $this->db->where('date', $requested_delivery_date);
    $query = $this->db->get('sma_today_special_items');

    return $query->num_rows() > 0;
}
public function getTitleForDate($requested_delivery_date) {
    $this->db->select('title');
    $this->db->where('date', $requested_delivery_date);
    $this->db->limit(1);
    $query = $this->db->get('sma_today_special_items');

    if ($query->num_rows() > 0) {
        return $query->row()->title;
    }
    return false;
}

}
?>
