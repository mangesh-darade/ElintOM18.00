<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Legacy URLs — entity tag admin lives under cms_admin/entity_tags.
 */
class Entity_mapping extends MY_Controller
{
    public function index()
    {
        redirect('cms_admin/entity_tags');
    }

    public function add()
    {
        redirect('cms_admin/entity_tags/add');
    }

    public function edit($entity_master_id = null, $entity_id = null)
    {
        redirect('cms_admin/entity_tags/edit/' . (int) $entity_master_id . '/' . (int) $entity_id);
    }

    public function delete($entity_master_id = null, $entity_id = null)
    {
        redirect('cms_admin/entity_tags/delete/' . (int) $entity_master_id . '/' . (int) $entity_id);
    }

    public function get_entities_by_type()
    {
        redirect('cms_admin/entity_tags/entities_by_type?' . http_build_query(array(
            'entity_master_id' => (int) $this->input->get('entity_master_id'),
        )));
    }
}
