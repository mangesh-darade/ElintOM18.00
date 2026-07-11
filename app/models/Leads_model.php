<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Leads_model extends CI_Model {

    // Function to get all leads from the database
    public function get_all_leads() {
        $query = $this->db->get('sma_leads'); // "sma_leads" is your table name
        return $query->result(); // Returns the data as an array of objects
    }
    //Get Leads By Id
    public function getLeadsByID($id)
    {
        $q = $this->db->get_where('sma_leads', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getLeadTypes() {
        $q = $this->db->get('sma_lead_type'); // "sma_leads" is your table name
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               $data[$row->id] = $row;
            }
            return $data;
        }
    }
    public function deleteLeads($id)
    {
        $this->db->where('id', $id);
        $this->db->update('sma_leads', ['is_delete' => '1']);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    public function updateLeads($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('sma_leads', $data)) {             
            return true;
        }
        return false;
    }
    public function addLeads($data = [])
    {
        if ($this->db->insert('sma_leads', $data)) {
            $eid = $this->db->insert_id();             
            return $eid;
        }
        return false;
    }
    ///////////////////////////////// Deals ///////////////////////////////////
    public function getDealsByID($id) {
        $this->db->select('deals.*, leads.full_name as name');  // Select relevant columns from both tables
        $this->db->from('deals');
        $this->db->join('leads', 'leads.id = deals.Leadid', 'left');  // Perform a left join on the 'leads' table
        $this->db->where('deals.Leadid', $id);  // Filter by the provided lead ID
        
        $q = $this->db->get();  // Execute the query
        if ($q->num_rows() > 0) {
            return $q->row();  // Return the result row
        }
        return FALSE;  // Return false if no results are found
        
    }
    public function getDealsNameByID($id) {
        // Join deals with products and categories based on their relationships
        $this->db->select('deals.*,leads.full_name as name, products.name AS product_name, categories.name AS category_name');
        $this->db->from('deals');
        $this->db->join('leads', 'leads.id = deals.Leadid', 'left');  // Assuming 'product_id' is the foreign key in the 'deals' table
        $this->db->join('products', 'products.id = deals.productId', 'left');  // Assuming 'product_id' is the foreign key in the 'deals' table
        $this->db->join('categories', 'categories.id = deals.categoryId', 'left');  // Assuming 'category_id' is the foreign key in the 'deals' table
        $this->db->where('deals.id', $id);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();  // Return the deal along with product and category names
        }
        return FALSE;
    }
    
    
    public function delete_deals($id)
    {
        $this->db->where('id', $id);
        $this->db->update('deals', ['is_delete' => '1']);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
        
    }
    public function updateDeals($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('deals', $data)) {             
            return true;
        }
        return false;
    }
    public function get_all_categories() {
        $query = $this->db->get('sma_categories'); // "sma_leads" is your table name
        return $query->result(); // Returns the data as an array of objects
    }
    public function get_all_products() {
        $query = $this->db->get('products'); // "sma_leads" is your table name
        return $query->result(); // Returns the data as an array of objects
    }
    public function addDeals($data = [])
    {
        if ($this->db->insert('deals', $data)) {
            $eid = $this->db->insert_id();             
            return $eid;
        }
        return false;
    }
    public function getCommentsHistory($id) {
        $this->db->select('leads_history.*, leads.full_name as name,leads.id as leadId');  // Select relevant columns from both tables
        $this->db->from('leads_history');
        $this->db->join('leads', 'leads.id = leads_history.lead_id', 'left');  // Perform a left join on the 'leads' table
        $this->db->where('leads_history.Lead_id', $id);  // Filter by the provided lead ID
        
        $q = $this->db->get();  // Execute the query
        if ($q->num_rows() > 0) {
            return $q->row();  // Return the result row
        }
        return FALSE;  // Return false if no results are found
        
    }
}
